<div>
    <h5>Bank Transfer Payment</h5>
    <div class="mb-3"><strong>Bank Name:</strong> {{ $settings['bank_name'] ?? '' }}</div>
    <div class="mb-3"><strong>Bank Holder Name:</strong> {{ $settings['bank_holder_name'] ?? '' }}</div>
    <div class="mb-3"><strong>Bank Account Number:</strong> {{ $settings['bank_account_number'] ?? '' }}</div>
    <div class="mb-3"><strong>Bank IFSC Code:</strong> {{ $settings['bank_ifsc_code'] ?? '' }}</div>
    <div class="mb-3"><strong>Other Details:</strong> {{ $settings['bank_other_details'] ?? '' }}</div>
    <form method="post" action="{{ route('subscription.bank.transfer', \Illuminate\Support\Facades\Crypt::encrypt($subscription->id)) }}" enctype="multipart/form-data">
        @csrf
        <div class="mb-3">
            <label for="payment_receipt" class="form-label">Attachment</label>
            <input type="file" name="payment_receipt" id="payment_receipt" class="form-control">
        </div>
        <button type="submit" class="btn btn-success">Submit Bank Transfer</button>
    </form>
</div> 