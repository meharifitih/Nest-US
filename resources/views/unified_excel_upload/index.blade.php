@extends('layouts.app')
@section('page-title')
    {{ __('Upload Units & Tenants Excel') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('unit.index') }}">{{ __('Units') }}</a></li>
    <li class="breadcrumb-item" aria-current="page">{{ __('Upload Excel') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <h5>{{ __('Upload Units & Tenants Excel') }}</h5>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif
                    
                    @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form id="unifiedExcelUploadForm" method="POST" action="{{ route('unified-excel-upload.upload') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="property_id" class="form-label">{{ __('Select Property') }} <span class="text-danger">*</span></label>
                                    <select class="form-control" id="property_id" name="property_id" required>
                                        <option value="">{{ __('Select Property') }}</option>
                                        @foreach($properties as $property)
                                            <option value="{{ $property->id }}">{{ $property->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="excel_file" class="form-label">{{ __('Excel File') }} <span class="text-danger">*</span></label>
                                    <input type="file" class="form-control" id="excel_file" name="excel_file" accept=".xlsx,.xls,.csv" required>
                                    <small class="form-text text-muted">{{ __('Supported formats: XLSX, XLS, CSV. Maximum file size: 2MB.') }}</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <h6>{{ __('Important Notes:') }}</h6>
                                    <ul class="mb-0">
                                        <li>{{ __('Units and tenants should be in the same Excel sheet.') }}</li>
                                        <li>{{ __('Each row represents one unit with its tenant information.') }}</li>
                                        <li>{{ __('Download the sample template to see the required format.') }}</li>
                                        <li>{{ __('All required fields must be filled.') }}</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-12">
                                <a href="{{ route('unified-excel-upload.template') }}" class="btn btn-secondary me-2">
                                    <i class="ti ti-download me-1"></i> {{ __('Download Sample Template') }}
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="ti ti-upload me-1"></i> {{ __('Upload Excel') }}
                                </button>
                                <a href="{{ route('unit.index') }}" class="btn btn-light ms-2">
                                    <i class="ti ti-arrow-left me-1"></i> {{ __('Back to Units') }}
                                </a>
                            </div>
                        </div>

                        <!-- Progress Indicator -->
                        <div id="uploadProgress" class="row mt-3 d-none">
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <div class="d-flex align-items-center">
                                        <div class="spinner-border spinner-border-sm me-2" role="status">
                                            <span class="visually-hidden">{{ __('Loading...') }}</span>
                                        </div>
                                        <span id="uploadStatus">{{ __('Uploading file...') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                    @if($uploads->count() > 0)
                        <div class="row mt-4">
                            <div class="col-12">
                                <h6>{{ __('Recent Uploads') }}</h6>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>{{ __('File Name') }}</th>
                                                <th>{{ __('Property') }}</th>
                                                <th>{{ __('Status') }}</th>
                                                <th>{{ __('Imported') }}</th>
                                                <th>{{ __('Errors') }}</th>
                                                <th>{{ __('Upload Date') }}</th>
                                                <th>{{ __('Error Log') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($uploads as $upload)
                                                <tr>
                                                    <td>{{ $upload->original_name }}</td>
                                                    <td>{{ $upload->property ? $upload->property->name : '-' }}</td>
                                                    <td>
                                                        @if($upload->status == 'completed')
                                                            <span class="badge bg-success">{{ __('Completed') }}</span>
                                                        @elseif($upload->status == 'failed')
                                                            <span class="badge bg-danger">{{ __('Failed') }}</span>
                                                        @else
                                                            <span class="badge bg-warning">{{ __('Pending') }}</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($upload->imported_count > 0)
                                                            <span class="badge bg-success">{{ $upload->imported_count }}</span>
                                                        @else
                                                            <span class="text-muted">0</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($upload->error_count > 0)
                                                            <span class="badge bg-danger">{{ $upload->error_count }}</span>
                                                        @else
                                                            <span class="text-muted">0</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $upload->created_at->format('Y-m-d H:i:s') }}</td>
                                                    <td>
                                                        @if($upload->error_log)
                                                            <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#errorModal{{ $upload->id }}">
                                                                {{ __('View Errors') }}
                                                            </button>
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        @foreach($uploads as $upload)
                            @if($upload->error_log)
                                <div class="modal fade" id="errorModal{{ $upload->id }}" tabindex="-1">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">{{ __('Error Log - ') }}{{ $upload->original_name }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <pre class="bg-light p-3">{{ $upload->error_log }}</pre>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('unifiedExcelUploadForm');
            if (form) {
                form.onsubmit = function(e) {
                    e.preventDefault();
                    
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
                    
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="ti ti-loader ti-spin me-1"></i>{{ __("Uploading...") }}';
                    
                    // Show progress indicator
                    const progressDiv = document.getElementById('uploadProgress');
                    const statusSpan = document.getElementById('uploadStatus');
                    progressDiv.classList.remove('d-none');
                    statusSpan.textContent = '{{ __("Uploading file...") }}';
                    
                    // Show upload started notification
                    toastrs('info', '{{ __("Upload started. Please wait while we process your file...") }}', 'info');
                    
                    console.log('Submitting form to:', '{{ route("unified-excel-upload.upload") }}');
                    
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
                            window.location.href = response.url;
                        } else {
                            return response.json().catch(() => response.text());
                        }
                    })
                    .then(data => {
                        console.log('Response data:', data);
                        
                        // Hide progress indicator
                        const progressDiv = document.getElementById('uploadProgress');
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
                                        toastrs('warning', '{{ __("Some records had errors. Check the upload history below for details.") }}', 'warning');
                                    }, 1500);
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
                                toastrs('success', '{{ __("Units and Tenants imported successfully!") }}', 'success');
                                
                                // Reset form
                                form.reset();
                                
                                // Reload page immediately after success
                                setTimeout(() => {
                                    console.log('Refreshing page after successful upload (fallback)...');
                                    window.location.reload();
                                }, 1000);
                            } else if (data.includes('error')) {
                                toastrs('error', '{{ __("Error importing data. Please check your file format.") }}', 'error');
                                
                                // Re-enable submit button
                                submitBtn.disabled = false;
                                submitBtn.innerHTML = originalText;
                            } else {
                                window.location.reload();
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Upload error:', error);
                        
                        // Hide progress indicator
                        const progressDiv = document.getElementById('uploadProgress');
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
@endsection 