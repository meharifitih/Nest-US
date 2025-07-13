@extends('layouts.app')

@section('page-title')
    {{ __('HOA') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item" aria-current="page">{{ __('HOA') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="mb-0">{{ __('HOA List') }}</h5>
                        </div>
                        <div class="col-auto d-flex gap-2">
                            <button type="button" class="btn btn-primary px-3" data-bs-toggle="modal" data-bs-target="#filterModal">
                                <i class="ti ti-filter me-1"></i> {{ __('Filter') }}
                            </button>
                            @can('create hoa')
                                <a href="{{ route('hoa.create') }}" class="btn btn-secondary px-3">
                                    <i class="ti ti-plus me-1"></i> {{ __('Create HOA') }}
                                </a>
                            @endcan
                        </div>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Filter Modal -->
                    <div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="filterModalLabel">{{ __('Filter HOA') }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form method="GET" action="{{ route('hoa.index') }}">
                                    <div class="modal-body">
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    {{ Form::label('property_id', __('Property'), ['class' => 'form-label']) }}
                                                    <select name="property_id" class="form-select">
                                                        <option value="">{{ __('All') }}</option>
                                                        @foreach($properties as $property)
                                                            <option value="{{ $property->id }}" {{ request('property_id') == $property->id ? 'selected' : '' }}>
                                                                {{ $property->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    {{ Form::label('unit_id', __('Unit'), ['class' => 'form-label']) }}
                                                    <select name="unit_id" class="form-select">
                                                        <option value="">{{ __('All') }}</option>
                                                        @foreach($units as $unit)
                                                            <option value="{{ $unit->id }}" {{ request('unit_id') == $unit->id ? 'selected' : '' }}>
                                                                {{ $unit->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    {{ Form::label('tenant', __('Tenant'), ['class' => 'form-label']) }}
                                                    <select name="tenant" class="form-select">
                                                        <option value="">{{ __('All') }}</option>
                                                        @foreach($tenants as $tenant)
                                                            <option value="{{ $tenant->user_id }}" {{ request('tenant') == $tenant->user_id ? 'selected' : '' }}>
                                                                {{ $tenant->user ? $tenant->user->name : '-' }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    {{ Form::label('hoa_type', __('HOA Type'), ['class' => 'form-label']) }}
                                                    <select name="hoa_type" class="form-select">
                                                        <option value="">{{ __('All Types') }}</option>
                                                        @foreach($hoaTypes as $type)
                                                            <option value="{{ $type->id }}" {{ request('hoa_type') == $type->id ? 'selected' : '' }}>
                                                                {{ $type->title }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    {{ Form::label('status', __('Status'), ['class' => 'form-label']) }}
                                                    {{ Form::select('status', ['' => __('All')] + $statusOptions, request('status'), ['class' => 'form-select']) }}
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    {{ Form::label('due_date', __('Due Date'), ['class' => 'form-label']) }}
                                                    {{ Form::date('due_date', request('due_date'), ['class' => 'form-control']) }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-primary px-4">{{ __('Apply Filter') }}</button>
                                        <a href="{{ route('hoa.index') }}" class="btn btn-light px-4">{{ __('Reset') }}</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>{{ __('HOA Number') }}</th>
                                    <th>{{ __('Property') }}</th>
                                    <th>{{ __('Unit') }}</th>
                                    <th>{{ __('Tenant') }}</th>
                                    <th>{{ __('HOA Type') }}</th>
                                    <th>{{ __('Amount') }}</th>
                                    <th>{{ __('Frequency') }}</th>
                                    <th>{{ __('Due Date') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th class="text-end">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($hoas as $hoa)
                                    <tr class="clickable-hoa-row" data-href="{{ route('hoa.show', $hoa) }}">
                                        <td>{{ $hoa->hoa_number ?? '-' }}</td>
                                        <td>{{ $hoa->property->name ?? '-' }}</td>
                                        <td>{{ $hoa->unit->name ?? '-' }}</td>
                                        <td>{{ $hoa->unit && $hoa->unit->tenants && $hoa->unit->tenants->user ? $hoa->unit->tenants->user->name : '-' }}</td>
                                        <td>{{ $hoa->hoaType->title ?? '-' }}</td>
                                        <td><span class="fw-medium">{{ priceFormat($hoa->amount) }}</span></td>
                                        <td>{{ ucfirst($hoa->frequency) }}</td>
                                        <td>{{ $hoa->due_date ? dateFormat($hoa->due_date) : '-' }}</td>
                                        <td>
                                            @if ($hoa->status == 'pending')
                                                <span class="badge bg-warning-subtle text-warning">{{ __('Pending') }}</span>
                                            @elseif ($hoa->status == 'open')
                                                <span class="badge bg-info-subtle text-info">{{ __('Open') }}</span>
                                            @elseif ($hoa->status == 'paid')
                                                <span class="badge bg-success-subtle text-success">{{ __('Paid') }}</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('hoa.show', $hoa) }}" class="btn btn-sm btn-info" title="View"><i class="ti ti-eye"></i></a>
                                            <a href="{{ route('hoa.edit', $hoa) }}" class="btn btn-sm btn-secondary" title="Edit"><i class="ti ti-edit"></i></a>
                                            <form action="{{ route('hoa.destroy', $hoa) }}" method="POST" style="display:inline-block;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm('Are you sure?')"><i class="ti ti-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted py-3">{{ __('No data available') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script-page')
<script>
    $(document).on('click', '.clickable-hoa-row', function(e) {
        if (!$(e.target).closest('a, button, input, .cart-action').length) {
            window.location = $(this).data('href');
        }
    });
</script>
@endpush 