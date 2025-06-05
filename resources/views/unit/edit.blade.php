@extends('layouts.app')
@section('page-title')
    {{ __('Edit Unit') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('unit.index') }}">{{ __('Units') }}</a></li>
    <li class="breadcrumb-item active">{{ __('Edit') }}</li>
@endsection
@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">{{ __('Edit Unit') }}</h5>
            </div>
            <div class="card-body">
                {{Form::model($unit, array('route' => array('unit.update', $property_id, $unit->id), 'method' => 'PUT')) }}
                <div class="row">
                    <div class="form-group col-md-12">
                        {{Form::label('name',__('Unit Name/Number'),array('class'=>'form-label'))}}
                        {{Form::text('name', old('name', $unit->name), array('class'=>'form-control','placeholder'=>__('Enter unit name/number')))}}
                    </div>
                    <div class="form-group col-md-6">
                        {{Form::label('bedroom',__('Bedroom'),array('class'=>'form-label'))}}
                        {{Form::number('bedroom', old('bedroom', $unit->bedroom), array('class'=>'form-control','placeholder'=>__('Enter number of bedroom')))}}
                    </div>
                    <div class="form-group col-md-6">
                        {{Form::label('baths',__('Bathroom'),array('class'=>'form-label'))}}
                        {{Form::number('baths', old('baths', $unit->baths), array('class'=>'form-control','placeholder'=>__('Enter number of bathroom')))}}
                    </div>
                    <div class="form-group col-md-6">
                        {{Form::label('rent',__('Rent Price'),array('class'=>'form-label'))}}
                        {{Form::number('rent', old('rent', $unit->rent), array('class'=>'form-control','placeholder'=>__('Enter rent price')))}}
                    </div>
                    <div class="form-group col-md-6">
                        {{Form::label('rent_type',__('Rent Type'),array('class'=>'form-label'))}}
                        {{Form::select('rent_type', $rentTypes, old('rent_type', $unit->rent_type), array('class'=>'form-control hidesearch','id'=>'rent_type'))}}
                    </div>
                    <div class="form-group col-md-6">
                        {{Form::label('start_date',__('Rent Start Date'),array('class'=>'form-label'))}}
                        {{Form::date('start_date', old('start_date', $unit->start_date), array('class'=>'form-control'))}}
                    </div>
                    <div class="form-group col-md-6">
                        {{Form::label('end_date',__('Rent End Date'),array('class'=>'form-label'))}}
                        {{Form::date('end_date', old('end_date', $unit->end_date), array('class'=>'form-control'))}}
                    </div>
                    <div class="form-group col-md-12">
                        {{Form::label('notes',__('Notes'),array('class'=>'form-label'))}}
                        {{Form::textarea('notes', old('notes', $unit->notes), array('class'=>'form-control','rows'=>'3'))}}
                    </div>
                </div>
                <div class="text-end mt-3">
                    <button type="submit" class="btn btn-primary">{{__('Update')}}</button>
                </div>
                {{Form::close()}}
            </div>
        </div>
    </div>
</div>
@endsection
<script>
    $('#rent_type').on('change', function() {
        "use strict";
        var type=this.value;
        $('.rent_type').addClass('d-none')
        $('.'+type).removeClass('d-none')

        var input1= $('.rent_type').find('input');
        input1.prop('disabled', true);
        var input2= $('.'+type).find('input');
        input2.prop('disabled', false);
    });
    $('#rent_type').trigger('change');
</script>

