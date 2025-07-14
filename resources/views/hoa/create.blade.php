@extends('layouts.app')
@section('page-title')
    {{ __('Create HOA') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('hoa.index') }}">{{ __('HOA') }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ __('Create') }}</li>
@endsection

@push('script-page')
<script>
    $('#property_id').on('change', function() {
        var property_id = $(this).val();
        var url = '{{ route('property.unit', ':id') }}';
        url = url.replace(':id', property_id);
        $.ajax({
            url: url,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: { property_id: property_id },
            type: 'GET',
            success: function(data) {
                var options = '<option value="all">For all building</option>';
                $.each(data, function(key, value) {
                    options += `<option value="${key}">${value.name}</option>`;
                });
                $('#main_unit_select').html(options);
                if ($('#main_unit_select')[0].choicesInstance) {
                    $('#main_unit_select')[0].choicesInstance.destroy();
                }
                $('#main_unit_select')[0].choicesInstance = new Choices($('#main_unit_select')[0], {
                    removeItemButton: true
                });
                $('#tenant_name').val('');
            }
        });
    });

    // On form submit, set hidden inputs for selected units
    $('#hoa_form').on('submit', function(e) {
        var $unitSelect = $('#main_unit_select');
        var selected = $unitSelect.val();
        
        // Remove any previous hidden inputs
        $("input[name='unit_ids[]']").remove();
        
        // Add a hidden input for each selected unit
        if (selected && selected.length) {
            selected.forEach(function(val) {
                $('<input>').attr({type: 'hidden', name: 'unit_ids[]'}).val(val).appendTo('#hoa_form');
            });
        }
        $unitSelect.removeAttr('name');
    });

    // Prevent double submit
    $('#hoa_form').on('submit', function(e) {
        var $submitBtn = $(this).find('button[type=submit], input[type=submit]');
        $submitBtn.prop('disabled', true);
        setTimeout(function() { $submitBtn.prop('disabled', false); }, 5000); // fallback re-enable after 5s
    });
</script>
@endpush

@section('content')
    <form action="{{ route('hoa.store') }}" method="POST" id="hoa_form">
        @csrf
        <div class="row mt-4">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="form-group col-md-6 col-lg-4">
                                <label for="property_id" class="form-label">{{ __('Property') }}</label>
                                <select name="property_id" id="property_id" class="form-control">
                                    <option value="">{{ __('Select Property') }}</option>
                                    @foreach($properties as $property)
                                        <option value="{{ $property->id }}">{{ $property->name }}</option>
                                    @endforeach
                                </select>
                                @error('property_id')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group col-md-6 col-lg-4">
                                <label for="unit_id" class="form-label">{{ __('Unit') }}</label>
                                <div class="unit_div">
                                    <select class="form-control unit-multiselect" id="main_unit_select" name="unit_ids[]" multiple>
                                        <option value="all">For all building</option>
                                    </select>
                                </div>
                                @error('unit_ids')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group col-md-6 col-lg-4">
                                <label for="tenant_name" class="form-label">{{ __('Tenant') }}</label>
                                <input type="text" id="tenant_name" class="form-control" value="" readonly>
                            </div>
                            <div class="form-group col-md-6 col-lg-4">
                                <label for="hoa_type_id" class="form-label">{{ __('HOA Type') }}</label>
                                <select name="hoa_type_id" id="hoa_type_id" class="form-control">
                                    <option value="">{{ __('Select Type') }}</option>
                                    @foreach($hoa_types as $id => $title)
                                        <option value="{{ $id }}">{{ $title }}</option>
                                    @endforeach
                                </select>
                                @error('hoa_type_id')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group col-md-6 col-lg-4">
                                <label for="amount" class="form-label">{{ __('Amount') }}</label>
                                <input type="number" step="0.01" name="amount" id="amount" class="form-control" required>
                                @error('amount')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group col-md-6 col-lg-4">
                                <label for="frequency" class="form-label">{{ __('Payment Frequency') }}</label>
                                <select name="frequency" id="frequency" class="form-control" required>
                                    <option value="monthly">{{ __('Monthly') }}</option>
                                    <option value="quarterly">{{ __('Quarterly (3 months)') }}</option>
                                    <option value="semi_annual">{{ __('Semi-Annual (6 months)') }}</option>
                                    <option value="annual">{{ __('Annual (12 months)') }}</option>
                                </select>
                                @error('frequency')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group col-md-6 col-lg-4">
                                <label for="due_date" class="form-label">{{ __('Due Date') }}</label>
                                <input type="date" name="due_date" id="due_date" class="form-control" required>
                                @error('due_date')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group col-12">
                                <label for="description" class="form-label">{{ __('Description') }}</label>
                                <textarea name="description" id="description" rows="2" class="form-control"></textarea>
                                @error('description')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-12">
                <div class="group-button text-end">
                    <button type="submit" class="btn btn-secondary btn-rounded">{{ __('Create HOA') }}</button>
                    <a href="{{ route('hoa.index') }}" class="btn btn-light">{{ __('Cancel') }}</a>
                </div>
            </div>
        </div>
    </form>
@endsection 