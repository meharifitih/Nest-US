@extends('layouts.app')

@section('page-title')
    {{ __('Rent Invoices') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item" aria-current="page">{{ __('Rent Invoices') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="mb-0">{{ __('Rent Invoices') }}</h5>
                        </div>
                        <div class="col-auto d-flex gap-2">
                            <button type="button" class="btn btn-primary px-3" data-bs-toggle="modal" data-bs-target="#filterModal">
                                <i class="ti ti-filter me-1"></i> {{ __('Filter') }}
                            </button>
                            @can('create invoice')
                                <a href="{{ route('rent.create') }}" class="btn btn-secondary px-3">
                                    <i class="ti ti-plus me-1"></i> {{ __('Create Rent Invoice') }}
                                </a>
                            @endcan
                        </div>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Filter Modal -->
                    <div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="filterModalLabel">{{ __('Filter Rent Invoices') }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form method="GET" action="{{ route('rent.index') }}">
                                    <div class="modal-body">
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    {{ Form::label('status', __('Status'), ['class' => 'form-label']) }}
                                                    {{ Form::select('status', ['' => __('All')] + $statusOptions, request('status'), ['class' => 'form-select']) }}
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    {{ Form::label('tenant', __('Tenant'), ['class' => 'form-label']) }}
                                                    <select name="tenant" class="form-select">
                                                        <option value="">{{ __('All') }}</option>
                                                        @foreach($tenants as $tenant)
                                                            <option value="{{ $tenant->user_id }}" {{ request('tenant') == $tenant->user_id ? 'selected' : '' }}>
                                                                {{ $tenant->user ? $tenant->user->name : '-' }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    {{ Form::label('property_id', __('Property'), ['class' => 'form-label']) }}
                                                    <select name="property_id" class="form-select">
                                                        <option value="">{{ __('All') }}</option>
                                                        @foreach($properties as $property)
                                                            <option value="{{ $property->id }}" {{ request('property_id') == $property->id ? 'selected' : '' }}>
                                                                {{ $property->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    {{ Form::label('unit_id', __('Unit'), ['class' => 'form-label']) }}
                                                    <select name="unit_id" class="form-select">
                                                        <option value="">{{ __('All') }}</option>
                                                        @foreach($units as $unit)
                                                            <option value="{{ $unit->id }}" {{ request('unit_id') == $unit->id ? 'selected' : '' }}>
                                                                {{ $unit->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    {{ Form::label('invoice_month', __('Invoice Month'), ['class' => 'form-label']) }}
                                                    {{ Form::month('invoice_month', request('invoice_month'), ['class' => 'form-control']) }}
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    {{ Form::label('end_date', __('End Date'), ['class' => 'form-label']) }}
                                                    {{ Form::date('end_date', request('end_date'), ['class' => 'form-control']) }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-primary px-4">{{ __('Apply Filter') }}</button>
                                        <a href="{{ route('rent.index') }}" class="btn btn-light px-4">{{ __('Reset') }}</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
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
                                    <th class="text-end">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($invoices as $invoice)
                                    @include('invoice.partials.invoice_row', ['invoice' => $invoice])
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center text-muted py-3">{{ __('No data available') }}</td>
                                    </tr>
                                @endforelse
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