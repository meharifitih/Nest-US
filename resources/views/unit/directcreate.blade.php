@extends('layouts.app')
@section('page-title')
    {{ __('Create Unit') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('unit.index') }}">{{ __('Units') }}</a></li>
    <li class="breadcrumb-item active">{{ __('Create') }}</li>
@endsection
@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">{{ __('Create Unit') }}</h5>
            </div>
            <div class="card-body">
                {{ Form::open(array('url' => 'unit/direct-store','method'=>'post','enctype' => "multipart/form-data")) }}
                <div class="row">
                    <div class="form-group col-md-12">
                        {{Form::label('property_id',__('Property'),array('class'=>'form-label'))}}
                        {{Form::select('property_id',$name,old('property_id'),array('class'=>'form-control hidesearch','required'=>true,'placeholder'=>__('Select Property')))}}
                    </div>
                    <div class="form-group col-md-12">
                        {{Form::label('name',__('Unit Name/Number'),array('class'=>'form-label'))}}
                        {{Form::text('name',old('name'),array('class'=>'form-control','placeholder'=>__('Enter unit name/number')))}}
                    </div>
                    <div class="form-group col-md-6">
                        {{Form::label('bedroom',__('Bedroom'),array('class'=>'form-label'))}}
                        {{Form::number('bedroom',old('bedroom'),array('class'=>'form-control','placeholder'=>__('Enter number of bedroom')))}}
                    </div>
                    <div class="form-group col-md-6">
                        {{Form::label('baths',__('Bathroom'),array('class'=>'form-label'))}}
                        {{Form::number('baths',old('baths'),array('class'=>'form-control','placeholder'=>__('Enter number of bathroom')))}}
                    </div>
                    <div class="form-group col-md-6">
                        {{Form::label('rent',__('Rent Price'),array('class'=>'form-label'))}}
                        {{Form::number('rent',old('rent'),array('class'=>'form-control','placeholder'=>__('Enter rent price')))}}
                    </div>
                    <div class="form-group col-md-6">
                        {{Form::label('rent_type',__('Rent Type'),array('class'=>'form-label'))}}
                        {{Form::select('rent_type',$rentTypes,old('rent_type'),array('class'=>'form-control hidesearch','id'=>'rent_type'))}}
                    </div>
                    <div class="form-group col-md-12">
                        {{Form::label('notes',__('Notes'),array('class'=>'form-label'))}}
                        {{Form::textarea('notes',old('notes'),array('class'=>'form-control','rows'=>'3'))}}
                    </div>
                </div>
                <div class="text-end mt-3">
                    <button type="submit" class="btn btn-primary">{{__('Create')}}</button>
                </div>
                {{ Form::close() }}
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
</script>
