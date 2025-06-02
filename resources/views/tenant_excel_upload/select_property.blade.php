@extends('layouts.app')
@section('page-title')
    {{ __('Select Property for Tenant Excel Upload') }}
@endsection
@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>{{ __('Select Property') }}</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="" id="selectPropertyForm">
                    <div class="form-group mb-3">
                        <label for="property_id">{{ __('Property') }}</label>
                        <select class="form-control" id="property_id" name="property_id" required>
                            <option value="">{{ __('Select Property') }}</option>
                            @foreach($properties as $property)
                                <option value="{{ $property->id }}">{{ $property->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">{{ __('Next') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
document.getElementById('selectPropertyForm').onsubmit = function(e) {
    e.preventDefault();
    var propertyId = document.getElementById('property_id').value;
    if(propertyId) {
        window.location.href = '/tenant-excel-upload/' + propertyId;
    }
};
</script>
@endsection 