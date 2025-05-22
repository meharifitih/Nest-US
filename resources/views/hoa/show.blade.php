@extends('layouts.app')

@section('page-title')
    {{ __('HOA Details') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('hoa.index') }}">{{ __('HOA') }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ __('Details') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-8 col-md-12">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ __('HOA Payment Details') }}</h5>
                </div>
                <div class="card-body">
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
                        <dt class="col-sm-4">{{ __('Status') }}</dt>
                        <dd class="col-sm-8">
                            @if ($hoa->status == 'pending')
                                <span class="badge bg-light-warning">Pending</span>
                            @elseif ($hoa->status == 'open')
                                <span class="badge bg-light-warning">Open</span>
                            @elseif ($hoa->status == 'paid')
                                <span class="badge bg-light-success">Paid</span>
                            @endif
                        </dd>
                        @if($hoa->paid_date)
                            <dt class="col-sm-4">{{ __('Paid Date') }}</dt>
                            <dd class="col-sm-8">{{ dateFormat($hoa->paid_date) }}</dd>
                        @endif
                        @if($hoa->receipt)
                            <dt class="col-sm-4">{{ __('Payment Receipt') }}</dt>
                            <dd class="col-sm-8">
                                <a href="{{ asset('storage/'.$hoa->receipt) }}" target="_blank" class="btn btn-sm btn-primary">
                                    <i class="ti ti-file"></i> {{ __('View Receipt') }}
                                </a>
                            </dd>
                        @endif
                        <dt class="col-sm-4">{{ __('Description') }}</dt>
                        <dd class="col-sm-8">{{ $hoa->description ?: '-' }}</dd>
                    </dl>
                    <hr>
                    <div class="card mt-4">
                        <div class="card-header pb-2 pt-2">
                            <h6 class="mb-0">{{ __('Bank Transfer') }}</h6>
                        </div>
                        <div class="card-body pt-2 pb-2">
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>{{ __('Bank Name:') }}</strong> {{ $settings['bank_name'] ?? 'N/A' }}</p>
                                    <p class="mb-1"><strong>{{ __('Bank Holder Name:') }}</strong> {{ $settings['bank_holder_name'] ?? 'N/A' }}</p>
                                    <p class="mb-1"><strong>{{ __('Bank Account Number:') }}</strong> {{ $settings['bank_account_number'] ?? 'N/A' }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>{{ __('Bank IFSC Code:') }}</strong> {{ $settings['bank_ifsc_code'] ?? 'N/A' }}</p>
                                    @if (!empty($settings['bank_other_details']))
                                        <p class="mb-1"><strong>{{ __('Bank Other Details:') }}</strong> {{ $settings['bank_other_details'] }}</p>
                                    @endif
                                </div>
                            </div>
                            <p class="text-muted mt-2">{{ __('Please upload your payment screenshot after transferring to the owner\'s bank account.') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-12">
            @if(Auth::user()->hasRole('tenant') && $hoa->status == 'open')
                <div class="card">
                    <div class="card-header">
                        <h5>{{ __('Add Payment') }}</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('hoa.mark-as-paid', $hoa) }}" method="POST" enctype="multipart/form-data" class="text-start">
                            @csrf
                            <div class="mb-3">
                                <label for="payment_date" class="form-label">{{ __('Payment Date') }}</label>
                                <input type="date" name="payment_date" id="payment_date" class="form-control" value="{{ date('Y-m-d') }}">
                            </div>
                            <div class="mb-3">
                                <label for="amount" class="form-label">{{ __('Amount') }}</label>
                                <input type="number" name="amount" id="amount" class="form-control" value="{{ $hoa->amount }}" readonly>
                            </div>
                            <div class="mb-3">
                                <label for="receipt" class="form-label">{{ __('Receipt') }}</label>
                                <input type="file" name="receipt" id="receipt" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label for="notes" class="form-label">{{ __('Notes') }}</label>
                                <textarea name="notes" id="notes" class="form-control" rows="2"></textarea>
                            </div>
                            <button type="submit" class="btn btn-success w-100 btn-rounded fw-bold" style="font-size: 1.1rem;">{{ __('Submit Payment') }}</button>
                        </form>
                    </div>
                </div>
            @elseif(Auth::user()->hasRole('owner') && $hoa->status == 'pending')
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