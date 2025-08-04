{{Form::open(array('url'=>'users','method'=>'post'))}}
<div class="modal-body">
    <div class="row">
        @if(\Auth::user()->type != 'super admin')
            <div class="form-group col-md-6">
                {{ Form::label('role', __('Assign Role'),['class'=>'form-label']) }}
                {!! Form::select('role', $userRoles, null,array('class' => 'form-control basic-select','required'=>'required')) !!}
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
            {{Form::text('email',null,array('class'=>'form-control','placeholder'=>__('Enter user email'),'required'=>'required'))}}
        </div>
        <div class="form-group col-md-6">
            {{Form::label('password',__('User Password'),array('class'=>'form-label'))}}
            {{Form::password('password',array('class'=>'form-control','placeholder'=>__('Enter user password'),'required'=>'required','minlength'=>"6"))}}

        </div>
        <div class="form-group col-md-6">
            {{Form::label('phone_number',__('User Phone Number'),array('class'=>'form-label')) }}
            <div class="input-group">
                <span class="input-group-text">+1</span>
                <input type="text" class="form-control" id="phone_number" name="phone_number" placeholder="Enter user phone number (e.g. 555-123-4567)" />
            </div>
            <small class="text-muted">Enter US phone number (optional)</small>
            <span id="phone_error" class="text-danger" style="display: none;"></span>
        </div>

    </div>
</div>
<div class="modal-footer">
    {{Form::submit(__('Create'),array('class'=>'btn btn-secondary ml-10'))}}
</div>
{{Form::close()}}

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

