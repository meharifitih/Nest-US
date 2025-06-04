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