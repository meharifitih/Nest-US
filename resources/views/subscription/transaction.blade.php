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

                    </div>
                </div>
                <div class="card-body pt-0">
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
                                    @if($transaction->payment_status=='Pending' || $transaction->payment_status=='pending')
                                        <span class="d-inline badge text-bg-warning">{{$transaction->payment_status}}</span>
                                    @elseif($transaction->payment_status=='succeeded' || $transaction->payment_status=='Success' || $transaction->payment_status=='approved')
                                        <span class="d-inline badge text-bg-success">{{$transaction->payment_status}}</span>
                                    @else
                                        <span class="d-inline badge text-bg-danger">{{$transaction->payment_status}}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($transaction->payment_type=='Stripe')
                                        <a class="text-primary" data-bs-toggle="tooltip" target="_blank"
                                           data-bs-original-title="{{__('Receipt')}}" href="{{$transaction->receipt}}">
                                            <i data-feather="file"></i></a>
                                    @elseif($transaction->payment_type=='Bank Transfer')
                                        <a class="text-primary" data-bs-toggle="tooltip" target="_blank"
                                           data-bs-original-title="{{__('Receipt')}}"
                                           href="{{asset('/storage/upload/payment_receipt/'.$transaction->receipt)}}">
                                            <i data-feather="file"></i></a>
                                        @if(Auth::user()->type=='super admin' && ($transaction->payment_status=='Pending' || $transaction->payment_status=='pending'))
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
@endsection
