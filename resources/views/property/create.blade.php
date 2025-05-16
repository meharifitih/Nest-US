@extends('layouts.app')
@section('page-title')
    {{ __('Property Create') }}
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
        $('#property-submit').on('click', function() {
            "use strict";
            $('#property-submit').attr('disabled', true);
            var fd = new FormData();
            var file = document.getElementById('thumbnail').files[0];

            var files = $('#demo-upload').get(0).dropzone.getAcceptedFiles();
            $.each(files, function(key, file) {
                fd.append('property_images[' + key + ']', $('#demo-upload')[0].dropzone
                    .getAcceptedFiles()[key]); // attach dropzone image element
            });
            fd.append('thumbnail', file);
            var other_data = $('#property_form').serializeArray();
            $.each(other_data, function(key, input) {
                fd.append(input.name, input.value);
            });
            $.ajax({
                url: "{{ route('property.store') }}",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: fd,
                contentType: false,
                processData: false,
                type: 'POST',
                success: function(data) {
                    if (data.status == "success") {
                        $('#property-submit').attr('disabled', true);
                        toastrs(data.status, data.msg, data.status);
                        var url = '{{ route('property.show', ':id') }}';
                        url = url.replace(':id', data.id);
                        setTimeout(() => {
                            window.location.href = url;
                        }, "1000");

                    } else {
                        toastrs('Error', data.msg, 'error');
                        $('#property-submit').attr('disabled', false);
                    }
                },
                error: function(data) {
                    $('#property-submit').attr('disabled', false);
                    if (data.error) {
                        toastrs('Error', data.error, 'error');
                    } else {
                        toastrs('Error', data, 'error');
                    }
                },
            });
        });
    </script>

    <script>
        $('#rent_type').on('change', function() {
            "use strict";
            var type = this.value;
            $('.rent_type').addClass('d-none')
            $('.' + type).removeClass('d-none')

            var input1 = $('.rent_type').find('input');
            input1.prop('disabled', true);
            var input2 = $('.' + type).find('input');
            input2.prop('disabled', false);
        });
    </script>

    <script>
        $(document).ready(function() {
            $('.nextButton').on('click', function() {
                let $activeTab = $('.tab-content .tab-pane.active'); // Current active tab
                let $nextTab = $activeTab.next('.tab-pane'); // Next tab

                if ($nextTab.length > 0) {
                    let nextTabId = $nextTab.attr('id');
                    $('a[href="#' + nextTabId + '"]').tab('show'); // Move to next tab

                    // If the next tab is the last, change the button text to "Submit"
                    if ($nextTab.is(':last-child')) {
                        $(this).text('Submit').addClass('submit-button');
                    }
                } else if ($(this).hasClass('submit-button')) {
                    // Handle form submission
                    $('form').submit();
                }
            });


            $('a[data-toggle="tab"]').on('shown.bs.tab', function() {
                let $activeTab = $('.tab-content .tab-pane.active');
                let isLastTab = $activeTab.is(':last-child');

                if (!isLastTab) {
                    $('.nextButton').text('Next').removeClass('submit-button');
                }
            });
        });
    </script>


<script>
    $(document).ready(function() {

        function toggleRemoveServiceButton() {
            let serviceCount = $('.unit_list').length;
            $('.remove-service').toggle(serviceCount > 1);
        }

        $(document).on('click', '.add-unit', function() {
            let originalRow = $('.unit_list:first');


            let clonedRow = originalRow.clone();
            console.log(clonedRow);

            clonedRow.find('input, select').val('');
            clonedRow.find('.description').val('');

            let rowIndex = $('.unit_list').length;
            clonedRow.find('select[name^="skill"]').each(function() {
                $(this).attr('name', 'skill[' + rowIndex + '][]');
            });

            let hrElement = $('<hr class="mt-2 mb-4 border-dark">');
            $('.unit_list_results').append(clonedRow).append(hrElement);

            originalRow.find('.select2').select2();
            clonedRow.find('.select2').select2();

            toggleRemoveServiceButton();

        });

        $(document).on('click', '.remove-service', function() {
            $(this).parent().parent().closest('.unit_list').next('hr').remove();
            $(this).parent().parent().closest('.unit_list').remove();
            toggleRemoveServiceButton();
        });

        toggleRemoveServiceButton();
    });
</script>

@endpush

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">
        <a href="{{ route('property.index') }}">{{ __('Property') }}</a>
    </li>
    <li class="breadcrumb-item active"><a href="#">{{ __('Create') }}</a>
    </li>
@endsection

@section('content')
    {{ Form::open(['url' => 'property', 'method' => 'post', 'enctype' => 'multipart/form-data', 'id' => 'property_form']) }}
    <div class="row mt-4">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header pb-0">
                    <ul class="nav nav-tabs profile-tabs" id="myTab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="profile-tab-1" data-bs-toggle="tab" href="#profile-1"
                                role="tab" aria-selected="true">
                                <i class="material-icons-two-tone me-2">info</i>
                                {{ __('Property Details') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="profile-tab-2" data-bs-toggle="tab" href="#profile-2" role="tab"
                                aria-selected="true">
                                <i class="material-icons-two-tone me-2">image</i>
                                {{ __('Property Images') }}
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <div class="tab-pane active" id="profile-1" role="tabpanel" aria-labelledby="profile-tab-1">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card border">
                                        <div class="card-header">
                                            <h5> {{ __('Add Property Details') }}</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="row g-3 mb-4">
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        {{ Form::label('type', __('Type'), ['class' => 'form-label']) }}
                                                        {{ Form::select('type', $types, null, ['class' => 'form-control basic-select', 'required' => 'required']) }}
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}
                                                        {{ Form::text('name', null, ['class' => 'form-control', 'placeholder' => __('Enter Property Name'), 'required' => 'required']) }}
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        {{ Form::label('thumbnail', __('Thumbnail Image'), ['class' => 'form-label']) }}
                                                        {{ Form::file('thumbnail', ['class' => 'form-control']) }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row g-3 mb-4">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        {{ Form::label('location', __('Location'), ['class' => 'form-label']) }}
                                                        {{ Form::text('location', null, ['class' => 'form-control', 'placeholder' => __('Enter Property Location'), 'required' => 'required']) }}
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        {{ Form::label('description', __('Description'), ['class' => 'form-label']) }}
                                                        {{ Form::text('description', null, ['class' => 'form-control', 'placeholder' => __('Enter Property Description'), 'required' => 'required']) }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row g-3 mb-4">
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        {{ Form::label('house_number', __('House Number'), ['class' => 'form-label']) }}
                                                        {{ Form::text('house_number', null, ['class' => 'form-control', 'placeholder' => __('Enter House Number'), 'required' => 'required']) }}
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        {{ Form::label('woreda', __('Woreda'), ['class' => 'form-label']) }}
                                                        {{ Form::text('woreda', null, ['class' => 'form-control', 'placeholder' => __('Enter Woreda'), 'required' => 'required']) }}
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        {{ Form::label('sub_city', __('Sub-city'), ['class' => 'form-label']) }}
                                                        {{ Form::text('sub_city', null, ['class' => 'form-control', 'placeholder' => __('Enter Sub-city'), 'required' => 'required']) }}
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        {{ Form::label('city', __('City'), ['class' => 'form-label']) }}
                                                        {{ Form::text('city', null, ['class' => 'form-control', 'placeholder' => __('Enter City'), 'required' => 'required']) }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="text-end mt-3">
                                <button type="button" class="btn btn-secondary btn-rounded nextButton"
                                    data-next-tab="#profile-2">
                                    {{ __('Next') }}
                                </button>
                            </div>
                        </div>
                        <div class="tab-pane" id="profile-2" role="tabpanel" aria-labelledby="profile-tab-2">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card border">
                                        <div class="card-header">
                                            {{ Form::label('demo-upload', __('Add Property Images'), ['class' => 'form-label']) }}
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="dropzone needsclick" id='demo-upload' action="#">
                                                    <div class="dz-message needsclick">
                                                        <div class="upload-icon"><i class="fa fa-cloud-upload"></i></div>
                                                        <h3>{{ __('Drop files here or click to upload.') }}</h3>
                                                    </div>
                                                </div>
                                                <div class="preview-dropzon" style="display: none;">
                                                    <div class="dz-preview dz-file-preview">
                                                        <div class="dz-image"><img data-dz-thumbnail="" src=""
                                                                alt=""></div>
                                                        <div class="dz-details">
                                                            <div class="dz-size"><span data-dz-size=""></span></div>
                                                            <div class="dz-filename"><span data-dz-name=""></span></div>
                                                        </div>
                                                        <div class="dz-progress"><span class="dz-upload"
                                                                data-dz-uploadprogress=""> </span></div>
                                                        <div class="dz-success-mark"><i class="fa fa-check"
                                                                aria-hidden="true"></i></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="text-end mt-3">
                                <div class="group-button text-end">
                                    {{ Form::submit(__('Create'), ['class' => 'btn btn-secondary btn-rounded', 'id' => 'property-submit']) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{ Form::close() }}
@endsection
