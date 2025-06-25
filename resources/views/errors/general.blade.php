@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="text-center text-danger">
                        <i class="fas fa-exclamation-triangle"></i> 
                        @if($statusCode == 500)
                            Internal Server Error
                        @elseif($statusCode == 403)
                            Access Forbidden
                        @elseif($statusCode == 401)
                            Unauthorized Access
                        @else
                            Error {{ $statusCode }}
                        @endif
                    </h4>
                </div>
                <div class="card-body text-center">
                    <div class="mb-4">
                        <i class="fas fa-bug fa-5x text-muted"></i>
                    </div>
                    <h5 class="text-muted mb-3">{{ $message ?? 'An unexpected error occurred.' }}</h5>
                    <p class="text-muted mb-4">
                        We're sorry, but something went wrong. Our team has been notified and is working to fix the issue.
                    </p>
                    <div class="d-flex justify-content-center gap-2">
                        <a href="{{ url()->previous() }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Go Back
                        </a>
                        <a href="{{ route('dashboard') }}" class="btn btn-primary">
                            <i class="fas fa-home"></i> Go to Dashboard
                        </a>
                        <button onclick="location.reload()" class="btn btn-outline-primary">
                            <i class="fas fa-redo"></i> Try Again
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 