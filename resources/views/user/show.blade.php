@extends('layouts.app')
@php
    $profile = asset(Storage::url('upload/profile/'));
@endphp
@section('page-title')
    {{ __('Customer Details') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('users.index') }}">{{ __('Customer') }}</a></li>
    <li class="breadcrumb-item" aria-current="page"> {{ __('Show') }}</li>
@endsection
@push('script-page')
    <script>
        $(document).on('change', '.plan_change', function() {
            $('.plan_change_info').hide();
            var plan_id = $('.plan_change:checked').attr('id');
            $('.plan_change_info.' + plan_id).show();
            console.log($('.plan_change_info.' + plan_id));

        });
    </script>
@endpush
@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header pb-0">
                    <ul class="nav nav-tabs profile-tabs" id="myTab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="profile-tab-1" data-bs-toggle="tab" href="#profile-1"
                                role="tab" aria-selected="true">
                                <i class="material-icons-two-tone">request_quote</i>
                                {{ __('Transactions History') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="profile-tab-2" data-bs-toggle="tab" href="#profile-2" role="tab"
                                aria-selected="true">
                                <i class="material-icons-two-tone me-2">description</i>
                                {{ __('Packages') }}
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <div class="tab-pane show active" id="profile-1" role="tabpanel" aria-labelledby="profile-tab-1">
                            <div class="row">
                                <div class="col-lg-4 col-xxl-3">
                                    <div class="card border">
                                        <div class="card-header">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0">
                                                    <img class="img-radius img-fluid wid-40"
                                                        src="{{ !empty($user->profile) ? $profile . '/' . $user->profile : $profile . '/avatar.png' }}"
                                                        alt="User image" />
                                                </div>
                                                <div class="flex-grow-1 mx-3">
                                                    <h5 class="mb-1">{{ $user->name }}</h5>
                                                    <h6 class="text-muted mb-0">{!! $user->SubscriptionLeftDay() !!}</h6>
                                                </div>
                                                <div class="flex-shrink-0">
                                                    <div class="badge bg-primary rounded-pill text-base">
                                                        {{ $user->type }}</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body px-2 pb-0">
                                            <div class="user-info-list">
                                                <div class="user-info-row">
                                                    <div class="user-info-header">
                                                        <span class="user-info-icon"><i class="material-icons-two-tone">email</i></span>
                                                        <span class="user-info-label">{{ __('Email') }}</span>
                                                    </div>
                                                    <span class="user-info-value">{{ $user->email }}</span>
                                                </div>
                                                <div class="user-info-row">
                                                    <div class="user-info-header">
                                                        <span class="user-info-icon"><i class="material-icons-two-tone">badge</i></span>
                                                        <span class="user-info-label">{{ __('Fayda ID') }}</span>
                                                    </div>
                                                    <span class="user-info-value">{{ $user->fayda_id }}</span>
                                                </div>
                                                <div class="user-info-row">
                                                    <div class="user-info-header">
                                                        <span class="user-info-icon"><i class="material-icons-two-tone">phonelink_ring</i></span>
                                                        <span class="user-info-label">{{ __('Phone') }}</span>
                                                    </div>
                                                    <span class="user-info-value">{{ $user->phone_number }}</span>
                                                    @if($user->phone_number)
                                                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $user->phone_number) }}" target="_blank" class="wa-btn" title="Chat on WhatsApp">
                                                            <i class="fab fa-whatsapp"></i>
                                                        </a>
                                                    @endif
                                                </div>
                                                <div class="user-info-row">
                                                    <div class="user-info-header">
                                                        <span class="user-info-icon"><i class="material-icons-two-tone">pin_drop</i></span>
                                                        <span class="user-info-label">{{ __('Package') }}</span>
                                                    </div>
                                                    <span class="user-info-value">{{ !empty($user->subscriptions) ? $user->subscriptions->title : '' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-8 col-xxl-9">
                                    <div class="card border">
                                        <div class="card-header">
                                            <h5>{{ __('Transactions History') }}</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="dt-responsive table-responsive">
                                                <table class="table table-hover advance-datatable">
                                                    <thead>
                                                        <tr>
                                                            <th>{{ __('User') }}</th>
                                                            <th>{{ __('Date') }}</th>
                                                            <th>{{ __('Subscription') }}</th>
                                                            <th>{{ __('Amount') }}</th>
                                                            <th>{{ __('Payment Type') }}</th>
                                                            <th>{{ __('Payment Status') }}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($transactions as $transaction)
                                                            <tr>
                                                                <td>{{ !empty($transaction->users) ? $transaction->users->name : '' }}
                                                                </td>
                                                                <td>{{ dateFormat($transaction->created_at) }}</td>
                                                                <td>{{ !empty($transaction->subscriptions) ? $transaction->subscriptions->title : '-' }}
                                                                </td>
                                                                <td>{{ $settings['CURRENCY_SYMBOL'] . $transaction->amount }}
                                                                </td>
                                                                <td>{{ $transaction->payment_type }}</td>
                                                                <td>
                                                                    @if ($transaction->payment_status == 'Pending')
                                                                        <span
                                                                            class="d-inline badge text-bg-warning">{{ $transaction->payment_status }}</span>
                                                                    @elseif(strtolower($transaction->payment_status) == 'completed')
                                                                        <span
                                                                            class="d-inline badge text-bg-success">{{ $transaction->payment_status }}</span>
                                                                    @elseif($transaction->payment_status=='succeeded' || $transaction->payment_status=='Success')
                                                                        <span
                                                                            class="d-inline badge text-bg-success">{{ $transaction->payment_status }}</span>
                                                                    @else
                                                                        <span
                                                                            class="d-inline badge text-bg-danger">{{ $transaction->payment_status }}</span>
                                                                    @endif


                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="profile-2" role="tabpanel" aria-labelledby="profile-tab-2">
                            <div class="row">
                                <div class="col-lg-4 col-xxl-3">
                                    <div class="card border">
                                        <div class="card-header">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0">
                                                    <img class="img-radius img-fluid wid-40"
                                                        src="{{ !empty($user->profile) ? $profile . '/' . $user->profile : $profile . '/avatar.png' }}"
                                                        alt="User image" />
                                                </div>
                                                <div class="flex-grow-1 mx-3">
                                                    <h5 class="mb-1">{{ $user->name }}</h5>
                                                    <h6 class="text-muted mb-0">{!! $user->SubscriptionLeftDay() !!}</h6>
                                                </div>
                                                <div class="flex-shrink-0">
                                                    <div class="badge bg-primary rounded-pill text-base">
                                                        {{ $user->type }}</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body px-2 pb-0">
                                            <div class="user-info-list">
                                                <div class="user-info-row">
                                                    <div class="user-info-header">
                                                        <span class="user-info-icon"><i class="material-icons-two-tone">email</i></span>
                                                        <span class="user-info-label">{{ __('Email') }}</span>
                                                    </div>
                                                    <span class="user-info-value">{{ $user->email }}</span>
                                                </div>
                                                <div class="user-info-row">
                                                    <div class="user-info-header">
                                                        <span class="user-info-icon"><i class="material-icons-two-tone">badge</i></span>
                                                        <span class="user-info-label">{{ __('Fayda ID') }}</span>
                                                    </div>
                                                    <span class="user-info-value">{{ $user->fayda_id }}</span>
                                                </div>
                                                <div class="user-info-row">
                                                    <div class="user-info-header">
                                                        <span class="user-info-icon"><i class="material-icons-two-tone">phonelink_ring</i></span>
                                                        <span class="user-info-label">{{ __('Phone') }}</span>
                                                    </div>
                                                    <span class="user-info-value">{{ $user->phone_number }}</span>
                                                    @if($user->phone_number)
                                                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $user->phone_number) }}" target="_blank" class="wa-btn" title="Chat on WhatsApp">
                                                            <i class="fab fa-whatsapp"></i>
                                                        </a>
                                                    @endif
                                                </div>
                                                <div class="user-info-row">
                                                    <div class="user-info-header">
                                                        <span class="user-info-icon"><i class="material-icons-two-tone">pin_drop</i></span>
                                                        <span class="user-info-label">{{ __('Package') }}</span>
                                                    </div>
                                                    <span class="user-info-value">{{ !empty($user->subscriptions) ? $user->subscriptions->title : '' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-8 col-xxl-9">
                                    <div class="card border">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-sm-5">
                                                    <img src="../assets/images/admin/img-bulb.svg" alt="images"
                                                        class="img-fluid" />
                                                    @foreach ($subscriptions as $subscription_key => $subscription)
                                                        {{-- <ul class="d-flex flex-column gap-2 mt-3"> --}}
                                                        <ul class="gap-2 mt-3 plan_change_info customCheckdef{{ $subscription_key }}"
                                                            style="display:{{ $subscription->id == $user->subscription ? 'block' : 'none' }}">
                                                            <li>{{ __('User Limit') }} {{ $subscription->user_limit }}
                                                            </li>
                                                            <li>{{ __('Property Limit') }} {{ $subscription->property_limit }}
                                                            </li>
                                                            <li>{{ __('Tenant Limit') }} {{ $subscription->tenant_limit }}
                                                            </li>
                                                            @if ($subscription->enabled_logged_history)
                                                                <li>{{ __('Enabled') }} {{ __('Logged History') }}</li>
                                                            @else
                                                                <li>{{ __('Disable') }} {{ __('Logged History') }}</li>
                                                            @endif
                                                        </ul>
                                                    @endforeach
                                                </div>
                                                <div class="col-sm-7">
                                                    <div class="course-price">
                                                        @foreach ($subscriptions as $sitem_key => $item)
                                                            <div class="form-check p-0">
                                                                <input type="radio" name="radio1"
                                                                    class="form-check-input input-primary plan_change"
                                                                    {{ $item->id == $user->subscription ? 'checked' : '' }}
                                                                    id="customCheckdef{{ $sitem_key }}" />
                                                                <label class="form-check-label d-block"
                                                                    for="customCheckdef{{ $sitem_key }}">
                                                                    <span class="d-flex align-items-center">
                                                                        <span class="flex-grow-1 me-3">
                                                                            <span
                                                                                class="h5 d-block">{{ $item->title }}</span>
                                                                            @if ($item->id == $user->subscription)
                                                                                <span
                                                                                    class="badge">{{ $item->id == $user->subscription ? __('Active') : __('Click to Select') }}</span>
                                                                            @else
                                                                                {!! Form::open([
                                                                                    'method' => 'POST',
                                                                                    'route' => [
                                                                                        'subscription.manual_assign_package',
                                                                                        [\Illuminate\Support\Facades\Crypt::encrypt($item->id), $user->id],
                                                                                    ],
                                                                                ]) !!}
                                                                                <a class="text-danger confirm_dialog"
                                                                                    data-dialog-title="{{ __('Are you sure want to Change Package?') }}"
                                                                                    data-dialog-text="{{ __('This record can not be restore after change. Do you want to confirm?') }}"
                                                                                    data-bs-toggle="tooltip"
                                                                                    data-bs-original-title="{{ __('Select') }}"
                                                                                    href="#"> <span
                                                                                        class="badge">{{ $item->id == $user->subscription ? __('Active') : __('Click to Select') }}</span>
                                                                                </a>
                                                                                {!! Form::close() !!}
                                                                            @endif
                                                                        </span>
                                                                        <span class="flex-shrink-0">
                                                                            <span class="h3 mb-0">
                                                                                {{ $item->package_amount }}{{ subscriptionPaymentSettings()['CURRENCY_SYMBOL'] }}/
                                                                                <span
                                                                                    class="text-sm">{{ $item->interval }}</span>
                                                                            </span>
                                                                        </span>
                                                                    </span>
                                                                </label>
                                                            </div>
                                                        @endforeach
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
    </div>
@endsection

<style>
    .list-group-item-action {
        margin-bottom: 0.75rem;
        border-radius: 0.5rem;
    }
    .list-group-item-action:last-child {
        margin-bottom: 0;
    }
    .user-info-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    .user-info-row {
        background: #f8f9fa;
        border-radius: 0.5rem;
        padding: 1rem 1.25rem;
        box-shadow: 0 1px 2px rgba(60,60,60,0.03);
        min-width: 220px;
        width: 100%;
        word-break: break-word;
        display: flex;
        flex-direction: column;
        align-items: flex-start;
    }
    .user-info-header {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 0.25rem;
    }
    .user-info-icon {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #e9ecef;
        border-radius: 50%;
        font-size: 1.1rem;
        color: #4f4f4f;
    }
    .user-info-label {
        font-weight: 600;
        color: #222;
        font-size: 1rem;
        margin-left: 0.5rem;
    }
    .user-info-value {
        font-size: 0.96rem;
        color: #333;
        margin-top: 0.25rem;
        word-break: break-word;
        align-self: flex-start;
    }
    .wa-btn {
        background: #25d366;
        color: #fff;
        border-radius: 50%;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        margin-top: 0.5rem;
        transition: background 0.2s;
        border: none;
    }
    .wa-btn:hover {
        background: #128c7e;
        color: #fff;
        text-decoration: none;
    }
</style>
