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
                            <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#unifiedExcelUploadModal">
                                <i class="ti ti-upload align-text-bottom"></i> Upload Units & Tenants Excel
                            </button>
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
                    <div class="modal fade" id="unifiedExcelUploadModal" tabindex="-1" aria-labelledby="unifiedExcelUploadModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="unifiedExcelUploadModalLabel">{{ __('Upload Units & Tenants Excel') }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form id="unifiedExcelUploadForm" method="POST" action="{{ route('unified-excel-upload.upload') }}" enctype="multipart/form-data">
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
                                            <label for="excel_file" class="form-label">{{ __('Excel File') }}</label>
                                            <input type="file" class="form-control" id="excel_file" name="excel_file" accept=".xlsx,.xls,.csv" required>
                                            <small class="form-text text-muted">{{ __('Supported formats: XLSX, XLS, CSV. Maximum file size: 2MB. Units and tenants should be in the same sheet.') }}</small>
                                        </div>
                                        
                                        <!-- Upload Progress Indicator -->
                                        <div id="uploadProgress" class="d-none">
                                            <div class="alert alert-info">
                                                <div class="d-flex align-items-center">
                                                    <div class="spinner-border spinner-border-sm me-2" role="status">
                                                        <span class="visually-hidden">Loading...</span>
                                                    </div>
                                                    <span id="uploadStatus">{{ __('Uploading file...') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <a href="{{ route('unified-excel-upload.template') }}" class="btn btn-secondary">
                                            <i class="material-icons-two-tone me-2">download</i> {{ __('Download Sample Excel') }}
                                        </a>
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                                        <button type="submit" class="btn btn-primary">{{ __('Upload') }}</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const form = document.getElementById('unifiedExcelUploadForm');
                            if (form) {
                                form.onsubmit = function(e) {
                                    e.preventDefault();
                                    
                                    // Get form data
                                    const formData = new FormData(form);
                                    const submitBtn = form.querySelector('button[type="submit"]');
                                    const originalText = submitBtn.innerHTML;
                                    
                                    // Validate form
                                    const propertySelect = form.querySelector('#property_id');
                                    const fileInput = form.querySelector('#excel_file');
                                    
                                    if (!propertySelect.value) {
                                        toastrs('error', '{{ __("Please select a property.") }}', 'error');
                                        return;
                                    }
                                    
                                    if (!fileInput.files[0]) {
                                        toastrs('error', '{{ __("Please select an Excel file.") }}', 'error');
                                        return;
                                    }
                                    
                                    // Disable submit button and show loading
                                    submitBtn.disabled = true;
                                    submitBtn.innerHTML = '<i class="ti ti-loader ti-spin me-2"></i>{{ __("Uploading...") }}';
                                    
                                    // Show progress indicator
                                    const progressDiv = document.getElementById('uploadProgress');
                                    const statusSpan = document.getElementById('uploadStatus');
                                    progressDiv.classList.remove('d-none');
                                    statusSpan.textContent = '{{ __("Uploading file...") }}';
                                    
                                    // Show upload started notification
                                    toastrs('info', '{{ __("Upload started. Please wait while we process your file...") }}', 'info');
                                    
                                    console.log('Submitting form to:', '{{ route("unified-excel-upload.upload") }}');
                                    console.log('File:', fileInput.files[0].name);
                                    console.log('Property ID:', propertySelect.value);
                                    
                                    fetch('{{ route("unified-excel-upload.upload") }}', {
                                        method: 'POST',
                                        body: formData,
                                        headers: {
                                            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                                            'Accept': 'application/json'
                                        }
                                    })
                                    .then(response => {
                                        console.log('Response status:', response.status);
                                        
                                        if (response.redirected) {
                                            // Handle redirect (success case)
                                            console.log('Redirecting to:', response.url);
                                            window.location.href = response.url;
                                        } else {
                                            return response.json().catch(() => response.text());
                                        }
                                    })
                                    .then(data => {
                                        console.log('Response data:', data);
                                        
                                        // Hide progress indicator
                                        progressDiv.classList.add('d-none');
                                        
                                        if (typeof data === 'object' && data !== null) {
                                            // JSON response
                                            if (data.success) {
                                                let message = data.message || '{{ __("Upload successful!") }}';
                                                
                                                if (data.imported_count > 0) {
                                                    message += ` {{ __("Successfully imported") }} ${data.imported_count} {{ __("records") }}.`;
                                                }
                                                
                                                if (data.error_count > 0) {
                                                    message += ` {{ __("Failed to import") }} ${data.error_count} {{ __("records") }}.`;
                                                }
                                                
                                                // Show success notification
                                                toastrs('success', message, 'success');
                                                
                                                // Show detailed errors if any
                                                if (data.errors && data.errors.length > 0) {
                                                    setTimeout(() => {
                                                        toastrs('warning', '{{ __("Some records had errors. Check the upload history for details.") }}', 'warning');
                                                    }, 1500);
                                                }
                                                
                                                // Close modal immediately
                                                const modal = bootstrap.Modal.getInstance(document.getElementById('unifiedExcelUploadModal'));
                                                if (modal) {
                                                    modal.hide();
                                                }
                                                
                                                // Reset form
                                                form.reset();
                                                
                                                // Reload page immediately after success
                                                setTimeout(() => {
                                                    console.log('Refreshing page after successful upload...');
                                                    window.location.reload();
                                                }, 1000);
                                            } else {
                                                // Error response
                                                let errorMessage = data.message || '{{ __("Upload failed. Please check your file and try again.") }}';
                                                
                                                if (data.errors && data.errors.length > 0) {
                                                    // Show first few errors in notification
                                                    const firstErrors = data.errors.slice(0, 2);
                                                    errorMessage += '\n\n' + firstErrors.join('\n');
                                                    if (data.errors.length > 2) {
                                                        errorMessage += '\n... and ' + (data.errors.length - 2) + ' more errors';
                                                    }
                                                }
                                                
                                                // Show error notification
                                                toastrs('error', errorMessage, 'error');
                                                
                                                // Re-enable submit button
                                                submitBtn.disabled = false;
                                                submitBtn.innerHTML = originalText;
                                            }
                                        } else {
                                            // Text response (fallback)
                                            if (data.includes('success')) {
                                                toastrs('success', '{{ __("Upload successful! Units and Tenants have been imported.") }}', 'success');
                                                
                                                // Close modal immediately
                                                const modal = bootstrap.Modal.getInstance(document.getElementById('unifiedExcelUploadModal'));
                                                if (modal) {
                                                    modal.hide();
                                                }
                                                
                                                // Reset form
                                                form.reset();
                                                
                                                // Reload page immediately after success
                                                setTimeout(() => {
                                                    console.log('Refreshing page after successful upload (fallback)...');
                                                    window.location.reload();
                                                }, 1000);
                                            } else if (data.includes('error') || data.includes('failed') || data.includes('Error')) {
                                                // Extract error message from response
                                                let errorMessage = '{{ __("Upload failed. Please check your file and try again.") }}';
                                                
                                                // Try to extract specific error message
                                                const errorMatch = data.match(/<div class="alert alert-danger">(.*?)<\/div>/s);
                                                if (errorMatch) {
                                                    errorMessage = errorMatch[1].replace(/<[^>]*>/g, '').trim();
                                                }
                                                
                                                toastrs('error', errorMessage, 'error');
                                                
                                                // Re-enable submit button
                                                submitBtn.disabled = false;
                                                submitBtn.innerHTML = originalText;
                                            } else {
                                                console.log('No success/error detected, reloading page');
                                                window.location.reload();
                                            }
                                        }
                                    })
                                    .catch(error => {
                                        console.error('Upload error:', error);
                                        
                                        // Hide progress indicator
                                        progressDiv.classList.add('d-none');
                                        
                                        toastrs('error', '{{ __("Upload failed. Please try again.") }}', 'error');
                                        
                                        // Re-enable submit button
                                        submitBtn.disabled = false;
                                        submitBtn.innerHTML = originalText;
                                    });
                                };
                            }
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
