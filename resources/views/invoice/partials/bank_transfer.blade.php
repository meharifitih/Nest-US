<div>
    <h5>Bank Transfer Payment</h5>
    <div class="mb-3"><strong>Bank Name:</strong> {{ $settings['bank_name'] ?? '' }}</div>
    <div class="mb-3"><strong>Bank Holder Name:</strong> {{ $settings['bank_holder_name'] ?? '' }}</div>
    <div class="mb-3"><strong>Bank Account Number:</strong> {{ $settings['bank_account_number'] ?? '' }}</div>
    <div class="mb-3"><strong>Bank IFSC Code:</strong> {{ $settings['bank_ifsc_code'] ?? '' }}</div>
    <div class="mb-3"><strong>Other Details:</strong> {{ $settings['bank_other_details'] ?? '' }}</div>
    <form method="post" action="{{ route('invoice.banktransfer.payment', $invoice->id) }}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="amount" value="{{ $invoice->getInvoiceDueAmount() }}">
        <div class="mb-3">
            <label for="receipt" class="form-label">Attachment</label>
            <input type="file" name="receipt" id="receipt" class="form-control">
        </div>
        <button type="submit" class="btn btn-success">Submit Bank Transfer</button>
    </form>
</div> 