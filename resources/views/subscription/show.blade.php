@extends('layouts.app')
@section('page-title')
    {{ __('Subscription') }}
@endsection

@section('content')
    @if(!$subscription)
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card shadow-lg border-0">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-exclamation-triangle text-warning fa-3x mb-3"></i>
                            <h3 class="text-dark mb-3">{{ __('Subscription Not Found') }}</h3>
                            <p class="text-muted mb-4">{{ __('The subscription you are looking for does not exist or has been removed.') }}</p>
                            <a href="{{ route('subscriptions.index') }}" class="btn btn-primary btn-lg px-4">
                                <i class="fas fa-arrow-left me-2"></i>{{ __('Back to Subscriptions') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="card shadow-lg border-0 rounded-3">
                        <div class="card-header bg-gradient-primary text-white py-4">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-crown fa-2x me-3"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h3 class="mb-1">{{ $subscription->title }}</h3>
                                    <p class="mb-0 opacity-75">{{ __('Premium Subscription Package') }}</p>
                                </div>
                                <div class="flex-shrink-0">
                                    <span class="badge bg-light text-dark fs-6 px-3 py-2">
                                        {{ ucfirst($subscription->interval) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card-body p-0">
                            <div class="row g-0">
                                <!-- Package Details -->
                                <div class="col-lg-6 p-5">
                                    <h4 class="text-dark mb-4">
                                        <i class="fas fa-info-circle text-primary me-2"></i>
                                        {{ __('Package Details') }}
                                    </h4>
                                    
                                    <div class="row g-3 mb-4">
                                        <div class="col-6">
                                            <div class="feature-card bg-light rounded-3 p-3 text-center">
                                                <i class="fas fa-dollar-sign text-success fa-2x mb-2"></i>
                                                <h6 class="mb-1">{{ __('Amount') }}</h6>
                                                <span class="text-primary fw-bold fs-5">{{ isset($settings['CURRENCY']) ? $settings['CURRENCY'] : 'USD' }} {{ number_format($subscription->package_amount, 2) }}</span>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="feature-card bg-light rounded-3 p-3 text-center">
                                                <i class="fas fa-calendar-alt text-info fa-2x mb-2"></i>
                                                <h6 class="mb-1">{{ __('Interval') }}</h6>
                                                <span class="text-primary fw-bold">{{ ucfirst($subscription->interval) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="features-list">
                                        <div class="feature-item d-flex align-items-center mb-3">
                                            <i class="fas fa-users text-success me-3"></i>
                                            <div>
                                                <strong>{{ __('User Limit') }}</strong>
                                                <span class="text-muted ms-2">{{ $subscription->user_limit }}</span>
                                            </div>
                                        </div>
                                        <div class="feature-item d-flex align-items-center mb-3">
                                            <i class="fas fa-building text-primary me-3"></i>
                                            <div>
                                                <strong>{{ __('Property Limit') }}</strong>
                                                <span class="text-muted ms-2">{{ $subscription->property_limit }}</span>
                                            </div>
                                        </div>
                                        <div class="feature-item d-flex align-items-center mb-3">
                                            <i class="fas fa-user-friends text-warning me-3"></i>
                                            <div>
                                                <strong>{{ __('Tenant Limit') }}</strong>
                                                <span class="text-muted ms-2">{{ $subscription->tenant_limit }}</span>
                                            </div>
                                        </div>
                                        <div class="feature-item d-flex align-items-center mb-3">
                                            <i class="fas fa-home text-info me-3"></i>
                                            <div>
                                                <strong>{{ __('Min Units') }}</strong>
                                                <span class="text-muted ms-2">{{ $subscription->min_units }}</span>
                                            </div>
                                        </div>
                                        <div class="feature-item d-flex align-items-center">
                                            <i class="fas fa-home text-info me-3"></i>
                                            <div>
                                                <strong>{{ __('Max Units') }}</strong>
                                                <span class="text-muted ms-2">{{ $subscription->max_units }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Payment Section -->
                                <div class="col-lg-6 bg-light p-5">
                                    <h4 class="text-dark mb-4">
                                        <i class="fas fa-credit-card text-primary me-2"></i>
                                        {{ __('Payment Methods') }}
                                    </h4>
                                    
                                    <form id="stripe-form" method="POST" action="{{ route('subscription.stripe.payment', \Illuminate\Support\Facades\Crypt::encrypt($subscription->id)) }}">
                                        @csrf
                                        <input type="hidden" name="subscription_id" value="{{ $subscription->id }}">
                                        <input type="hidden" name="amount" id="amount" value="{{ $subscription->package_amount }}">
                                        
                                        <!-- Payment Method Selection -->
                                        <div class="payment-methods mb-4">
                                            @if(isset($settings['STRIPE_PAYMENT']) && $settings['STRIPE_PAYMENT'] == 'on')
                                                <div class="payment-account-card mb-3">
                                                    <input type="radio" name="selected_account" id="stripe" value="stripe" class="payment-radio d-none">
                                                    <label for="stripe" class="payment-label d-block">
                                                        <div class="payment-content">
                                                            <div class="payment-icon">
                                                                <i class="fab fa-stripe fa-2x text-primary"></i>
                                                            </div>
                                                            <div class="payment-info">
                                                                <h6 class="mb-1">{{ __('Credit Card') }}</h6>
                                                                <small class="text-muted">{{ __('Pay with Stripe') }}</small>
                                                            </div>
                                                            <div class="payment-check">
                                                                <i class="fas fa-check-circle"></i>
                                                            </div>
                                                        </div>
                                                    </label>
                                                </div>
                                            @endif
                                            
                                            @if(isset($settings['BANK_TRANSFER_PAYMENT']) && $settings['BANK_TRANSFER_PAYMENT'] == 'on')
                                                <div class="payment-account-card mb-3">
                                                    <input type="radio" name="selected_account" id="other" value="other" class="payment-radio d-none">
                                                    <label for="other" class="payment-label d-block">
                                                        <div class="payment-content">
                                                            <div class="payment-icon">
                                                                <i class="fas fa-university fa-2x text-success"></i>
                                                            </div>
                                                            <div class="payment-info">
                                                                <h6 class="mb-1">{{ __('Bank Transfer') }}</h6>
                                                                <small class="text-muted">{{ __('Pay via Bank Transfer') }}</small>
                                                            </div>
                                                            <div class="payment-check">
                                                                <i class="fas fa-check-circle"></i>
                                                            </div>
                                                        </div>
                                                    </label>
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Stripe Payment Form -->
                                        <div id="stripe-payment-form" style="display: none;">
                                            <div class="payment-form-card">
                                                <h6 class="mb-3 text-dark">
                                                    <i class="fas fa-credit-card me-2"></i>
                                                    {{ __('Credit Card Details') }}
                                                </h6>
                                                
                                                <div class="form-group mb-3">
                                                    <label for="card-holder-name" class="form-label fw-bold">{{ __('Cardholder Name') }}</label>
                                                    <input type="text" id="card-holder-name" name="name" class="form-control form-control-lg" 
                                                           placeholder="{{ __('Enter cardholder name') }}" required>
                                                </div>
                                                
                                                <div class="form-group mb-4">
                                                    <label for="card-element" class="form-label fw-bold">{{ __('Credit or debit card') }}</label>
                                                    <div id="card-element" class="form-control form-control-lg">
                                                        <!-- Stripe Elements will be inserted here -->
                                                    </div>
                                                    <div id="card-errors" class="text-danger mt-2" role="alert"></div>
                                                </div>
                                                
                                                <button type="submit" id="stripe-pay-button" class="btn btn-primary btn-lg w-100">
                                                    <i class="fas fa-credit-card me-2"></i>{{ __('Pay with Stripe') }}
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
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
        // Simple Stripe Integration
        let stripe, card;
        
        $(document).ready(function() {
            console.log('Document ready - initializing Stripe');
            
            // Initialize Stripe immediately
            if (typeof Stripe !== 'undefined') {
                console.log('Stripe.js loaded, initializing...');
                const stripeKey = '{{ isset($settings['STRIPE_KEY']) ? $settings['STRIPE_KEY'] : '' }}';
                if (stripeKey && stripeKey.startsWith('pk_')) {
                    initializeStripe();
                } else {
                    console.error('Invalid Stripe key:', stripeKey);
                }
            } else {
                console.error('Stripe.js not loaded');
            }
        });
        
        function initializeStripe() {
            const stripeKey = '{{ isset($settings['STRIPE_KEY']) ? $settings['STRIPE_KEY'] : '' }}';
            console.log('Initializing Stripe with key:', stripeKey);
            
            if (!stripeKey || !stripeKey.startsWith('pk_')) {
                console.error('Invalid Stripe key');
                $('#card-errors').html('Invalid Stripe configuration. Please contact administrator.');
                return;
            }
            
            try {
                stripe = Stripe(stripeKey);
                var elements = stripe.elements();
                
                var style = {
                    base: {
                        fontSize: '16px',
                        color: '#32325d',
                    }
                };
                
                card = elements.create('card', {style: style});
                card.mount('#card-element');
                
                console.log('Stripe initialized successfully');
            } catch (error) {
                console.error('Stripe initialization error:', error);
                $('#card-errors').html('Failed to initialize payment system. Please refresh the page.');
            }
        }

        // Payment method selection
        $(document).on('change', 'input[name="selected_account"]', function() {
            let selected = $('input[name="selected_account"]:checked').val();
            console.log('Payment method changed to:', selected);
            
            // Hide all payment forms
            $('#telebirr-receipt').hide();
            $('#cbe-receipt').hide();
            $('#stripe-payment-form').hide();
            
            // Remove selected class from all cards
            $('.payment-account-card').removeClass('selected');
            
            // Add selected class to current card
            $(this).closest('.payment-account-card').addClass('selected');
            
            if (selected === 'telebirr') {
                $('#telebirr-receipt').show();
            } else if (selected === 'cbe') {
                $('#cbe-receipt').show();
            } else if (selected === 'stripe') {
                $('#stripe-payment-form').show();
                console.log('Stripe form shown');
                // Initialize Stripe Elements if not already done
                if (typeof stripe === 'undefined') {
                    console.log('Initializing Stripe...');
                    initializeStripe();
                } else {
                    console.log('Stripe already initialized');
                }
            } else if (selected === 'other') {
                $('#bankTransferModal').modal('show');
            }
        });

        // Simple form submission handler
        $(document).on('submit', '#stripe-form', function(e) {
            e.preventDefault();
            console.log('Form submission intercepted');
            console.log('Form data:', $(this).serialize());
            console.log('Stripe object:', typeof stripe);
            console.log('Card object:', typeof card);
            
            if (typeof stripe === 'undefined' || !stripe) {
                console.error('Stripe is not initialized');
                $('#card-errors').html('Payment system not initialized. Please refresh the page.');
                return false;
            }
            
            const submitButton = $('#stripe-pay-button');
            submitButton.prop('disabled', true);
            submitButton.html('<i class="fas fa-spinner fa-spin me-2"></i>Processing...');
            
            $('#card-errors').html('');
            
            console.log('Creating Stripe token...');
            stripe.createToken(card).then(function(result) {
                console.log('Stripe result:', result);
                if (result.error) {
                    console.error('Stripe token creation error:', result.error);
                    $('#card-errors').text(result.error.message);
                    submitButton.prop('disabled', false);
                    submitButton.html('<i class="fas fa-credit-card me-2"></i>Pay with Stripe');
                } else {
                    console.log('Token created successfully:', result.token.id);
                    
                    var hiddenInput = document.createElement('input');
                    hiddenInput.setAttribute('type', 'hidden');
                    hiddenInput.setAttribute('name', 'stripeToken');
                    hiddenInput.setAttribute('value', result.token.id);
                    document.getElementById('stripe-form').appendChild(hiddenInput);
                    
                    console.log('Submitting form with token:', result.token.id);
                    document.getElementById('stripe-form').submit();
                }
            });
            
            return false;
        });

        // Coupon handling for Stripe
        $(document).on('change', '#have_stripe_coupon', function() {
            if ($(this).is(':checked')) {
                $('#stripe-coupon-section').show();
            } else {
                $('#stripe-coupon-section').hide();
            }
        });

        // Other payment handlers
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
    .bg-gradient-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    
    .feature-card {
        transition: all 0.3s ease;
        border: 1px solid #e9ecef;
    }
    
    .feature-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    
    .features-list .feature-item {
        padding: 0.75rem 0;
        border-bottom: 1px solid #f8f9fa;
    }
    
    .features-list .feature-item:last-child {
        border-bottom: none;
    }
    
    .payment-account-card {
        cursor:pointer;
        border:2px solid #eee;
        transition:all 0.3s ease;
        min-height:200px;
        font-size:1.2rem;
        border-radius: 12px;
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    }
    .payment-account-card:hover {
        box-shadow:0 8px 25px rgba(0,123,255,0.15);
        border-color:#007bff;
        transform: translateY(-2px);
    }
    .payment-account-card.selected {
        border-color:#28a745;
        box-shadow:0 8px 25px rgba(40,167,69,0.15);
        background: linear-gradient(135deg, #f8fff9 0%, #e8f5e8 100%);
    }
    .payment-account-card input:checked ~ label {
        box-shadow:none;
        border-color:inherit;
    }
    .payment-account-card input:focus {
        outline:none;
        box-shadow:none;
    }
    .payment-account-card input:checked + label {
        color: #28a745;
    }
    
    .payment-label {
        cursor: pointer;
        margin: 0;
    }
    
    .payment-content {
        display: flex;
        align-items: center;
        padding: 1.5rem;
    }
    
    .payment-icon {
        margin-right: 1rem;
    }
    
    .payment-info {
        flex-grow: 1;
    }
    
    .payment-check {
        opacity: 0;
        color: #28a745;
        transition: opacity 0.3s ease;
    }
    
    .payment-account-card.selected .payment-check {
        opacity: 1;
    }
    
    .payment-radio:checked + .payment-label .payment-check {
        opacity: 1;
    }
    
    .payment-form-card {
        background: white;
        border-radius: 12px;
        padding: 2rem;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        border: 1px solid #e9ecef;
    }
    
    .form-control-lg {
        padding: 0.75rem 1rem;
        font-size: 1rem;
        border-radius: 8px;
        border: 2px solid #e9ecef;
        transition: all 0.3s ease;
    }
    
    .form-control-lg:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25);
    }
    
    .btn-lg {
        padding: 0.75rem 1.5rem;
        font-size: 1.1rem;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        border: none;
    }
    
    .btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0,123,255,0.3);
    }
    
    .card {
        overflow: hidden;
    }
    
    .rounded-3 {
        border-radius: 1rem !important;
    }
    
    .shadow-lg {
        box-shadow: 0 1rem 3rem rgba(0,0,0,0.175) !important;
    }
    </style>
@endpush
