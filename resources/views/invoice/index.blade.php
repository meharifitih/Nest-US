@extends('layouts.app')

@section('page-title')
    {{ __('Invoice') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item" aria-current="page"> {{ __('Invoice') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card table-card">
                <div class="card-header">
                    <div class="row align-items-center g-2">
                        <div class="col">
                            <h5>{{ __('Invoice List') }}</h5>
                        </div>
                        <div class="col-auto">
                            <form method="GET" action="">
                                <select name="type_filter" class="form-select" onchange="this.form.submit()">
                                    <option value="">All Types</option>
                                    @foreach($types as $type)
                                        <option value="{{ $type->id }}" {{ request('type_filter') == $type->id ? 'selected' : '' }}>{{ $type->title }}</option>
                                    @endforeach
                                </select>
                            </form>
                        </div>
                        @if (Gate::check('create invoice'))
                            <div class="col-auto">
                                <a href="{{ route('invoice.create') }}" class="btn btn-secondary"> <i
                                        class="ti ti-circle-plus align-text-bottom"></i> {{ __('Create Invoice') }}</a>
                            </div>
                        @endif
                    </div>
                </div>
                
                <div class="card-body pt-0">
                    <div class="dt-responsive table-responsive">
                        <table class="table table-hover advance-datatable">
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
                                    @if (Gate::check('edit invoice') || Gate::check('delete invoice') || Gate::check('show invoice'))
                                        <th class="text-right">{{ __('Action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $rentInvoices = $invoices->filter(function($invoice) {
                                        return $invoice->types->first() && $invoice->types->first()->types && $invoice->types->first()->types->type === 'rent';
                                    });
                                    $otherInvoices = $invoices->filter(function($invoice) {
                                        return !($invoice->types->first() && $invoice->types->first()->types && $invoice->types->first()->types->type === 'rent');
                                    });
                                @endphp

                                @if($rentInvoices->count() > 0)
                                    <tr class="table-primary">
                                        <td colspan="10"><strong>{{ __('Rent Invoices') }}</strong></td>
                                    </tr>
                                    @foreach ($rentInvoices as $invoice)
                                        @include('invoice.partials.invoice_row', ['invoice' => $invoice])
                                    @endforeach
                                @endif

                                @foreach ($otherInvoices as $invoice)
                                    @include('invoice.partials.invoice_row', ['invoice' => $invoice])
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
