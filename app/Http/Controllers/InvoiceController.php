<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoicePayment;
use App\Models\Notification;
use App\Models\Property;
use App\Models\Tenant;
use App\Models\Type;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\PropertyUnit;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        if (\Auth::user()->can('manage invoice')) {
            $typeFilter = $request->type_filter;
            $query = null;
            
            if (\Auth::user()->type == 'tenant') {
                // For tenants, only show invoices for their unit
                $tenant = \App\Models\Tenant::where('user_id', \Auth::user()->id)->first();
                $query = \App\Models\Invoice::where('unit_id', $tenant->unit)->where('parent_id', parentId());
            } else {
                // For owners, show all invoices for their properties
                $query = \App\Models\Invoice::whereHas('properties', function($q) {
                    $q->where('parent_id', parentId());
                })->where('parent_id', parentId());
            }

            if ($typeFilter) {
                $query = $query->whereHas('types', function($q) use ($typeFilter) {
                    $q->where('invoice_type', $typeFilter);
                });
            }
            if ($request->status) {
                $query->where('status', $request->status);
            }
            if ($request->tenant) {
                $query->whereHas('tenants', function($q) use ($request) {
                    $q->where('user_id', $request->tenant);
                });
            }
            if ($request->property_id) {
                $query->where('property_id', $request->property_id);
            }
            if ($request->unit_id) {
                $query->where('unit_id', $request->unit_id);
            }
            if ($request->invoice_month) {
                $query->whereMonth('invoice_month', date('m', strtotime($request->invoice_month)));
                $query->whereYear('invoice_month', date('Y', strtotime($request->invoice_month)));
            }
            if ($request->end_date) {
                $query->whereDate('end_date', $request->end_date);
            }
            // Exclude rent invoices
            $query = $query->whereDoesntHave('types', function($q) {
                $q->whereHas('types', function($q2) {
                    $q2->where('type', 'rent');
                });
            });
            
            $invoices = $query->get();
            $types = \App\Models\Type::where('parent_id', parentId())->where('type', 'invoice')->get();
            $statusOptions = \App\Models\Invoice::$status;
            
            // Only show relevant properties and units based on user type
            if (\Auth::user()->type == 'tenant') {
                $tenant = \App\Models\Tenant::where('user_id', \Auth::user()->id)->first();
                $properties = \App\Models\Property::where('id', $tenant->property)->get();
                $units = \App\Models\PropertyUnit::where('id', $tenant->unit)->get();
                $tenants = \App\Models\Tenant::where('id', $tenant->id)->with('user')->get();
            } else {
                $properties = \App\Models\Property::where('parent_id', parentId())->get();
                $units = \App\Models\PropertyUnit::where('parent_id', parentId())->get();
                $tenants = \App\Models\Tenant::with('user')->where('parent_id', parentId())->get();
            }
            
            return view('invoice.index', compact('invoices', 'types', 'statusOptions', 'tenants', 'properties', 'units'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }


    public function create()
    {
        if (\Auth::user()->can('create invoice')) {
            $property = Property::where('parent_id', parentId())->get()->pluck('name', 'id');
            $property->prepend(__('Select Property'), '');
            $types = Type::where('parent_id', parentId())->where('type', 'invoice')->get()->pluck('title', 'id');
            $types->prepend(__('Select Type'), '');

            $invoiceNumber = $this->invoiceNumber();
            return view('invoice.create', compact('types', 'property', 'invoiceNumber'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }


    public function store(Request $request)
    {
        if (\Auth::user()->can('create invoice')) {
            $unitIds = $request->unit_ids ?? [];
            if (in_array('all', $unitIds)) {
                $unitIds = \App\Models\PropertyUnit::where('property_id', $request->property_id)->pluck('id')->toArray();
                $request->merge(['unit_ids' => $unitIds]);
            } else {
                // Remove any accidental 'all'
                $unitIds = array_filter($unitIds, function($v) { return $v !== 'all'; });
                $request->merge(['unit_ids' => $unitIds]);
            }
            // If no units found, show error
            if (empty($unitIds)) {
                return redirect()->back()->with('error', 'No units found for the selected property.');
            }
            \Log::info('Invoice store unit IDs', ['unit_ids' => $unitIds, 'property_id' => $request->property_id]);
            $validator = \Validator::make(
                $request->all(),
                [
                    'property_id' => 'required',
                    'unit_ids' => 'required|array|min:1',
                    'unit_ids.*' => 'required|integer',
                    'invoice_month' => 'required',
                    'end_date' => 'required',
                    'types' => 'required|array|min:1',
                    'types.*.invoice_type' => 'required',
                    'types.*.amount' => 'required',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $invoiceNumber = $this->invoiceNumber();
            foreach ($unitIds as $idx => $unitId) {
                $invoice = new \App\Models\Invoice();
                $invoice->invoice_id = $invoiceNumber + $idx;
                $invoice->property_id = $request->property_id;
                $invoice->unit_id = $unitId;
                $invoice->invoice_month = $request->invoice_month . '-01';
                $invoice->end_date = $request->end_date;
                $invoice->notes = $request->notes;
                $invoice->parent_id = parentId();
                $invoice->status = 'open';
                $invoice->save();

                $types = $request->types;
                foreach ($types as $type) {
                    $invoiceItem = new \App\Models\InvoiceItem();
                    $invoiceItem->invoice_id = $invoice->id;
                    $invoiceItem->invoice_type = $type['invoice_type'];
                    $invoiceItem->amount = $type['amount'];
                    $invoiceItem->description = $type['description'];
                    $invoiceItem->save();
                }
            }

            return redirect()->route('invoice.index')->with('success', __('Invoices successfully created for selected units.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }


    public function show(Invoice $invoice)
    {
        if (\Auth::user()->can('show invoice')) {
            $invoiceNumber = $invoice->invoice_id;
            $tenant = Tenant::where('property', $invoice->property_id)->where('unit', $invoice->unit_id)->first();
            $invoicePaymentSettings = invoicePaymentSettings($invoice->parent_id);
            $notification = Notification::where('parent_id', parentId())->where('module', 'payment_reminder')->first();
            return view('invoice.show', compact('invoiceNumber', 'invoice', 'tenant', 'invoicePaymentSettings', 'notification'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }


    public function edit(Invoice $invoice)
    {
        if (\Auth::user()->can('edit invoice')) {
            $property = Property::where('parent_id', parentId())->get()->pluck('name', 'id');
            $property->prepend(__('Select Property'), '');
            $types = Type::where('parent_id', parentId())->where('type', 'invoice')->get()->pluck('title', 'id');
            $types->prepend(__('Select Type'), '');

            $invoiceNumber = $invoice->invoice_id;
            return view('invoice.edit', compact('types', 'property', 'invoiceNumber', 'invoice'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }


    public function update(Request $request, Invoice $invoice)
    {
        if (\Auth::user()->can('edit invoice')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'property_id' => 'required',
                    'unit_id' => 'required',
                    'invoice_month' => 'required',
                    'end_date' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $invoice->property_id = $request->property_id;
            $invoice->unit_id = $request->unit_id;
            $invoice->invoice_month = $request->invoice_month . '-01';
            $invoice->end_date = $request->end_date;
            $invoice->notes = $request->notes;
            $invoice->save();
            $types = $request->types;

            for ($i = 0; $i < count($types); $i++) {
                $invoiceItem = InvoiceItem::find($types[$i]['id']);
                if ($invoiceItem == null) {
                    $invoiceItem = new InvoiceItem();
                    $invoiceItem->invoice_id = $invoice->id;
                }

                $invoiceItem->invoice_type = $types[$i]['invoice_type'];
                $invoiceItem->amount = $types[$i]['amount'];
                $invoiceItem->description = $types[$i]['description'];
                $invoiceItem->save();
            }
            return redirect()->route('invoice.index')->with('success', __('Invoice successfully updated.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }


    public function destroy(Invoice $invoice)
    {
        if (\Auth::user()->can('delete invoice')) {
            InvoiceItem::where('invoice_id', $invoice->id)->delete();
            InvoicePayment::where('invoice_id', $invoice->id)->delete();
            $invoice->delete();
            return redirect()->route('invoice.index')->with('success', __('Invoice successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }

    public function invoiceNumber()
    {
        $latest = Invoice::where('parent_id', parentId())->latest()->first();
        if ($latest == null) {
            return 1;
        } else {
            return $latest->invoice_id + 1;
        }
    }

    public function invoiceTypeDestroy(Request $request)
    {
        if (\Auth::user()->can('delete invoice type')) {
            $invoiceType = InvoiceItem::find($request->id);
            $invoiceType->delete();

            return response()->json([
                'status' => 'success',
                'msg' => __('Property successfully updated.'),
            ]);
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }

    public function invoicePaymentCreate($invoice_id)
    {
        $invoice = Invoice::find($invoice_id);
        return view('invoice.payment', compact('invoice_id', 'invoice'));
    }

    public function invoicePaymentStore(Request $request, $invoice_id)
    {
        if (\Auth::user()->can('create invoice payment')) {
            $invoice = Invoice::find($invoice_id);
            $dueAmount = $invoice->getInvoiceDueAmount();

            $validator = \Validator::make(
                $request->all(), [
                    'payment_date' => 'required',
                    'amount' => 'required|numeric|min:1|max:' . $dueAmount,
                ],
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            if (!empty($request->receipt)) {
                $receiptFilenameWithExt = $request->file('receipt')->getClientOriginalName();
                $receiptFilename = pathinfo($receiptFilenameWithExt, PATHINFO_FILENAME);
                $receiptExtension = $request->file('receipt')->getClientOriginalExtension();
                $receiptFileName = $receiptFilename . '_' . time() . '.' . $receiptExtension;
                $dir = storage_path('upload/receipt');
                if (!file_exists($dir)) {
                    mkdir($dir, 0777, true);
                }
                $request->file('receipt')->storeAs('upload/receipt', $receiptFileName, 'public');
            }

            $payment = new InvoicePayment();
            $payment->invoice_id = $invoice_id;
            $payment->transaction_id = md5(time());
            $payment->payment_type = __('Manually');
            $payment->amount = $request->amount;
            $payment->payment_date = $request->payment_date;
            $payment->receipt = !empty($request->receipt) ? $receiptFileName : '';
            $payment->notes = $request->notes;
            $payment->parent_id = parentId();
            $payment->save();

            // Check if this is a rent invoice
            $isRentInvoice = $invoice->types()->whereHas('types', function($q) {
                $q->where('type', 'rent');
            })->exists();

            if ($isRentInvoice) {
                // Get property owner
                $property = Property::find($invoice->property_id);
                if ($property) {
                    $owner = User::find($property->user_id);
                    if ($owner && !empty($owner->phone_number)) {
                        // Format phone number
                        $phone = preg_replace('/[^0-9+]/', '', $owner->phone_number);
                        if (substr($phone, 0, 1) !== '+') {
                            $phone = '+' . $phone;
                        }

                        $tenant = $invoice->tenants();
                        $unit = PropertyUnit::find($invoice->unit_id);

                        $message = "🔔 *System Notification*\n\n";
                        $message .= "Rent payment received:\n\n";
                        $message .= "📋 *Payment Details:*\n";
                        $message .= "Invoice #: " . invoicePrefix() . $invoice->invoice_id . "\n";
                        $message .= "Property: {$property->name}\n";
                        $message .= "Unit: {$unit->name}\n";
                        $message .= "Tenant: {$tenant->user->name}\n";
                        $message .= "Amount: " . priceFormat($payment->amount) . "\n";
                        $message .= "Payment Date: " . date('d M Y', strtotime($payment->payment_date)) . "\n";
                        $message .= "Payment Method: {$payment->payment_type}\n\n";
                        $message .= "Best regards,\n" . settings()['company_name'];

                        $response = $this->whatsappService->sendMessage($phone, $message);
                        
                        if ($response['status'] === 'error') {
                            \Log::error('Failed to send WhatsApp notification to owner', [
                                'error' => $response['message'],
                                'owner_id' => $owner->id,
                                'phone' => $phone
                            ]);
                        } else {
                            \Log::info('WhatsApp notification sent to owner', [
                                'owner_id' => $owner->id,
                                'phone' => $phone
                            ]);
                        }
                    }
                }
            }

            if (auth()->user()->type == 'tenant') {
                $status = 'pending';
            } else {
                if ($invoice->getInvoiceDueAmount() <= 0) {
                    $status = 'paid';
                } elseif ($invoice->getInvoiceDueAmount() == $invoice->getInvoiceSubTotalAmount()) {
                    $status = 'open';
                } else {
                    $status = 'partial_paid';
                }
            }
            Invoice::statusChange($invoice->id, $status);

            return redirect()->route('invoice.show', $invoice_id)->with('success', __('Invoice payment successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }

    public function invoicePaymentDestroy($invoice_id, $id)
    {
        if (\Auth::user()->can('delete invoice payment')) {
            $payment = InvoicePayment::find($id);
            $payment->delete();

            $invoice = Invoice::find($invoice_id);
            if ($invoice->getInvoiceDueAmount() <= 0) {
                $status = 'paid';
            } elseif ($invoice->getInvoiceDueAmount() == $invoice->getInvoiceSubTotalAmount()) {
                $status = 'open';
            } else {
                $status = 'partial_paid';
            }
            Invoice::statusChange($invoice->id, $status);
            return redirect()->back()->with('success', __('Invoice payment successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }

    public function invoicePaymentRemind($id)
    {
        $notification = Notification::where('parent_id', parentId())->where('module', 'payment_reminder')->first();
        $short_code = $notification->short_code;
        $notification->short_code = json_decode($notification->short_code);

        $Notifications = Notification::$modules;
        $notification_option = [];
        foreach ($Notifications as $key => $value) {
            $notification_option[$key] = $value['name'];
        }
        return view('invoice.remind', compact('notification', 'notification_option', 'Notifications', 'id'));
    }

    public function invoicePaymentRemindData(Request $request, $id)
    {
        $invoice = Invoice::find($id);
        $tenant = Tenant::where('property', $invoice->property_id)->where('unit', $invoice->unit_id)->first();
        $user = User::find($tenant->user_id);

        $notification = Notification::where('parent_id', parentId())->where('module', 'payment_reminder')->first();
        $module = 'payment_reminder';

        $setting = settings();
         $errorMessage = '';
        if (!empty($notification) && $notification->enabled_email == 1) {


            $return['subject'] = $request->subject;
            $return['message'] = $request->message;
            $settings = settings();

            if (!empty($request->subject) && !empty($request->message)) {
                $search = [];
                $replace = [];

                $invoice = Invoice::find($id);
                $user_name = $invoice->tenants()->user->name;
                $invoice_number = invoicePrefix() . $invoice->invoice_id;
                $search = ['{company_name}', '{company_email}', '{company_phone_number}', '{company_address}', '{company_currency}', '{user_name}', '{invoice_number}', '{invoice_date}', '{invoice_due_date}', '{amount}', '{invoice_description}'];
                $replace = [$settings['company_name'], $settings['company_email'], $settings['company_phone'], $settings['company_address'], $settings['CURRENCY_SYMBOL'], $user_name, $invoice_number, $invoice->created_at, $invoice->end_date, priceFormat($invoice->getInvoiceDueAmount()), $invoice->notes];

                $return['subject'] = str_replace($search, $replace, $request->subject);
                $return['message'] = str_replace($search, $replace, $request->message);
            }

            $datas['subject'] = $return['subject'];
            $datas['message'] = $return['message'];
            $datas['module'] = $module;
            $datas['logo'] =  $setting['company_logo'];
            $to = $user->email;
            $response = commonEmailSend($to, $datas);
            if ($response['status'] == 'error') {
                $errorMessage = $response['message'];
            }
        }

        return redirect()->back()->with('success', __('Email successfully sent.') . '</br>' . $errorMessage);
    }

    public function markPaid(Invoice $invoice)
    {
        if (auth()->user()->type == 'owner') {
            $invoice->status = 'paid';
            $invoice->save();
            return redirect()->back()->with('success', 'Invoice marked as paid.');
        }
        return redirect()->back()->with('error', 'Permission Denied!');
    }

    public function ajaxReceipt(Request $request)
    {
        $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'receipt_number' => 'required|string',
            'receipt_type' => 'required|in:cbe,telebirr',
        ]);

        $invoice = \App\Models\Invoice::find($request->invoice_id);
        $dueAmount = $invoice->getInvoiceDueAmount();
        if ($dueAmount <= 0) {
            return response()->json(['success' => false, 'error' => 'Payment not allowed.'], 400);
        }
        $payment = new \App\Models\InvoicePayment();
        $payment->invoice_id = $invoice->id;
        $payment->transaction_id = uniqid('', true);
        $payment->payment_type = strtoupper($request->receipt_type);
        $payment->amount = $dueAmount;
        $payment->payment_date = now();
        $payment->receipt = $request->receipt_type === 'cbe'
            ? 'https://apps.cbe.com.et:100/?id=' . urlencode($request->receipt_number)
            : 'https://transactioninfo.ethiotelecom.et/receipt/' . urlencode($request->receipt_number);
        $payment->notes = '';
        $payment->parent_id = parentId();
        $payment->save();

        // Set invoice status to pending and save
        $invoice->status = 'pending';
        $invoice->save();

        return response()->json(['success' => true, 'redirect' => route('invoice.index')]);
    }
}
