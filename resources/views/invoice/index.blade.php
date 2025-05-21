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
                                @foreach ($invoices as $invoice)
                                    <tr>
                                        <td>{{ invoicePrefix() . $invoice->invoice_id }} </td>
                                        <td>{{ !empty($invoice->properties) ? $invoice->properties->name : '-' }} </td>
                                        <td>{{ !empty($invoice->units) ? $invoice->units->name : '-' }} </td>
                                        <td>{{ date('F Y', strtotime($invoice->invoice_month)) }} </td>
                                        <td>{{ dateFormat($invoice->end_date) }} </td>
                                        <td>{{ priceFormat($invoice->getInvoiceSubTotalAmount()) }}</td>
                                        <td>
                                            @if ($invoice->status == 'pending')
                                                <span class="badge bg-light-warning">Pending</span>
                                            @elseif ($invoice->status == 'open')
                                                <span
                                                    class="badge bg-light-info">{{ \App\Models\Invoice::$status[$invoice->status] }}</span>
                                            @elseif($invoice->status == 'paid')
                                                <span
                                                    class="badge bg-light-success">{{ \App\Models\Invoice::$status[$invoice->status] }}</span>
                                            @elseif($invoice->status == 'partial_paid')
                                                <span
                                                    class="badge bg-light-warning">{{ \App\Models\Invoice::$status[$invoice->status] }}</span>
                                            @endif
                                        </td>
                                        <td>{{ !empty($invoice->tenants()) && !empty($invoice->tenants()->user) ? $invoice->tenants()->user->name : '-' }}</td>
                                        <td>
                                            @php
                                                $type = $invoice->types->first();
                                                echo $type && $type->types ? $type->types->title : '-';
                                            @endphp
                                        </td>
                                        @if (Gate::check('edit invoice') || Gate::check('delete invoice') || Gate::check('show invoice'))
                                            <td>
                                                <div class="cart-action">
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['invoice.destroy', $invoice->id]]) !!}

                                                    @can('show invoice')
                                                        <a class="avtar avtar-xs btn-link-warning text-warning"
                                                            href="{{ route('invoice.show', $invoice->id) }}"
                                                            data-bs-toggle="tooltip"
                                                            data-bs-original-title="{{ __('View') }}"> <i
                                                                data-feather="eye"></i></a>
                                                    @endcan
                                                    @can('edit invoice')
                                                        <a class="avtar avtar-xs btn-link-secondary text-secondary" data-bs-original-title="{{ __('Edit') }}"
                                                            href="{{ route('invoice.edit', $invoice->id) }}" data-bs-toggle="tooltip"
                                                            data-bs-original-title="{{ __('Edit') }}"> <i
                                                                data-feather="edit"></i></a>
                                                    @endcan
                                                    @can('delete invoice')
                                                        <a class="avtar avtar-xs btn-link-danger text-danger confirm_dialog" data-bs-toggle="tooltip"
                                                            data-bs-original-title="{{ __('Detete') }}" href="#"> <i
                                                                data-feather="trash-2"></i></a>
                                                    @endcan
                                                    @if (auth()->user()->type == 'tenant' && $invoice->status == 'open')
                                                        <a href="{{ route('invoice.payment.create', $invoice->id) }}" class="btn btn-primary btn-sm">Pay Now</a>
                                                    @endif
                                                    {!! Form::close() !!}
                                                </div>

                                            </td>
                                        @endif
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
