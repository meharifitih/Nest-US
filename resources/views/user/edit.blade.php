@extends('layouts.app')

@section('page-title')
    {{ __('Edit User') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('users.index') }}">{{ __('Users') }}</a></li>
    <li class="breadcrumb-item active">{{ __('Edit') }}</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">{{ __('Edit User') }}</h5>
            </div>
            <div class="card-body">
                {{ Form::model($user, array('route' => array('users.update', $user->id), 'method' => 'PUT')) }}
                <div class="row g-3">
                    @if(\Auth::user()->type != 'super admin')
                        <div class="col-md-6">
                            {{ Form::label('role', __('Assign Role'),['class'=>'form-label']) }}
                            {!! Form::select('role', $userRoles, !empty($user->roles)?$user->roles[0]->id:null,array('class' => 'form-select hidesearch','required'=>'required')) !!}
                        </div>
                    @endif
                    @if(\Auth::user()->type == 'super admin')
                        <div class="col-md-6">
                            {{Form::label('name',__('Name'),array('class'=>'form-label')) }}
                            {{Form::text('name',null,array('class'=>'form-control','placeholder'=>__('Enter Name'),'required'=>'required'))}}
                        </div>
                    @else
                        <div class="col-md-6">
                            {{Form::label('first_name',__('First Name'),array('class'=>'form-label')) }}
                            {{Form::text('first_name',null,array('class'=>'form-control','placeholder'=>__('Enter First Name'),'required'=>'required'))}}
                        </div>
                        <div class="col-md-6">
                            {{Form::label('last_name',__('Last Name'),array('class'=>'form-label')) }}
                            {{Form::text('last_name',null,array('class'=>'form-control','placeholder'=>__('Enter Name'),'required'=>'required'))}}
                        </div>
                    @endif
                    <div class="col-md-6">
                        {{Form::label('email',__('User Email'),array('class'=>'form-label'))}}
                        {{Form::text('email',null,array('class'=>'form-control','placeholder'=>__('Enter User Email'),'required'=>'required'))}}
                    </div>
                    <div class="col-md-6">
                        {{Form::label('phone_number',__('User Phone Number'),array('class'=>'form-label')) }}
                        <div class="input-group">
                            <span class="input-group-text">+1</span>
                            <input type="text" class="form-control" id="phone_number" name="phone_number" value="{{ preg_replace('/^\\+1/', '', $user->phone_number) }}" placeholder="Enter phone number (e.g. 555-123-4567)" />
                        </div>
                        <small class="text-muted">Enter US phone number (optional)</small>
                        <span id="phone_error" class="text-danger" style="display: none;"></span>
                    </div>
                </div>
                <div class="card-footer text-end mt-4">
                    {{Form::submit(__('Update'),array('class'=>'btn btn-secondary btn-rounded'))}}
                </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('script-page')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const phoneInput = document.getElementById('phone_number');
    const phoneError = document.getElementById('phone_error');
    
    if (phoneInput && phoneError) {
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
            const digitsOnly = value.replace(/\D/g, '');
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
    }
});
</script>
@endpush
