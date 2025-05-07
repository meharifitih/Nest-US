@extends('layouts.app')
@section('page-title')
    {{ __('Maintenance Request') }}
@endsection
@push('script-page')
@endpush


@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item" aria-current="page"> {{ __('Maintenance Request') }}</li>
@endsection


@section('card-action-btn')
    @can('create maintenance request')
        <a class="btn btn-secondary btn-sm ml-20 customModal" href="#" data-size="lg"
            data-url="{{ route('maintenance-request.create') }}" data-title="{{ __('Create Maintenance Request') }}"> <i
                class="ti-plus mr-5"></i>{{ __('Create Maintenance Request') }}</a>
    @endcan
@endsection


@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card table-card">
                <div class="card-header">
                    <div class="row align-items-center g-2">
                        <div class="col">
                            <h5>{{ __('Maintenance Request List') }}</h5>
                        </div>
                        @can('create maintenance request')
                            <div class="col-auto">
                                <a href="#" class="btn btn-secondary customModal" data-size="lg"
                                    data-url="{{ route('maintenance-request.create') }}" data-title="{{ __('Create Maintenance Request') }}"> <i
                                        class="ti ti-circle-plus align-text-bottom"></i> {{ __('Create Maintenance Request') }}</a>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="dt-responsive table-responsive">
                        <table class="table table-hover advance-datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Property') }}</th>
                                    <th>{{ __('Unit') }}</th>
                                    <th>{{ __('Issue') }}</th>
                                    <th>{{ __('Maintainer') }}</th>
                                    <th>{{ __('Request Date') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Attachment') }}</th>
                                    @if (Gate::check('edit maintenance request') ||
                                            Gate::check('delete maintenance request') ||
                                            Gate::check('show maintenance request'))
                                        <th class="text-right">{{ __('Action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($maintenanceRequests as $request)
                                    <tr role="row">
                                        <td> {{ !empty($request->properties) ? $request->properties->name : '-' }} </td>
                                        <td> {{ !empty($request->units) ? $request->units->name : '-' }} </td>
                                        <td> {{ !empty($request->types) ? $request->types->title : '-' }} </td>
                                        <td> {{ !empty($request->maintainers) ? $request->maintainers->name : '-' }} </td>
                                        <td> {{ dateFormat($request->request_date) }} </td>
                                        <td>
                                            @if ($request->status == 'pending')
                                                <span class="badge bg-light-warning">
                                                    {{ \App\Models\MaintenanceRequest::$status[$request->status] }}</span>
                                            @elseif($request->status == 'in_progress')
                                                <span class="badge bg-light-info">
                                                    {{ \App\Models\MaintenanceRequest::$status[$request->status] }}</span>
                                            @else
                                                <span class="badge bg-light-success">
                                                    {{ \App\Models\MaintenanceRequest::$status[$request->status] }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if (!empty($request->issue_attachment))
                                                <a href="{{ asset(Storage::url('upload/issue_attachment')) . '/' . $request->issue_attachment }}"
                                                    download="download"><i class="ti ti-download"></i></a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        @if (Gate::check('edit maintenance request') ||
                                                Gate::check('delete maintenance request') ||
                                                Gate::check('show maintenance request'))
                                            <td class="text-right">
                                                <div class="cart-action">
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['maintenance-request.destroy', $request->id]]) !!}
                                                    @can('show maintenance request')
                                                        <a class="avtar avtar-xs btn-link-warning text-warning customModal" data-size="lg"
                                                            data-bs-toggle="tooltip"
                                                            data-bs-original-title="{{ __('View') }}" href="#"
                                                            data-url="{{ route('maintenance-request.show', $request->id) }}"
                                                            data-title="{{ __('Maintenance Request Details') }}"> <i
                                                                data-feather="eye"></i></a>
                                                    @endcan
                                                    @can('edit maintenance request')
                                                        <a class="avtar avtar-xs btn-link-secondary text-secondary customModal" data-size="lg"
                                                            data-bs-toggle="tooltip"
                                                            data-bs-original-title="{{ __('Edit') }}" href="#"
                                                            data-url="{{ route('maintenance-request.edit', $request->id) }}"
                                                            data-title="{{ __('Maintenance Request') }}"> <i
                                                                data-feather="edit"></i></a>
                                                    @endcan
                                                    @can('delete maintenance request')
                                                        <a class="avtar avtar-xs btn-link-danger text-danger confirm_dialog" data-bs-toggle="tooltip"
                                                            data-bs-original-title="{{ __('Detete') }}" href="#"> <i
                                                                data-feather="trash-2"></i></a>
                                                    @endcan
                                                    @if (\Auth::user()->type == 'maintainer')
                                                        <a class="avtar avtar-xs btn-link-secondary text-secondary customModal" data-size="lg"
                                                            data-bs-toggle="tooltip"
                                                            data-bs-original-title="{{ __('Status Update') }}"
                                                            href="#"
                                                            data-url="{{ route('maintenance-request.action', $request->id) }}"
                                                            data-title="{{ __('Maintenance Request Status') }}"> <i
                                                                data-feather="check-square"></i></a>
                                                    @endif
                                                    {!! Form::close() !!}
                                                </div>

                                            </td>
                                        @endif
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
