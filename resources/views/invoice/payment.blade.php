@php $settings = $settings ?? settings(); @endphp
<div class="d-flex flex-wrap justify-content-center gap-3 mb-4">
    {{-- Stripe Payment --}}
    @if($settings['STRIPE_PAYMENT'] == 'on' && !empty($settings['STRIPE_KEY']))
    <div class="payment-account-card stripe-card align-items-start" style="width:320px;">
        <div class="card-body p-3 w-100">
            <div class="form-check d-flex align-items-center w-100 mb-2">
                <input class="form-check-input me-2" type="radio" name="selected_account" value="stripe">
                <img src="https://stripe.com/img/v3/home/twitter.png" alt="Stripe Logo" style="height:60px;width:60px;object-fit:contain;margin-right:16px;">
                <label class="form-check-label w-100">
                    <strong style="font-size:1.3rem;">Credit Card</strong><br>
                    <span style="font-size:1.1rem;">Pay with Stripe</span><br>
                    <span style="font-size:1.1rem;">Secure payment</span>
                </label>
            </div>
            <div class="stripe-payment-section w-100" style="display:none;">
                <form action="{{ route('invoice.stripe.payment', \Illuminate\Support\Facades\Crypt::encrypt($invoice->id)) }}" method="POST" id="stripe-payment-form">
                    @csrf
                    <div class="form-group mb-3">
                        <label for="card-holder-name">{{ __('Card Holder Name') }}</label>
                        <input type="text" id="card-holder-name" name="card_holder_name" class="form-control" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="card-element">{{ __('Card Details') }}</label>
                        <div id="card-element" class="form-control"></div>
                        <div id="card-errors" class="text-danger mt-2"></div>
                    </div>
                    <div class="form-group mb-3">
                        <label for="amount">{{ __('Amount') }}</label>
                        <input type="number" id="amount" name="amount" class="form-control amount" step="0.01" min="0.01" value="{{ $invoice->getInvoiceDueAmount() }}" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-credit-card"></i> {{ __('Pay') }} {{ $settings['CURRENCY_SYMBOL'] ?? '$' }}{{ number_format($invoice->getInvoiceDueAmount(), 2) }}
                    </button>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- PayPal Payment --}}
    @if($settings['paypal_payment'] == 'on' && !empty($settings['paypal_client_id']))
    <div class="payment-account-card paypal-card align-items-start" style="width:320px;">
        <div class="card-body p-3 w-100">
            <div class="form-check d-flex align-items-center w-100 mb-2">
                <input class="form-check-input me-2" type="radio" name="selected_account" value="paypal">
                <img src="https://www.paypalobjects.com/webstatic/mktg/logo/pp_cc_mark_37x23.jpg" alt="PayPal Logo" style="height:60px;width:60px;object-fit:contain;margin-right:16px;">
                <label class="form-check-label w-100">
                    <strong style="font-size:1.3rem;">PayPal</strong><br>
                    <span style="font-size:1.1rem;">Pay with PayPal</span><br>
                    <span style="font-size:1.1rem;">Fast & secure</span>
                </label>
            </div>
            <div class="paypal-payment-section w-100" style="display:none;">
                <form action="{{ route('invoice.paypal', \Illuminate\Support\Facades\Crypt::encrypt($invoice->id)) }}" method="POST">
                    @csrf
                    <div class="form-group mb-3">
                        <label for="paypal-amount">{{ __('Amount') }}</label>
                        <input type="number" id="paypal-amount" name="amount" class="form-control" step="0.01" min="0.01" value="{{ $invoice->getInvoiceDueAmount() }}" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fab fa-paypal"></i> {{ __('Pay with PayPal') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- Telebirr --}}
    @if(isset($settings['telebirr_payment']) && $settings['telebirr_payment'] == 'on' && !empty($settings['telebirr_account_name']) && !empty($settings['telebirr_account_number']))
    <div class="payment-account-card telebirr-card align-items-start" style="width:320px;">
        <div class="card-body p-3 w-100">
            <div class="form-check d-flex align-items-center w-100 mb-2">
                <input class="form-check-input me-2" type="radio" name="selected_account" value="telebirr">
                <img src="https://play-lh.googleusercontent.com/Mtnybz6w7FMdzdQUbc7PWN3_0iLw3t9lUkwjmAa_usFCZ60zS0Xs8o00BW31JDCkAiQk" alt="Telebirr Logo" style="height:60px;width:60px;object-fit:contain;margin-right:16px;">
                <label class="form-check-label w-100">
                    <strong style="font-size:1.3rem;">TELEBIRR</strong><br>
                    <span style="font-size:1.1rem;">{{ $settings['telebirr_account_name'] ?? '' }}</span><br>
                    <span style="font-size:1.1rem;">{{ $settings['telebirr_account_number'] ?? '' }}</span>
                </label>
            </div>
            <div class="telebirr-receipt-section w-100" style="display:none;">
                <label class="mb-1">Telebirr Receipt Number</label>
                <input type="text" class="form-control telebirr-receipt-input mb-2" placeholder="Telebirr receipt number" autocomplete="off">
                <div class="d-flex gap-2 mb-2">
                    <button type="button" class="btn btn-secondary btn-sm confirm-telebirr">Confirm</button>
                    <button type="button" class="btn btn-light btn-sm cancel-telebirr">Cancel</button>
                </div>
                <a href="#" class="telebirr-receipt-link btn btn-link btn-sm d-none" target="_blank"><i class="fa fa-receipt"></i> View Receipt</a>
            </div>
        </div>
    </div>
    @endif
    {{-- CBE --}}
    @if(isset($settings['cbe_payment']) && $settings['cbe_payment'] == 'on' && !empty($settings['cbe_account_name']) && !empty($settings['cbe_account_number']))
    <div class="payment-account-card cbe-card align-items-start" style="width:320px;">
        <div class="card-body p-3 w-100">
            <div class="form-check d-flex align-items-center w-100 mb-2">
                <input class="form-check-input me-2" type="radio" name="selected_account" value="cbe">
                <img src="https://www.cbeib.com.et/ARCIB-4/modelbank/unprotected/assets/cbe.png" alt="CBE Logo" style="height:60px;width:60px;object-fit:contain;margin-right:16px;">
                <label class="form-check-label w-100">
                    <strong style="font-size:1.3rem;">CBE</strong><br>
                    <span style="font-size:1.1rem;">{{ $settings['cbe_account_name'] ?? '' }}</span><br>
                    <span style="font-size:1.1rem;">{{ $settings['cbe_account_number'] ?? '' }}</span>
                </label>
            </div>
            <div class="cbe-receipt-section w-100" style="display:none;">
                <label class="mb-1">CBE Receipt Number</label>
                <input type="text" class="form-control cbe-receipt-input mb-2" placeholder="CBE receipt number" autocomplete="off">
                <div class="d-flex gap-2 mb-2">
                    <button type="button" class="btn btn-secondary btn-sm confirm-cbe">Confirm</button>
                    <button type="button" class="btn btn-light btn-sm cancel-cbe">Cancel</button>
                </div>
                <a href="#" class="cbe-receipt-link btn btn-link btn-sm d-none" target="_blank"><i class="fa fa-receipt"></i> View Receipt</a>
            </div>
        </div>
    </div>
    @endif
    {{-- Bank Transfer --}}
    @if($settings['bank_transfer_payment'] == 'on' && !empty($settings['bank_name']) && !empty($settings['bank_account_number']))
    <div class="payment-account-card bank-card align-items-start" style="width:320px;">
        <div class="card-body p-3 w-100">
            <div class="form-check w-100">
                <input class="form-check-input" type="radio" name="selected_account" value="other">
                <label class="form-check-label w-100">
                    <strong style="font-size:1.3rem;">Bank Transfer Payment</strong>
                </label>
            </div>
        </div>
    </div>
    @endif
</div>
<!-- Bank Transfer Modal -->
<div class="modal fade" id="bankTransferModal" tabindex="-1" aria-labelledby="bankTransferModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="bankTransferModalLabel">Bank Transfer Payment</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        @include('invoice.partials.bank_transfer', ['settings' => $settings, 'invoice' => $invoice])
      </div>
    </div>
  </div>
</div>
<style>
.payment-account-card { cursor:pointer; border:2px solid #eee; transition:box-shadow .2s; min-height:200px; font-size:1.2rem; }
.payment-account-card.selected, .payment-account-card:hover { border-color:#0ab39c; box-shadow:0 0 0 2px #0ab39c33; }
.payment-account-card input:focus, .payment-account-card input:active { outline:none; box-shadow:none; }
.payment-account-card input:checked { outline:none; box-shadow:none; }
</style>


