<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\PropertyUnit;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateRentInvoices extends Command
{
    protected $signature = 'rent:generate-invoices';
    protected $description = 'Generate rent invoices based on tenant lease start date';

    public function handle()
    {
        $units = PropertyUnit::with(['tenants.user', 'property'])->whereHas('tenants')->get();
        $now = Carbon::now();

        foreach ($units as $unit) {
            $tenant = $unit->tenants;
            if (!$tenant) continue;

            // Get tenant's lease start date
            $leaseStartDate = Carbon::parse($tenant->lease_start_date);
            $leaseEndDate = Carbon::parse($tenant->lease_end_date);

            // Skip if we're past the lease end date
            if ($now->gt($leaseEndDate)) continue;

            // Get the last invoice for this unit
            $lastInvoice = Invoice::where('unit_id', $unit->id)
                ->orderBy('invoice_month', 'desc')
                ->first();

            $startDate = $lastInvoice ? Carbon::parse($lastInvoice->invoice_month)->addMonth() : $leaseStartDate;

            // Generate invoices based on rent type
            switch ($unit->rent_type) {
                case 'monthly':
                    $this->generateMonthlyInvoice($unit, $tenant, $startDate, $leaseEndDate);
                    break;
                case 'quarterly':
                    $this->generateQuarterlyInvoice($unit, $tenant, $startDate, $leaseEndDate);
                    break;
                case 'six_months':
                    $this->generateSixMonthInvoice($unit, $tenant, $startDate, $leaseEndDate);
                    break;
                case 'yearly':
                    $this->generateYearlyInvoice($unit, $tenant, $startDate, $leaseEndDate);
                    break;
            }
        }

        $this->info('Rent invoices generated successfully.');
    }

    private function generateMonthlyInvoice($unit, $tenant, $startDate, $leaseEndDate)
    {
        $now = Carbon::now();
        
        // Calculate next invoice date based on lease start date
        $nextInvoiceDate = $startDate->copy();
        while ($nextInvoiceDate->lte($now) && $nextInvoiceDate->lte($leaseEndDate)) {
            $this->createInvoice($unit, $tenant, $nextInvoiceDate, $nextInvoiceDate->copy()->endOfMonth());
            $nextInvoiceDate->addMonth();
        }
    }

    private function generateQuarterlyInvoice($unit, $tenant, $startDate, $leaseEndDate)
    {
        $now = Carbon::now();
        
        // Calculate next invoice date based on lease start date
        $nextInvoiceDate = $startDate->copy();
        while ($nextInvoiceDate->lte($now) && $nextInvoiceDate->lte($leaseEndDate)) {
            $this->createInvoice($unit, $tenant, $nextInvoiceDate, $nextInvoiceDate->copy()->addMonths(2)->endOfMonth());
            $nextInvoiceDate->addMonths(3);
        }
    }

    private function generateSixMonthInvoice($unit, $tenant, $startDate, $leaseEndDate)
    {
        $now = Carbon::now();
        
        // Calculate next invoice date based on lease start date
        $nextInvoiceDate = $startDate->copy();
        while ($nextInvoiceDate->lte($now) && $nextInvoiceDate->lte($leaseEndDate)) {
            $this->createInvoice($unit, $tenant, $nextInvoiceDate, $nextInvoiceDate->copy()->addMonths(5)->endOfMonth());
            $nextInvoiceDate->addMonths(6);
        }
    }

    private function generateYearlyInvoice($unit, $tenant, $startDate, $leaseEndDate)
    {
        $now = Carbon::now();
        
        // Calculate next invoice date based on lease start date
        $nextInvoiceDate = $startDate->copy();
        while ($nextInvoiceDate->lte($now) && $nextInvoiceDate->lte($leaseEndDate)) {
            $this->createInvoice($unit, $tenant, $nextInvoiceDate, $nextInvoiceDate->copy()->addMonths(11)->endOfMonth());
            $nextInvoiceDate->addYear();
        }
    }

    private function createInvoice($unit, $tenant, $startDate, $endDate)
    {
        // Check if invoice already exists for this period
        $existingInvoice = Invoice::where('unit_id', $unit->id)
            ->where('invoice_month', $startDate->format('Y-m-d'))
            ->first();

        if ($existingInvoice) return;

        // Ensure unit is fresh from database to get latest rent value
        $unit = PropertyUnit::find($unit->id);
        
        // Defensive: ensure rent is loaded and numeric
        $rent = floatval($unit->rent);
        
        // Log rent value for debugging
        \Log::info('Creating invoice for unit', [
            'unit_id' => $unit->id,
            'rent' => $rent,
            'rent_type' => $unit->rent_type,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d')
        ]);

        if ($rent <= 0) {
            \Log::error('Unit rent is zero or not set for unit ID: ' . $unit->id, [
                'unit' => $unit->toArray(),
                'tenant' => $tenant->toArray()
            ]);
            return; // Don't create invoice if rent is zero
        }

        // Calculate rent amount based on rent type
        $rentAmount = $rent;
        switch ($unit->rent_type) {
            case 'quarterly':
                $rentAmount = $rent * 3;
                break;
            case 'six_months':
                $rentAmount = $rent * 6;
                break;
            case 'yearly':
                $rentAmount = $rent * 12;
                break;
        }

        // Log final rent amount
        \Log::info('Final rent amount calculated', [
            'unit_id' => $unit->id,
            'rent_amount' => $rentAmount,
            'rent_type' => $unit->rent_type
        ]);

        $invoice = new Invoice();
        $invoice->invoice_id = $this->generateInvoiceNumber();
        $invoice->property_id = $unit->property_id;
        $invoice->unit_id = $unit->id;
        $invoice->invoice_month = $startDate->format('Y-m-d');
        $invoice->end_date = $endDate->format('Y-m-d');
        $invoice->status = 'open';
        $invoice->parent_id = $unit->parent_id;
        $invoice->save();

        // Add rent item
        $rentType = \App\Models\Type::firstOrCreate(
            [
                'type' => 'invoice',
                'title' => 'Rent',
                'parent_id' => $unit->parent_id
            ]
        );
        $invoiceItem = new InvoiceItem();
        $invoiceItem->invoice_id = $invoice->id;
        $invoiceItem->invoice_type = $rentType->id;
        $invoiceItem->amount = $rentAmount;
        $invoiceItem->description = ucfirst($unit->rent_type) . ' Rent for ' . $startDate->format('M Y') . ' to ' . $endDate->format('M Y');
        $invoiceItem->save();

        // Send email notification to tenant's user
        $user = $tenant->user;
        if ($user && $user->email) {
            $data = [
                'name' => $user->name,
                'invoice_id' => $invoice->invoice_id,
                'amount' => $rentAmount,
                'due_date' => $endDate->format('Y-m-d'),
                'property_name' => $unit->property ? $unit->property->name : '',
                'unit_name' => $unit->name,
                'period' => $startDate->format('M Y') . ' to ' . $endDate->format('M Y')
            ];

            \Mail::send('emails.invoice_created', $data, function($message) use ($user, $invoice) {
                $message->to($user->email, $user->name)
                    ->subject('New Rent Invoice #' . $invoice->invoice_id . ' Generated');
            });
        }
    }

    private function generateInvoiceNumber()
    {
        $lastInvoice = Invoice::orderBy('id', 'desc')->first();
        $number = $lastInvoice ? $lastInvoice->invoice_id + 1 : 1;
        return str_pad($number, 6, '0', STR_PAD_LEFT);
    }
} 