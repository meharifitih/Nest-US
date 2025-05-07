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
                        <li class="nav-item">
                            <a class="nav-link" id="profile-tab-3" data-bs-toggle="tab" href="#profile-3" role="tab"
                                aria-selected="true">
                                <i class="material-icons-two-tone me-2">layers</i>
                                {{ __('Unit') }}
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
                                            <div class="row">
                                                <div class="col-sm-4">
                                                    <div class="mb-3">
                                                        <div class="form-group ">
                                                            {{ Form::label('type', __('Type'), ['class' => 'form-label']) }}
                                                            {{ Form::select('type', $types, null, ['class' => 'form-control basic-select', 'required' => 'required']) }}
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-sm-4">
                                                    <div class="mb-3">
                                                        <div class="form-group">
                                                            {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}
                                                            {{ Form::text('name', null, ['class' => 'form-control', 'placeholder' => __('Enter Property Name'), 'required' => 'required']) }}
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-sm-4">
                                                    <div class="mb-3">
                                                        <div class="form-group">
                                                            {{ Form::label('thumbnail', __('Thumbnail Image'), ['class' => 'form-label']) }}
                                                            {{ Form::file('thumbnail', ['class' => 'form-control']) }}
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-sm-12">
                                                    <div class="mb-3">
                                                        <div class="form-group ">
                                                            {{ Form::label('description', __('Description'), ['class' => 'form-label']) }}
                                                            {{ Form::textarea('description', null, ['class' => 'form-control', 'rows' => 4, 'placeholder' => __('Enter Property Description'), 'required' => 'required']) }}
                                                        </div>

                                                    </div>
                                                </div>
                                                <div class="col-sm-3">
                                                    <div class="mb-3">
                                                        <div class="form-group">
                                                            {{ Form::label('country', __('Country'), ['class' => 'form-label']) }}
                                                            {{ Form::text('country', null, ['class' => 'form-control', 'placeholder' => __('Enter Property Country'), 'required' => 'required']) }}
                                                        </div>

                                                    </div>
                                                </div>
                                                <div class="col-sm-3">
                                                    <div class="mb-3">
                                                        <div class="form-group">
                                                            {{ Form::label('state', __('State'), ['class' => 'form-label']) }}
                                                            {{ Form::text('state', null, ['class' => 'form-control', 'placeholder' => __('Enter Property State'), 'required' => 'required']) }}
                                                        </div>

                                                    </div>
                                                </div>
                                                <div class="col-sm-3">
                                                    <div class="mb-3">
                                                        <div class="form-group">
                                                            {{ Form::label('city', __('City'), ['class' => 'form-label']) }}
                                                            {{ Form::text('city', null, ['class' => 'form-control', 'placeholder' => __('Enter Property City'), 'required' => 'required']) }}
                                                        </div>

                                                    </div>
                                                </div>
                                                <div class="col-sm-3">
                                                    <div class="mb-3">
                                                        <div class="form-group">
                                                            {{ Form::label('zip_code', __('Zip Code'), ['class' => 'form-label']) }}
                                                            {{ Form::text('zip_code', null, ['class' => 'form-control', 'placeholder' => __('Enter Property Zip Code'), 'required' => 'required']) }}
                                                        </div>

                                                    </div>
                                                </div>
                                                <div class="col-sm-12">
                                                    <div class="mb-3">
                                                        <div class="form-group ">
                                                            {{ Form::label('address', __('Address'), ['class' => 'form-label']) }}
                                                            {{ Form::textarea('address', null, ['class' => 'form-control', 'rows' => 3, 'placeholder' => __('Enter Property Address'), 'required' => 'required']) }}
                                                        </div>

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
                                <button type="button" class="btn btn-secondary btn-rounded nextButton"
                                    data-next-tab="#profile-2">
                                    {{ __('Next') }}
                                </button>
                            </div>
                        </div>



                        <div class="tab-pane show " id="profile-3" role="tabpanel" aria-labelledby="profile-tab-3">
                            <div class="card border">
                                <div class="card-header">
                                    <h5>{{ __('Add Unit') }}</h5>
                                </div>
                               

                                <div class="card-body">
                                    <div class="row unit_list">
                                        <div class="form-group col-md-4">
                                            {{ Form::label('unitname', __('Name'), ['class' => 'form-label']) }}
                                            {{ Form::text('unitname', null, ['class' => 'form-control', 'placeholder' => __('Enter unit name'), 'required' => 'required']) }}
                                        </div>
                                        <div class="form-group col-md-2">
                                            {{ Form::label('bedroom', __('Bedroom'), ['class' => 'form-label']) }}
                                            {{ Form::number('bedroom', null, ['class' => 'form-control', 'placeholder' => __('Enter number of bedroom'), 'required' => 'required']) }}
                                        </div>
                                        <div class="form-group col-md-2">
                                            {{ Form::label('kitchen', __('Kitchen'), ['class' => 'form-label']) }}
                                            {{ Form::number('kitchen', null, ['class' => 'form-control', 'placeholder' => __('Enter number of kitchen'), 'required' => 'required']) }}
                                        </div>
                                        <div class="form-group col-md-2">
                                            {{ Form::label('baths', __('Bath'), ['class' => 'form-label']) }}
                                            {{ Form::number('baths', null, ['class' => 'form-control', 'placeholder' => __('Enter number of bath'), 'required' => 'required']) }}
                                        </div>
                                        <div class="form-group col-md-2">
                                            {{ Form::label('rent', __('Rent'), ['class' => 'form-label']) }}
                                            {{ Form::number('rent', null, ['class' => 'form-control', 'placeholder' => __('Enter unit rent'), 'required' => 'required']) }}
                                        </div>
                                        <div class="form-group col-md-6">
                                            {{ Form::label('rent_type', __('Rent Type'), ['class' => 'form-label']) }}
                                            {{ Form::select('rent_type', $rentTypes, null, ['class' => 'form-control hidesearch', 'id' => 'rent_type', 'required' => 'required']) }}
                                        </div>
                                        <div class="form-group col-md-6 rent_type monthly">
                                            {{ Form::label('rent_duration', __('Rent Duration'), ['class' => 'form-label']) }}
                                            {{ Form::number('rent_duration', null, ['class' => 'form-control', 'placeholder' => __('Enter day of month between 1 to 30')]) }}
                                        </div>
                                        <div class="form-group col-md-6 rent_type yearly d-none">
                                            {{ Form::label('rent_duration', __('Rent Duration'), ['class' => 'form-label']) }}
                                            {{ Form::number('rent_duration', null, ['class' => 'form-control', 'placeholder' => __('Enter month of year between 1 to 12'), 'disabled']) }}
                                        </div>
                                        <div class="form-group col-md-2 rent_type custom d-none">
                                            {{ Form::label('start_date', __('Start Date'), ['class' => 'form-label']) }}
                                            {{ Form::date('start_date', null, ['class' => 'form-control', 'disabled']) }}
                                        </div>
                                        <div class="form-group col-md-2 rent_type custom d-none">
                                            {{ Form::label('end_date', __('End Date'), ['class' => 'form-label']) }}
                                            {{ Form::date('end_date', null, ['class' => 'form-control', 'disabled']) }}
                                        </div>
                                        <div class="form-group col-md-2 rent_type custom d-none">
                                            {{ Form::label('payment_due_date', __('Payment Due Date'), ['class' => 'form-label']) }}
                                            {{ Form::date('payment_due_date', null, ['class' => 'form-control', 'disabled']) }}
                                        </div>
                                        <div class="form-group col-md-4">
                                            {{ Form::label('deposit_type', __('Deposit Type'), ['class' => 'form-label']) }}
                                            {{ Form::select('deposit_type', $unitTypes, null, ['class' => 'form-control hidesearch', 'required' => 'required']) }}
                                        </div>
                                        <div class="form-group col-md-3">
                                            {{ Form::label('deposit_amount', __('Deposit Amount'), ['class' => 'form-label']) }}
                                            {{ Form::number('deposit_amount', null, ['class' => 'form-control', 'placeholder' => __('Enter deposit amount'), 'required' => 'required']) }}
                                        </div>
                                        <div class="form-group col-md-3">
                                            {{ Form::label('late_fee_type', __('Late Fee Type'), ['class' => 'form-label']) }}
                                            {{ Form::select('late_fee_type', $unitTypes, null, ['class' => 'form-control hidesearch', 'required' => 'required']) }}
                                        </div>
                                        <div class="form-group col-md-2">
                                            {{ Form::label('late_fee_amount', __('Late Fee Amount'), ['class' => 'form-label']) }}
                                            {{ Form::number('late_fee_amount', null, ['class' => 'form-control', 'placeholder' => __('Enter late fee amount'), 'required' => 'required']) }}
                                        </div>
                                        <div class="form-group col-md-6">
                                            {{ Form::label('incident_receipt_amount', __('Incident Receipt Amount'), ['class' => 'form-label']) }}
                                            {{ Form::number('incident_receipt_amount', null, ['class' => 'form-control', 'placeholder' => __('Enter incident receipt amount'), 'required' => 'required']) }}
                                        </div>
                                        <div class="form-group col-md-6">
                                            {{ Form::label('notes', __('Notes'), ['class' => 'form-label']) }}
                                            {{ Form::textarea('notes', null, ['class' => 'form-control', 'rows' => 3, 'placeholder' => __('Enter notes')]) }}
                                        </div>
                                    </div>



                                </div>
                            </div>


                            <div class="col-lg-12 mb-2">
                                <div class="group-button text-end">
                                    {{ Form::submit(__('Create'), ['class' => 'btn btn-secondary btn-rounded nextButton', 'id' => 'property-submit']) }}
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
