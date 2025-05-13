@extends('layouts.app')

@section('page-title')
    {{ __('Account Review') }}
@endsection

@section('content')
{{-- Subscription selection comes first --}}
@php($subscriptions = \App\Models\Subscription::all())
@include('subscription.index')

@if(auth()->user()->approval_status == 'pending')
    <div class="alert alert-warning">
        {{ __('Your account is pending approval. Please wait for admin review.') }}
    </div>
    {{-- Show tutorial videos --}}
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