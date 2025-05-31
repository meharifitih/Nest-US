@extends('layouts.app')
@section('page-title')
    {{ __('Maintainer') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item" aria-current="page"> {{ __('Maintainer') }}</li>
@endsection


@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center g-2">
                        <div class="col">
                            <h5>{{ __('Maintainer List') }}</h5>
                        </div>
                        @can('create maintainer')
                            <div class="col-auto">
                                <a class="btn btn-secondary customModal" href="#"
                                    data-url="{{ route('maintainer.create') }}" data-title="{{ __('Create Maintainer') }}"
                                    data-size="lg"> <i class="ti ti-circle-plus align-text-bottom "></i>
                                    {{ __('Create Maintainer') }}</a>
                            </div>
                        @endcan
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        @foreach ($maintainers as $maintainer)
                            <div class="col-xxl-3 col-xl-4 col-md-6">
                                <div class="card follower-card">
                                    <div class="card-body p-3">
                                        <div class="d-flex align-items-start mb-3">
                                            <div class="flex-grow-1 ms-3 mx-2">
                                                <img class="img-fluid wid-70"
                                                    src="{{ !empty($maintainer->user) && !empty($maintainer->user->profile) ? asset(Storage::url('upload/profile/' . $maintainer->user->profile)) : asset(Storage::url('upload/profile/avatar.png')) }}"
                                                    alt="">
                                            </div>
                                            @if (Gate::check('edit maintainer') || Gate::check('delete maintainer') || Gate::check('show maintainer'))
                                                <div class="flex-shrink-0">
                                                    <div class="dropdown">
                                                        <a class="dropdown-toggle text-primary opacity-50 arrow-none"
                                                            href="#" data-bs-toggle="dropdown" aria-haspopup="true"
                                                            aria-expanded="false">
                                                            <i class="ti ti-dots f-16"></i>
                                                        </a>
                                                        <div class="dropdown-menu dropdown-menu-end">
                                                            @can('edit maintainer')
                                                                <a class="dropdown-item customModal" href="#"
                                                                    data-url="{{ route('maintainer.edit', $maintainer->id) }}"
                                                                    data-title="{{ __('Edit Maintainer') }}">
                                                                    <i class="material-icons-two-tone">edit</i>
                                                                    {{ __('Edit Maintainer') }}
                                                                </a>
                                                            @endcan
                                                            @can('delete maintainer')
                                                                {!! Form::open([
                                                                    'method' => 'DELETE',
                                                                    'route' => ['maintainer.destroy', $maintainer->id],
                                                                    'id' => 'maintainer-' . $maintainer->id,
                                                                ]) !!}
                                                                <a class="dropdown-item confirm_dialog" href="#">
                                                                    <i class="material-icons-two-tone">delete</i>
                                                                    {{ __('Delete Maintainer') }}
                                                                </a>
                                                                {!! Form::close() !!}
                                                            @endcan
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>

                                        <a class="customModal" href="#"
                                            data-url="{{ route('maintainer.edit', $maintainer->id) }}"
                                            data-title="{{ __('Edit Maintainer') }}">
                                            <h4>{{ ucfirst(!empty($maintainer->user) ? $maintainer->user->first_name : '') . ' ' . ucfirst(!empty($maintainer->user) ? $maintainer->user->last_name : '') }}
                                            </h4>

                                        </a>
                                        <h6 class="text-truncate text-muted d-flex align-items-center mb-2">
                                            {{ !empty($maintainer->user) ? $maintainer->user->email : '-' }}</h6>

                                        <div class="row">
                                            {{-- <div class="col-sm-12 mb-2">
                                                <h5 class="text-primary"><i
                                                        class="ti ti-info-circle bg-light-info rounded-pill"></i>
                                                    {{ __('Infomation') }}
                                                </h5>

                                            </div> --}}
                                            @if ($maintainer->user->phone_number)
                                                <div class="col-sm-6 mb-3">
                                                    <p class="mb-0 text-muted text-sm">{{ __('Phone') }} :</p>
                                                    <h6 class="mb-0 d-flex align-items-center">
                                                        {{ !empty($maintainer->user) ? $maintainer->user->phone_number : '-' }}
                                                        @if(!empty($maintainer->user) && $maintainer->user->phone_number)
                                                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $maintainer->user->phone_number) }}" target="_blank" class="btn btn-sm btn-success ms-2">
                                                                <i class="fab fa-whatsapp"></i>
                                                            </a>
                                                        @endif
                                                    </h6>
                                                </div>
                                            @endif
                                            <div class="col-sm-6 mb-3">
                                                <p class="mb-0 text-muted text-sm">{{ __('Type') }} :</p>
                                                <h6 class="mb-0">
                                                    {{ !empty($maintainer->types) ? $maintainer->types->title : '-' }}</h6>
                                            </div>
                                            <div class="col-sm-6 mb-3">
                                                <p class="mb-0 text-muted text-sm">{{ __('Created Date') }} :</p>
                                                <h6 class="mb-0">
                                                    {{ dateFormat($maintainer->created_at) }}</h6>
                                            </div>
                                            <div class="col-sm-12 mb-3">
                                                <span>{{ __('Property') }} :</span>
                                                <ul>
                                                    @foreach ($maintainer->properties() as $property)
                                                        <li>{{ $property->name }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
