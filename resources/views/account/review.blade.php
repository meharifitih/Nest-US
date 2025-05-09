@extends('layouts.app')

@section('page-title')
    {{ __('Account Review') }}
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="text-center mb-4">
                    <h2 class="text-primary">{{ __('Your Account is Under Review') }}</h2>
                    <p class="text-muted">{{ __('Thank you for registering! Your account is currently being reviewed by our team. This process typically takes 1-2 business days.') }}</p>
                    <p class="text-muted">{{ __('While you wait, feel free to explore our tutorial videos below to get familiar with the system.') }}</p>
                </div>

                <div class="row">
                    @foreach($tutorialVideos as $video)
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title">{{ $video['title'] }}</h5>
                                <p class="card-text">{{ $video['description'] }}</p>
                                <div class="ratio ratio-16x9">
                                    <iframe src="{{ $video['url'] }}" title="{{ $video['title'] }}" allowfullscreen></iframe>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
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