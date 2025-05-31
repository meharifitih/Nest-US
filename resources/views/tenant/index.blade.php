@extends('layouts.app')
@section('page-title')
    {{ __('Tenant') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item" aria-current="page"> {{ __('Tenant') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center g-2">
                        <div class="col">
                            <h5>{{ __('Tenant List') }}</h5>
                        </div>
                        @can('create tenant')
                            <div class="col-auto">
                                <a class="btn btn-secondary" href="{{ route('tenant.create') }}" data-size="md"> <i
                                        class="ti ti-circle-plus align-text-bottom"></i> {{ __('Create Tenant') }}</a>
                            </div>
                        @endcan
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        @foreach ($tenants as $tenant)
                            <div class="col-xxl-3 col-xl-4 col-md-6">
                                <div class="card follower-card tenant-card mx-auto" style="position:relative;display:block;" onclick="goToTenantDetail('{{ route('tenant.show', $tenant->id) }}')">
                                    <div class="tenant-card-actions">
                                        @can('edit tenant')
                                            <a href="{{ route('tenant.edit', $tenant->id) }}" class="tenant-action-btn" title="Edit Tenant" onclick="event.stopPropagation();">
                                                <i class="ti ti-edit"></i>
                                            </a>
                                        @endcan
                                        @can('delete tenant')
                                            <form method="POST" action="{{ route('tenant.destroy', $tenant->id) }}" style="display:inline;" onsubmit="event.stopPropagation(); return confirm('Are you sure you want to delete this tenant?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="tenant-action-btn" title="Delete Tenant">
                                                    <i class="ti ti-trash"></i>
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                    <img src="{{ !empty($tenant->user) && !empty($tenant->user->profile) ? asset(Storage::url('upload/profile/' . $tenant->user->profile)) : asset(Storage::url('upload/profile/avatar.png')) }}" class="tenant-avatar mb-2" alt="avatar">
                                    <div class="tenant-name">{{ !empty($tenant->user) ? $tenant->user->first_name . ' ' . $tenant->user->last_name : '-' }}</div>
                                    <div class="tenant-email">{{ !empty($tenant->user) ? $tenant->user->email : '-' }}</div>
                                    <div class="tenant-info-list mt-3">
                                        <div class="tenant-info-item">
                                            <div class="tenant-info-label">Phone</div>
                                            <div class="tenant-info-value">
                                                {{ !empty($tenant->user) ? $tenant->user->phone_number : '-' }}
                                                @if(!empty($tenant->user) && $tenant->user->phone_number)
                                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $tenant->user->phone_number) }}" target="_blank" class="wa-btn-tenant" title="Chat on WhatsApp" onclick="event.stopPropagation();">
                                                        <i class="fab fa-whatsapp"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="tenant-info-item">
                                            <div class="tenant-info-label">Family Member</div>
                                            <div class="tenant-info-value">{{ $tenant->family_member ?? '-' }}</div>
                                        </div>
                                        <div class="tenant-info-item">
                                            <div class="tenant-info-label">Property</div>
                                            <div class="tenant-info-value">{{ !empty($tenant->properties) ? $tenant->properties->name : '-' }}</div>
                                        </div>
                                        <div class="tenant-info-item">
                                            <div class="tenant-info-label">Unit</div>
                                            <div class="tenant-info-value">{{ !empty($tenant->units) ? $tenant->units->name : '-' }}</div>
                                        </div>
                                        <div class="tenant-info-item">
                                            <div class="tenant-info-label">Lease Start Date</div>
                                            <div class="tenant-info-value">{{ $tenant->lease_start_date ? dateFormat($tenant->lease_start_date) : '-' }}</div>
                                        </div>
                                        <div class="tenant-info-item">
                                            <div class="tenant-info-label">Lease End Date</div>
                                            <div class="tenant-info-value">{{ $tenant->lease_end_date ? dateFormat($tenant->lease_end_date) : '-' }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

<style>
.tenant-card-link {
    text-decoration: none;
    color: inherit;
}
.tenant-card {
    background: #fff;
    border-radius: 1.1rem;
    box-shadow: 0 4px 18px rgba(60,60,60,0.10);
    padding: 1rem 0.7rem 0.7rem 0.7rem;
    margin-bottom: 1.5rem;
    width: 230px;
    min-width: 180px;
    max-width: 90vw;
    border: 1px solid #f0f0f0;
    transition: box-shadow 0.18s, border 0.18s, transform 0.18s;
    position: relative;
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
    cursor: pointer;
    font-size: 0.95rem;
}
.tenant-card:hover {
    box-shadow: 0 10px 32px rgba(60,60,60,0.18);
    border: 1.5px solid #25d366;
    transform: translateY(-2px) scale(1.015);
}
.tenant-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
    margin-bottom: 0.5rem;
    border: 2.5px solid #e9ecef;
}
.tenant-name {
    font-size: 1rem;
    font-weight: 700;
    margin-bottom: 0.03rem;
    color: #222;
}
.tenant-email {
    font-size: 0.85rem;
    color: #888;
    margin-bottom: 0.5rem;
}
.tenant-info-list {
    display: flex;
    flex-direction: column;
    gap: 0.22rem;
    width: 100%;
    margin-top: 0.2rem;
}
.tenant-info-item {
    display: flex;
    flex-direction: column;
    align-items: center;
}
.tenant-info-label {
    color: #888;
    font-size: 0.82rem;
    font-weight: 500;
    margin-bottom: 0.1rem;
}
.tenant-info-value {
    color: #222;
    font-weight: 600;
    font-size: 0.89rem;
    display: flex;
    align-items: center;
    gap: 0.3rem;
}
.wa-btn-tenant {
    background: #25d366;
    color: #fff;
    border-radius: 50%;
    width: 22px;
    height: 22px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.98rem;
    margin-left: 0.25rem;
    transition: background 0.2s;
    border: none;
    box-shadow: 0 1px 3px rgba(60,60,60,0.08);
}
.wa-btn-tenant:hover {
    background: #128c7e;
    color: #fff;
    text-decoration: none;
}
.tenant-card-actions {
    position: absolute;
    top: 0.5rem;
    right: 0.7rem;
    display: flex;
    gap: 0.4rem;
    z-index: 2;
}
.tenant-action-btn {
    background: #f5f5f5;
    border: none;
    border-radius: 50%;
    width: 26px;
    height: 26px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #555;
    font-size: 1.1rem;
    transition: background 0.18s, color 0.18s;
    cursor: pointer;
    box-shadow: 0 1px 2px rgba(60,60,60,0.07);
}
.tenant-action-btn:hover {
    background: #e9ecef;
    color: #25d366;
}
</style>

<script>
function goToTenantDetail(url) {
    window.location = url;
}
</script>
