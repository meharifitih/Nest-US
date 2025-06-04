@extends('layouts.app')
@section('page-title', __('Create Rent Invoice'))
@section('content')
    {{ Form::open(['url' => route('rent.store'), 'method' => 'post', 'id' => 'rent_invoice_form']) }}
    <div class="row mt-4">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">
                    <div class="info-group">
                        <div class="row">
                            <div class="form-group col-md-6 col-lg-4">
                                {{ Form::label('property_id', __('Property'), ['class' => 'form-label']) }}
                                {{ Form::select('property_id', $property, null, ['class' => 'form-control hidesearch', 'id' => 'property_id']) }}
                            </div>
                            <div class="form-group col-md-6 col-lg-4">
                                {{ Form::label('unit_id', __('Unit'), ['class' => 'form-label']) }}
                                <div class="unit_div">
                                    <select class="form-control hidesearch unit" id="unit" name="unit_id">
                                        <option value="">{{ __('Select Unit') }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group col-md-6 col-lg-4">
                                <div class="form-group">
                                    {{ Form::label('invoice_id', __('Invoice Number'), ['class' => 'form-label']) }}
                                    <div class="input-group">
                                        <span class="input-group-text ">
                                            {{ invoicePrefix() }}
                                        </span>
                                        {{ Form::text('invoice_id', $invoiceNumber, ['class' => 'form-control', 'placeholder' => __('Enter Invoice Number')]) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-4">
                            <div class="form-group col-md-6 col-lg-4">
                                {{ Form::label('invoice_month', __('Invoice Month'), ['class' => 'form-label']) }}
                                {{ Form::month('invoice_month', null, ['class' => 'form-control', 'required']) }}
                            </div>
                            <div class="form-group col-md-6 col-lg-4">
                                {{ Form::label('end_date', __('End Date'), ['class' => 'form-label']) }}
                                {{ Form::date('end_date', null, ['class' => 'form-control', 'required']) }}
                            </div>
                        </div>
                        <div class="row mt-4">
                            <div class="col-12">
                                <table class="table table-bordered" id="invoice-items-table">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Type') }}</th>
                                            <th>{{ __('Amount') }}</th>
                                            <th>{{ __('Description') }}</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody data-repeater-item>
                                        <tr>
                                            <td width="30%">
                                                <input type="text" class="form-control" name="types[0][invoice_type]" value="Rent" readonly>
                                            </td>
                                            <td>
                                                <input type="number" class="form-control" id="unit_rent_amount" name="types[0][amount]" readonly required>
                                            </td>
                                            <td>
                                                <input type="text" class="form-control" name="types[0][description]" value="" readonly>
                                            </td>
                                            <td></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-12">
            <div class="group-button text-end">
                {{ Form::submit(__('Create'), ['class' => 'btn btn-secondary btn-rounded', 'id' => 'rent-invoice-submit']) }}
            </div>
        </div>
    </div>
    {{ Form::close() }}
    @push('script-page')
    <script>
    $('#property_id').on('change', function() {
        var property_id = $(this).val();
        var url = "{{ route('property.unit', ':id') }}".replace(':id', property_id);
        $.ajax({
            url: url,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: { property_id: property_id },
            contentType: false,
            processData: false,
            type: 'GET',
            success: function(data) {
                var unit = `<select class=\"form-control hidesearch unit\" id=\"unit\" name=\"unit_id\"><option value=\"\">Select Unit</option>`;
                $.each(data, function(key, value) {
                    unit += '<option value="' + key + '">' + value.name + '</option>';
                });
                unit += '</select>';
                $('.unit_div').html(unit);
                $(".hidesearch").each(function() {
                    var basic_select = new Choices(this, {
                        searchEnabled: false,
                        removeItemButton: true,
                    });
                });
                // Store rent data for later use
                window.unitRentData = data;
            },
            error: function() {
                var unit = `<select class=\"form-control hidesearch unit\" id=\"unit\" name=\"unit_id\"></select>`;
                $('.unit_div').html(unit);
                window.unitRentData = {};
            }
        });
    });
    // Update rent field when unit changes
    $('#property_id, .unit_div').on('change', '#unit', function() {
        var unitId = $(this).val();
        if(window.unitRentData && unitId && window.unitRentData[unitId]) {
            $('#unit_rent_amount').val(window.unitRentData[unitId].rent);
        } else {
            $('#unit_rent_amount').val('');
        }
    });
    </script>
    @endpush
@endsection 