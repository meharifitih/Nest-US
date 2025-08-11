<div class="dt-responsive table-responsive">
    <table class="table table-hover ">
        <thead>
            <tr>
                <th>{{ __('Transaction Id') }}</th>
                <th>{{ __('Payment Date') }}</th>
                <th>{{ __('Amount') }}</th>
                <th>{{ __('Notes') }}</th>
                <th>{{ __('Receipt') }}</th>
                <th>{{ __('Status') }}</th>
                @can('delete invoice payment')
                    <th class="text-right">{{ __('Action') }}</th>
                @endcan
            </tr>
        </thead>
        <tbody>
            @foreach ($invoice->payments as $payment)
                <tr role="row">
                    <td>{{ $payment->transaction_id }} </td>
                    <td>{{ dateFormat($payment->payment_date) }} </td>
                    <td>{{ priceFormat($payment->amount) }} </td>
                    <td>{{ $payment->notes }} </td>
                    <td>
                        @if(strtoupper($payment->payment_type) == 'CBE' && !empty($payment->receipt))
                            <a href="{{ $payment->receipt }}" target="_blank"><i class="fa fa-receipt"></i> View Receipt</a>
                        @elseif(strtoupper($payment->payment_type) == 'TELEBIRR' && !empty($payment->receipt))
                            <a href="{{ $payment->receipt }}" target="_blank"><i class="fa fa-receipt"></i> View Receipt</a>
                        @elseif(strtolower($payment->payment_type) == 'bank transfer' && !empty($payment->receipt))
                            <a href="#" data-bs-toggle="modal" data-bs-target="#receiptModal{{ $payment->id }}"><i class="fa fa-image"></i> View Receipt</a>
                            <!-- Modal -->
                            <div class="modal fade" id="receiptModal{{ $payment->id }}" tabindex="-1" aria-labelledby="receiptModalLabel{{ $payment->id }}" aria-hidden="true">
                              <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                  <div class="modal-header">
                                    <h5 class="modal-title" id="receiptModalLabel{{ $payment->id }}">Bank Transfer Receipt</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                  </div>
                                  <div class="modal-body text-center">
                                    <img src="{{ asset(Storage::url('upload/receipt')) . '/' . $payment->receipt }}" alt="Bank Transfer Receipt" class="img-fluid" style="max-width:100%;max-height:400px;">
                                  </div>
                                </div>
                              </div>
                            </div>
                        @elseif(strtoupper($payment->payment_type) == 'STRIPE' && !empty($payment->receipt))
                            <a href="#" data-bs-toggle="modal" data-bs-target="#stripeReceiptModal{{ $payment->id }}"><i class="fa fa-credit-card"></i> View Receipt</a>
                            <!-- Stripe Receipt Modal -->
                            <div class="modal fade" id="stripeReceiptModal{{ $payment->id }}" tabindex="-1" aria-labelledby="stripeReceiptModalLabel{{ $payment->id }}" aria-hidden="true">
                              <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                  <div class="modal-header bg-primary text-white">
                                    <h5 class="modal-title" id="stripeReceiptModalLabel{{ $payment->id }}">
                                        <i class="fa fa-credit-card me-2"></i>Stripe Payment Receipt
                                    </h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                  </div>
                                  <div class="modal-body">
                                    @php
                                        $receiptParts = explode(':', $payment->receipt, 2);
                                        $transactionId = $receiptParts[1] ?? $payment->receipt;
                                    @endphp
                                    <div class="text-center mb-4">
                                        <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                            <i class="fa fa-credit-card fa-2x text-primary"></i>
                                        </div>
                                        <h6 class="text-success mb-0">Payment Successful</h6>
                                        <small class="text-muted">Processed via Stripe</small>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="card border-0 bg-light">
                                                <div class="card-body">
                                                    <div class="row mb-3">
                                                        <div class="col-6">
                                                            <strong class="text-muted">Transaction ID:</strong>
                                                        </div>
                                                        <div class="col-6">
                                                            <code class="text-primary">{{ $transactionId }}</code>
                                                        </div>
                                                    </div>
                                                    <div class="row mb-3">
                                                        <div class="col-6">
                                                            <strong class="text-muted">Payment Method:</strong>
                                                        </div>
                                                        <div class="col-6">
                                                            <span class="badge bg-primary">Credit Card</span>
                                                        </div>
                                                    </div>
                                                    <div class="row mb-3">
                                                        <div class="col-6">
                                                            <strong class="text-muted">Amount:</strong>
                                                        </div>
                                                        <div class="col-6">
                                                            <span class="h5 text-success mb-0">{{ priceFormat($payment->amount) }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="row mb-3">
                                                        <div class="col-6">
                                                            <strong class="text-muted">Date:</strong>
                                                        </div>
                                                        <div class="col-6">
                                                            {{ dateFormat($payment->payment_date) }}
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-6">
                                                            <strong class="text-muted">Status:</strong>
                                                        </div>
                                                        <div class="col-6">
                                                            <span class="badge bg-success">Completed</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="alert alert-info mt-3 mb-0">
                                        <i class="fa fa-info-circle me-2"></i>
                                        <small>This payment was processed securely through Stripe. You can view detailed transaction information in your Stripe dashboard using the transaction ID above.</small>
                                    </div>
                                  </div>
                                </div>
                              </div>
                            </div>
                        @elseif(strtoupper($payment->payment_type) == 'PAYPAL' && !empty($payment->receipt))
                            <a href="#" data-bs-toggle="modal" data-bs-target="#paypalReceiptModal{{ $payment->id }}"><i class="fab fa-paypal"></i> View Receipt</a>
                            <!-- PayPal Receipt Modal -->
                            <div class="modal fade" id="paypalReceiptModal{{ $payment->id }}" tabindex="-1" aria-labelledby="paypalReceiptModalLabel{{ $payment->id }}" aria-hidden="true">
                              <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                  <div class="modal-header bg-primary text-white">
                                    <h5 class="modal-title" id="paypalReceiptModalLabel{{ $payment->id }}">
                                        <i class="fab fa-paypal me-2"></i>PayPal Payment Receipt
                                    </h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                  </div>
                                  <div class="modal-body">
                                    @php
                                        $receiptParts = explode(':', $payment->receipt, 2);
                                        $transactionId = $receiptParts[1] ?? $payment->receipt;
                                    @endphp
                                    <div class="text-center mb-4">
                                        <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                            <i class="fab fa-paypal fa-2x text-primary"></i>
                                        </div>
                                        <h6 class="text-success mb-0">Payment Successful</h6>
                                        <small class="text-muted">Processed via PayPal</small>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="card border-0 bg-light">
                                                <div class="card-body">
                                                    <div class="row mb-3">
                                                        <div class="col-6">
                                                            <strong class="text-muted">Transaction ID:</strong>
                                                        </div>
                                                        <div class="col-6">
                                                            <code class="text-primary">{{ $transactionId }}</code>
                                                        </div>
                                                    </div>
                                                    <div class="row mb-3">
                                                        <div class="col-6">
                                                            <strong class="text-muted">Payment Method:</strong>
                                                        </div>
                                                        <div class="col-6">
                                                            <span class="badge bg-primary">PayPal</span>
                                                        </div>
                                                    </div>
                                                    <div class="row mb-3">
                                                        <div class="col-6">
                                                            <strong class="text-muted">Amount:</strong>
                                                        </div>
                                                        <div class="col-6">
                                                            <span class="h5 text-success mb-0">{{ priceFormat($payment->amount) }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="row mb-3">
                                                        <div class="col-6">
                                                            <strong class="text-muted">Date:</strong>
                                                        </div>
                                                        <div class="col-6">
                                                            {{ dateFormat($payment->payment_date) }}
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-6">
                                                            <strong class="text-muted">Status:</strong>
                                                        </div>
                                                        <div class="col-6">
                                                            <span class="badge bg-success">Completed</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="alert alert-info mt-3 mb-0">
                                        <i class="fa fa-info-circle me-2"></i>
                                        <small>This payment was processed through PayPal. You can view detailed transaction information in your PayPal account using the transaction ID above.</small>
                                    </div>
                                  </div>
                                </div>
                              </div>
                            </div>
                        @elseif(!empty($payment->receipt))
                            <a href="#" data-bs-toggle="modal" data-bs-target="#genericReceiptModal{{ $payment->id }}"><i class="fa fa-file"></i> View Receipt</a>
                            <!-- Generic Receipt Modal -->
                            <div class="modal fade" id="genericReceiptModal{{ $payment->id }}" tabindex="-1" aria-labelledby="genericReceiptModalLabel{{ $payment->id }}" aria-hidden="true">
                              <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                  <div class="modal-header">
                                    <h5 class="modal-title" id="genericReceiptModalLabel{{ $payment->id }}">Payment Receipt</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                  </div>
                                  <div class="modal-body">
                                    <div class="alert alert-info">
                                        <strong>Payment Details:</strong><br>
                                        <strong>Payment Type:</strong> {{ ucfirst($payment->payment_type) }}<br>
                                        <strong>Receipt:</strong> {{ $payment->receipt }}<br>
                                        <strong>Amount:</strong> {{ priceFormat($payment->amount) }}<br>
                                        <strong>Date:</strong> {{ dateFormat($payment->payment_date) }}
                                    </div>
                                  </div>
                                </div>
                              </div>
                            </div>
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        @if(isset($payment->status))
                            {{ ucfirst($payment->status) }}
                        @else
                            -
                        @endif
                    </td>
                    @can('delete invoice payment')
                        <td class="text-right">
                            <div class="cart-action d-flex align-items-center gap-2">
                                {!! Form::open(['method' => 'DELETE', 'route' => ['invoice.payment.destroy', $invoice->id, $payment->id]]) !!}
                                    <a class="avtar avtar-xs btn-link-danger text-danger confirm_dialog" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Delete') }}" href="#"> <i data-feather="trash-2"></i></a>
                                {!! Form::close() !!}
                                @if(auth()->user()->type == 'owner' && $invoice->status == 'pending' && $loop->last)
                                    <form action="{{ route('invoice.markPaid', $invoice->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-primary btn-sm">Mark as Paid</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    @endcan
                </tr>
            @endforeach
        </tbody>
    </table>
</div> 