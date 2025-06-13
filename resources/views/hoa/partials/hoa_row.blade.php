<tr class="clickable-hoa-row" data-href="{{ route('hoa.show', $hoa) }}">
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
        <div class="d-flex justify-content-end gap-2">
            @if(auth()->user()->type == 'tenant')
                @if($hoa->status == 'open')
                    <a href="{{ route('hoa.show', $hoa) }}" class="btn btn-sm btn-primary px-3" onclick="event.stopPropagation();" data-bs-toggle="tooltip" title="{{ __('Pay Now') }}">
                        {{ __('Pay Now') }}
                    </a>
                @else
                    <a href="{{ route('hoa.show', $hoa) }}" class="btn btn-sm btn-light" onclick="event.stopPropagation();" data-bs-toggle="tooltip" title="{{ __('View') }}">
                        <i class="ti ti-eye"></i>
                    </a>
                @endif
            @else
                @can('show hoa')
                    <a href="{{ route('hoa.show', $hoa) }}" class="btn btn-sm btn-light" onclick="event.stopPropagation();" data-bs-toggle="tooltip" title="{{ __('View') }}">
                        <i class="ti ti-eye"></i>
                    </a>
                @endcan
                @can('edit hoa')
                    <a href="{{ route('hoa.edit', $hoa) }}" class="btn btn-sm btn-light" onclick="event.stopPropagation();" data-bs-toggle="tooltip" title="{{ __('Edit') }}">
                        <i class="ti ti-pencil"></i>
                    </a>
                @endcan
                @can('delete hoa')
                    <form action="{{ route('hoa.destroy', $hoa) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-light text-danger" onclick="event.stopPropagation();" data-bs-toggle="tooltip" title="{{ __('Delete') }}">
                            <i class="ti ti-trash"></i>
                        </button>
                    </form>
                @endcan
            @endif
        </div>
    </td>
</tr> 