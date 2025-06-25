@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="text-center text-danger">
                        <i class="fas fa-exclamation-triangle"></i> Page Not Found
                    </h4>
                </div>
                <div class="card-body text-center">
                    <div class="mb-4">
                        <i class="fas fa-search fa-5x text-muted"></i>
                    </div>
                    <h5 class="text-muted mb-3">{{ $message ?? 'The page you are looking for could not be found.' }}</h5>
                    <p class="text-muted mb-4">
                        The page might have been moved, deleted, or you entered the wrong URL.
                    </p>
                    <div class="d-flex justify-content-center gap-2">
                        <a href="{{ url()->previous() }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Go Back
                        </a>
                        <a href="{{ route('dashboard') }}" class="btn btn-primary">
                            <i class="fas fa-home"></i> Go to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 