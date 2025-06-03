@extends('layouts.app')
@section('page-title', __('Rent Invoices'))
@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card table-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>{{ __('Rent Invoices') }}</h5>
                    @if(auth()->user()->type !== 'tenant')
                    <a href="{{ route('rent.create') }}" class="btn btn-secondary">
                        <i class="ti ti-circle-plus align-text-bottom"></i> {{ __('Create Rent Invoice') }}
                    </a>
                    @endif
                </div>
                <div class="card-body pt-0">
                    <div class="dt-responsive table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>{{ __('Invoice') }}</th>
                                    <th>{{ __('Property') }}</th>
                                    <th>{{ __('Unit') }}</th>
                                    <th>{{ __('Invoice Month') }}</th>
                                    <th>{{ __('End Date') }}</th>
                                    <th>{{ __('Amount') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Tenant') }}</th>
                                    <th>{{ __('Type') }}</th>
                                    <th class="text-right">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($invoices as $invoice)
                                    <tr class="clickable-rent-row" data-href="{{ route('invoice.show', $invoice->id) }}">
                                        @include('invoice.partials.invoice_row', ['invoice' => $invoice])
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script-page')
<script>
    $(document).on('click', '.clickable-rent-row', function(e) {
        if (!$(e.target).closest('a, button, input, .cart-action').length) {
            window.location = $(this).data('href');
        }
    });
</script>
@endpush 