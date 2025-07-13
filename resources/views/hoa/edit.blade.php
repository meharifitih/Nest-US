@extends('layouts.app')
@section('page-title')
    {{ __('Edit HOA') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('hoa.index') }}">{{ __('HOA') }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ __('Edit') }}</li>
@endsection

@section('content')
    <form action="{{ route('hoa.update', $hoa->id) }}" method="POST" id="hoa_form">
        @csrf
        @method('PUT')
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
                                        <option value="{{ $property->id }}" {{ $hoa->property_id == $property->id ? 'selected' : '' }}>{{ $property->name }}</option>
                                    @endforeach
                                </select>
                                @error('property_id')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group col-md-6 col-lg-4">
                                <label for="unit_id" class="form-label">{{ __('Unit') }}</label>
                                <select name="unit_id" id="unit_id" class="form-control">
                                    <option value="">{{ __('Select Unit') }}</option>
                                    @foreach($units as $id => $name)
                                        <option value="{{ $id }}" {{ $hoa->unit_id == $id ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                                @error('unit_id')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group col-md-6 col-lg-4">
                                <label for="tenant_name" class="form-label">{{ __('Tenant') }}</label>
                                <input type="text" id="tenant_name" class="form-control" value="{{ $hoa->unit && $hoa->unit->tenants && $hoa->unit->tenants->user ? $hoa->unit->tenants->user->name : '' }}" readonly>
                            </div>
                            <div class="form-group col-md-6 col-lg-4">
                                <label for="hoa_type_id" class="form-label">{{ __('HOA Type') }}</label>
                                <select name="hoa_type_id" id="hoa_type_id" class="form-control">
                                    <option value="">{{ __('Select Type') }}</option>
                                    @foreach($hoa_types as $id => $title)
                                        <option value="{{ $id }}" {{ $hoa->hoa_type_id == $id ? 'selected' : '' }}>{{ $title }}</option>
                                    @endforeach
                                </select>
                                @error('hoa_type_id')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group col-md-6 col-lg-4">
                                <label for="amount" class="form-label">{{ __('Amount') }}</label>
                                <input type="number" step="0.01" name="amount" id="amount" class="form-control" value="{{ $hoa->amount }}" required>
                                @error('amount')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group col-md-6 col-lg-4">
                                <label for="frequency" class="form-label">{{ __('Payment Frequency') }}</label>
                                <select name="frequency" id="frequency" class="form-control">
                                    <option value="monthly" {{ $hoa->frequency == 'monthly' ? 'selected' : '' }}>{{ __('Monthly') }}</option>
                                    <option value="quarterly" {{ $hoa->frequency == 'quarterly' ? 'selected' : '' }}>{{ __('Quarterly') }}</option>
                                    <option value="semi_annual" {{ $hoa->frequency == 'semi_annual' ? 'selected' : '' }}>{{ __('Semi-Annual') }}</option>
                                    <option value="annual" {{ $hoa->frequency == 'annual' ? 'selected' : '' }}>{{ __('Annual') }}</option>
                                </select>
                                @error('frequency')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group col-md-6 col-lg-4">
                                <label for="due_date" class="form-label">{{ __('Due Date') }}</label>
                                <input type="date" name="due_date" id="due_date" class="form-control" value="{{ $hoa->due_date ? $hoa->due_date->format('Y-m-d') : '' }}">
                                @error('due_date')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group col-md-12 col-lg-12">
                                <label for="description" class="form-label">{{ __('Description') }}</label>
                                <textarea name="description" id="description" class="form-control" rows="3">{{ $hoa->description }}</textarea>
                                @error('description')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-4 text-end">
                    <button type="submit" class="btn btn-primary px-4">{{ __('Update HOA') }}</button>
                    <a href="{{ route('hoa.index') }}" class="btn btn-light ms-2">{{ __('Cancel') }}</a>
                </div>
            </div>
        </div>
    </form>
@endsection 