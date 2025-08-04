@extends('layouts.app')

@section('page-title')
    {{ __('Account Pending') }}
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm mt-5">
            <div class="card-body text-center">
                <div class="mb-4">
                    <i class="fas fa-clock text-warning" style="font-size: 4rem;"></i>
                </div>
                <h2 class="text-primary mb-3">{{ __('Payment Received Successfully!') }}</h2>
                <p class="text-muted mb-4">
                    {{ __('Your payment has been processed and your account is now pending admin approval.') }}<br>
                    {{ __('This process typically takes 1-2 business days.') }}
                </p>
                
                @if(isset($latestTransaction))
                <div class="card mb-4 mx-auto" style="max-width: 400px;">
                    <div class="card-header text-center"><strong>{{ __('Payment Details') }}</strong></div>
                    <div class="card-body">
                        <p><strong>{{ __('Amount:') }}</strong> {{ $settings['CURRENCY_SYMBOL'] ?? '$' }}{{ $latestTransaction->amount }}</p>
                        <p><strong>{{ __('Payment Type:') }}</strong> {{ $latestTransaction->payment_type }}</p>
                        <p><strong>{{ __('Status:') }}</strong> 
                            <span class="badge bg-warning">{{ __('Pending') }}</span>
                        </p>
                        <p><strong>{{ __('Subscription:') }}</strong> {{ $latestTransaction->subscription ? $latestTransaction->subscription->title : '-' }}</p>
                        @if($latestTransaction->receipt)
                        <p><strong>{{ __('Receipt:') }}</strong> 
                            <a href="{{ $latestTransaction->receipt }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-download"></i> {{ __('View Receipt') }}
                            </a>
                        </p>
                        @endif
                    </div>
                </div>
                @endif
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    {{ __('You will receive an email notification once your account has been approved.') }}
                </div>
                
                <div class="tutorial-videos mb-4">
                    <h5 class="text-center">{{ __('Tutorial Videos') }}</h5>
                    <ul class="list-unstyled text-center">
                        <li><a href="https://www.youtube.com/watch?v=dQw4w9WgXcQ" target="_blank">{{ __('Getting Started') }}</a></li>
                        <li><a href="https://www.youtube.com/watch?v=9bZkp7q19f0" target="_blank">{{ __('How to Use the Dashboard') }}</a></li>
                    </ul>
                </div>
                
                <p class="text-muted mt-4">
                    {{ __('If you have any questions, please contact our support team.') }}
                </p>
            </div>
        </div>
    </div>
</div>
@endsection 