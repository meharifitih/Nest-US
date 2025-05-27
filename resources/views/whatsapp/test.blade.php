@extends('layouts.app')

@section('page-title')
    {{ __('WhatsApp Test') }}
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Send Test WhatsApp Message') }}</h4>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('whatsapp.test.send') }}">
                        @csrf
                        <div class="form-group">
                            <label for="phone">{{ __('Phone Number') }}</label>
                            <input type="text" class="form-control" id="phone" name="phone" 
                                placeholder="+1234567890" required>
                            <small class="form-text text-muted">
                                {{ __('Enter phone number in international format (e.g., +1234567890)') }}
                            </small>
                        </div>

                        <div class="form-group">
                            <label for="message">{{ __('Message') }}</label>
                            <textarea class="form-control" id="message" name="message" rows="4" required></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            {{ __('Send Message') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection 