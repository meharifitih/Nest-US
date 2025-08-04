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
            
            // Initial validation for pre-filled values
            if (phoneInput && phoneError) {
                const initialValue = phoneInput.value;
                if (initialValue) {
                    const digitsOnly = initialValue.replace(/\D/g, '');
                    if (digitsOnly.length === 10) {
                        const areaCode = digitsOnly.substring(0, 3);
                        if (areaCode.charAt(0) >= '2' && areaCode.charAt(0) <= '9') {
                            phoneError.style.display = 'none';
                        } else {
                            phoneError.textContent = 'Enter a valid US phone number.';
                            phoneError.style.display = 'block';
                        }
                    }
                }
            }
            
            phoneInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                
                // Format as US phone number
                if (value.length > 0) {
                    if (value.length <= 3) {
                        value = value;
                    } else if (value.length <= 6) {
                        value = value.slice(0, 3) + '-' + value.slice(3);
                    } else {
                        value = value.slice(0, 3) + '-' + value.slice(3, 6) + '-' + value.slice(6, 10);
                    }
                }
                
                // Limit to 10 digits (excluding formatting)
                const digitsOnly = value.replace(/\D/g, '');
                if (digitsOnly.length > 10) {
                    value = value.slice(0, 12); // Account for dashes
                }
                
                e.target.value = value;
                
                // Validate US phone number format
                if (digitsOnly.length > 0) {
                    // Check if it's a valid 10-digit US phone number
                    if (digitsOnly.length === 10) {
                        const areaCode = digitsOnly.substring(0, 3);
                        const nextDigit = digitsOnly.substring(3, 4);
                        
                        // US phone number rules: area code must start with 2-9
                        if (areaCode.charAt(0) >= '2' && areaCode.charAt(0) <= '9') {
                            phoneError.style.display = 'none';
                        } else {
                            phoneError.textContent = 'Enter a valid US phone number.';
                            phoneError.style.display = 'block';
                        }
                    } else if (digitsOnly.length < 10) {
                        // Still typing, don't show error yet
                        phoneError.style.display = 'none';
                    } else {
                        phoneError.textContent = 'Enter a valid US phone number.';
                        phoneError.style.display = 'block';
                    }
                } else {
                    phoneError.style.display = 'none';
                }
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
                    placeholder="{{ __('Name') }}" value="{{ old('name') }}" required />
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
                    placeholder="{{ __('Email address') }}" value="{{ old('email') }}" required />
                <label for="email">{{ __('Email address') }}</label>
                @error('email')
                    <span class="invalid-email text-danger" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
            <div class="form-floating mb-3">
                <div class="input-group">
                    <input type="text" class="form-control" id="phone_number" name="phone_number"
                        placeholder="Phone Number (e.g., (555) 123-4567 or 555-123-4567)" value="{{ old('phone_number') }}" required />
                </div>
                <small class="text-muted">Enter a valid US phone number.</small>
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
                <small class="form-text text-muted">
                    Password must be at least 8 characters long and contain uppercase, lowercase, number, and special character (@$!%*?&).
                </small>
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
