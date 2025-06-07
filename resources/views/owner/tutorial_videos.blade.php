@extends('layouts.app')
@section('page-title')
    {{ __('Tutorial Videos') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item active">{{ __('Tutorial Videos') }}</li>
@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h2 class="mb-4 text-primary">{{ __('Tutorial Videos') }}</h2>
                    <div class="row justify-content-center">
                        @if(count($tutorialVideos) > 0)
                            @foreach($tutorialVideos as $video)
                                @php
                                    $url = is_array($video) && isset($video['url']) ? $video['url'] : (is_string($video) ? $video : null);
                                    $title = is_array($video) && isset($video['title']) ? $video['title'] : __('Tutorial Video');
                                @endphp
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card h-100 shadow-sm border-0">
                                        <div class="card-body p-2 d-flex flex-column align-items-center">
                                            <div class="ratio ratio-16x9 w-100 mb-2" style="background:#f8f9fa;border-radius:8px;overflow:hidden;">
                                                <iframe src="{{ $url }}" allowfullscreen style="border-radius:8px;width:100%;height:100%;border:0;"></iframe>
                                            </div>
                                            <div class="w-100 text-center mt-2">
                                                <span class="fw-bold text-dark small">{{ $title }}</span>
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
                </div>
            </div>
        </div>
    </div>
@endsection 