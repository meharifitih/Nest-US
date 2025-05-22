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
                var unit = `<select class="form-control" id="unit_id" name="unit_id"><option value="">{{ __('Select Unit') }}</option>`;
                $.each(data, function(key, value) {
                    unit += `<option value="${key}">${value}</option>`;
                });
                unit += '</select>';
                $('.unit_div').html(unit);
                $('#tenant_name').val('');
            }
        });
    });
    $(document).on('change', '#unit_id', function() {
        var unit_id = $(this).val();
        if(unit_id) {
            $.ajax({
                url: '/hoa/unit/' + unit_id + '/tenant',
                type: 'GET',
                success: function(data) {
                    $('#tenant_name').val(data.tenant ? data.tenant : '');
                }
            });
        } else {
            $('#tenant_name').val('');
        }
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
                                    <select class="form-control" id="unit_id" name="unit_id">
                                        <option value="">{{ __('Select Unit') }}</option>
                                        @if(!empty($units))
                                            @foreach($units as $id => $name)
                                                <option value="{{ $id }}">{{ $name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                @error('unit_id')
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