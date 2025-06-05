@extends('layouts.app')
@section('page-title')
    {{ __('Tenant Edit') }}
@endsection
@push('script-page')
    <script src="{{ asset('assets/js/vendors/dropzone/dropzone.js') }}"></script>

    <script>
        var dropzone = new Dropzone('#demo-upload', {
            previewTemplate: document.querySelector('.preview-dropzon').innerHTML,
            parallelUploads: 10,
            thumbnailHeight: 120,
            thumbnailWidth: 120,
            maxFilesize: 10,
            filesizeBase: 1000,
            autoProcessQueue: false,
            thumbnail: function(file, dataUrl) {
                if (file.previewElement) {
                    file.previewElement.classList.remove("dz-file-preview");
                    var images = file.previewElement.querySelectorAll("[data-dz-thumbnail]");
                    for (var i = 0; i < images.length; i++) {
                        var thumbnailElement = images[i];
                        thumbnailElement.alt = file.name;
                        thumbnailElement.src = dataUrl;
                    }
                    setTimeout(function() {
                        file.previewElement.classList.add("dz-image-preview");
                    }, 1);
                }
            }

        });
        $('#tenant-submit').on('click', function() {
            "use strict";
            $('#tenant-submit').attr('disabled', true);
            var fd = new FormData();
            var file = document.getElementById('profile').files[0];
            if (file == undefined) {
                fd.append('profile', '');
            } else {
                fd.append('profile', file);
            }
            var files = $('#demo-upload').get(0).dropzone.getAcceptedFiles();
            $.each(files, function(key, file) {
                fd.append('tenant_images[' + key + ']', $('#demo-upload')[0].dropzone
                    .getAcceptedFiles()[key]); // attach dropzone image element
            });

            var other_data = $('#tenant_form').serializeArray();
            $.each(other_data, function(key, input) {
                fd.append(input.name, input.value);
            });

            $.ajax({
                url: "{{ route('tenant.update', $tenant->id) }}",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: fd,
                contentType: false,
                processData: false,
                type: 'POST',
                success: function(data) {
                    if (data.status == "success") {
                        $('#tenant-submit').attr('disabled', true);
                        toastrs(data.status, data.msg, data.status);
                        var url = '{{ route('tenant.index') }}';
                        setTimeout(() => {
                            window.location.href = url;
                        }, "1000");

                    } else {
                        toastrs('Error', data.msg, 'error');
                        $('#tenant-submit').attr('disabled', false);
                    }
                },
                error: function(data) {
                    $('#tenant-submit').attr('disabled', false);
                    if (data.error) {
                        toastrs('Error', data.error, 'error');
                    } else {
                        toastrs('Error', data, 'error');
                    }
                },
            });
        });

        $('#property').on('change', function() {
            "use strict";
            var property_id = $(this).val();
            var url = '{{ route('property.unit', ':id') }}';
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
                success: function(data) {
                    $('.unit').empty();
                    var unit = `<select class="form-control hidesearch unit" id="unit" name="unit"></select>`;
                    $('.unit_div').html(unit);
                    var tenant_id = $('#edit_unit').val();
                    $.each(data, function(key, value) {
                        var text = (typeof value === 'object' && value !== null && value.name) ? value.name : value;
                        if (key == tenant_id) {
                            $('.unit').append('<option selected value="' + key + '">' + text + '</option>');
                        } else {
                            $('.unit').append('<option value="' + key + '">' + text + '</option>');
                        }
                    });
                    $('.hidesearch').select2({
                        minimumResultsForSearch: -1
                    });
                },
            });
        });

        $('#property').trigger('change');

        document.addEventListener('DOMContentLoaded', function() {
            const phoneInput = document.getElementById('phone_number');
            const phoneError = document.getElementById('phone_error');
            phoneInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length > 0) {
                    const firstDigit = value.charAt(0);
                    if (firstDigit !== '9' && firstDigit !== '7') {
                        phoneError.textContent = 'Phone number must start with 9 or 7';
                        phoneError.style.display = 'block';
                    } else {
                        phoneError.style.display = 'none';
                    }
                }
                if (value.length > 9) {
                    value = value.slice(0, 9);
                }
                e.target.value = value;
            });
        });
    </script>
@endpush

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item" aria-current="page"> <a href="{{ route('tenant.index') }}"> {{ __('Tenant') }}</a></li>
    <li class="breadcrumb-item active">
        <a href="#">{{ __('Edit') }}</a>
    </li>
@endsection

@section('content')
    <div class="row">

        {{ Form::model($tenant, ['route' => ['tenant.update', $tenant->id], 'method' => 'PUT', 'enctype' => 'multipart/form-data', 'id' => 'tenant_form']) }}
        <div class="row">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5>{{ __('Personal Details') }}</h5>
                    </div>
                    <div class="card-body">

                        <div class="row">
                            <div class="form-group col-lg-6 col-md-6">
                                {{ Form::label('first_name', __('First Name'), ['class' => 'form-label']) }}
                                {{ Form::text('first_name', $user->first_name, ['class' => 'form-control', 'placeholder' => __('Enter First Name')]) }}
                            </div>
                            <div class="form-group col-lg-6 col-md-6">
                                {{ Form::label('last_name', __('Last Name'), ['class' => 'form-label']) }}
                                {{ Form::text('last_name', $user->last_name, ['class' => 'form-control', 'placeholder' => __('Enter Last Name')]) }}
                            </div>
                            <div class="form-group ">
                                {{ Form::label('email', __('Email'), ['class' => 'form-label']) }}
                                {{ Form::text('email', $user->email, ['class' => 'form-control', 'placeholder' => __('Enter Email')]) }}
                            </div>
                            <div class="form-group col-lg-6 col-md-6">
                                {{ Form::label('phone_number', __('Phone Number'), ['class' => 'form-label']) }}
                                <div class="input-group">
                                    <span class="input-group-text">+251</span>
                                    <input type="text" class="form-control" id="phone_number" name="phone_number" maxlength="9" pattern="[79][0-9]{8}" value="{{ isset($user->phone_number) ? ltrim($user->phone_number, '+251') : '' }}" placeholder="Enter phone number (e.g. 912345678)" required>
                                </div>
                                <small class="text-muted">Enter number starting with 9 (Ethio Telecom) or 7 (Safaricom)</small>
                                <span id="phone_error" class="text-danger" style="display: none;"></span>
                            </div>
                            <div class="form-group col-lg-6 col-md-6">
                                {{ Form::label('family_member', __('Total Family Member'), ['class' => 'form-label']) }}
                                {{ Form::number('family_member', null, ['class' => 'form-control', 'placeholder' => __('Enter Total Family Member')]) }}
                            </div>
                            <div class="form-group">
                                {{ Form::label('profile', __('Profile'), ['class' => 'form-label']) }}
                                {{ Form::file('profile', ['class' => 'form-control']) }}
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5>{{ __('Address Details') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                {{ Form::label('sub_city', __('Sub City'), ['class' => 'form-label']) }}
                                {{ Form::text('sub_city', $tenant->sub_city, ['class' => 'form-control', 'placeholder' => __('Enter Sub City')]) }}
                            </div>
                            <div class="col-md-4">
                                {{ Form::label('woreda', __('Woreda'), ['class' => 'form-label']) }}
                                {{ Form::text('woreda', $tenant->woreda, ['class' => 'form-control', 'placeholder' => __('Enter Woreda')]) }}
                            </div>
                            <div class="col-md-4">
                                {{ Form::label('house_number', __('House Number'), ['class' => 'form-label']) }}
                                {{ Form::text('house_number', $tenant->house_number, ['class' => 'form-control', 'placeholder' => __('Enter House Number')]) }}
                            </div>
                            <div class="col-md-6">
                                {{ Form::label('location', __('Location'), ['class' => 'form-label']) }}
                                {{ Form::text('location', $tenant->location, ['class' => 'form-control', 'placeholder' => __('Enter Location')]) }}
                            </div>
                            <div class="col-md-6">
                                {{ Form::label('city', __('City'), ['class' => 'form-label']) }}
                                {{ Form::text('city', $tenant->city, ['class' => 'form-control', 'placeholder' => __('Enter City')]) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5>{{ __('Property Details') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="form-group col-lg-6 col-md-6">
                                {{ Form::label('property', __('Property'), ['class' => 'form-label']) }}
                                {{ Form::select('property', $property, null, ['class' => 'form-control basic-select', 'id' => 'property']) }}
                            </div>
                            <div class="form-group col-lg-6 col-md-6">
                                {{ Form::label('unit', __('Unit'), ['class' => 'form-label']) }}
                                <input type="hidden" id="edit_unit" value="{{ $tenant->unit }}">
                                <div class="unit_div">
                                    <select class="form-control hidesearch unit" id="unit" name="unit">
                                        <option value="">{{ __('Select Unit') }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group col-lg-6 col-md-6">
                                {{ Form::label('lease_start_date', __('Lease Start Date'), ['class' => 'form-label']) }}
                                {{ Form::date('lease_start_date', $tenant->lease_start_date, ['class' => 'form-control']) }}
                            </div>
                            <div class="form-group col-lg-6 col-md-6">
                                {{ Form::label('lease_end_date', __('Lease End Date'), ['class' => 'form-label']) }}
                                {{ Form::date('lease_end_date', $tenant->lease_end_date, ['class' => 'form-control']) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5>{{ __('Documents') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="dropzone needsclick" id='demo-upload' action="#">
                            <div class="dz-message needsclick">
                                <div class="upload-icon"><i class="fa fa-cloud-upload"></i></div>
                                <h3>{{ __('Drop files here or click to upload.') }}</h3>
                            </div>
                        </div>
                        <div class="preview-dropzon" style="display: none;">
                            <div class="dz-preview dz-file-preview">
                                <div class="dz-image"><img data-dz-thumbnail="" src="" alt=""></div>
                                <div class="dz-details">
                                    <div class="dz-size"><span data-dz-size=""></span></div>
                                    <div class="dz-filename"><span data-dz-name=""></span></div>
                                </div>
                                <div class="dz-progress"><span class="dz-upload" data-dz-uploadprogress=""> </span>
                                </div>
                                <div class="dz-success-mark"><i class="fa fa-check" aria-hidden="true"></i></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-12 mb-2">
                <div class="group-button text-end">
                    {{ Form::submit(__('Update'), ['class' => 'btn btn-secondary btn-rounded', 'id' => 'tenant-submit']) }}
                </div>
            </div>
        </div>
        {{ Form::close() }}
    </div>
@endsection
