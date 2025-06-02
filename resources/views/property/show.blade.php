@extends('layouts.app')
@section('page-title')
    {{ __('Property Details') }}
@endsection
@section('page-class')
    product-detail-page
@endsection
@push('script-page')
<script>
    $(document).ready(function() {
        $('#uploadExcelForm').on('submit', function(e) {
            e.preventDefault();
            var formData = new FormData(this);
            
            $.ajax({
                url: "{{ route('property.upload.tenant.excel', $property->id) }}",
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.status === 'success') {
                        toastrs('success', response.msg, 'success');
                        loadExcelUploads();
                    } else {
                        toastrs('error', response.msg, 'error');
                    }
                },
                error: function(xhr) {
                    toastrs('error', xhr.responseJSON.msg || 'Error uploading file', 'error');
                }
            });
        });

        function loadExcelUploads() {
            $.ajax({
                url: "{{ route('property.tenant.excel.uploads', $property->id) }}",
                type: 'GET',
                success: function(response) {
                    if (response.status === 'success') {
                        var html = '';
                        response.uploads.forEach(function(upload) {
                            html += '<tr>';
                            html += '<td>' + upload.original_name + '</td>';
                            html += '<td>' + upload.status + '</td>';
                            html += '<td>' + upload.created_at + '</td>';
                            if (upload.error_log) {
                                html += '<td><span class="text-danger">' + upload.error_log + '</span></td>';
                            } else {
                                html += '<td>-</td>';
                            }
                            html += '</tr>';
                        });
                        $('#excelUploadsTable tbody').html(html);
                    }
                }
            });
        }

        // Load uploads on page load
        loadExcelUploads();
    });
</script>
@endpush

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">
        <a href="{{ route('property.index') }}">{{ __('Property') }}</a>
    </li>
    <li class="breadcrumb-item active">
        <a href="#">{{ __('Details') }}</a>
    </li>
@endsection

@section('content')
    <div class="row property-page mt-3">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="row justify-content-center">
                                <div class="col-xl-12 col-xxl-12">
                                    <div class="card border">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-5">
                                                    <div class="sticky-md-top product-sticky">
                                                        <div id="carouselExampleCaptions"
                                                            class="carousel slide carousel-fade"
                                                            data-bs-ride="carousel">
                                                            <div class="carousel-inner">
                                                                @foreach ($property->propertyImages as $key => $image)
                                                                    @php
                                                                        $img = !empty($image->image)
                                                                            ? $image->image
                                                                            : 'default.jpg';
                                                                    @endphp
                                                                    <div
                                                                        class="carousel-item {{ $key === 0 ? 'active' : '' }}">
                                                                        <img src="{{ asset(Storage::url('upload/property') . '/' . $img) }}"
                                                                            class="d-block w-100 rounded"
                                                                            alt="Product image" />
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                            <ol
                                                                class="carousel-indicators position-relative product-carousel-indicators my-sm-3 mx-0">
                                                                @foreach ($property->propertyImages as $key => $image)
                                                                    @php
                                                                        $img = !empty($image->image)
                                                                            ? $image->image
                                                                            : 'default.jpg';
                                                                    @endphp
                                                                    <li data-bs-target="#carouselExampleCaptions"
                                                                        data-bs-slide-to="{{ $key }}"
                                                                        class="{{ $key === 0 ? 'active' : '' }} w-25 h-auto">
                                                                        <img src="{{ asset(Storage::url('upload/property') . '/' . $img) }}"
                                                                            class="d-block wid-50 rounded"
                                                                            alt="Product image" />
                                                                    </li>
                                                                @endforeach
                                                            </ol>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-7">
                                                    <h3 class="">
                                                        {{ ucfirst($property->name) }}
                                                    </h3>
                                                    <span class="badge bg-light-primary f-14 mt-1"
                                                        data-bs-toggle="tooltip"
                                                        data-bs-original-title="{{ __('Type') }}">{{ \App\Models\Property::$Type[$property->type] }}</span>
                                                    <h5 class="mt-4">{{ __('Property Details') }}</h5>
                                                    <hr class="my-3" />
                                                    <p class="text-muted">
                                                        {{ $property->description }}
                                                    </p>
                                                    <h5>{{ __('Property Address') }}</h5>
                                                    <hr class="my-3" />
                                                    <div class="mb-1 row">
                                                        <label class="col-form-label col-lg-3 col-sm-12 text-lg-end">
                                                            {{ __('Woreda') }} :
                                                        </label>
                                                        <div class="col-lg-6 col-md-12 col-sm-12 d-flex align-items-center">
                                                            {{ $property->woreda }}
                                                        </div>
                                                    </div>
                                                    <div class="mb-1 row">
                                                        <label class="col-form-label col-lg-3 col-sm-12 text-lg-end">
                                                            {{ __('Sub-city') }} :
                                                        </label>
                                                        <div class="col-lg-6 col-md-12 col-sm-12 d-flex align-items-center">
                                                            {{ $property->sub_city }}
                                                        </div>
                                                    </div>
                                                    <div class="mb-1 row">
                                                        <label class="col-form-label col-lg-3 col-sm-12 text-lg-end">
                                                            {{ __('House Number') }} :
                                                        </label>
                                                        <div class="col-lg-6 col-md-12 col-sm-12 d-flex align-items-center">
                                                            {{ $property->house_number }}
                                                        </div>
                                                    </div>
                                                    <div class="mb-1 row">
                                                        <label class="col-form-label col-lg-3 col-sm-12 text-lg-end">
                                                            {{ __('Location') }} :
                                                        </label>
                                                        <div class="col-lg-6 col-md-12 col-sm-12 d-flex align-items-center">
                                                            {{ $property->location }}
                                                        </div>
                                                    </div>
                                                    <div class="mb-1 row">
                                                        <label class="col-form-label col-lg-3 col-sm-12 text-lg-end">
                                                            {{ __('City') }} :
                                                        </label>
                                                        <div class="col-lg-6 col-md-12 col-sm-12 d-flex align-items-center">
                                                            {{ $property->city }}
                                                        </div>
                                                    </div>
                                                    <hr class="my-3" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
