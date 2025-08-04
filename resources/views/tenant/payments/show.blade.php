@extends('layouts.app')

@section('page-title')
    {{ __('Payment Details') }}
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">{{ __('Payment Details') }}</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>{{ __('Payment Information') }}</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>{{ __('Payment ID') }}:</strong></td>
                                    <td>#{{ $payment->id }}</td>
                                </tr>
                                <tr>
                                    <td><strong>{{ __('Amount') }}:</strong></td>
                                    <td>{{ $payment->formatted_amount }}</td>
                                </tr>
                                <tr>
                                    <td><strong>{{ __('Status') }}:</strong></td>
                                    <td>
                                        <span class="badge {{ $payment->status_badge }}">
                                            {{ ucfirst($payment->status) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>{{ __('Payment Method') }}:</strong></td>
                                    <td>{{ ucfirst($payment->payment_method) }}</td>
                                </tr>
                                <tr>
                                    <td><strong>{{ __('Due Date') }}:</strong></td>
                                    <td>{{ $payment->due_date->format('M d, Y') }}</td>
                                </tr>
                            </table>
                        </div>
                        
                        <div class="col-md-6">
                            <h5>{{ __('Tenant Information') }}</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>{{ __('Name') }}:</strong></td>
                                    <td>{{ $payment->tenant->user->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>{{ __('Email') }}:</strong></td>
                                    <td>{{ $payment->tenant->user->email ?? 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($payment->status == 'pending')
                    <div class="row mt-4">
                        <div class="col-md-12 text-center">
                            <a href="{{ route('tenant.payments.payment', $payment->id) }}" class="btn btn-primary btn-lg">
                                <i class="fas fa-credit-card"></i> {{ __('Pay Now') }}
                            </a>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 