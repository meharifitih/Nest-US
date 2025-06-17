@extends('layouts.app')
@section('page-title')
    {{ __('Expense') }}
@endsection
@push('script-page')
<script>
    $(document).on('click', '.clickable-expense-row', function(e) {
        if (!$(e.target).closest('a, button, input, .cart-action').length) {
            window.location = $(this).data('href');
        }
    });
</script>
@endpush
@section('breadcrumb')
    <ul class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
        </li>
        <li class="breadcrumb-item active">
            <a href="#">{{ __('Expense') }}</a>
        </li>
    </ul>
@endsection
@section('card-action-btn')

@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card table-card">
                <div class="card-header">
                    <div class="row align-items-center g-2">
                        <div class="col">
                            <h5>{{ __('Expense List') }}</h5>
                        </div>
                        <div class="col-auto d-flex gap-2">
                            <button type="button" class="btn btn-primary px-3" data-bs-toggle="modal" data-bs-target="#filterModal">
                                <i class="ti ti-filter me-1"></i> {{ __('Filter') }}
                            </button>
                            @if (Gate::check('create expense'))
                                <a class="btn btn-secondary customModal" href="#" data-size="lg" data-url="{{ route('expense.create') }}"
                                data-title="{{ __('Create Expense') }}"> <i class="ti ti-circle-plus align-text-bottom"></i>{{ __('Create Expense') }}</a>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <!-- Filter Modal -->
                    <div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="filterModalLabel">{{ __('Filter Expense') }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form method="GET" action="{{ route('expense.index') }}">
                                    <div class="modal-body">
                                        <div class="row g-3">
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
                                                    {{ Form::label('expense_type_filter', __('Expense Type'), ['class' => 'form-label']) }}
                                                    <select name="expense_type_filter" class="form-select">
                                                        <option value="">{{ __('All Types') }}</option>
                                                        @foreach($types as $id => $title)
                                                            <option value="{{ $id }}" {{ request('expense_type_filter') == $id ? 'selected' : '' }}>{{ $title }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    {{ Form::label('date', __('Date'), ['class' => 'form-label']) }}
                                                    {{ Form::date('date', request('date'), ['class' => 'form-control']) }}
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    {{ Form::label('amount', __('Amount'), ['class' => 'form-label']) }}
                                                    {{ Form::number('amount', request('amount'), ['class' => 'form-control', 'placeholder' => __('Enter Amount')]) }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-primary px-4">{{ __('Apply Filter') }}</button>
                                        <a href="{{ route('expense.index') }}" class="btn btn-light px-4">{{ __('Reset') }}</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="dt-responsive table-responsive">
                        <table class="table table-hover advance-datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Expense') }}</th>
                                    <th>{{ __('Title') }}</th>
                                    <th>{{ __('Property') }}</th>
                                    <th>{{ __('Unit') }}</th>
                                    <th>{{ __('Type') }}</th>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Amount') }}</th>
                                    <th>{{ __('Receipt') }}</th>
                                    @if (Gate::check('edit expense') || Gate::check('delete expense') || Gate::check('show expense'))
                                        <th class="text-right">{{ __('Action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($expenses as $expense)
                                    <tr class="clickable-expense-row" data-href="{{ route('expense.show', $expense->id) }}">
                                        <td>{{ expensePrefix() . $expense->expense_id }} </td>
                                        <td> {{ $expense->title }} </td>
                                        <td> {{ !empty($expense->properties) ? $expense->properties->name : '-' }} </td>
                                        <td> {{ !empty($expense->units) ? $expense->units->name : '-' }} </td>
                                        <td> {{ !empty($expense->types) ? $expense->types->title : '-' }} </td>
                                        <td> {{ dateFormat($expense->date) }} </td>
                                        <td> {{ priceFormat($expense->amount) }} </td>
                                        <td>
                                            @if (!empty($expense->receipt))
                                                <a href="{{ asset(Storage::url('upload/receipt')) . '/' . $expense->receipt }}"
                                                    download="download" onclick="event.stopPropagation();"><i data-feather="download"></i></a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        @if (Gate::check('edit expense') || Gate::check('delete expense') || Gate::check('show expense'))
                                            <td class="text-right">
                                                <div class="cart-action">
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['expense.destroy', $expense->id]]) !!}
                                                    @can('show expense')
                                                        <a class="avtar avtar-xs btn-link-warning text-warning"
                                                            href="{{ route('expense.show', $expense->id) }}"
                                                            data-bs-toggle="tooltip"
                                                            data-bs-original-title="{{ __('View') }}" onclick="event.stopPropagation();"> <i
                                                                data-feather="eye"></i></a>
                                                    @endcan
                                                    @can('edit expense')
                                                        <a class="avtar avtar-xs btn-link-secondary text-secondary" 
                                                            href="{{ route('expense.edit', $expense->id) }}"
                                                            data-bs-toggle="tooltip"
                                                            data-bs-original-title="{{ __('Edit') }}" 
                                                            onclick="event.stopPropagation();"> 
                                                            <i data-feather="edit"></i>
                                                        </a>
                                                    @endcan
                                                    @can('delete expense')
                                                        <button type="submit"
                                                            class="avtar avtar-xs btn-link-danger text-danger"
                                                            data-bs-toggle="tooltip"
                                                            data-bs-original-title="{{ __('Delete') }}"
                                                            onclick="event.stopPropagation();">
                                                            <i data-feather="trash-2"></i>
                                                        </button>
                                                    @endcan
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
