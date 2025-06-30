@extends('layouts.app')
@section('page-title')
    {{ __('Packages') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item" aria-current="page"> {{ __('Packages') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center g-2">
                        <div class="col">
                            <h5>{{ __('Pricing Packages List') }}</h5>
                        </div>
                        @if (
                            \Auth::user()->type == 'super admin' &&
                                (subscriptionPaymentSettings()['STRIPE_PAYMENT'] == 'on' ||
                                    subscriptionPaymentSettings()['paypal_payment'] == 'on' ||
                                    subscriptionPaymentSettings()['bank_transfer_payment'] == 'on' ||
                                    subscriptionPaymentSettings()['flutterwave_payment'] == 'on'))
                            <div class="col-auto">
                                <a href="#" class="btn btn-secondary customModal" data-size="md"
                                    data-url="{{ route('subscriptions.create') }}" data-title="{{ __('Create Package') }}">
                                    <i class="ti ti-circle-plus align-text-bottom"></i> {{ __('Create Package') }}
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    @php
                        // Get only intervals that have active packages
                        $activeIntervals = $subscriptions->pluck('interval')->unique();
                    @endphp
                    
                    @if($activeIntervals->count() > 0)
                        <div class="text-center mb-4">
                            <ul class="nav nav-tabs justify-content-center" id="intervalTabs" role="tablist">
                                @foreach ($activeIntervals as $idx => $interval)
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link @if($loop->first) active @endif" id="tab-{{ $interval }}" data-bs-toggle="tab" data-bs-target="#interval-{{ $interval }}" type="button" role="tab" aria-controls="interval-{{ $interval }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                                            {{ __($interval) }}
                                        </button>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        <div class="tab-content" id="intervalTabsContent">
                            @foreach ($activeIntervals as $idx => $interval)
                                <div class="tab-pane fade @if($loop->first) show active @endif" id="interval-{{ $interval }}" role="tabpanel" aria-labelledby="tab-{{ $interval }}">
                                    <div class="row text-center justify-content-center">
                                        @foreach ($subscriptions->where('interval', $interval)->sortBy('package_amount') as $subscription)
                                            <div class="col-md-6 col-lg-4 mb-4">
                                                <div class="card price-card border shadow-sm h-100">
                                                    <div class="card-body d-flex flex-column align-items-center justify-content-center p-4 position-relative">
                                                        @if (\Auth::user()->type == 'super admin')
                                                            <div class="position-absolute top-0 end-0 m-2 d-flex gap-2">
                                                                @can('edit pricing packages')
                                                                    <a class="btn btn-sm btn-secondary customModal"
                                                                        data-url="{{ route('subscriptions.edit', $subscription->id) }}"
                                                                        data-title="{{ __('Edit Package') }}">
                                                                        <i class="ti ti-edit"></i>
                                                                    </a>
                                                                @endcan
                                                                @can('delete pricing packages')
                                                                    <button type="button" class="btn btn-sm btn-danger delete-package-btn" data-package-id="{{ $subscription->id }}">
                                                                        <i class="ti ti-trash"></i>
                                                                    </button>
                                                                @endcan
                                                            </div>
                                                        @endif
                                                        <h2 class="mb-2 mt-2">{{ $subscription->title }}</h2>
                                                        <div class="price-price mb-4">
                                                            <sup class="h5">{{ subscriptionPaymentSettings()['CURRENCY_SYMBOL'] }}</sup>
                                                            <span class="h1 fw-bold">{{ $subscription->package_amount }}</span>
                                                            <span class="h6 text-muted">/{{ $subscription->interval }}</span>
                                                        </div>
                                                        <div class="d-flex flex-column align-items-center justify-content-center w-100">
                                                            <ul class="list-unstyled text-start mb-4" style="max-width: 320px;">
                                                                <li class="mb-2"><i class="ti ti-circle-check text-success me-2"></i>{{ __('User Limit') }}: {{ $subscription->user_limit }}</li>
                                                                <li class="mb-2"><i class="ti ti-circle-check text-success me-2"></i>{{ __('Property Limit') }}: {{ $subscription->property_limit }}</li>
                                                                <li class="mb-2"><i class="ti ti-circle-check text-success me-2"></i>{{ __('Tenant Limit') }}: {{ $subscription->tenant_limit }}</li>
                                                                <li class="mb-2"><i class="ti ti-circle-check text-success me-2"></i>{{ __('Unit Range') }}: {{ $subscription->min_units }} - {{ $subscription->max_units == 0 ? 'Unlimited' : $subscription->max_units }}</li>
                                                                <li class="mb-2">
                                                                    <i class="ti {{ $subscription->enabled_logged_history ? 'ti-circle-check text-success' : 'ti-circle-x text-danger' }} me-2"></i>
                                                                    {{ __('Enabled Logged History') }}
                                                                </li>
                                                                <li class="mb-2">
                                                                    <i class="ti {{ $subscription->couponCheck() > 0 ? 'ti-circle-check text-success' : 'ti-circle-x text-danger' }} me-2"></i>
                                                                    {{ __('Coupon Applicable') }}
                                                                </li>
                                                            </ul>
                                                        </div>
                                                        <div class="mt-auto w-100">
                                                            @if (\Auth::user()->type != 'super admin' && \Auth::user()->subscription == $subscription->id)
                                                                <div class="text-center">
                                                                    <span class="badge bg-success mb-2">{{ __('Active') }}</span>
                                                                    <br>
                                                                    <span class="text-muted">
                                                                        {{ \Auth::user()->subscription_expire_date ? dateFormat(\Auth::user()->subscription_expire_date) : __('Unlimited') }}
                                                                        {{ __('Expiry Date') }}
                                                                    </span>
                                                                </div>
                                                            @else
                                                                @if (\Auth::user()->type == 'owner' && \Auth::user()->subscription != $subscription->id && $subscription->package_amount > 0)
                                                                    <a href="{{ route('subscriptions.show', \Illuminate\Support\Facades\Crypt::encrypt($subscription->id)) }}"
                                                                        class="btn btn-primary w-100">
                                                                        {{ __('Purchase Now') }}
                                                                    </a>
                                                                @endif
                                                                @if ($subscription->package_amount == 0 && \Auth::user()->type == 'owner')
                                                                    <form action="{{ route('subscriptions.subscribe', $subscription->id) }}" method="POST">
                                                                        @csrf
                                                                        <button type="submit" class="btn btn-success w-100">{{ __('Subscribe Now') }}</button>
                                                                    </form>
                                                                @endif
                                                            @endif
                                                        </div>
                                                        <form id="delete-form-{{ $subscription->id }}" action="{{ route('subscriptions.destroy', $subscription->id) }}" method="POST" style="display:none;">
                                                            @csrf
                                                            @method('DELETE')
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                        @if(Auth::user()->type != 'super admin')
                                            <!-- Enterprise Card -->
                                            <div class="col-md-6 col-lg-4 mb-4">
                                                <div class="card price-card border shadow-sm h-100">
                                                    <div class="card-body d-flex flex-column align-items-center justify-content-center p-4 position-relative">
                                                        <h2 class="mb-2 mt-2">Enterprise</h2>
                                                        <div class="price-price mb-4">
                                                            <span>Contact us for pricing</span>
                                                        </div>
                                                        <div class="d-flex flex-column align-items-center justify-content-center w-100">
                                                            <ul class="list-unstyled text-start mb-4" style="max-width: 320px;">
                                                                <li class="mb-2"><i class="ti ti-circle-check text-success me-2"></i>Custom User Limit</li>
                                                                <li class="mb-2"><i class="ti ti-circle-check text-success me-2"></i>Custom Property Limit</li>
                                                                <li class="mb-2"><i class="ti ti-circle-check text-success me-2"></i>Custom Tenant Limit</li>
                                                                <li class="mb-2"><i class="ti ti-circle-check text-success me-2"></i>Custom Unit Range</li>
                                                                <li class="mb-2"><i class="ti ti-circle-check text-success me-2"></i>Priority Support</li>
                                                                <li class="mb-2"><i class="ti ti-circle-check text-success me-2"></i>Custom Features</li>
                                                            </ul>
                                                        </div>
                                                        <div class="mt-auto w-100">
                                                            <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#enterpriseContactModal">
                                                                Contact Us
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center">
                            <p>{{ __('No packages available.') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deletePackageModal" tabindex="-1" aria-labelledby="deletePackageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deletePackageModalLabel">{{ __('Confirm Delete') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    {{ __('Are you sure you want to delete this package? This action cannot be undone.') }}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">{{ __('Delete') }}</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Enterprise Contact Modal -->
    <div class="modal fade" id="enterpriseContactModal" tabindex="-1" aria-labelledby="enterpriseContactModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="enterpriseContactModalLabel">Enterprise Package Inquiry</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="enterpriseContactForm">
                        @csrf
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ Auth::user()->name ?? '' }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="{{ Auth::user()->email ?? '' }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone (Optional)</label>
                            <input type="text" class="form-control" id="phone" name="phone" placeholder="Enter phone number (optional)">
                        </div>
                        <div class="mb-3">
                            <label for="property_limit" class="form-label">Desired Property Limit</label>
                            <input type="text" class="form-control" id="property_limit" name="property_limit" required>
                        </div>
                        <div class="mb-3">
                            <label for="unit_limit" class="form-label">Desired Unit Limit</label>
                            <input type="text" class="form-control" id="unit_limit" name="unit_limit" required>
                        </div>
                        <div class="mb-3">
                            <label for="interval" class="form-label">Preferred Interval</label>
                            <select class="form-control" id="interval" name="interval" required>
                                <option value="Monthly">Monthly</option>
                                <option value="Quarterly">Quarterly</option>
                                <option value="Yearly">Yearly</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="message" class="form-label">Additional Message (Optional)</label>
                            <textarea class="form-control" id="message" name="message" rows="3"></textarea>
                        </div>
                        <div id="enterpriseContactMsg" class="mb-2"></div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="submitEnterpriseContact">Submit</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    let packageToDelete = null;
    let deleteModal = null;
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.delete-package-btn').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                console.log('Delete button clicked for package:', btn.getAttribute('data-package-id'));
                packageToDelete = btn.getAttribute('data-package-id');
                deleteModal = new bootstrap.Modal(document.getElementById('deletePackageModal'));
                deleteModal.show();
            });
        });
        document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
            if (packageToDelete) {
                const form = document.getElementById('delete-form-' + packageToDelete);
                if (form) {
                    form.submit();
                } else {
                    alert('Delete form not found!');
                }
                if(deleteModal) deleteModal.hide();
            }
        });
        document.getElementById('deletePackageModal').addEventListener('hidden.bs.modal', function () {
            packageToDelete = null;
        });
    });

    document.getElementById('submitEnterpriseContact').onclick = function() {
        var form = document.getElementById('enterpriseContactForm');
        var formData = new FormData(form);
        var msgDiv = document.getElementById('enterpriseContactMsg');
        msgDiv.innerHTML = '';
        fetch('/enterprise/contact', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.message) {
                msgDiv.innerHTML = '<div class="alert alert-success">' + data.message + '</div>';
                form.reset();
            } else if (data.errors) {
                let errors = Object.values(data.errors).map(e => e[0]).join('<br>');
                msgDiv.innerHTML = '<div class="alert alert-danger">' + errors + '</div>';
            }
        })
        .catch(() => {
            msgDiv.innerHTML = '<div class="alert alert-danger">Error sending request. Please try again later.</div>';
        });
    };
</script>
@endpush
