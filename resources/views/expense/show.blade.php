@extends('layouts.app')
@section('page-title')
    {{ __('Expense Details') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('expense.index') }}">{{ __('Expense') }}</a></li>
    <li class="breadcrumb-item active">{{ __('Details') }}</li>
@endsection
@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8 col-md-10">
        <div class="card shadow border mb-4">
            <div class="card-header bg-light-primary text-primary d-flex align-items-center justify-content-between">
                <h4 class="mb-0 fw-bold"><i class="ti ti-receipt me-2"></i>{{ __('Expense Details') }}</h4>
                <span class="badge bg-primary text-white fs-6">{{ expensePrefix() . $expense->expense_id }}</span>
            </div>
            <div class="card-body py-4">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="mb-2 text-muted small">{{ __('Title') }}</div>
                        <div class="fs-5 fw-semibold text-dark">{{ $expense->title }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-2 text-muted small">{{ __('Type') }}</div>
                        <div class="fs-5 fw-semibold text-dark">{{ !empty($expense->types) ? $expense->types->title : '-' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-2 text-muted small">{{ __('Property') }}</div>
                        <div class="fs-5 fw-semibold text-dark">{{ !empty($expense->properties) ? $expense->properties->name : '-' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-2 text-muted small">{{ __('Unit') }}</div>
                        <div class="fs-5 fw-semibold text-dark">{{ !empty($expense->units) ? $expense->units->name : '-' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-2 text-muted small">{{ __('Date') }}</div>
                        <div class="fs-5 fw-semibold text-dark"><i class="ti ti-calendar me-1"></i>{{ dateFormat($expense->date) }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-2 text-muted small">{{ __('Amount') }}</div>
                        <div class="fs-5 fw-semibold text-success"><i class="ti ti-currency-dollar me-1"></i>{{ priceFormat($expense->amount) }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-2 text-muted small">{{ __('Receipt') }}</div>
                        <div>
                            @if (!empty($expense->receipt))
                                <a href="{{ asset(Storage::url('upload/receipt')) . '/' . $expense->receipt }}" class="btn btn-outline-primary btn-sm" download>
                                    <i class="ti ti-download me-1"></i>{{ __('Download') }}
                                </a>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-2 text-muted small">{{ __('Notes') }}</div>
                        <div class="fs-6 text-dark">{{ $expense->notes ?: '-' }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="text-end">
            <a href="{{ route('expense.index') }}" class="btn btn-light"><i class="ti ti-arrow-left me-1"></i>{{ __('Back to List') }}</a>
        </div>
    </div>
</div>
@endsection
