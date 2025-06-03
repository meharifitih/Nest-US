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
                        <div class="col-auto">
                            <form method="GET" action="">
                                <select name="expense_type_filter" class="form-select" onchange="this.form.submit()">
                                    <option value="">All Expense Types</option>
                                    @foreach($types as $id => $title)
                                        <option value="{{ $id }}" {{ request('expense_type_filter') == $id ? 'selected' : '' }}>{{ $title }}</option>
                                    @endforeach
                                </select>
                            </form>
                        </div>
                        @if (Gate::check('create expense'))
                            <div class="col-auto">
                                <a class="btn btn-secondary customModal" href="#" data-size="lg" data-url="{{ route('expense.create') }}"
                                data-title="{{ __('Create Expense') }}"> <i class="ti ti-circle-plus align-text-bottom"></i>{{ __('Create Expense') }}</a>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="card-body pt-0">
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
                                                        <a class="avtar avtar-xs btn-link-secondary text-secondary customModal" data-size="lg"
                                                            data-bs-toggle="tooltip"
                                                            data-bs-original-title="{{ __('Edit') }}" href="#"
                                                            data-url="{{ route('expense.edit', $expense->id) }}"
                                                            data-title="{{ __('Edit Expense') }}" onclick="event.stopPropagation();"> <i
                                                                data-feather="edit"></i></a>
                                                    @endcan
                                                    @can('delete expense')
                                                        <a class="avtar avtar-xs btn-link-danger text-danger confirm_dialog" data-bs-toggle="tooltip"
                                                            data-bs-original-title="{{ __('Detete') }}" href="#" onclick="event.stopPropagation();"> <i
                                                                data-feather="trash-2"></i></a>
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
