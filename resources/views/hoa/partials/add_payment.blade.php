<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">{{ __('Add Payment') }}</h5>
    </div>
    <div class="card-body">
        <div class="row justify-content-center align-items-stretch g-3">
            <div class="col-md-4 d-flex">
                {{-- Telebirr --}}
                <div class="payment-account-card telebirr-card flex-fill align-items-start" style="width:100%;">
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
                            <form method="POST" action="{{ route('hoa.receipt.payment', $hoa->id) }}" class="d-flex flex-column gap-2">
                                @csrf
                                <input type="hidden" name="payment_type" value="telebirr">
                                <label class="mb-1">Telebirr Receipt Number</label>
                                <input type="text" name="receipt_number" class="form-control telebirr-receipt-input mb-2" placeholder="Telebirr receipt number" autocomplete="off" required>
                                <div class="d-flex gap-2 mb-2">
                                    <button type="submit" class="btn btn-success btn-sm">Confirm</button>
                                    <button type="button" class="btn btn-light btn-sm cancel-telebirr">Cancel</button>
                                </div>
                            </form>
                            <a href="#" class="telebirr-receipt-link btn btn-link btn-sm d-none" target="_blank"><i class="fa fa-receipt"></i> View Receipt</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 d-flex">
                {{-- CBE --}}
                <div class="payment-account-card cbe-card flex-fill align-items-start" style="width:100%;">
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
                            <form method="POST" action="{{ route('hoa.receipt.payment', $hoa->id) }}" class="d-flex flex-column gap-2">
                                @csrf
                                <input type="hidden" name="payment_type" value="cbe">
                                <label class="mb-1">CBE Receipt Number</label>
                                <input type="text" name="receipt_number" class="form-control cbe-receipt-input mb-2" placeholder="CBE receipt number" autocomplete="off" required>
                                <div class="d-flex gap-2 mb-2">
                                    <button type="submit" class="btn btn-success btn-sm">Confirm</button>
                                    <button type="button" class="btn btn-light btn-sm cancel-cbe">Cancel</button>
                                </div>
                            </form>
                            <a href="#" class="cbe-receipt-link btn btn-link btn-sm d-none" target="_blank"><i class="fa fa-receipt"></i> View Receipt</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 d-flex">
                {{-- Bank Transfer --}}
                <div class="payment-account-card bank-card flex-fill align-items-start" style="width:100%;">
                    <div class="card-body p-3 w-100">
                        <div class="form-check w-100">
                            <input class="form-check-input" type="radio" name="selected_account" value="other">
                            <label class="form-check-label w-100">
                                <strong style="font-size:1.3rem;">Bank Transfer Payment</strong>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
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
                <div class="mb-3"><strong>Bank Name:</strong> {{ $settings['bank_name'] ?? '' }}</div>
                <div class="mb-3"><strong>Bank Holder Name:</strong> {{ $settings['bank_holder_name'] ?? '' }}</div>
                <div class="mb-3"><strong>Bank Account Number:</strong> {{ $settings['bank_account_number'] ?? '' }}</div>
                <div class="mb-3"><strong>Bank IFSC Code:</strong> {{ $settings['bank_ifsc_code'] ?? '' }}</div>
                <div class="mb-3"><strong>Other Details:</strong> {{ $settings['bank_other_details'] ?? '' }}</div>
                <form method="post" action="{{ route('hoa.banktransfer.payment', $hoa->id) }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="amount" value="{{ $hoa->amount }}">
                    <div class="mb-3">
                        <label for="receipt" class="form-label">Attachment</label>
                        <input type="file" name="receipt" id="receipt" class="form-control">
                    </div>
                    <button type="submit" class="btn btn-success">Submit Bank Transfer</button>
                </form>
              </div>
            </div>
          </div>
        </div>
    </div>
</div>
@push('script-page')
<style>
.payment-account-card { cursor:pointer; border:2px solid #eee; transition:box-shadow .2s; min-height:200px; font-size:1.2rem; }
.payment-account-card.selected, .payment-account-card:hover { border-color:#0ab39c; box-shadow:0 0 0 2px #0ab39c33; }
.payment-account-card input:focus, .payment-account-card input:active { outline:none; box-shadow:none; }
.payment-account-card input:checked { outline:none; box-shadow:none; }
</style>
<script>
$(document).ready(function() {
    function hideAllReceiptSections() {
        $('.telebirr-receipt-section').hide();
        $('.cbe-receipt-section').hide();
    }
    $(document).on('change', 'input[name="selected_account"]', function() {
        hideAllReceiptSections();
        let selected = $(this).val();
        if (selected === 'telebirr') {
            $(this).closest('.payment-account-card').find('.telebirr-receipt-section').show();
        } else if (selected === 'cbe') {
            $(this).closest('.payment-account-card').find('.cbe-receipt-section').show();
        } else if (selected === 'other') {
            $('#bankTransferModal').modal('show');
            setTimeout(function(){
                $('input[name="selected_account"][value="other"]').prop('checked', false);
            }, 500);
        }
    });
    $(document).on('click', '.cancel-cbe', function() {
        $(this).closest('.cbe-receipt-section').hide();
        $('input[name="selected_account"][value!="cbe"]').prop('checked', false);
    });
    $(document).on('click', '.cancel-telebirr', function() {
        $(this).closest('.telebirr-receipt-section').hide();
        $('input[name="selected_account"][value!="telebirr"]').prop('checked', false);
    });
});
</script>
@endpush 