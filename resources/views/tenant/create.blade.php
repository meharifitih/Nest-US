@extends('layouts.app')
@section('page-title')
    {{ __('Tenant Create') }}
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

            var files = $('#demo-upload').get(0).dropzone.getAcceptedFiles();
            $.each(files, function(key, file) {
                fd.append('tenant_images[' + key + ']', $('#demo-upload')[0].dropzone.getAcceptedFiles()[key]);
            });
            fd.append('profile', file);
            var other_data = $('#tenant_form').serializeArray();
            $.each(other_data, function(key, input) {
                fd.append(input.name, input.value);
            });
            $.ajax({
                url: "{{ route('tenant.store') }}",
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
                        toastrs('Success!', data.msg, 'success');
                        setTimeout(function() {
                            window.location.href = "{{ route('tenant.index') }}";
                        }, 1000);
                    } else {
                        toastrs('Error', data.msg || 'Unknown error', 'error');
                        $('#tenant-submit').attr('disabled', false);
                    }
                },
                error: function(xhr) {
                    $('#tenant-submit').attr('disabled', false);
                    var msg = 'Error';
                    if (xhr.responseJSON && xhr.responseJSON.msg) {
                        msg = xhr.responseJSON.msg;
                    } else if (xhr.responseText) {
                        msg = xhr.responseText;
                    }
                    toastrs('Error', msg, 'error');
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
                    var unit =
                        `<select class="form-control hidesearch unit" id="unit" name="unit"></select>`;
                    $('.unit_div').html(unit);

                    $.each(data, function(key, value) {
                        $('.unit').append('<option value="' + key + '">' + value + '</option>');
                    });
                    $(".hidesearch").each(function() {
                        var basic_select = new Choices(this, {
                            searchEnabled: false,
                            removeItemButton: true,
                        });
                    });
                },

            });
        });

        $(document).ready(function() {
            // Handle property change to load units
            $('#property').change(function() {
                var propertyId = $(this).val();
                if(propertyId) {
                    $.ajax({
                        url: "{{ route('get.units') }}",
                        type: "POST",
                        data: {
                            property_id: propertyId,
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(data) {
                            $('#unit').empty();
                            $('#unit').append('<option value="">{{ __("Select Unit") }}</option>');
                            $.each(data, function(key, value) {
                                var selected = value.id == "{{ old('unit') }}" ? 'selected' : '';
                                $('#unit').append('<option value="' + value.id + '" ' + selected + '>' + value.name + '</option>');
                            });
                        }
                    });
                }
            });

            // Trigger property change if there's an old value
            if($('#property').val()) {
                $('#property').trigger('change');
            }
        });
    </script>
@endpush

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item" aria-current="page"> <a href="{{ route('tenant.index') }}"> {{ __('Tenant') }}</a></li>
    <li class="breadcrumb-item active">
        <a href="#">{{ __('Create') }}</a>
    </li>
@endsection


@section('content')
    <div class="row">

        {{ Form::open(['url' => 'tenant', 'method' => 'post', 'enctype' => 'multipart/form-data', 'id' => 'tenant_form']) }}
        <div class="row">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5>{{ __('Personal Details') }}</h5>
                    </div>
                    <div class="card-body">

                        <div class="row">
                            <div class="form-group col-lg-6 col-md-6">
                                <label for="first_name" class="form-label">{{ __('First Name') }} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="first_name" name="first_name" value="{{ old('first_name') }}" required>
                            </div>
                            <div class="form-group col-lg-6 col-md-6">
                                <label for="last_name" class="form-label">{{ __('Last Name') }} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="last_name" name="last_name" value="{{ old('last_name') }}" required>
                            </div>
                            <div class="form-group col-lg-6 col-md-6">
                                <label for="email" class="form-label">{{ __('Email') }} <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required>
                            </div>
                            <div class="form-group col-lg-6 col-md-6">
                                {{ Form::label('password', __('Password'), ['class' => 'form-label']) }}
                                {{ Form::password('password', ['class' => 'form-control', 'placeholder' => __('Enter Password')]) }}
                            </div>
                            <div class="form-group col-lg-6 col-md-6">
                                <label for="phone_number" class="form-label">{{ __('Phone Number') }} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="phone_number" name="phone_number" value="{{ old('phone_number') }}" required>
                            </div>
                            <div class="form-group col-lg-6 col-md-6">
                                <label for="family_member" class="form-label">{{ __('Family Members') }}</label>
                                <input type="number" class="form-control" id="family_member" name="family_member" value="{{ old('family_member') }}">
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
                                <label for="sub_city" class="form-label">{{ __('Sub City') }}</label>
                                <input type="text" class="form-control" id="sub_city" name="sub_city" value="{{ old('sub_city') }}">
                            </div>
                            <div class="col-md-4">
                                <label for="woreda" class="form-label">{{ __('Woreda') }}</label>
                                <input type="text" class="form-control" id="woreda" name="woreda" value="{{ old('woreda') }}">
                            </div>
                            <div class="col-md-4">
                                <label for="house_number" class="form-label">{{ __('House Number') }}</label>
                                <input type="text" class="form-control" id="house_number" name="house_number" value="{{ old('house_number') }}">
                            </div>
                            <div class="col-md-6">
                                <label for="location" class="form-label">{{ __('Location') }}</label>
                                <input type="text" class="form-control" id="location" name="location" value="{{ old('location') }}">
                            </div>
                            <div class="col-md-6">
                                <label for="city" class="form-label">{{ __('City') }}</label>
                                <input type="text" class="form-control" id="city" name="city" value="{{ old('city') }}">
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
                                <label for="property" class="form-label">{{ __('Property') }} <span class="text-danger">*</span></label>
                                <select class="form-control" id="property" name="property" required>
                                    <option value="">{{ __('Select Property') }}</option>
                                    @foreach($properties as $property)
                                        <option value="{{ $property->id }}" {{ old('property') == $property->id ? 'selected' : '' }}>{{ $property->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-lg-6 col-md-6">
                                <label for="unit" class="form-label">{{ __('Unit') }} <span class="text-danger">*</span></label>
                                <div class="unit_div">
                                    <select class="form-control hidesearch unit" id="unit" name="unit" required>
                                        <option value="">{{ __('Select Unit') }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group col-lg-6 col-md-6">
                                <label for="lease_start_date" class="form-label">{{ __('Lease Start Date') }}</label>
                                <input type="date" class="form-control" id="lease_start_date" name="lease_start_date" value="{{ old('lease_start_date') }}">
                            </div>
                            <div class="form-group col-lg-6 col-md-6">
                                <label for="lease_end_date" class="form-label">{{ __('Lease End Date') }}</label>
                                <input type="date" class="form-control" id="lease_end_date" name="lease_end_date" value="{{ old('lease_end_date') }}">
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
                                <div class="dz-image"><img data-dz-thumbnail="" src="" alt="">
                                </div>
                                <div class="dz-details">
                                    <div class="dz-size"><span data-dz-size=""></span></div>
                                    <div class="dz-filename"><span data-dz-name=""></span></div>
                                </div>
                                <div class="dz-progress"><span class="dz-upload" data-dz-uploadprogress="">
                                    </span></div>
                                <div class="dz-success-mark"><i class="fa fa-check" aria-hidden="true"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-12 mb-2">
                <div class="group-button text-end">
                    {{ Form::submit(__('Create'), ['class' => 'btn btn-secondary btn-rounded', 'id' => 'tenant-submit']) }}
                </div>
            </div>
        </div>
        {{ Form::close() }}
    </div>
@endsection
