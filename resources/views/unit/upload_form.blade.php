@extends('layouts.app')
@section('page-title')
    {{ __('Unit Excel Upload') }}
@endsection
@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5>{{ __('Upload Unit Excel') }}</h5>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif
                <form method="POST" action="{{ route('unit-excel-upload.upload') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group mb-3">
                        <label for="property_id">{{ __('Select Property') }}</label>
                        <select class="form-control" id="property_id" name="property_id" required>
                            <option value="">{{ __('Select Property') }}</option>
                            @foreach($properties as $property)
                                <option value="{{ $property->id }}">{{ $property->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label for="excel_file">{{ __('Excel File') }}</label>
                        <input type="file" class="form-control" id="excel_file" name="excel_file" accept=".xlsx,.xls,.csv" required>
                        <small class="form-text text-muted">
                            {{ __('Supported formats: XLSX, XLS, CSV. Maximum file size: 2MB') }}
                        </small>
                    </div>
                    <button type="submit" class="btn btn-primary">{{ __('Upload') }}</button>
                    <a href="{{ route('unit-excel-template') }}" class="btn btn-secondary ms-2">
                        <i class="material-icons-two-tone me-2">download</i>
                        {{ __('Download Sample Excel') }}
                    </a>
                </form>
            </div>
        </div>
        <div class="card mt-4">
            <div class="card-header">
                <h5>{{ __('Unit Upload History') }}</h5>
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
                        @foreach($uploads as $upload)
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