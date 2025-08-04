@extends('layouts.app')

@section('page-title')
    {{ __('Payment') }}
@endsection

@push('script-page')
    <script src="https://js.stripe.com/v3/"></script>
    <script>
        $(document).ready(function() {
            // Stripe Elements
            @if ($settings['STRIPE_PAYMENT'] == 'on' && !empty($settings['STRIPE_KEY']))
                var stripe = Stripe('{{ $settings['STRIPE_KEY'] }}');
                var elements = stripe.elements();
                
                var style = {
                    base: {
                        fontSize: '16px',
                        color: '#32325d',
                    }
                };
                
                var card = elements.create('card', {style: style});
                card.mount('#card-element');
                
                var form = document.getElementById('stripe-payment-form');
                form.addEventListener('submit', function(event) {
                    event.preventDefault();
                    
                    stripe.createToken(card).then(function(result) {
                        if (result.error) {
                            var errorElement = document.getElementById('card-errors');
                            errorElement.textContent = result.error.message;
                        } else {
                            var hiddenInput = document.createElement('input');
                            hiddenInput.setAttribute('type', 'hidden');
                            hiddenInput.setAttribute('name', 'stripeToken');
                            hiddenInput.setAttribute('value', result.token.id);
                            form.appendChild(hiddenInput);
                            form.submit();
                        }
                    });
                });
            @endif

            // Payment method selection
            $('.payment-method').on('change', function() {
                $('.payment-form').hide();
                $('#' + $(this).val() + '-form').show();
            });

            // Show first payment method by default
            $('.payment-method:first').trigger('change');
        });
    </script>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">{{ __('Complete Payment') }}</h4>
                </div>
                <div class="card-body">
                    <!-- Payment Details -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5>{{ __('Payment Details') }}</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>{{ __('Amount') }}:</strong></td>
                                    <td>{{ $settings['CURRENCY_SYMBOL'] }}{{ number_format($payment->amount, 2) }}</td>
                                </tr>
                                <tr>
                                    <td><strong>{{ __('Payment Type') }}:</strong></td>
                                    <td>{{ ucfirst($payment->payment_type) }}</td>
                                </tr>
                                <tr>
                                    <td><strong>{{ __('Due Date') }}:</strong></td>
                                    <td>{{ $payment->due_date->format('M d, Y') }}</td>
                                </tr>
                                @if($payment->is_recurring)
                                <tr>
                                    <td><strong>{{ __('Recurring') }}:</strong></td>
                                    <td>{{ ucfirst($payment->recurring_interval) }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5>{{ __('Tenant Information') }}</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>{{ __('Name') }}:</strong></td>
                                    <td>{{ $payment->tenant->user->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>{{ __('Email') }}:</strong></td>
                                    <td>{{ $payment->tenant->user->email }}</td>
                                </tr>
                                @if($payment->invoice)
                                <tr>
                                    <td><strong>{{ __('Invoice') }}:</strong></td>
                                    <td>{{ $payment->invoice->invoice_id }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>

                    <!-- Payment Methods -->
                    <div class="row">
                        <div class="col-md-12">
                            <h5>{{ __('Select Payment Method') }}</h5>
                            
                            <!-- Stripe Payment -->
                            @if($settings['STRIPE_PAYMENT'] == 'on' && !empty($settings['STRIPE_KEY']))
                            <div class="form-check mb-3">
                                <input class="form-check-input payment-method" type="radio" name="payment_method" id="stripe" value="stripe" checked>
                                <label class="form-check-label" for="stripe">
                                    <i class="fab fa-stripe"></i> {{ __('Pay with Card (Stripe)') }}
                                </label>
                            </div>
                            
                            <div id="stripe-form" class="payment-form mb-4">
                                <div class="card">
                                    <div class="card-body">
                                        <form action="{{ route('tenant.payments.stripe', $payment->id) }}" method="POST" id="stripe-payment-form">
                                            @csrf
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="card-holder-name">{{ __('Card Holder Name') }}</label>
                                                        <input type="text" id="card-holder-name" class="form-control" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="card-element">{{ __('Card Details') }}</label>
                                                        <div id="card-element" class="form-control"></div>
                                                        <div id="card-errors" class="text-danger mt-2"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-credit-card"></i> {{ __('Pay') }} {{ $settings['CURRENCY_SYMBOL'] }}{{ number_format($payment->amount, 2) }}
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <!-- PayPal Payment -->
                            @if($settings['paypal_payment'] == 'on' && !empty($settings['paypal_client_id']))
                            <div class="form-check mb-3">
                                <input class="form-check-input payment-method" type="radio" name="payment_method" id="paypal" value="paypal">
                                <label class="form-check-label" for="paypal">
                                    <i class="fab fa-paypal"></i> {{ __('Pay with PayPal') }}
                                </label>
                            </div>
                            
                            <div id="paypal-form" class="payment-form mb-4" style="display: none;">
                                <div class="card">
                                    <div class="card-body">
                                        <form action="{{ route('tenant.payments.paypal', $payment->id) }}" method="POST">
                                            @csrf
                                            <div class="text-center">
                                                <p class="mb-3">{{ __('You will be redirected to PayPal to complete your payment.') }}</p>
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="fab fa-paypal"></i> {{ __('Pay with PayPal') }}
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <!-- Bank Transfer -->
                            <div class="form-check mb-3">
                                <input class="form-check-input payment-method" type="radio" name="payment_method" id="bank_transfer" value="bank_transfer">
                                <label class="form-check-label" for="bank_transfer">
                                    <i class="fas fa-university"></i> {{ __('Bank Transfer') }}
                                </label>
                            </div>
                            
                            <div id="bank_transfer-form" class="payment-form mb-4" style="display: none;">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="alert alert-info">
                                            <h6>{{ __('Bank Transfer Details') }}</h6>
                                            <p>{{ __('Please transfer the amount to the following account:') }}</p>
                                            <ul class="list-unstyled">
                                                <li><strong>{{ __('Account Name') }}:</strong> {{ settings()['bank_holder_name'] ?? 'Nest Management' }}</li>
                                                <li><strong>{{ __('Account Number') }}:</strong> {{ settings()['bank_account_number'] ?? '1234567890' }}</li>
                                                <li><strong>{{ __('Bank Name') }}:</strong> {{ settings()['bank_name'] ?? 'Sample Bank' }}</li>
                                                <li><strong>{{ __('Amount') }}:</strong> {{ $settings['CURRENCY_SYMBOL'] }}{{ number_format($payment->amount, 2) }}</li>
                                            </ul>
                                        </div>
                                        <form action="{{ route('tenant.payments.bank-transfer', $payment->id) }}" method="POST" enctype="multipart/form-data">
                                            @csrf
                                            <div class="form-group">
                                                <label for="receipt">{{ __('Upload Receipt') }}</label>
                                                <input type="file" id="receipt" name="receipt" class="form-control" accept="image/*,.pdf" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="notes">{{ __('Notes (Optional)') }}</label>
                                                <textarea id="notes" name="notes" class="form-control" rows="3"></textarea>
                                            </div>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-upload"></i> {{ __('Submit Payment') }}
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Security Notice -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="alert alert-success">
                                <i class="fas fa-shield-alt"></i>
                                <strong>{{ __('Secure Payment') }}</strong>
                                <p class="mb-0">{{ __('Your payment information is encrypted and secure. We use industry-standard SSL encryption to protect your data.') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 