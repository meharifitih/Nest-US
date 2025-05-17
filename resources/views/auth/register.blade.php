@extends('layouts.auth')
@php
    $settings = settings();
@endphp
@section('tab-title')
    {{ __('Register') }}
@endsection
@push('script-page')
    @if ($settings['google_recaptcha'] == 'on')
        {!! NoCaptcha::renderJs() !!}
    @endif
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const phoneInput = document.getElementById('phone_number');
            const phoneError = document.getElementById('phone_error');
            
            phoneInput.addEventListener('input', function(e) {
                let value = e.target.value;
                // Remove any non-digit characters
                value = value.replace(/\D/g, '');
                
                // Check if number starts with 9 (Ethio Telecom) or 7 (Safaricom)
                if (value.length > 0) {
                    const firstDigit = value.charAt(0);
                    if (firstDigit !== '9' && firstDigit !== '7') {
                        phoneError.textContent = 'Phone number must start with 9 (Ethio Telecom) or 7 (Safaricom)';
                        phoneError.style.display = 'block';
                    } else {
                        phoneError.style.display = 'none';
                    }
                }
                
                // Limit to 9 digits
                if (value.length > 9) {
                    value = value.slice(0, 9);
                }
                
                e.target.value = value;
            });
        });
    </script>
@endpush
@section('content')
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="d-flex justify-content-center">
                    <div class="auth-header">
                        <h2 class="text-secondary"><b>{{ __('Sign up') }} </b></h2>
                        <p class="f-16 mt-2">{{ __('Enter your details and create account') }}</p>
                    </div>
                </div>
            </div>

            {{ Form::open(['route' => 'register', 'method' => 'post', 'id' => 'register-Form']) }}
            @if (session('error'))
                <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
            @endif
            @if (session('success'))
                <div class="alert alert-success" role="alert">{{ session('success') }}</div>
            @endif
            <div class="form-floating mb-3">
                <input type="text" class="form-control" id="name" name="name"
                    placeholder="{{ __('Name') }}" required />
                <label for="name">{{ __('Name') }}</label>
                @error('name')
                    <span class="invalid-name text-danger" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
            <input type="hidden" name="type" value="owner" />
            <div class="form-floating mb-3">
                <input type="email" class="form-control" id="email" name="email"
                    placeholder="{{ __('Email address') }}" required />
                <label for="email">{{ __('Email address') }}</label>
                @error('email')
                    <span class="invalid-email text-danger" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
            <div class="form-floating mb-3">
                <input type="text" class="form-control" id="fayda_id" name="fayda_id"
                    placeholder="{{ __('Fayda ID') }}" required />
                <label for="fayda_id">{{ __('Fayda ID') }}</label>
                @error('fayda_id')
                    <span class="invalid-fayda_id text-danger" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
            <div class="form-floating mb-3">
                <div class="input-group">
                    <span class="input-group-text">+251</span>
                    <input type="text" class="form-control" id="phone_number" name="phone_number"
                        placeholder="{{ __('Phone Number (e.g., 912345678)') }}" required />
                </div>
                <small class="text-muted">Enter number starting with 9 (Ethio Telecom) or 7 (Safaricom)</small>
                <span id="phone_error" class="text-danger" style="display: none;"></span>
                @error('phone_number')
                    <span class="invalid-phone_number text-danger" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
            <div class="form-floating mb-3">
                <input type="password" class="form-control" id="password" name="password"
                    placeholder="{{ __('Password') }}" required />
                <label for="password">{{ __('Password') }}</label>
                @error('password')
                    <span class="invalid-password text-danger" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
            <div class="form-floating mb-3">
                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation"
                    placeholder="{{ __('Confirm Password') }}" required />
                <label for="password_confirmation">{{ __('Confirm Password') }}</label>
                @error('password_confirmation')
                    <span class="invalid-password_confirmation text-danger" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
            <div class="form-check mt-3">
                <input class="form-check-input input-primary" type="checkbox" id="agree" name="agree" required />
                <label class="form-check-label" for="agree">
                    <span class="h5 mb-0">
                        {{ __('Agree with') }}
                        <span><a
                                href="{{ !empty($menu->slug) ? route('page', $menu->slug) : '#' }}">{{ __('Terms and conditions') }}</a>.</span>
                    </span>
                </label>
            </div>
            @if ($settings['google_recaptcha'] == 'on')
                <div class="form-group">
                    <label for="email" class="form-label"></label>
                    {!! NoCaptcha::display() !!}
                    @error('g-recaptcha-response')
                        <span class="small text-danger" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            @endif
            <div class="d-grid mt-4">
                <button type="submit" class="btn btn-secondary p-2">{{ __('Sign Up') }}</button>
            </div>
            <hr />
            <h5 class="d-flex justify-content-center">{{ __('Already have an account?') }} <a class="ms-1 text-secondary"
                    href="{{ route('login') }}">{{ __('Login in here') }}</a>
            </h5>
            {{ Form::close() }}
        </div>
    </div>
@endsection
