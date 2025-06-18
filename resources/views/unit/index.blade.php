@extends('layouts.app')
@section('page-title')
    {{ __('Units') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item" aria-current="page"> {{ __('Units') }}</li>
@endsection


@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card table-card">
                <div class="card-header">
                    <div class="row align-items-center g-2">
                        <div class="col">
                            <h5>{{ __('Unit List') }}</h5>
                        </div>
                        <div class="col-auto d-flex gap-2">
                            <button type="button" class="btn btn-primary px-3" data-bs-toggle="modal" data-bs-target="#filterModal">
                                <i class="ti ti-filter me-1"></i> {{ __('Filter') }}
                            </button>
                            @if (Gate::check('create unit'))
                                <a href="{{ route('unit.direct-create') }}" class="btn btn-secondary">
                                    <i class="ti ti-circle-plus align-text-bottom"></i> {{ __('Create Unit') }}
                                </a>
                            @endif
                            <a href="{{ route('unit-excel-upload.form') }}" class="btn btn-secondary">
                                <i class="ti ti-upload align-text-bottom"></i> {{ __('Unit Excel Upload') }}
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <!-- Filter Modal -->
                    <div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="filterModalLabel">{{ __('Filter Units') }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form method="GET" action="{{ route('unit.index') }}">
                                    <div class="modal-body">
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    {{ Form::label('property_id', __('Property'), ['class' => 'form-label']) }}
                                                    <select name="property_id" class="form-select">
                                                        <option value="">{{ __('All') }}</option>
                                                        @foreach($properties as $property)
                                                            <option value="{{ $property->id }}" {{ request('property_id') == $property->id ? 'selected' : '' }}>
                                                                {{ $property->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    {{ Form::label('rent_type', __('Rent Type'), ['class' => 'form-label']) }}
                                                    <select name="rent_type" class="form-select">
                                                        <option value="">{{ __('All') }}</option>
                                                        @foreach(\App\Models\PropertyUnit::$rentTypes as $key => $label)
                                                            <option value="{{ $key }}" {{ request('rent_type') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    {{ Form::label('rent', __('Rent'), ['class' => 'form-label']) }}
                                                    {{ Form::number('rent', request('rent'), ['class' => 'form-control', 'placeholder' => __('Enter Rent')]) }}
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    {{ Form::label('start_date', __('Rent Start Date'), ['class' => 'form-label']) }}
                                                    {{ Form::date('start_date', request('start_date'), ['class' => 'form-control']) }}
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    {{ Form::label('end_date', __('Rent End Date'), ['class' => 'form-label']) }}
                                                    {{ Form::date('end_date', request('end_date'), ['class' => 'form-control']) }}
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    {{ Form::label('tenant', __('Tenant'), ['class' => 'form-label']) }}
                                                    <select name="tenant" class="form-select">
                                                        <option value="">{{ __('All') }}</option>
                                                        @foreach($tenants as $tenant)
                                                            <option value="{{ $tenant->user_id }}" {{ request('tenant') == $tenant->user_id ? 'selected' : '' }}>
                                                                {{ $tenant->user ? $tenant->user->name : '-' }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-primary px-4">{{ __('Apply Filter') }}</button>
                                        <a href="{{ route('unit.index') }}" class="btn btn-light px-4">{{ __('Reset') }}</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <!-- Unit Excel Upload Modal -->
                    <div class="modal fade" id="unitExcelUploadModal" tabindex="-1" aria-labelledby="unitExcelUploadModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="unitExcelUploadModalLabel">{{ __('Upload Unit Excel') }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form id="unitExcelUploadForm" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label for="property_id" class="form-label">{{ __('Select Property') }}</label>
                                            <select class="form-control" id="property_id" name="property_id" required>
                                                <option value="">{{ __('Select Property') }}</option>
                                                @foreach($properties as $property)
                                                    <option value="{{ $property->id }}">{{ $property->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="unit_excel_file" class="form-label">{{ __('Excel File') }}</label>
                                            <input type="file" class="form-control" id="unit_excel_file" name="excel_file" accept=".xlsx,.xls,.csv" required>
                                            <small class="form-text text-muted">{{ __('Supported formats: XLSX, XLS, CSV. Maximum file size: 2MB') }}</small>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                                        <button type="submit" class="btn btn-primary">{{ __('Upload') }}</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const form = document.getElementById('unitExcelUploadForm');
                            form.onsubmit = function(e) {
                                e.preventDefault();
                                const formData = new FormData(form);
                                const propertyId = document.getElementById('property_id').value;
                                if (!propertyId) {
                                    alert('Please select a property.');
                                    return;
                                }
                                fetch(`/property/${propertyId}/upload-unit-excel`, {
                                    method: 'POST',
                                    body: formData,
                                    headers: {
                                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                                    }
                                })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.status === 'success') {
                                        alert(data.msg);
                                        location.reload();
                                    } else {
                                        alert(data.msg);
                                    }
                                })
                                .catch(() => alert('Upload failed'));
                            };
                        });
                    </script>
                    <div class="dt-responsive table-responsive">
                        <table class="table table-hover advance-datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Rent Type') }}</th>
                                    <th>{{ __('Rent') }}</th>
                                    <th>{{ __('Rent Start Date') }}</th>
                                    <th>{{ __('Rent End Date') }}</th>
                                    <th>{{ __('Property') }}</th>
                                    <th>{{ __('Tenant') }}</th>
                                    @if (Gate::check('edit unit') || Gate::check('delete unit'))
                                        <th class="text-right">{{ __('Action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($units as $unit)
                                    <tr>
                                        <td>{{ $unit->name }} </td>
                                        <td>{{ $unit->rent_type }} </td>
                                        <td>{{ priceFormat($unit->rent) }} </td>
                                        <td>{{ $unit->start_date ? dateFormat($unit->start_date) : '-' }}</td>
                                        <td>{{ $unit->end_date ? dateFormat($unit->end_date) : '-' }}</td>
                                        <td>{{ !empty($unit->properties) ? $unit->properties->name : '-' }} </td>
                                        <td>{{ !empty($unit->tenants) && !empty($unit->tenants->user) ? $unit->tenants->user->name : '-' }}</td>
                                        @if (Gate::check('edit unit') || Gate::check('delete unit'))
                                            <td class="text-right">
                                                <div class="cart-action">
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['unit.destroy', [$unit->property_id, $unit->id]]]) !!}
                                                    @can('edit unit')
                                                        <a class="avtar avtar-xs btn-link-secondary text-secondary"
                                                            href="{{ route('unit.edit', [$unit->property_id, $unit->id]) }}"
                                                            data-bs-toggle="tooltip"
                                                            data-bs-original-title="{{ __('Edit') }}">
                                                            <i data-feather="edit"></i></a>
                                                    @endcan
                                                    @can('delete unit')
                                                        <a class="avtar avtar-xs btn-link-danger text-danger confirm_dialog" data-bs-toggle="tooltip"
                                                            data-bs-original-title="{{ __('Detete') }}" href="#"> <i
                                                                data-feather="trash-2"></i></a>
                                                    @endcan
                                                    {!! Form::close() !!}
                                                </div>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach

                            </tbody>

                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
