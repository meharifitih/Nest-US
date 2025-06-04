@extends('layouts.app')
@section('page-title')
    {{ __('Invoice') }}
@endsection
@php
    $admin_logo = getSettingsValByName('company_logo');
    $settings = settings();

@endphp


@push('script-page')
    <script src="{{ asset('assets/js/plugins/ckeditor/classic/ckeditor.js') }}"></script>
    <script>
        setTimeout(() => {
            feather.replace();
        }, 500);
    </script>
@endpush


@push('script-page')
    <script>
        $(document).on('click', '.print', function() {
            var printContents = document.getElementById('invoice-print').innerHTML;
            var originalContents = document.body.innerHTML;

            document.body.innerHTML = printContents;

            window.print();

            document.body.innerHTML = originalContents;

        });
    </script>
    <script src="https://js.stripe.com/v3/"></script>

    <script type="text/javascript">
        @if (
            $invoicePaymentSettings['STRIPE_PAYMENT'] == 'on' &&
                !empty($invoicePaymentSettings['STRIPE_KEY']) &&
                !empty($invoicePaymentSettings['STRIPE_SECRET']))
            var stripe_key = Stripe('{{ $invoicePaymentSettings['STRIPE_KEY'] }}');
            var stripe_elements = stripe_key.elements();
            var strip_css = {
                base: {
                    fontSize: '14px',
                    color: '#32325d',
                },
            };
            var stripe_card = stripe_elements.create('card', {
                style: strip_css
            });
            stripe_card.mount('#card-element');

            var stripe_form = document.getElementById('stripe-payment');
            stripe_form.addEventListener('submit', function(event) {
                event.preventDefault();

                stripe_key.createToken(stripe_card).then(function(result) {
                    if (result.error) {
                        $("#stripe_card_errors").html(result.error.message);
                        $.NotificationApp.send("Error", result.error.message, "top-right",
                            "rgba(0,0,0,0.2)", "error");
                    } else {
                        var token = result.token;
                        var stripeForm = document.getElementById('stripe-payment');
                        var stripeHiddenData = document.createElement('input');
                        stripeHiddenData.setAttribute('type', 'hidden');
                        stripeHiddenData.setAttribute('name', 'stripeToken');
                        stripeHiddenData.setAttribute('value', token.id);
                        stripeForm.appendChild(stripeHiddenData);
                        stripeForm.submit();
                    }
                });
            });
        @endif
    </script>

    <script src="https://api.ravepay.co/flwv3-pug/getpaidx/api/flwpbf-inline.js"></script>

    {{-- <script>
        @if ($invoicePaymentSettings['flutterwave_payment'] == 'on' && !empty($invoicePaymentSettings['flutterwave_public_key']) && !empty($invoicePaymentSettings['flutterwave_secret_key']))

            $(document).on("click", "#flutterwavePaymentBtn", function() {
                var amount = $('.amount').val();
                var flutterwaveCallbackURL = "{{ url('invoice/flutterwave') }}";
                var tx_ref = "RX1_" + Math.floor((Math.random() * 1000000000) + 1);
                var customer_email = '{{ \Auth::user()->email }}';
                var flutterwave_public_key = '{{ $invoicePaymentSettings['flutterwave_public_key'] }}';
                var nowTim = "{{ date('d-m-Y-h-i-a') }}";
                var currency = '{{ $invoicePaymentSettings['CURRENCY'] }}';

                if (amount) {
                    var flutterwavePayment = getpaidSetup({
                        txref: tx_ref,
                        PBFPubKey: flutterwave_public_key,
                        amount: amount,
                        currency: currency,
                        customer_email: customer_email,
                        meta: [{
                            metaname: "payment_id",
                            metavalue: "id"
                        }],
                        onclose: function() {},
                        callback: function(result) {
                            var txRef = result.tx.txRef;
                            var redirectUrl = flutterwaveCallbackURL + '/' +
                                '{{ \Illuminate\Support\Facades\Crypt::encrypt($invoice->id) }}' +
                                '/' + txRef;
                            if (result.tx.chargeResponseCode == "00" || result.tx.chargeResponseCode ==
                                "0") {
                                window.location.href = redirectUrl;
                            } else {
                                alert('Payment failed');
                            }
                            flutterwavePayment.close();
                        }
                    });
                } else {
                    alert('Please enter a valid amount');
                }
            });
        @endif
    </script> --}}

    <script>
        $(document).on("click", "#flutterwavePaymentBtn", function() {
            var amount = $('.amount').val().trim();
            if (!amount || amount <= 0) {
                alert('Please enter a valid amount');
                return;
            }

            var tx_ref = "RX1_" + Math.floor((Math.random() * 1000000000) + 1);
            var customer_email = '{{ \Auth::user()->email }}';
            var customer_name = '{{ \Auth::user()->name }}';
            var flutterwave_public_key = '{{ $invoicePaymentSettings['flutterwave_public_key'] }}';
            var currency = '{{ $invoicePaymentSettings['CURRENCY'] }}';

            var flutterwavePayment = getpaidSetup({
                txref: tx_ref,
                PBFPubKey: flutterwave_public_key,
                amount: amount, // Ensure amount is passed
                currency: currency,
                customer_email: customer_email,
                customer_name: customer_name,
                meta: [{
                    metaname: "payment_id",
                    metavalue: "id"
                }],
                onclose: function() {},
                callback: function(result) {
                    if (result.tx.chargeResponseCode == "00" || result.tx.chargeResponseCode == "0") {
                        var txRef = result.tx.txRef;
                        var redirectUrl =
                            "{{ url('invoice/flutterwave') }}/{{ \Illuminate\Support\Facades\Crypt::encrypt($invoice->id) }}/" +
                            txRef + "?amount=" + amount;
                        window.location.href = redirectUrl;
                    } else {
                        alert('Payment failed');
                    }
                    flutterwavePayment.close();
                }
            });
        });
    </script>
@endpush

@push('script-page')
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

    // Confirm Payment AJAX for CBE/Telebirr
    $(document).on('click', '.confirm-cbe', function() {
        var $section = $(this).closest('.cbe-receipt-section');
        var receipt = $section.find('.cbe-receipt-input').val();
        if (!receipt) { alert('Enter CBE receipt number'); return; }
        $.post({
            url: '{{ route('invoice.ajax.receipt') }}',
            data: {
                _token: '{{ csrf_token() }}',
                receipt_number: receipt,
                receipt_type: 'cbe',
                invoice_id: '{{ $invoice->id }}'
            },
            success: function(response) {
                if(response.redirect) {
                    window.location.href = response.redirect;
                    return;
                }
                $section.find('.cbe-receipt-link').attr('href', 'https://apps.cbe.com.et:100/?id=' + encodeURIComponent(receipt)).removeClass('d-none');
                $section.find('.cbe-receipt-input').val('');
            },
            error: function(xhr) {
                // Do nothing (no alert)
            }
        });
    });
    $(document).on('click', '.confirm-telebirr', function() {
        var $section = $(this).closest('.telebirr-receipt-section');
        var receipt = $section.find('.telebirr-receipt-input').val();
        if (!receipt) { alert('Enter Telebirr receipt number'); return; }
        $.post({
            url: '{{ route('invoice.ajax.receipt') }}',
            data: {
                _token: '{{ csrf_token() }}',
                receipt_number: receipt,
                receipt_type: 'telebirr',
                invoice_id: '{{ $invoice->id }}'
            },
            success: function(response) {
                if(response.redirect) {
                    window.location.href = response.redirect;
                    return;
                }
                $section.find('.telebirr-receipt-link').attr('href', 'https://transactioninfo.ethiotelecom.et/receipt/' + encodeURIComponent(receipt)).removeClass('d-none');
                $section.find('.telebirr-receipt-input').val('');
            },
            error: function(xhr) {
                // Do nothing (no alert)
            }
        });
    });
});
</script>
@endpush

@section('breadcrumb')
    <ul class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="{{ route('dashboard') }}">
                {{ __('Dashboard') }}
            </a>
        </li>
        <li class="breadcrumb-item">
            <a href="{{ route('invoice.index') }}">{{ __('Invoice') }}</a>
        </li>
        <li class="breadcrumb-item active">
            <a href="#">{{ __('Details') }}</a>
        </li>
    </ul>
@endsection
@section('content')




    <div class="row g-4">
        <div class="col-12">
            {{-- Invoice Details Card --}}
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="h5 mb-0">{{ __('Invoice Details') }}</span>
                    <a href="#" class="btn btn-light btn-sm print" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Download') }}">
                        <i class="ph-duotone ph-printer"></i>
                    </a>
                    </div>
                <div class="card-body">
                    @include('invoice.partials.details', ['invoice' => $invoice, 'settings' => $settings, 'tenant' => $tenant])
                </div>
            </div>
        </div>
    </div>
        @if ($invoice->getInvoiceDueAmount() > 0 && auth()->user()->type == 'tenant')
    <div class="row g-4">
        <div class="col-12">
            {{-- Add Payment Card --}}
            <div class="card mb-4">
                            <div class="card-header">
                    <h5 class="mb-0">{{ __('Add Payment') }}</h5>
                            </div>
                <div class="card-body d-flex flex-column align-items-center justify-content-center">
                    @include('invoice.payment', ['settings' => $settings, 'invoice_id' => $invoice->id, 'invoice' => $invoice])
                </div>
            </div>
        </div>
    </div>
    @endif
    {{-- Payment History --}}
    <div class="row g-4">
        <div class="col-12">
            <div class="card" id="invoice-print">
                <div class="card-header">
                    <h5>{{ __('Payment History') }}</h5>
                </div>
                <div class="card-body pt-0">
                    @include('invoice.partials.history', ['invoice' => $invoice])
                </div>
            </div>
        </div>
    </div>

@endsection

@push('css-page')
<style>
.payment-account-card {
    min-width: 220px;
    min-height: 120px;
    display: flex;
    align-items: center;
    justify-content: flex-start;
    gap: 1rem;
    border: 2px solid #eee;
    border-radius: 12px;
    margin-bottom: 1rem;
    padding: 1.5rem;
    transition: box-shadow .2s, border-color .2s;
    background: #fff;
}
.payment-account-card.selected, .payment-account-card:hover {
    border-color: #0ab39c;
    box-shadow: 0 0 0 2px #0ab39c33;
}
.payment-account-card img {
    width: 48px;
    height: 48px;
    object-fit: contain;
}
@media (max-width: 991px) {
    .payment-account-card {
        min-width: 100%;
        flex-direction: column;
    }
}
</style>
@endpush
