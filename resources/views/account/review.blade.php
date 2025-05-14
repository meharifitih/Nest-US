@extends('layouts.app')

@section('page-title')
    {{ __('Account Review') }}
@endsection

@section('content')
@php
    $hasPendingPayment = isset($latestTransaction) && in_array(strtolower($latestTransaction->payment_status), ['pending']);
@endphp

@if(auth()->user()->approval_status == 'pending')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm mt-5">
                <div class="card-body text-center">
                    <h2 class="text-primary mb-3">Your Account is Under Review</h2>
                    <p class="text-muted mb-4">
                        Thank you for registering! Your account is currently being reviewed by our team. This process typically takes 1-2 business days.<br>
                        While you wait, feel free to explore our tutorial videos below to get familiar with the system.
                    </p>
                    @if($hasPendingPayment)
                        <div class="card mb-4 mx-auto" style="max-width: 400px;">
                            <div class="card-header text-center"><strong>Your Latest Payment</strong></div>
                            <div class="card-body">
                                <p><strong>Amount:</strong> {{ $latestTransaction->amount }}</p>
                                <p><strong>Payment Type:</strong> {{ $latestTransaction->payment_type }}</p>
                                <p><strong>Status:</strong> {{ ucfirst($latestTransaction->payment_status) }}</p>
                                <p><strong>Subscription:</strong> {{ $latestTransaction->subscription ? $latestTransaction->subscription->title : '-' }}</p>
                            </div>
                        </div>
                        <div class="alert alert-info">You have a pending payment. You cannot make another payment until this one is reviewed.</div>
                    @else
                        {{-- Only show subscription selection if user has NOT paid yet --}}
                        @php($subscriptions = \App\Models\Subscription::all())
                        @include('subscription.index')
                    @endif
                    <div class="tutorial-videos mb-4">
                        <h5 class="text-center">Tutorial Videos</h5>
                        <ul class="list-unstyled text-center">
                            <li><a href="https://www.youtube.com/watch?v=dQw4w9WgXcQ" target="_blank">Getting Started</a></li>
                            <li><a href="https://www.youtube.com/watch?v=9bZkp7q19f0" target="_blank">How to Use the Dashboard</a></li>
                        </ul>
                    </div>
                    <p class="text-muted mt-4">We will notify you via email once your account has been approved.<br>If you have any questions, please contact our support team.</p>
                </div>
            </div>
        </div>
    </div>
@elseif(auth()->user()->approval_status == 'rejected')
    <div class="alert alert-danger">
        {{ __('Your account was rejected.') }}
        @if(auth()->user()->rejection_reason)
            <br>
            {{ __('Reason:') }} {{ auth()->user()->rejection_reason }}
        @endif
    </div>
@endif

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="text-center mb-4">
                    <h2 class="text-primary">{{ __('Your Account is Under Review') }}</h2>
                    <p class="text-muted">{{ __('Thank you for registering! Your account is currently being reviewed by our team. This process typically takes 1-2 business days.') }}</p>
                    <p class="text-muted">{{ __('While you wait, feel free to explore our tutorial videos below to get familiar with the system.') }}</p>
                </div>

                <div class="row justify-content-center">
                    @if(count($tutorialVideos) > 0)
                        @foreach($tutorialVideos as $video)
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100 shadow-sm">
                                <div class="card-body p-2">
                                    <div class="ratio ratio-16x9">
                                        <iframe src="{{ $video['url'] }}" allowfullscreen style="border-radius:8px;"></iframe>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <div class="col-12 text-center">
                            <p class="text-muted">{{ __('No tutorial videos available at the moment. Please check back later!') }}</p>
                        </div>
                    @endif
                </div>

                <div class="text-center mt-4">
                    <p class="text-muted">{{ __('We will notify you via email once your account has been approved.') }}</p>
                    <p class="text-muted">{{ __('If you have any questions, please contact our support team.') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 