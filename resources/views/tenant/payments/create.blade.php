@extends('layouts.app')

@section('page-title')
    {{ __('Create Payment') }}
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">{{ __('Create New Payment') }}</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('tenant.payments.store') }}" method="POST">
                        @csrf
                        
                        @if($invoice)
                            <input type="hidden" name="invoice_id" value="{{ $invoice->id }}">
                        @endif

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tenant_id">{{ __('Tenant') }} <span class="text-danger">*</span></label>
                                    <select name="tenant_id" id="tenant_id" class="form-control" required>
                                        <option value="">{{ __('Select Tenant') }}</option>
                                        @foreach($tenants as $tenant)
                                            <option value="{{ $tenant->id }}" 
                                                {{ $tenant && $tenant->id == $tenant->id ? 'selected' : '' }}>
                                                {{ $tenant->user->name ?? 'N/A' }} 
                                                @if($tenant->property)
                                                    - {{ $tenant->property->name ?? 'N/A' }}
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('tenant_id')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="payment_type">{{ __('Payment Type') }} <span class="text-danger">*</span></label>
                                    <select name="payment_type" id="payment_type" class="form-control" required>
                                        <option value="">{{ __('Select Payment Type') }}</option>
                                        <option value="rent" {{ $invoice && $invoice->types->where('types.type', 'rent')->count() > 0 ? 'selected' : '' }}>
                                            {{ __('Rent') }}
                                        </option>
                                        <option value="utilities">{{ __('Utilities') }}</option>
                                        <option value="maintenance">{{ __('Maintenance') }}</option>
                                        <option value="other">{{ __('Other') }}</option>
                                    </select>
                                    @error('payment_type')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="amount">{{ __('Amount') }} <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">{{ settings()['CURRENCY_SYMBOL'] ?? '$' }}</span>
                                        <input type="number" name="amount" id="amount" class="form-control" 
                                               step="0.01" min="0.01" required
                                               value="{{ $invoice ? $invoice->getInvoiceSubTotalAmount() : '' }}">
                                    </div>
                                    @error('amount')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="due_date">{{ __('Due Date') }} <span class="text-danger">*</span></label>
                                    <input type="date" name="due_date" id="due_date" class="form-control" required
                                           value="{{ $invoice ? $invoice->end_date : date('Y-m-d') }}">
                                    @error('due_date')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="form-check">
                                        <input type="checkbox" name="is_recurring" id="is_recurring" class="form-check-input" value="1">
                                        <label class="form-check-label" for="is_recurring">
                                            {{ __('Make this a recurring payment') }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group" id="recurring_interval_group" style="display: none;">
                                    <label for="recurring_interval">{{ __('Recurring Interval') }}</label>
                                    <select name="recurring_interval" id="recurring_interval" class="form-control">
                                        <option value="monthly">{{ __('Monthly') }}</option>
                                        <option value="quarterly">{{ __('Quarterly') }}</option>
                                        <option value="yearly">{{ __('Yearly') }}</option>
                                    </select>
                                    @error('recurring_interval')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="notes">{{ __('Notes (Optional)') }}</label>
                                    <textarea name="notes" id="notes" class="form-control" rows="3" 
                                              placeholder="{{ __('Add any additional notes about this payment...') }}"></textarea>
                                    @error('notes')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('tenant.payments.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left"></i> {{ __('Back') }}
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> {{ __('Create Payment') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('script-page')
<script>
    $(document).ready(function() {
        // Show/hide recurring interval based on checkbox
        $('#is_recurring').change(function() {
            if ($(this).is(':checked')) {
                $('#recurring_interval_group').show();
                $('#recurring_interval').prop('required', true);
            } else {
                $('#recurring_interval_group').hide();
                $('#recurring_interval').prop('required', false);
            }
        });

        // Auto-populate amount if invoice exists
        @if($invoice)
            $('#amount').val('{{ $invoice->getInvoiceSubTotalAmount() }}');
        @endif
    });
</script>
@endpush
@endsection 