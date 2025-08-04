@extends('layouts.app')

@section('page-title')
    {{ __('Tenant Payments') }}
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-md-6">
                            <h4 class="mb-0">{{ __('Tenant Payments') }}</h4>
                        </div>
                        <div class="col-md-6 text-end">
                            @if(Auth::user()->type != 'tenant')
                                <a href="{{ route('tenant.payments.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> {{ __('Create Payment') }}
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if($payments->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>{{ __('Date') }}</th>
                                        @if(Auth::user()->type != 'tenant')
                                            <th>{{ __('Tenant') }}</th>
                                        @endif
                                        <th>{{ __('Type') }}</th>
                                        <th>{{ __('Amount') }}</th>
                                        <th>{{ __('Method') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($payments as $payment)
                                        <tr>
                                            <td>{{ $payment->created_at->format('M d, Y') }}</td>
                                            @if(Auth::user()->type != 'tenant')
                                                <td>
                                                    @if($payment->tenant && $payment->tenant->user)
                                                        {{ $payment->tenant->user->name }}
                                                    @else
                                                        {{ __('N/A') }}
                                                    @endif
                                                </td>
                                            @endif
                                            <td>
                                                <span class="badge badge-info">{{ ucfirst($payment->payment_type) }}</span>
                                                @if($payment->is_recurring)
                                                    <span class="badge badge-warning">{{ __('Recurring') }}</span>
                                                @endif
                                            </td>
                                            <td>{{ $payment->formatted_amount }}</td>
                                            <td>
                                                @if($payment->payment_method == 'stripe')
                                                    <i class="fab fa-stripe"></i> Stripe
                                                @elseif($payment->payment_method == 'paypal')
                                                    <i class="fab fa-paypal"></i> PayPal
                                                @elseif($payment->payment_method == 'bank_transfer')
                                                    <i class="fas fa-university"></i> Bank Transfer
                                                @else
                                                    {{ ucfirst($payment->payment_method) }}
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge {{ $payment->status_badge }}">
                                                    {{ $payment->status }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('tenant.payments.show', $payment->id) }}" 
                                                       class="btn btn-sm btn-info" title="{{ __('View') }}">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    
                                                    @if($payment->status == 'pending')
                                                        <a href="{{ route('tenant.payments.payment', $payment->id) }}" 
                                                           class="btn btn-sm btn-primary" title="{{ __('Pay') }}">
                                                            <i class="fas fa-credit-card"></i>
                                                        </a>
                                                    @endif
                                                    
                                                    @if($payment->is_recurring && $payment->status == 'completed')
                                                        <form action="{{ route('tenant.payments.cancel-recurring', $payment->id) }}" 
                                                              method="POST" style="display: inline;">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-warning" 
                                                                    onclick="return confirm('{{ __('Are you sure you want to cancel this recurring payment?') }}')"
                                                                    title="{{ __('Cancel Recurring') }}">
                                                                <i class="fas fa-times"></i>
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
                        
                        <div class="d-flex justify-content-center">
                            {{ $payments->links() }}
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-credit-card fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">{{ __('No payments found') }}</h5>
                            <p class="text-muted">{{ __('No payment records have been created yet.') }}</p>
                            @if(Auth::user()->type != 'tenant')
                                <a href="{{ route('tenant.payments.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> {{ __('Create First Payment') }}
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 