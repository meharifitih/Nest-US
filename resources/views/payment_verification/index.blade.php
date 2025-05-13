@extends('layouts.app')
@section('page-title')
    {{ __('Payment Verification') }}
@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>{{ __('Pending Payment Verifications') }}</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>{{ __('User') }}</th>
                                    <th>{{ __('Package') }}</th>
                                    <th>{{ __('Amount') }}</th>
                                    <th>{{ __('Payment Screenshot') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($transactions as $transaction)
                                    <tr>
                                        <td>{{ $transaction->user->name }}</td>
                                        <td>{{ $transaction->subscription->title }}</td>
                                        <td>{{ subscriptionPaymentSettings()['CURRENCY_SYMBOL'] }}{{ $transaction->amount }}</td>
                                        <td>
                                            @if($transaction->payment_screenshot)
                                                <a href="{{ asset('storage/payment_screenshots/'.$transaction->payment_screenshot) }}" target="_blank">
                                                    <img src="{{ asset('storage/payment_screenshots/'.$transaction->payment_screenshot) }}" alt="Payment Screenshot" style="max-width: 100px;">
                                                </a>
                                            @else
                                                {{ __('No screenshot') }}
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-warning">{{ __('Pending') }}</span>
                                        </td>
                                        <td>
                                            <div class="action-btn bg-success ms-2">
                                                <a href="#" class="mx-3 btn btn-sm align-items-center" data-bs-toggle="modal" data-bs-target="#approveModal{{ $transaction->id }}">
                                                    <i class="ti ti-check text-white"></i>
                                                </a>
                                            </div>
                                            <div class="action-btn bg-danger ms-2">
                                                <a href="#" class="mx-3 btn btn-sm align-items-center" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $transaction->id }}">
                                                    <i class="ti ti-x text-white"></i>
                                                </a>
                                            </div>

                                            <!-- Approve Modal -->
                                            <div class="modal fade" id="approveModal{{ $transaction->id }}" tabindex="-1" role="dialog" aria-labelledby="approveModalLabel{{ $transaction->id }}" aria-hidden="true">
                                                <div class="modal-dialog" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="approveModalLabel{{ $transaction->id }}">{{ __('Approve Payment') }}</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            {{ __('Are you sure you want to approve this payment?') }}
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                                                            <a href="{{ route('payment.verification.approve', $transaction->id) }}" class="btn btn-success">{{ __('Approve') }}</a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Reject Modal -->
                                            <div class="modal fade" id="rejectModal{{ $transaction->id }}" tabindex="-1" role="dialog" aria-labelledby="rejectModalLabel{{ $transaction->id }}" aria-hidden="true">
                                                <div class="modal-dialog" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="rejectModalLabel{{ $transaction->id }}">{{ __('Reject Payment') }}</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <form action="{{ route('payment.verification.reject', $transaction->id) }}" method="POST">
                                                            @csrf
                                                            <div class="modal-body">
                                                                <div class="form-group">
                                                                    <label for="rejection_reason">{{ __('Rejection Reason') }}</label>
                                                                    <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="3" required></textarea>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                                                                <button type="submit" class="btn btn-danger">{{ __('Reject') }}</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
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