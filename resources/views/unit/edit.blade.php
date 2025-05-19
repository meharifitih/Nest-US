{{Form::model($unit, array('route' => array('unit.update', $property_id,$unit->id), 'method' => 'PUT')) }}

<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-12">
            {{Form::label('name',__('Unit Name/Number'),array('class'=>'form-label'))}}
            {{Form::text('name',null,array('class'=>'form-control','placeholder'=>__('Enter unit name/number')))}}
        </div>
        <div class="form-group col-md-6">
            {{Form::label('bedroom',__('Bedroom'),array('class'=>'form-label'))}}
            {{Form::number('bedroom',null,array('class'=>'form-control','placeholder'=>__('Enter number of bedroom')))}}
        </div>
        <div class="form-group col-md-6">
            {{Form::label('baths',__('Bathroom'),array('class'=>'form-label'))}}
            {{Form::number('baths',null,array('class'=>'form-control','placeholder'=>__('Enter number of bathroom')))}}
        </div>
        <div class="form-group col-md-6">
            {{Form::label('rent',__('Rent Price'),array('class'=>'form-label'))}}
            {{Form::number('rent',null,array('class'=>'form-control','placeholder'=>__('Enter rent price')))}}
        </div>
        <div class="form-group col-md-6">
            {{Form::label('rent_type',__('Rent Type'),array('class'=>'form-label'))}}
            {{Form::select('rent_type',$rentTypes,null,array('class'=>'form-control hidesearch','id'=>'rent_type'))}}
        </div>
        <div class="form-group col-md-6">
            {{Form::label('start_date',__('Rent Start Date'),array('class'=>'form-label'))}}
            {{Form::date('start_date',null,array('class'=>'form-control'))}}
        </div>
        <div class="form-group col-md-6">
            {{Form::label('end_date',__('Rent End Date'),array('class'=>'form-label'))}}
            {{Form::date('end_date',null,array('class'=>'form-control'))}}
        </div>
        <div class="form-group col-md-12">
            {{Form::label('notes',__('Notes'),array('class'=>'form-label'))}}
            {{Form::textarea('notes',null,array('class'=>'form-control','rows'=>'3'))}}
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="Cancel" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Update')}}" class="btn btn-primary">
</div>
{{Form::close()}}
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

