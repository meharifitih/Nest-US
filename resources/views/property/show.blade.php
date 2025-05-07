@extends('layouts.app')
@section('page-title')
    {{ __('Property Details') }}
@endsection
@section('page-class')
    product-detail-page
@endsection
@push('script-page')
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
    <div class="row">
        <div class="col-sm-12">
            <div class="">
                <div class="card-header">
                    <div class="row align-items-center g-2">
                        <div class="col">

                        </div>
                        @can('create property')
                            <div class="col-auto">
                                <a class="btn btn-secondary customModal" data-size="lg" href="#"  data-url="{{ route('unit.create', $property->id) }}" data-title="{{ __('Add Unit') }}"> <i
                                        class="ti ti-circle-plus align-text-bottom " ></i>
                                    {{ __('Add Unit') }}</a>

                                {{-- <a href="#" class="btn btn-secondary btn-sm customModal" data-title="{{ __('Add Unit') }}"
                                    data-url="{{ route('unit.create', $property->id) }}" data-size="lg"> <i
                                        class="ti-plus mr-5"></i>{{ __('Add Unit') }}</a> --}}
                            </div>
                        @endcan
                    </div>
                </div>
            </div>

        </div>
    </div>
    <div class="row property-page mt-3">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header pb-0">
                    <ul class="nav nav-tabs profile-tabs" id="myTab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="profile-tab-1" data-bs-toggle="tab" href="#profile-1"
                                role="tab" aria-selected="true">
                                <i class="material-icons-two-tone me-2">meeting_room</i>
                                {{ __('Property Details') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="profile-tab-2" data-bs-toggle="tab" href="#profile-2" role="tab"
                                aria-selected="true">
                                <i class="material-icons-two-tone me-2">ad_units</i>
                                {{ __('Property Units') }}
                            </a>
                        </li>

                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <div class="tab-pane show active" id="profile-1" role="tabpanel" aria-labelledby="profile-tab-1">
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
                                                                <label
                                                                    class="col-form-label col-lg-3 col-sm-12 text-lg-end">
                                                                    {{ __('Address') }} :

                                                                </label>
                                                                <div
                                                                    class="col-lg-6 col-md-12 col-sm-12 d-flex align-items-center">
                                                                    {{ $property->address }}
                                                                </div>
                                                            </div>
                                                            <div class="mb-1 row">
                                                                <label
                                                                    class="col-form-label col-lg-3 col-sm-12 text-lg-end">
                                                                    {{ __('Location') }} :

                                                                </label>
                                                                <div
                                                                    class="col-lg-6 col-md-12 col-sm-12 d-flex align-items-center">
                                                                    {{ $property->city . ', ' . $property->state . ', ' . $property->country }}
                                                                </div>
                                                            </div>
                                                            <div class="mb-1 row">
                                                                <label
                                                                    class="col-form-label col-lg-3 col-sm-12 text-lg-end">
                                                                    {{ __('Zip Code') }} :

                                                                </label>
                                                                <div
                                                                    class="col-lg-6 col-md-12 col-sm-12 d-flex align-items-center">
                                                                    {{ $property->zip_code }}
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
                        <div class="tab-pane " id="profile-2" role="tabpanel" aria-labelledby="profile-tab-2">
                            <div class="row">
                                @foreach ($units as $unit)
                                    <div class="col-xxl-3 col-xl-4 col-md-6">
                                        <div class="card follower-card">
                                            <div class="card-body p-3">
                                                <div class="d-flex align-items-start mb-3">
                                                    <div class="flex-grow-1 ">
                                                        <h2 class="mb-1 text-truncate">{{ ucfirst($unit->name) }}</h2>
                                                    </div>
                                                    <div class="flex-shrink-0">
                                                        <div class="dropdown">
                                                            <a class="dropdown-toggle text-primary opacity-50 arrow-none"
                                                                href="#" data-bs-toggle="dropdown"
                                                                aria-haspopup="true" aria-expanded="false">
                                                                <i class="ti ti-dots f-16"></i>
                                                            </a>
                                                            <div class="dropdown-menu dropdown-menu-end">

                                                                @can('edit unit')
                                                                    <a class="dropdown-item customModal" href="#"
                                                                        data-url="{{ route('unit.edit', [$property->id, $unit->id]) }}"
                                                                        data-title="{{ __('Edit Unit') }}" data-size="lg">
                                                                        <i
                                                                            class="material-icons-two-tone">edit</i>{{ __('Edit Unit') }}</a>
                                                                @endcan

                                                                @can('delete unit')
                                                                    {!! Form::open([
                                                                        'method' => 'DELETE',
                                                                        'route' => ['unit.destroy', $property->id, $unit->id],
                                                                        'id' => 'unit-' . $unit->id,
                                                                    ]) !!}

                                                                    <a class="dropdown-item confirm_dialog" href="#">
                                                                        <i class="material-icons-two-tone">delete</i>
                                                                        {{ __('Delete Unit') }}

                                                                    </a>
                                                                    {!! Form::close() !!}
                                                                @endcan
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <hr class="my-3" />


                                                <div class="row">
                                                    <p class="mb-1">{{ __('Bedroom') }} :
                                                        <span class="text-muted">{{ $unit->bedroom }}</span>
                                                    </p>
                                                    <p class="mb-1">{{ __('Kitchen') }} :
                                                        <span class="text-muted">{{ $unit->kitchen }}</span>
                                                    </p>
                                                    <p class="mb-1">{{ __('Bath') }} :
                                                        <span class="text-muted">{{ $unit->baths }}</span>
                                                    </p>
                                                    <p class="mb-1">{{ __('Rent Type') }} :
                                                        <span class="text-muted">{{ $unit->rent_type }}</span>
                                                    </p>
                                                    <p class="mb-1">{{ __('Rent') }} :
                                                        <span class="text-muted">{{ priceFormat($unit->rent) }}</span>
                                                    </p>
                                                     @if ($unit->rent_type == 'custom')
                                                        <p class="mb-1">{{ __('Start Date') }} :
                                                            <span class="text-muted">{{ $unit->start_date }}</span>
                                                        </p>
                                                        <p class="mb-1">{{ __('End Date') }} :
                                                            <span class="text-muted">{{ $unit->end_date }}</span>
                                                        </p>
                                                        <p class="mb-1">{{ __('Payment Due Date') }} :
                                                            <span class="text-muted">{{ $unit->payment_due_date }}</span>
                                                        </p>
                                                    @else
                                                        <p class="mb-1">{{ __('Rent Duration') }} :
                                                            <span class="text-muted">{{ $unit->rent_duration }}</span>
                                                        </p>
                                                    @endif

                                                    <p class="mb-1">{{ __('Deposit Type') }} :
                                                        <span class="text-muted">{{ $unit->deposit_type }}</span>
                                                    </p>
                                                    <p class="mb-1">{{ __('Deposit Amount') }} :
                                                        <span class="text-muted">
                                                            {{ $unit->deposit_type == 'fixed' ? priceFormat($unit->deposit_amount) : $unit->deposit_amount . '%' }}
                                                        </span>
                                                    </p>
                                                    <p class="mb-1">{{ __('Late Fee Type') }} :
                                                        <span class="text-muted">{{ $unit->late_fee_type }}</span>
                                                    </p>
                                                    <p class="mb-1">{{ __('Late Fee Amount') }} :
                                                        <span class="text-muted">
                                                            {{ $unit->late_fee_type == 'fixed' ? priceFormat($unit->late_fee_amount) : $unit->late_fee_amount . '%' }}
                                                        </span>
                                                    </p>
                                                    <p class="mb-1">{{ __('Incident Receipt Amount') }} :
                                                        <span
                                                            class="text-muted">{{ priceFormat($unit->incident_receipt_amount) }}</span>
                                                    </p>
                                                </div>

                                                <hr class="my-2" />
                                                <p class="my-3 text-muted text-sm">
                                                    {{ $unit->notes }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
