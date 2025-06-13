@extends('layouts.app')
@section('page-title')
    {{ __('Transaction') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item" aria-current="page"> {{ __('Transaction') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card table-card">
                <div class="card-header">
                    <div class="row align-items-center g-2">
                        <div class="col">
                            <h5>{{ __('Pricing Package Transaction List') }}</h5>
                        </div>
                        <div class="col-auto d-flex gap-2">
                            <button type="button" class="btn btn-primary px-3" data-bs-toggle="modal" data-bs-target="#filterModal">
                                <i class="ti ti-filter me-1"></i> {{ __('Filter') }}
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <!-- Filter Modal -->
                    <div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="filterModalLabel">{{ __('Filter Transactions') }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form method="GET" action="{{ route('subscription.transaction') }}">
                                    <div class="modal-body">
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    {{ Form::label('user_id', __('User'), ['class' => 'form-label']) }}
                                                    <select name="user_id" class="form-select">
                                                        <option value="">{{ __('All') }}</option>
                                                        @foreach($users as $user)
                                                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                                                {{ $user->name ?? ($user->first_name . ' ' . $user->last_name) }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    {{ Form::label('subscription_id', __('Subscription'), ['class' => 'form-label']) }}
                                                    <select name="subscription_id" class="form-select">
                                                        <option value="">{{ __('All') }}</option>
                                                        @foreach($subscriptions as $subscription)
                                                            <option value="{{ $subscription->id }}" {{ request('subscription_id') == $subscription->id ? 'selected' : '' }}>
                                                                {{ $subscription->title }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    {{ Form::label('payment_type', __('Payment Type'), ['class' => 'form-label']) }}
                                                    <select name="payment_type" class="form-select">
                                                        <option value="">{{ __('All') }}</option>
                                                        <option value="Stripe" {{ request('payment_type') == 'Stripe' ? 'selected' : '' }}>Stripe</option>
                                                        <option value="Bank Transfer" {{ request('payment_type') == 'Bank Transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                                        <option value="CBE" {{ request('payment_type') == 'CBE' ? 'selected' : '' }}>CBE</option>
                                                        <option value="TELEBIRR" {{ request('payment_type') == 'TELEBIRR' ? 'selected' : '' }}>TELEBIRR</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    {{ Form::label('payment_status', __('Payment Status'), ['class' => 'form-label']) }}
                                                    <select name="payment_status" class="form-select">
                                                        <option value="">{{ __('All') }}</option>
                                                        <option value="completed" {{ request('payment_status') == 'completed' ? 'selected' : '' }}>{{ __('Completed') }}</option>
                                                        <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                                                        <option value="rejected" {{ request('payment_status') == 'rejected' ? 'selected' : '' }}>{{ __('Rejected') }}</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    {{ Form::label('date', __('Date'), ['class' => 'form-label']) }}
                                                    {{ Form::date('date', request('date'), ['class' => 'form-control']) }}
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    {{ Form::label('amount', __('Amount'), ['class' => 'form-label']) }}
                                                    {{ Form::number('amount', request('amount'), ['class' => 'form-control', 'placeholder' => __('Enter Amount')]) }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-primary px-4">{{ __('Apply Filter') }}</button>
                                        <a href="{{ route('subscription.transaction') }}" class="btn btn-light px-4">{{ __('Reset') }}</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="dt-responsive table-responsive">
                        <table class="table table-hover advance-datatable">
                            <thead>
                                <tr>
                                    <th>{{__('User')}}</th>
                                    <th>{{__('Date')}}</th>
                                    <th>{{__('Subscription')}}</th>
                                    <th>{{__('Amount')}}</th>
                                    <th>{{__('Payment Type')}}</th>
                                    <th>{{__('Payment Status')}}</th>
                                    <th>{{__('Receipt')}}</th>
                                    @if(Auth::user()->type=='super admin')
                                        <th>{{__('Action')}}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($transactions as $transaction)
                            <tr>
                                <td>{{!empty($transaction->user)?$transaction->user->name:''}}</td>
                                <td>{{dateFormat($transaction->created_at)}}</td>
                                <td>{{!empty($transaction->subscription)?$transaction->subscription->title:'-'}}</td>
                                <td>{{$settings['CURRENCY_SYMBOL'].$transaction->amount}}</td>
                                <td>{{$transaction->payment_type}}</td>
                                <td>
                                    @if($transaction->payment_status == 'completed')
                                        <span class="badge bg-success">completed</span>
                                    @elseif($transaction->payment_status == 'pending')
                                        <span class="badge bg-warning">pending</span>
                                    @else
                                        <span class="badge bg-secondary">{{$transaction->payment_status}}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($transaction->payment_type=='Stripe')
                                        <a class="text-primary" data-bs-toggle="tooltip" target="_blank"
                                           data-bs-original-title="{{__('Receipt')}}" href="{{$transaction->receipt}}">
                                            <i data-feather="file"></i></a>
                                    @elseif($transaction->payment_type=='Bank Transfer')
                                        @if(!empty($transaction->receipt))
                                            <a class="text-primary" data-bs-toggle="tooltip" target="_blank"
                                               data-bs-original-title="{{__('Receipt')}}"
                                               href="{{ asset('storage/upload/payment_receipt/' . $transaction->receipt) }}">
                                                <i data-feather="file"></i>
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    @elseif($transaction->payment_type=='CBE' || $transaction->payment_type=='TELEBIRR')
                                        @if(!empty($transaction->receipt))
                                            <a class="text-primary" data-bs-toggle="tooltip" target="_blank"
                                               data-bs-original-title="{{__('Receipt')}}"
                                               href="{{ $transaction->receipt }}">
                                                <i data-feather="file"></i>
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                @if(Auth::user()->type=='super admin')
                                <td>
                                    @if(($transaction->payment_status=='Pending' || $transaction->payment_status=='pending'))
                                        <form action="{{ route('admin.payments.approve', $transaction->id) }}" method="POST" style="display:inline-block;">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-sm" data-bs-toggle="tooltip" data-bs-original-title="{{__('Approve')}}">
                                                <i data-feather="user-check"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.payments.reject', $transaction->id) }}" method="POST" style="display:inline-block;">
                                            @csrf
                                            <button type="submit" class="btn btn-danger btn-sm" data-bs-toggle="tooltip" data-bs-original-title="{{__('Reject')}}">
                                                <i data-feather="user-x"></i>
                                            </button>
                                        </form>
                                    @endif
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
