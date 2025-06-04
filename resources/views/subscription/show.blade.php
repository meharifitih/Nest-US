@extends('layouts.app')
@section('page-title')
    {{ __('Subscription') }}
@endsection

@push('script-page')
    <script src="https://js.stripe.com/v3/"></script>
    <script>
        $(document).on('click', '.have_coupon', function() {
            var element = $(this).parent().parent().parent().parent().parent().find('.coupon_div');
            if ($(this).is(":checked")) {
                $(element).removeClass('d-none');
            } else {
                $(element).addClass('d-none');
            }
        });

        $(document).on('click', '.packageCouponApplyBtn', function() {
            var element = $(this);
            var couponCode = element.closest('.row').find('.packageCouponCode').val();
            $.ajax({
                url: '{{ route('coupons.apply') }}',
                datType: 'json',
                data: {
                    package: '{{ \Illuminate\Support\Facades\Crypt::encrypt($subscription->id) }}',
                    coupon: couponCode
                },
                success: function(result) {
                    $('.discoutedPrice').text(result.discoutedPrice);
                    $('#amount').val(result.discoutedPrice);
                    if (result != '') {
                        if (result.status == true) {
                            toastrs('success', result.msg, 'success');
                        } else {
                            toastrs('Error', result.msg, 'error');
                        }
                    } else {
                        toastrs('Error', "{{ __('Please enter coupon code.') }}", 'error');
                    }
                }
            })
        });
    </script>

    <script>
        @if ($settings['STRIPE_PAYMENT'] == 'on' && !empty($settings['STRIPE_KEY']) && !empty($settings['STRIPE_SECRET']))
            var stripe_key = Stripe('{{ $settings['STRIPE_KEY'] }}');
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

            var stripe_form = document.getElementById('stripe-payment-form');
            stripe_form.addEventListener('submit', function(event) {
                event.preventDefault();

                stripe_key.createToken(stripe_card).then(function(result) {
                    if (result.error) {
                        $("#stripe_card_errors").html(result.error.message);
                        $.NotificationApp.send("Error", result.error.message, "top-right",
                            "rgba(0,0,0,0.2)", "error");
                    } else {
                        var token = result.token;
                        var stripeForm = document.getElementById('stripe-payment-form');
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

    <script>
        @if (
            $settings['flutterwave_payment'] == 'on' &&
                !empty($settings['flutterwave_public_key']) &&
                !empty($settings['flutterwave_secret_key']))

            $(document).on("click", "#flutterwavePaymentBtn", function() {
                var discoutedPrice = $('.discoutedPrice').text();
                var currency_symbol = '{{ $settings['CURRENCY_SYMBOL'] }}';
                var amount = discoutedPrice.replace(currency_symbol, "");
                var flutterwaveCallbackURL = "{{ url('subscription/flutterwave/') }}";
                var tx_ref = "RX1_" + Math.floor((Math.random() * 1000000000) + 1);
                var customer_email = '{{ \Auth::user()->email }}';
                var customer_name = '{{ \Auth::user()->name }}';
                var flutterwave_public_key = '{{ $settings['flutterwave_public_key'] }}';
                var nowTim = "{{ date('d-m-Y-h-i-a') }}";
                var currency = '{{ $settings['CURRENCY'] }}';

                if (amount) {
                    var flutterwavePayment = getpaidSetup({
                        txref: tx_ref,
                        PBFPubKey: flutterwave_public_key,
                        amount: amount,
                        currency: currency,
                        customer_name: customer_name,
                        customer_email: customer_email,
                        meta: [{
                            consumer_id: "23",
                            consumer_mac: "92a3-912ba-1192a"
                        }],
                        onclose: function() {},
                        callback: function(result) {
                            var txRef = result.tx.txRef;
                            var redirectUrl = flutterwaveCallbackURL + '/' +
                                '{{ \Illuminate\Support\Facades\Crypt::encrypt($subscription->id) }}' +
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
            <a href="{{ route('subscriptions.index') }}">{{ __('Subscription') }}</a>
        </li>
        <li class="breadcrumb-item active">
            <a href="#">{{ __('Details') }}</a>
        </li>
    </ul>
@endsection

@section('content')
    <div class="row pricing-grid">
        <div class="col-12">
            <div class="card">
                <div class="card-body pt-0">
                    <div class="dt-responsive table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>{{ __('Title') }}</th>
                                    <th>{{ __('Amount') }}</th>
                                    <th>{{ __('Interval') }}</th>
                                    <th>{{ __('User Limit') }}</th>
                                    <th>{{ __('Property Limit') }}</th>
                                    <th>{{ __('Tenant Limit') }}</th>
                                    <th>{{ __('Coupon Applicable') }}</th>
                                    <th>{{ __('User Logged History') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        {{ $subscription->title }}
                                    </td>
                                    <td>
                                        <b class="discoutedPrice">
                                            {{ subscriptionPaymentSettings()['CURRENCY_SYMBOL'] }}{{ $subscription->package_amount }}</b>
                                    </td>
                                    <td>{{ isset($subscription) ? $subscription->interval : '' }} </td>
                                    <td>{{ $subscription->user_limit }} </td>
                                    <td>{{ $subscription->property_limit }}</td>
                                    <td>{{ $subscription->tenant_limit }}</td>
                                    <td>
                                        @if ($subscription->couponCheck() > 0)
                                            <i class="text-success mr-4" data-feather="check-circle"></i>
                                        @else
                                            <i class="text-danger mr-4" data-feather="x-circle"></i>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($subscription->enabled_logged_history == 1)
                                            <i class="text-success mr-4" data-feather="check-circle"></i>
                                        @else
                                            <i class="text-danger mr-4" data-feather="x-circle"></i>
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>


        <div class="row pricing-grid">
            <div class="col-lg-12">
                <div class="row">
                    @if ($settings['bank_transfer_payment'] == 'on')
                        {{-- BEGIN REMOVE Bank Transfer Payment card section --}}
                        {{--
                        <div class="col-sm-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5>{{ __('Bank Transfer Payment') }}</h5>
                                    @if ($subscription->couponCheck() > 0)
                                        <div class="setting-card action-menu">
                                            <div class="form-group">
                                                <div class="form-check custom-chek">
                                                    <input class="form-check-input have_coupon" type="checkbox"
                                                        value="" id="have_bank_tran_coupon">
                                                    <label class="form-check-label"
                                                        for="have_bank_tran_coupon">{{ __('Have a Discount Coupon Code?') }}
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                <div class="card-body profile-user-box">
                                    <form
                                        action="{{ route('subscription.bank.transfer', \Illuminate\Support\Facades\Crypt::encrypt($subscription->id)) }}"
                                        method="post" class="require-validation" id="bank-payment"
                                        enctype="multipart/form-data">
                                        @csrf
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="card-name-on"
                                                        class="form-label text-dark">{{ __('Bank Name') }}</label>
                                                    <p>{{ $settings['bank_name'] }}</p>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="card-name-on"
                                                        class="form-label text-dark">{{ __('Bank Holder Name') }}</label>
                                                    <p>{{ $settings['bank_holder_name'] }}</p>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="card-name-on"
                                                        class="form-label text-dark">{{ __('Bank Account Number') }}</label>
                                                    <p>{{ $settings['bank_account_number'] }}</p>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="card-name-on"
                                                        class="form-label text-dark">{{ __('Bank IFSC Code') }}</label>
                                                    <p>{{ $settings['bank_ifsc_code'] }}</p>
                                                </div>
                                            </div>
                                            @if (!empty($settings['bank_other_details']))
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label for="card-name-on"
                                                            class="form-label text-dark">{{ __('Bank Other Details') }}</label>
                                                        <p>{{ $settings['bank_other_details'] }}</p>
                                                    </div>
                                                </div>
                                            @endif

                                            <div class="col-md-12 d-none coupon_div">
                                                <div class="form-group">
                                                    <label for="card-name-on"
                                                        class="form-label text-dark">{{ __('Coupon Code') }}</label>
                                                    <input type="text" name="coupon"
                                                        class="form-control required packageCouponCode"
                                                        placeholder="{{ __('Enter Coupon Code') }}">
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label for="card-name-on"
                                                        class="form-label text-dark">{{ __('Attachment') }}</label>
                                                    <input type="file" name="payment_receipt" id="payment_receipt"
                                                        class="form-control" required>
                                                </div>
                                            </div>
                                            <div class="col-sm-12 ">
                                                <input type="button" value="{{ __('Coupon Apply') }}"
                                                    class="btn btn-primary packageCouponApplyBtn d-none coupon_div">
                                                <input type="submit" value="{{ __('Pay') }}" class="btn btn-secondary">
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        --}}
                        {{-- END REMOVE Bank Transfer Payment card section --}}
                    @endif
                    @if ($settings['STRIPE_PAYMENT'] == 'on' && !empty($settings['STRIPE_KEY']) && !empty($settings['STRIPE_SECRET']))
                        <div class="col-sm-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5>{{ __('Stripe Payment') }}</h5>
                                    @if ($subscription->couponCheck() > 0)
                                        <div class="setting-card action-menu">
                                            <div class="form-group">
                                                <div class="form-check custom-chek">
                                                    <input class="form-check-input have_coupon" type="checkbox"
                                                        value="" id="have_stripe_coupon">
                                                    <label class="form-check-label"
                                                        for="have_stripe_coupon">{{ __('Have a Discount Coupon Code?') }}
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                <div class="card-body profile-user-box">
                                    <form
                                        action="{{ route('subscription.stripe.payment', \Illuminate\Support\Facades\Crypt::encrypt($subscription->id)) }}"
                                        method="post" class="require-validation" id="stripe-payment-form">
                                        @csrf
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="card-name-on"
                                                        class="form-label text-dark">{{ __('Card Name') }}</label>
                                                    <input type="text" name="name" id="card-name-on"
                                                        class="form-control required"
                                                        placeholder="{{ __('Card Holder Name') }}">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="card-name-on"
                                                    class="form-label text-dark">{{ __('Card Details') }}</label>
                                                <div id="card-element">
                                                </div>
                                                <div id="stripe_card_errors" role="alert"></div>
                                            </div>

                                            @if ($subscription->couponCheck() > 0)
                                                <div class="col-md-12 d-none coupon_div">
                                                    <div class="form-group">
                                                        <label for="card-name-on"
                                                            class="form-label text-dark">{{ __('Coupon Code') }}</label>
                                                        <input type="text" name="coupon"
                                                            class="form-control required packageCouponCode"
                                                            placeholder="{{ __('Enter Coupon Code') }}">
                                                    </div>
                                                </div>
                                            @endif
                                            <div class="col-sm-12 mt-15">
                                                @if ($subscription->couponCheck() > 0)
                                                    <input type="button" value="{{ __('Coupon Apply') }}"
                                                        class="btn btn-primary packageCouponApplyBtn d-none coupon_div">
                                                @endif
                                                <input type="submit" value="{{ __('Pay') }}"
                                                    class="btn btn-secondary">

                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif
                    @if (
                        $settings['flutterwave_payment'] == 'on' &&
                            !empty($settings['flutterwave_public_key']) &&
                            !empty($settings['flutterwave_secret_key']))
                        <div class="col-sm-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5>{{ __('Flutterwave Payment') }}</h5>
                                    @if ($subscription->couponCheck() > 0)
                                        <div class="setting-card action-menu">
                                            <div class="form-group">
                                                <div class="form-check custom-chek">
                                                    <input class="form-check-input have_coupon" type="checkbox"
                                                        value="" id="have_flutterwave_coupon">
                                                    <label class="form-check-label"
                                                        for="have_flutterwave_coupon">{{ __('Have a Discount Coupon Code?') }}
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                <div class="card-body profile-user-box">
                                    <form action="#" class="require-validation" method="get">
                                        @csrf
                                        <div class="row">
                                            @if ($subscription->couponCheck() > 0)
                                                <div class="col-md-12 d-none coupon_div">
                                                    <div class="form-group">
                                                        <label for="card-name-on"
                                                            class="form-label text-dark">{{ __('Coupon Code') }}</label>
                                                        <input type="text" name="coupon"
                                                            class="form-control required packageCouponCode"
                                                            placeholder="{{ __('Enter Coupon Code') }}">
                                                    </div>
                                                </div>
                                            @endif
                                            <div class="col-sm-12">
                                                @if ($subscription->couponCheck() > 0)
                                                    <input type="button" value="{{ __('Coupon Apply') }}"
                                                        class="btn btn-primary packageCouponApplyBtn d-none coupon_div">
                                                @endif
                                                <input type="button" value="{{ __('Pay') }}"
                                                    id="flutterwavePaymentBtn" class="btn btn-secondary">
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif
                    @if (
                        $settings['paypal_payment'] == 'on' &&
                            !empty($settings['paypal_client_id']) &&
                            !empty($settings['paypal_secret_key']))
                        <div class="col-sm-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5>{{ __('Paypal Payment') }}</h5>
                                    @if ($subscription->couponCheck() > 0)
                                        <div class="setting-card action-menu">
                                            <div class="form-group">
                                                <div class="form-check custom-chek">
                                                    <input class="form-check-input have_coupon" type="checkbox"
                                                        value="" id="have_paypal_coupon">
                                                    <label class="form-check-label"
                                                        for="have_paypal_coupon">{{ __('Have a Discount Coupon Code?') }}
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                <div class="card-body profile-user-box">
                                    <form
                                        action="{{ route('subscription.paypal', \Illuminate\Support\Facades\Crypt::encrypt($subscription->id)) }}"
                                        method="post" class="require-validation">
                                        @csrf
                                        <div class="row">
                                            @if ($subscription->couponCheck() > 0)
                                                <div class="col-md-12 mt-15 d-none coupon_div">
                                                    <div class="form-group">
                                                        <label for="card-name-on"
                                                            class="form-label text-dark">{{ __('Coupon Code') }}</label>
                                                        <input type="text" name="coupon"
                                                            class="form-control required packageCouponCode"
                                                            placeholder="{{ __('Enter Coupon Code') }}">
                                                    </div>
                                                </div>
                                            @endif
                                            <div class="col-sm-12 mt-15">
                                                @if ($subscription->couponCheck() > 0)
                                                    <input type="button" value="{{ __('Coupon Apply') }}"
                                                        class="btn btn-primary packageCouponApplyBtn d-none coupon_div">
                                                @endif
                                                <input type="submit" value="{{ __('Pay') }}"
                                                    class="btn btn-secondary">
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="row mb-4 justify-content-center">
        <div class="col-md-4">
            <div class="card payment-account-card mb-4 p-4" style="min-height:200px; font-size:1.2rem;">
                <div class="card-body d-flex flex-column align-items-center justify-content-center">
                    <div class="form-check d-flex align-items-center w-100">
                        <input class="form-check-input me-2" type="radio" name="selected_account" id="telebirr" value="telebirr">
                        <img src="https://play-lh.googleusercontent.com/Mtnybz6w7FMdzdQUbc7PWN3_0iLw3t9lUkwjmAa_usFCZ60zS0Xs8o00BW31JDCkAiQk" alt="Telebirr Logo" style="height:60px;width:60px;object-fit:contain;margin-right:16px;">
                        <label class="form-check-label w-100" for="telebirr">
                            <strong style="font-size:1.3rem;">TELEBIRR</strong><br>
                            <span style="font-size:1.1rem;">{{ $settings['telebirr_account_name'] ?? '' }}</span><br>
                            <span style="font-size:1.1rem;">{{ $settings['telebirr_account_number'] ?? '' }}</span>
                        </label>
                    </div>
                    <div id="telebirr-receipt" class="mt-3 w-100" style="display:none;">
                        <label for="telebirr_receipt_number">Telebirr Receipt Number</label>
                        <input type="text" name="telebirr_receipt_number" id="telebirr_receipt_number" class="form-control" placeholder="Enter Telebirr receipt number">
                        <div class="mt-2 d-flex gap-2">
                            <button type="button" class="btn btn-secondary" id="confirm-telebirr">Confirm Payment</button>
                            <button type="button" class="btn btn-light" id="cancel-telebirr">Cancel</button>
                            <a href="#" id="telebirr-receipt-link" class="btn btn-link d-none" target="_blank"><i class="fa fa-receipt"></i> View Receipt</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card payment-account-card mb-4 p-4" style="min-height:200px; font-size:1.2rem;">
                <div class="card-body d-flex flex-column align-items-center justify-content-center">
                    <div class="form-check d-flex align-items-center w-100">
                        <input class="form-check-input me-2" type="radio" name="selected_account" id="cbe" value="cbe">
                        <img src="https://www.cbeib.com.et/ARCIB-4/modelbank/unprotected/assets/cbe.png" alt="CBE Logo" style="height:60px;width:60px;object-fit:contain;margin-right:16px;">
                        <label class="form-check-label w-100" for="cbe">
                            <strong style="font-size:1.3rem;">CBE</strong><br>
                            <span style="font-size:1.1rem;">{{ $settings['cbe_account_name'] ?? '' }}</span><br>
                            <span style="font-size:1.1rem;">{{ $settings['cbe_account_number'] ?? '' }}</span>
                        </label>
                    </div>
                    <div id="cbe-receipt" class="mt-3 w-100" style="display:none;">
                        <label for="cbe_receipt_number">CBE Receipt Number</label>
                        <input type="text" name="cbe_receipt_number" id="cbe_receipt_number" class="form-control" placeholder="Enter CBE receipt number">
                        <div class="mt-2 d-flex gap-2">
                            <button type="button" class="btn btn-secondary" id="confirm-cbe">Confirm Payment</button>
                            <button type="button" class="btn btn-light" id="cancel-cbe">Cancel</button>
                            <a href="#" id="cbe-receipt-link" class="btn btn-link d-none" target="_blank"><i class="fa fa-receipt"></i> View Receipt</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card payment-account-card mb-4 p-4" style="min-height:200px; font-size:1.2rem;">
                <div class="card-body d-flex flex-column align-items-center justify-content-center">
                    <div class="form-check w-100">
                        <input class="form-check-input" type="radio" name="selected_account" id="other" value="other">
                        <label class="form-check-label w-100" for="other">
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
            @include('subscription.partials.bank_transfer')
          </div>
        </div>
      </div>
    </div>
@endsection

@push('script-page')
    <script>
        $(document).ready(function() {
            $('#submit_payment').click(function(e) {
                e.preventDefault();
                
                var formData = new FormData();
                formData.append('payment_screenshot', $('#payment_screenshot')[0].files[0]);
                formData.append('subscription_id', '{{ $subscription->id }}');
                formData.append('_token', '{{ csrf_token() }}');

                $.ajax({
                    url: '{{ route('payment.verification.upload') }}',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            toastrs('success', response.message, 'success');
                            setTimeout(function() {
                                window.location.href = '{{ route('subscriptions.index') }}';
                            }, 2000);
                        } else {
                            toastrs('error', response.error, 'error');
                        }
                    },
                    error: function(xhr) {
                        toastrs('error', xhr.responseJSON.error, 'error');
                    }
                });
            });
        });
    </script>
    <script>
        $(document).on('change', 'input[name="selected_account"]', function() {
            let selected = $('input[name="selected_account"]:checked').val();
            $('#telebirr-receipt').hide();
            $('#cbe-receipt').hide();
            if (selected === 'telebirr') {
                $('#telebirr-receipt').show();
            } else if (selected === 'cbe') {
                $('#cbe-receipt').show();
            } else if (selected === 'other') {
                $('#bankTransferModal').modal('show');
            }
        });
    </script>
    <script>
        $(document).on('click', '#cancel-cbe', function() {
            $('#cbe-receipt').hide();
            $('input[name="selected_account"][value!="cbe"]').prop('checked', false);
        });
        $(document).on('click', '#cancel-telebirr', function() {
            $('#telebirr-receipt').hide();
            $('input[name="selected_account"][value!="telebirr"]').prop('checked', false);
        });
        $(document).on('click', '#confirm-cbe', function() { submitReceiptPayment('cbe'); });
        $(document).on('click', '#confirm-telebirr', function() { submitReceiptPayment('telebirr'); });
    </script>
    <style>
    .payment-account-card {
        cursor:pointer;
        border:2px solid #eee;
        transition:box-shadow .2s;
        min-height:200px;
        font-size:1.2rem;
    }
    .payment-account-card:hover {
        box-shadow:0 0 0 2px #007bff;
        border-color:#007bff;
    }
    .payment-account-card input:checked ~ label {
        /* Remove blue border effect */
        box-shadow:none;
        border-color:inherit;
    }
    .payment-account-card input:focus {
        outline:none;
        box-shadow:none;
    }
    </style>
@endpush

<script>
    function submitReceiptPayment(type) {
        let receipt = type === 'cbe' ? $('#cbe_receipt_number').val() : $('#telebirr_receipt_number').val();
        if (!receipt) {
            alert('Enter ' + (type === 'cbe' ? 'CBE' : 'Telebirr') + ' receipt number');
            return;
        }
        $.ajax({
            url: '{{ route('subscription.bank.transfer', \Illuminate\Support\Facades\Crypt::encrypt($subscription->id)) }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                receipt_number: receipt,
                receipt_type: type
            },
            success: function(response) {
                toastrs('success', 'Payment submitted and is now pending review.', 'success');
                if(response.redirect) {
                    window.location.href = response.redirect;
                    return;
                }
                if(type === 'cbe') {
                    $('#cbe-receipt').hide();
                    $('#cbe-receipt-link').attr('href', getReceiptUrl('cbe', receipt)).removeClass('d-none');
                }
                if(type === 'telebirr') {
                    $('#telebirr-receipt').hide();
                    $('#telebirr-receipt-link').attr('href', getReceiptUrl('telebirr', receipt)).removeClass('d-none');
                }
                $('input[name="selected_account"]').prop('checked', false);
            },
            error: function(xhr) {
                toastrs('error', xhr.responseJSON?.error || 'Submission failed', 'error');
            }
        });
    }
    function getReceiptUrl(type, receipt) {
        if(type === 'cbe') {
            return 'https://mobile.cbe.com.et/ArcIBInternetBanking/TransactionReceiptPrint?id=' + encodeURIComponent(receipt);
        } else if(type === 'telebirr') {
            return 'https://portal.telebirr.com/receipt/' + encodeURIComponent(receipt);
        }
        return '#';
    }
</script>
