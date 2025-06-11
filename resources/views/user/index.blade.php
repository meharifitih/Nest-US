@extends('layouts.app')
@php
    $profile = asset(Storage::url('upload/profile/'));
@endphp
@section('page-title')
    @if (\Auth::user()->type == 'super admin')
        {{ __('Customer') }}
    @else
        {{ __('User') }}
    @endif
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
    </li>
    <li class="breadcrumb-item" aria-current="page">
        @if (\Auth::user()->type == 'super admin')
            {{ __('Customers') }}
        @else
            {{ __('Users') }}
        @endif
    </li>
@endsection
@section('content')
    <div class="row">
        <div class="col-sm-12">


            <div class="card table-card">
                <div class="card-header">
                    <div class="row align-items-center g-2">
                        <div class="col">
                            <h5> @if (\Auth::user()->type == 'super admin')
                                {{ __('Customer List') }}
                            @else
                                {{ __('User List') }}
                            @endif</h5>
                        </div>
                        @if (Gate::check('create user'))
                            <div class="col-auto">
                                <a href="#" class="btn btn-secondary customModal" data-size="lg"
                                    data-url="{{ route('users.create') }}" data-title="{{ __('Create User') }}"> <i
                                        class="ti ti-circle-plus align-text-bottom"></i> {{ __('Create User') }}</a>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="dt-responsive table-responsive">
                        <table class="table table-hover advance-datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('User') }}</th>
                                    <th>{{ __('Email') }}</th>
                                    <th>{{ __('Active Package') }}</th>
                                    <th>{{ __('Package Due Date') }}</th>
                                    <th>{{ __('Approval Status') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($users as $user)
                                    <tr class="clickable-customer-row" data-href="{{ route('users.show', $user->id) }}">
                                        <td class="table-user">
                                            <img src="{{ !empty($user->avatar) ? asset(Storage::url('upload/profile')) . '/' . $user->avatar : asset(Storage::url('upload/profile')) . '/avatar.png' }}"
                                                alt="" class="mr-2 avatar-sm rounded-circle user-avatar">
                                            <a href="#" class="text-body font-weight-semibold" onclick="event.stopPropagation();">{{ $user->name }}</a>
                                        </td>
                                        <td>{{ $user->email }} </td>
                                        @if (\Auth::user()->type == 'super admin')
                                            <td>{{ !empty($user->subscriptions) ? $user->subscriptions->title : '-' }}</td>
                                            <td>{{ !empty($user->subscription_expire_date) ? dateFormat($user->subscription_expire_date) : __('Unlimited') }}</td>
                                            <td>
                                                <span class="badge bg-{{ $user->approval_status === 'approved' ? 'success' : ($user->approval_status === 'rejected' ? 'danger' : 'warning') }}">
                                                    {{ ucfirst($user->approval_status) }}
                                                </span>
                                            </td>
                                        @else
                                            <td>-</td>
                                            <td>-</td>
                                            <td>-</td>
                                        @endif
                                        <td>
                                            <div class="cart-action">
                                                @can('show user')
                                                    <a class="avtar avtar-xs btn-link-warning text-warning" data-bs-toggle="tooltip"
                                                        data-bs-original-title="{{ __('Show') }}"
                                                        href="{{ route('users.show', $user->id) }}"
                                                        data-title="{{ __('Edit User') }}" onclick="event.stopPropagation();"> <i data-feather="eye"></i></a>
                                                @endcan
                                                @can('edit user')
                                                    <a class="avtar avtar-xs btn-link-secondary text-secondary customModal" data-bs-toggle="tooltip"
                                                        data-size="lg" data-bs-original-title="{{ __('Edit') }}"
                                                        href="#" data-url="{{ route('users.edit', $user->id) }}"
                                                        data-title="{{ __('Edit User') }}" onclick="event.stopPropagation();"> <i data-feather="edit"></i></a>
                                                @endcan
                                                @can('delete user')
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['users.destroy', $user->id], 'class' => 'd-inline']) !!}
                                                        <button type="submit" class="avtar avtar-xs btn-link-danger text-danger confirm_dialog" data-bs-toggle="tooltip"
                                                            data-bs-original-title="{{ __('Delete') }}" onclick="event.stopPropagation();">
                                                            <i data-feather="trash-2"></i>
                                                        </button>
                                                    {!! Form::close() !!}
                                                @endcan
                                                @if (Auth::user()->canImpersonate())
                                                    <a class="avtar avtar-xs btn-link-info text-info" data-bs-toggle="tooltip"
                                                        data-bs-original-title="{{ __('Continue as Customer') }}"
                                                        href="{{ route('impersonate', $user->id) }}" target="_blank" onclick="event.stopPropagation();"> <i
                                                            data-feather="log-in"></i></a>
                                                @endif
                                                @if (\Auth::user()->type == 'super admin' && $user->type === 'owner' && $user->approval_status === 'pending')
                                                    <form action="{{ route('users.approve', $user->id) }}" method="POST" class="d-inline" onsubmit="event.stopPropagation();">
                                                        @csrf
                                                        <button type="submit" class="avtar avtar-xs btn-link-success text-success" data-bs-toggle="tooltip"
                                                            data-bs-original-title="{{ __('Approve') }}">
                                                            <i data-feather="check"></i>
                                                        </button>
                                                    </form>
                                                    <form action="{{ route('users.reject', $user->id) }}" method="POST" class="d-inline" onsubmit="event.stopPropagation();">
                                                        @csrf
                                                        <button type="submit" class="avtar avtar-xs btn-link-danger text-danger" data-bs-toggle="tooltip"
                                                            data-bs-original-title="{{ __('Reject') }}">
                                                            <i data-feather="x"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4 d-flex justify-content-center">
                        {{ $users->links('vendor.pagination.bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script-page')
<script>
    $(document).on('click', '.clickable-customer-row', function(e) {
        if (!$(e.target).closest('a, button, input, .cart-action').length) {
            window.location = $(this).data('href');
        }
    });
</script>
@endpush
