{{ Form::model($subscription, array('route' => array('subscriptions.update', $subscription->id), 'method' => 'PUT')) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group">
            {{Form::label('title',__('Title'),array('class'=>'form-label'))}}
            {{Form::text('title',null,array('class'=>'form-control','placeholder'=>__('Enter subscription title'),'required'=>'required'))}}
        </div>
        <div class="form-group">
            {{ Form::label('interval', __('Interval'),array('class'=>'form-label')) }}
            {!! Form::select('interval', $intervals, null,array('class' => 'form-control hidesearch','required'=>'required')) !!}
        </div>
        <div class="form-group">
            {{Form::label('package_amount',__('Package Amount'),array('class'=>'form-label'))}}
            {{Form::number('package_amount',null,array('class'=>'form-control','placeholder'=>__('Enter package amount'),'step'=>'0.01'))}}
        </div>
        <div class="form-group">
            {{Form::label('user_limit',__('User Limit'),array('class'=>'form-label'))}}
            {{Form::number('user_limit',null,array('class'=>'form-control','placeholder'=>__('Enter user limit'),'required'=>'required'))}}
        </div>
        <div class="form-group">
            {{Form::label('property_limit',__('Property Limit'),array('class'=>'form-label'))}}
            {{Form::number('property_limit',null,array('class'=>'form-control','placeholder'=>__('Enter property limit'),'required'=>'required'))}}
        </div>
        <div class="form-group">
            {{Form::label('tenant_limit',__('Tenant Limit'),array('class'=>'form-label'))}}
            {{Form::number('tenant_limit',null,array('class'=>'form-control','placeholder'=>__('Enter tenant limit'),'required'=>'required'))}}
        </div>
        <div class="form-group">
            {{Form::label('min_units',__('Minimum Units'),array('class'=>'form-label'))}}
            {{Form::number('min_units',null,array('class'=>'form-control','placeholder'=>__('Enter minimum units'),'required'=>'required'))}}
        </div>
        <div class="form-group">
            {{Form::label('max_units',__('Maximum Units'),array('class'=>'form-label'))}}
            {{Form::number('max_units',null,array('class'=>'form-control','placeholder'=>__('Enter maximum units (0 for unlimited)'),'required'=>'required'))}}
        </div>
        <div class="form-group">
            {{Form::label('enabled_logged_history',__('Enable Logged History'),array('class'=>'form-label'))}}
            {{Form::checkbox('enabled_logged_history',1,$subscription->enabled_logged_history,array('class'=>'form-check-input'))}}
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{__('Close')}}</button>
    {{Form::submit(__('Update'),array('class'=>'btn btn-primary'))}}
</div>
{{ Form::close() }}


