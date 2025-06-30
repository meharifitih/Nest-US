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
                            <span class="input-group-text">+251</span>
                            <input type="text" class="form-control" id="phone_number" name="phone_number" maxlength="9" pattern="[79][0-9]{8}" value="{{ preg_replace('/^\\+251/', '', $user->phone_number) }}" placeholder="912345678" required />
                        </div>
                        <small class="text-muted">Phone will be saved as +251XXXXXXXXX. Enter 9 digits starting with 9 or 7.</small>
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
<script>
setTimeout(function() {
    const phoneInput = document.getElementById('phone_number');
    const phoneError = document.getElementById('phone_error');
    if (phoneInput && phoneError) {
        phoneInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 0) {
                const firstDigit = value.charAt(0);
                if (firstDigit !== '9' && firstDigit !== '7') {
                    phoneError.textContent = 'Phone number must start with 9 or 7';
                    phoneError.style.display = 'block';
                } else {
                    phoneError.style.display = 'none';
                }
            }
            if (value.length > 9) {
                value = value.slice(0, 9);
            }
            e.target.value = value;
        });
    }
}, 300);
</script>
@endsection
