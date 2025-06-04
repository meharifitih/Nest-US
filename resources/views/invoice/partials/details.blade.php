<div class="row g-3">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
            <div class="d-flex align-items-center gap-3">
                <img src="{{ asset(Storage::url('upload/logo/')) . '/' . (isset($admin_logo) && !empty($admin_logo) ? $admin_logo : 'logo.png') }}" class="img-fluid brand-logo" alt="images" style="height:48px;">
                <div>
                    <div class="fw-bold" style="font-size:1.2rem;">{{ invoicePrefix() . $invoice->invoice_id }}</div>
                </div>
            </div>
            <div class="text-end">
                <div class="mb-1">
                    <span class="fw-bold">{{ __('Invoice Month') }}:</span>
                    <span class="text-muted">{{ date('F Y', strtotime($invoice->invoice_month)) }}</span>
                </div>
                <div class="mb-1">
                    <span class="fw-bold">{{ __('Due Date') }}:</span>
                    <span class="text-muted">{{ dateFormat($invoice->end_date) }}</span>
                </div>
                <div>
                    <span class="fw-bold">{{ __('Status') }}:</span>
                    @if ($invoice->status == 'open')
                        <span class="badge bg-light-info ms-1">Open</span>
                    @elseif($invoice->status == 'pending')
                        <span class="badge bg-light-warning ms-1">Pending</span>
                    @elseif($invoice->status == 'paid')
                        <span class="badge bg-light-success ms-1">Paid</span>
                    @elseif($invoice->status == 'partial_paid')
                        <span class="badge bg-light-warning ms-1">Partial Paid</span>
                    @else
                        <span class="badge bg-light-secondary ms-1">{{ ucfirst($invoice->status) }}</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="border rounded p-3">
            <h6 class="mb-0">From:</h6>
            <h5>{{ $settings['company_name'] }}</h5>
            <p class="mb-0">{{ $settings['company_phone'] }}</p>
            <p class="mb-0">{{ $settings['company_email'] }}</p>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="border rounded p-3">
            <h6 class="mb-0">To:</h6>
            <h5>{{ !empty($tenant) && !empty($tenant->user) ? $tenant->user->first_name . ' ' . $tenant->user->last_name : '' }}</h5>
            <p class="mb-0">{{ !empty($tenant) && !empty($tenant->user) ? $tenant->user->phone_number : '-' }}</p>
            <p class="mb-0">{{ !empty($tenant) ? $tenant->address : '' }}</p>
        </div>
    </div>
    <div class="col-12">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th width="46%">{{ __('Type') }}</th>
                        <th width="46%">{{ __('Description') }}</th>
                        <th>{{ __('Amount') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($invoice->types as $k => $type)
                        <tr>
                            <td>{{ !empty($type->types) ? $type->types->title : '-' }}</td>
                            <td>{{ $type->description }}</td>
                            <td>{{ priceFormat($type->amount) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="text-start">
            <hr class="mb-2 mt-1 border-secondary border-opacity-50" />
        </div>
    </div>
    <div class="col-12 ">
        <div class="invoice-total ms-auto">
            <div class="row">
                <div class="col-4">
                    <p class="f-w-600 mb-1 text-start">{{ __('Total') }} :</p>
                </div>
                <div class="col-7">
                    <p class="f-w-600 mb-1 text-end">
                        {{ priceFormat($invoice->getInvoiceSubTotalAmount()) }}
                    </p>
                </div>
                <div class="col-4">
                    <p class="f-w-600 mb-1 text-start">{{ __('Due Amount') }} :</p>
                </div>
                <div class="col-7">
                    <p class="f-w-600 mb-1 text-end">
                        {{ priceFormat($invoice->getInvoiceDueAmount()) }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div> 