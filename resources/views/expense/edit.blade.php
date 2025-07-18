@extends('layouts.app')
@section('page-title')
    {{ __('Edit Expense') }}
@endsection
@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8 col-md-10">
        <div class="card shadow border mb-4 mt-4">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0 fw-bold"><i class="ti ti-edit me-2"></i>{{ __('Edit Expense') }}</h4>
            </div>
            <div class="card-body">
                {{ Form::model($expense, ['route' => ['expense.update', $expense->id], 'method' => 'PUT', 'enctype' => 'multipart/form-data']) }}
                <div class="row g-3">
                    <div class="col-md-12">
                        <label for="title" class="form-label">{{ __('Expense Title') }}</label>
                        {{ Form::text('title', null, ['class' => 'form-control', 'placeholder' => __('Enter Expense Title'), 'id' => 'title']) }}
                    </div>
                    <div class="col-md-6">
                        <label for="expense_id" class="form-label">{{ __('Expense Number') }}</label>
                        <div class="input-group">
                            <span class="input-group-text">{{ expensePrefix() }}</span>
                            {{ Form::text('expense_id', null, ['class' => 'form-control', 'placeholder' => __('Enter Expense Number'), 'id' => 'expense_id']) }}
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="expense_type" class="form-label">{{ __('Expense Type') }}</label>
                        {{ Form::select('expense_type', $types, null, ['class' => 'form-select', 'id' => 'expense_type']) }}
                    </div>
                    <div class="col-md-6">
                        <label for="property_id" class="form-label">{{ __('Property') }}</label>
                        {{ Form::select('property_id', $property, null, ['class' => 'form-select', 'id' => 'property_id']) }}
                    </div>
                    <div class="col-md-6">
                        <label for="unit_id" class="form-label">{{ __('Unit') }}</label>
                        <input type="hidden" id="edit_unit" value="{{ $expense->unit_id }}">
                        <div class="unit_div">
                            <select class="form-select unit" id="unit_id" name="unit_id">
                                <option value="">{{ __('Select Unit') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="date" class="form-label">{{ __('Date') }}</label>
                        {{ Form::date('date', null, ['class' => 'form-control', 'id' => 'date']) }}
                    </div>
                    <div class="col-md-6">
                        <label for="amount" class="form-label">{{ __('Amount') }}</label>
                        {{ Form::number('amount', null, ['class' => 'form-control', 'placeholder' => __('Enter Expense Amount'), 'id' => 'amount']) }}
                    </div>
                    <div class="col-md-12">
                        <label for="receipt" class="form-label">{{ __('Receipt') }}</label>
                        {{ Form::file('receipt', ['class' => 'form-control', 'id' => 'receipt']) }}
                    </div>
                    <div class="col-md-12">
                        <label for="notes" class="form-label">{{ __('Notes') }}</label>
                        {{ Form::textarea('notes', null, ['class' => 'form-control', 'rows' => 3, 'id' => 'notes', 'placeholder' => __('Enter any notes')]) }}
                    </div>
                </div>
                <div class="mt-4 text-end">
                    <button type="submit" class="btn btn-primary px-4"><i class="ti ti-check me-1"></i> {{ __('Update') }}</button>
                    <a href="{{ route('expense.index') }}" class="btn btn-light ms-2">{{ __('Cancel') }}</a>
                </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>
@endsection
<script>
    $('#property_id').on('change', function () {
        "use strict";
        var property_id = $(this).val();
        var url = '{{ route("property.unit", ":id") }}';
        url = url.replace(':id', property_id);
        $.ajax({
            url: url,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                property_id: property_id,
            },
            contentType: false,
            processData: false,
            type: 'GET',
            success: function (data) {
                $('.unit').empty();
                var unit = `<select class="form-control hidesearch unit" id="unit_id" name="unit_id"></select>`;
                $('.unit_div').html(unit);

                $.each(data, function (key, value) {
                    var text = (typeof value === 'object' && value !== null && value.name) ? value.name : value;
                    var unit_id = $('#edit_unit').val();
                    if (key == unit_id) {
                        $('.unit').append('<option selected value="' + key + '">' + text + '</option>');
                    } else {
                        $('.unit').append('<option   value="' + key + '">' + text + '</option>');
                    }
                });
                $(".hidesearch").each(function() {
                    var basic_select = new Choices(this, {
                        searchEnabled: false,
                        removeItemButton: true,
                    });
                });
            },

        });
    });

    $('#property_id').trigger('change');
</script>



