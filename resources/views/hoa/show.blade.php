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
                <div class="row mb-4">
                    @foreach($paymentAccounts as $account)
                        <div class="col-md-4">
                            <div class="card payment-account-card mb-3">
                                <div class="card-body">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="selected_account" id="account_{{ $account->id }}" value="{{ $account->id }}">
                                        <label class="form-check-label" for="account_{{ $account->id }}">
                                            <strong>{{ strtoupper($account->account_type) }}</strong><br>
                                            {{ $account->account_name }}<br>
                                            {{ $account->account_number }}
                                        </label>
                                    </div>
                                    <div>
                                        @if($account->account_type === 'telebirr')
                                            <span class="badge bg-info">Instant Activation</span>
                                        @elseif($account->account_type === 'cbe')
                                            <span class="badge bg-warning">Instant Activation</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div id="receipt-number-section" style="display:none;">
                    <label for="receipt_number">Receipt Number</label>
                    <input type="text" name="receipt_number" id="receipt_number" class="form-control" placeholder="Enter receipt number">
                    <small class="text-muted">
                        For CBE: Enter the number after <code>?id=</code><br>
                        For Telebirr: Enter the number after <code>/receipt/</code>
                    </small>
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
    @if(Auth::user()->hasRole('tenant') && $hoa->status == 'open')
        <script>
            $(document).on('change', 'input[name="selected_account"]', function() {
                let selected = $('input[name="selected_account"]:checked').closest('.payment-account-card').find('.form-check-label').text();
                if (selected.includes('CBE') || selected.includes('TELEBIRR')) {
                    $('#receipt-number-section').show();
                } else {
                    $('#receipt-number-section').hide();
                }
            });
        </script>
        <style>
        .payment-account-card { cursor:pointer; border:2px solid #eee; transition:box-shadow .2s; }
        .payment-account-card:hover, .payment-account-card input:checked ~ label { box-shadow:0 0 0 2px #007bff; border-color:#007bff; }
        </style>
    @endif
@endsection 