{{ Form::model($user, array('route' => array('users.update', $user->id), 'method' => 'PUT')) }}
<div class="modal-body">
    <div class="row">
        @if(\Auth::user()->type != 'super admin')
            <div class="form-group col-md-6">
                {{ Form::label('role', __('Assign Role'),['class'=>'form-label']) }}
                {!! Form::select('role', $userRoles, !empty($user->roles)?$user->roles[0]->id:null,array('class' => 'form-control hidesearch ','required'=>'required')) !!}
            </div>
        @endif
            @if(\Auth::user()->type == 'super admin')
                <div class="form-group col-md-6">
                    {{Form::label('name',__('Name'),array('class'=>'form-label')) }}
                    {{Form::text('name',null,array('class'=>'form-control','placeholder'=>__('Enter Name'),'required'=>'required'))}}
                </div>
            @else
                <div class="form-group col-md-6">
                    {{Form::label('first_name',__('First Name'),array('class'=>'form-label')) }}
                    {{Form::text('first_name',null,array('class'=>'form-control','placeholder'=>__('Enter First Name'),'required'=>'required'))}}
                </div>
                <div class="form-group col-md-6">
                    {{Form::label('last_name',__('Last Name'),array('class'=>'form-label')) }}
                    {{Form::text('last_name',null,array('class'=>'form-control','placeholder'=>__('Enter Name'),'required'=>'required'))}}
                </div>
            @endif
        <div class="form-group col-md-6">
            {{Form::label('email',__('User Email'),array('class'=>'form-label'))}}
            {{Form::text('email',null,array('class'=>'form-control','placeholder'=>__('Enter User Email'),'required'=>'required'))}}
        </div>
        <div class="form-group col-md-6">
            {{Form::label('fayda_id',__('Fayda ID'),array('class'=>'form-label')) }}
            {{Form::text('fayda_id',$user->fayda_id,array('class'=>'form-control','placeholder'=>__('Enter Fayda ID'), 'required'=>'required'))}}
        </div>
        <div class="form-group col-md-6">
            {{Form::label('phone_number',__('User Phone Number'),array('class'=>'form-label')) }}
            <div class="input-group">
                <span class="input-group-text">+251</span>
                <input type="text" class="form-control" id="phone_number" name="phone_number" maxlength="9" pattern="[79][0-9]{8}" value="{{ (strlen($user->phone_number) === 12 && substr($user->phone_number, 0, 3) === '251') ? substr($user->phone_number, 3) : ($user->phone_number ?? '') }}" placeholder="Enter user phone number (e.g. 912345678)" required />
            </div>
            <small class="text-muted">Enter number starting with 9 (Ethio Telecom) or 7 (Safaricom)</small>
            <span id="phone_error" class="text-danger" style="display: none;"></span>
        </div>

    </div>
</div>
<div class="modal-footer">
    {{Form::submit(__('Update'),array('class'=>'btn btn-secondary btn-rounded'))}}
</div>
{{ Form::close() }}

@push('script-page')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const phoneInput = document.getElementById('phone_number');
    const phoneError = document.getElementById('phone_error');
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
});
</script>
@endpush
