@extends('layouts.app')
@section('page-title')
    {{ __('Tenant Excel Upload for ') . $property->name }}
@endsection
@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5>{{ __('Upload Tenant Excel for ') . $property->name }}</h5>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif
                <form method="POST" action="{{ route('tenant-excel-upload.upload', $property->id) }}" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group mb-3">
                        <label for="excel_file">{{ __('Excel File') }}</label>
                        <input type="file" class="form-control" id="excel_file" name="excel_file" accept=".xlsx,.xls,.csv" required>
                        <small class="form-text text-muted">
                            {{ __('Supported formats: XLSX, XLS, CSV. Maximum file size: 2MB') }}
                        </small>
                        <small class="form-text text-muted">
                            {{ __('Phone numbers must be in the format +251XXXXXXXXX (e.g. +251912345678).') }}
                        </small>
                    </div>
                    <button type="submit" class="btn btn-primary">{{ __('Upload') }}</button>
                    <a href="{{ route('property.tenant.excel.template') }}" class="btn btn-secondary ms-2">
                        <i class="material-icons-two-tone me-2">download</i>
                        {{ __('Download Template') }}
                    </a>
                </form>
            </div>
        </div>
        <div class="card mt-4">
            <div class="card-header">
                <h5>{{ __('Upload History') }}</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>{{ __('File Name') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Error') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach(\App\Models\TenantExcelUpload::where('property_id', $property->id)->orderBy('created_at', 'desc')->get() as $upload)
                            <tr>
                                <td>{{ $upload->original_name }}</td>
                                <td>{{ $upload->status }}</td>
                                <td>{{ $upload->created_at }}</td>
                                <td>{!! $upload->error_log ? '<span class="text-danger">' . $upload->error_log . '</span>' : '-' !!}</td>
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