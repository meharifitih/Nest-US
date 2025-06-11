@extends('layouts.app')

@section('page-title')
    {{ __('HOA') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item" aria-current="page"> {{ __('HOA') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card table-card">
                <div class="card-header">
                    <div class="row align-items-center g-2">
                        <div class="col">
                            <h5>{{ __('HOA List') }}</h5>
                        </div>
                        <div class="col-auto">
                            <form method="GET" action="">
                                <select name="hoa_type_filter" class="form-select" onchange="this.form.submit()">
                                    <option value="">All HOA Types</option>
                                    @foreach($hoa_types as $id => $title)
                                        <option value="{{ $id }}" {{ request('hoa_type_filter') == $id ? 'selected' : '' }}>{{ $title }}</option>
                                    @endforeach
                                </select>
                            </form>
                        </div>
                        @if (Auth::user()->hasRole('owner'))
                            <div class="col-auto">
                                <a href="{{ route('hoa.create') }}" class="btn btn-secondary">
                                    <i class="ti ti-circle-plus align-text-bottom"></i> {{ __('Create HOA') }}
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="dt-responsive table-responsive">
                        <table class="table table-hover advance-datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Property') }}</th>
                                    <th>{{ __('Unit') }}</th>
                                    <th>{{ __('Tenant') }}</th>
                                    <th>{{ __('HOA Type') }}</th>
                                    <th>{{ __('Amount') }}</th>
                                    <th>{{ __('Frequency') }}</th>
                                    <th>{{ __('Due Date') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($hoas as $hoa)
                                    <tr class="clickable-hoa-row" data-href="{{ route('hoa.show', $hoa) }}">
                                        <td>{{ $hoa->property->name ?? '-' }}</td>
                                        <td>{{ $hoa->unit->name ?? '-' }}</td>
                                        <td>{{ $hoa->unit && $hoa->unit->tenants && $hoa->unit->tenants->user ? $hoa->unit->tenants->user->name : '-' }}</td>
                                        <td>{{ $hoa->hoaType->title ?? '-' }}</td>
                                        <td>{{ priceFormat($hoa->amount) }}</td>
                                        <td>{{ ucfirst($hoa->frequency) }}</td>
                                        <td>{{ $hoa->due_date ? dateFormat($hoa->due_date) : '-' }}</td>
                                        <td>
                                            @if ($hoa->status == 'pending')
                                                <span class="badge bg-light-warning">Pending</span>
                                            @elseif ($hoa->status == 'open')
                                                <span class="badge bg-light-warning">Open</span>
                                            @elseif ($hoa->status == 'paid')
                                                <span class="badge bg-light-success">Paid</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="cart-action">
                                                @if(Auth::user()->hasRole('tenant') && $hoa->status == 'open')
                                                    <a href="{{ route('hoa.show', $hoa) }}" class="btn btn-primary btn-sm" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Pay Now') }}" onclick="event.stopPropagation();">
                                                        <i class="ti ti-credit-card"></i> {{ __('Pay Now') }}
                                                    </a>
                                                @elseif(Auth::user()->hasRole('tenant'))
                                                    <a class="avtar avtar-xs btn-link-warning text-warning"
                                                        href="{{ route('hoa.show', $hoa) }}"
                                                        data-bs-toggle="tooltip"
                                                        data-bs-original-title="{{ __('View') }}" onclick="event.stopPropagation();">
                                                        <i data-feather="eye"></i>
                                                    </a>
                                                @endif
                                                @if(Auth::user()->hasRole('owner'))
                                                    <a href="{{ route('hoa.show', $hoa) }}" class="btn btn-warning btn-sm" data-bs-toggle="tooltip" data-bs-original-title="{{ __('View') }}" onclick="event.stopPropagation();">
                                                        <i class="ti ti-eye"></i>
                                                    </a>
                                                    <a href="{{ route('hoa.edit', $hoa) }}" class="btn btn-info btn-sm" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Edit') }}" onclick="event.stopPropagation();">
                                                        <i class="ti ti-edit"></i>
                                                    </a>
                                                    <form action="{{ route('hoa.destroy', $hoa) }}" method="POST" style="display:inline;" onsubmit="event.stopPropagation();">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-sm confirm_dialog" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Delete') }}">
                                                            <i class="ti ti-trash"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="mt-4">
                            {{ $hoas->links('vendor.pagination.bootstrap-5') }}
                        </div>
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