@extends('layouts.app')

@section('page-title')
    {{ __('HOA') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('hoa.index') }}">{{ __('HOA') }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ __('Details') }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-4 shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center bg-white border-bottom-0" style="padding-bottom:0.5rem;">
                <h5 class="mb-0">{{ __('HOA Payment Details') }}</h5>
                <span>
                    @if ($hoa->status == 'pending')
                        <span class="badge bg-warning text-dark" style="font-size:1rem;">Pending</span>
                    @elseif ($hoa->status == 'open')
                        <span class="badge bg-light-warning text-dark" style="font-size:1rem;">Open</span>
                    @elseif ($hoa->status == 'paid')
                        <span class="badge bg-success" style="font-size:1rem;">Paid</span>
                    @endif
                </span>
            </div>
            <div class="card-body py-4">
                <div class="row mb-0">
                    <div class="col-12">
                        <dl class="row mb-0">
                            <dt class="col-sm-4">{{ __('Property') }}</dt>
                            <dd class="col-sm-8">{{ $hoa->property->name ?? '-' }}</dd>
                            <dt class="col-sm-4">{{ __('Unit') }}</dt>
                            <dd class="col-sm-8">{{ $hoa->unit->name ?? '-' }}</dd>
                            <dt class="col-sm-4">{{ __('Tenant') }}</dt>
                            <dd class="col-sm-8">{{ $hoa->unit && $hoa->unit->tenants && $hoa->unit->tenants->user ? $hoa->unit->tenants->user->name : '-' }}</dd>
                            <dt class="col-sm-4">{{ __('HOA Type') }}</dt>
                            <dd class="col-sm-8">{{ $hoa->hoaType->title ?? '-' }}</dd>
                            <dt class="col-sm-4">{{ __('Amount') }}</dt>
                            <dd class="col-sm-8">{{ priceFormat($hoa->amount) }}</dd>
                            <dt class="col-sm-4">{{ __('Frequency') }}</dt>
                            <dd class="col-sm-8">{{ ucfirst($hoa->frequency) }}</dd>
                            <dt class="col-sm-4">{{ __('Due Date') }}</dt>
                            <dd class="col-sm-8">{{ $hoa->due_date ? dateFormat($hoa->due_date) : '-' }}</dd>
                            @if($hoa->paid_date)
                                <dt class="col-sm-4">{{ __('Paid Date') }}</dt>
                                <dd class="col-sm-8">{{ dateFormat($hoa->paid_date) }}</dd>
                            @endif
                            <dt class="col-sm-4">{{ __('Description') }}</dt>
                            <dd class="col-sm-8">{{ $hoa->description ?: '-' }}</dd>
                        </dl>
                    </div>
                </div>
                @if($hoa->receipt)
                    @php
                        $receiptParts = explode(':', $hoa->receipt, 2);
                        $receiptType = strtolower($receiptParts[0] ?? '');
                        $receiptNumber = $receiptParts[1] ?? '';
                        $telebirrBase = 'https://transactioninfo.ethiotelecom.et/receipt/';
                        $cbeBase = 'https://apps.cbe.com.et:100/?id=';
                    @endphp
                    <div class="mt-4">
                        @if($receiptType === 'telebirr')
                            <a href="{{ $telebirrBase . urlencode($receiptNumber) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                                <i class="ti ti-file"></i> View Telebirr Receipt
                            </a>
                        @elseif($receiptType === 'cbe')
                            <a href="{{ $cbeBase . urlencode($receiptNumber) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                                <i class="ti ti-file"></i> View CBE Receipt
                            </a>
                        @else
                            <a href="{{ asset('storage/'.$hoa->receipt) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                                <i class="ti ti-file"></i> {{ __('View Receipt') }}
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
        @if(Auth::user()->hasRole('tenant') && $hoa->status == 'open')
            @include('hoa.partials.add_payment', ['hoa' => $hoa, 'settings' => $settings])
        @endif
        @if(Auth::user()->hasRole('owner') && $hoa->status == 'pending')
        <div class="card">
            <div class="card-header">
                <h5>{{ __('Approve Payment') }}</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('hoa.mark-as-paid', $hoa) }}" method="POST" class="text-start">
                    @csrf
                    <button type="submit" class="btn btn-success w-100 btn-rounded fw-bold" style="font-size: 1.1rem;">{{ __('Approve Payment') }}</button>
                </form>
            </div>
        </div>
        @endif
        <div class="mt-3 text-end">
            <a href="{{ route('hoa.index') }}" class="btn btn-light">{{ __('Back') }}</a>
            @if(Auth::user()->hasRole('owner'))
                <form action="{{ route('hoa.destroy', $hoa) }}" method="POST" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger confirm_dialog">{{ __('Delete') }}</button>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection 