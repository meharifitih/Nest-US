<tr class="clickable-invoice-row" data-href="{{ route('invoice.show', $invoice->id) }}">
    <td>
        <span class="fw-medium">{{ invoicePrefix() . $invoice->invoice_id }}</span>
    </td>
    <td>{{ !empty($invoice->properties) ? $invoice->properties->name : '-' }}</td>
    <td>{{ !empty($invoice->units) ? $invoice->units->name : '-' }}</td>
    <td>{{ date('F Y', strtotime($invoice->invoice_month)) }}</td>
    <td>{{ dateFormat($invoice->end_date) }}</td>
    <td>
        <span class="fw-medium">{{ priceFormat($invoice->getInvoiceSubTotalAmount()) }}</span>
    </td>
    <td>
        @if ($invoice->status == 'pending')
            <span class="badge bg-warning-subtle text-warning">{{ __('Pending') }}</span>
        @elseif ($invoice->status == 'open')
            <span class="badge bg-info-subtle text-info">{{ __('Open') }}</span>
        @elseif($invoice->status == 'paid')
            <span class="badge bg-success-subtle text-success">{{ __('Paid') }}</span>
        @elseif($invoice->status == 'partial_paid')
            <span class="badge bg-warning-subtle text-warning">{{ __('Partially Paid') }}</span>
        @endif
    </td>
    <td>{{ !empty($invoice->tenants()) && !empty($invoice->tenants()->user) ? $invoice->tenants()->user->name : '-' }}</td>
    <td>
        @php
            $type = $invoice->types->first();
            echo $type && $type->types ? $type->types->title : '-';
        @endphp
    </td>
    @if (auth()->user()->type == 'tenant')
        <td class="text-end">
            <div class="d-flex justify-content-end gap-2">
                @if ($invoice->status == 'open')
                    <a href="{{ route('invoice.show', $invoice->id) }}" class="btn btn-sm btn-primary px-3" onclick="event.stopPropagation();">
                        {{ __('Pay Now') }}
                    </a>
                @else
                    <a href="{{ route('invoice.show', $invoice->id) }}" class="btn btn-sm btn-light" onclick="event.stopPropagation();" data-bs-toggle="tooltip" title="{{ __('View') }}">
                        <i class="ti ti-eye"></i>
                    </a>
                @endif
            </div>
        </td>
    @else
        <td class="text-end">
            <div class="d-flex justify-content-end gap-2">
                @can('show invoice')
                    <a href="{{ route('invoice.show', $invoice->id) }}" class="btn btn-sm btn-light" onclick="event.stopPropagation();" data-bs-toggle="tooltip" title="{{ __('View') }}">
                        <i class="ti ti-eye"></i>
                    </a>
                @endcan
                @can('edit invoice')
                    <a href="{{ route('invoice.edit', $invoice->id) }}" class="btn btn-sm btn-light" onclick="event.stopPropagation();" data-bs-toggle="tooltip" title="{{ __('Edit') }}">
                        <i class="ti ti-pencil"></i>
                    </a>
                @endcan
                @can('delete invoice')
                    @php
                        $isRent = false;
                        if (isset($invoice->types) && $invoice->types->first() && isset($invoice->types->first()->types) && $invoice->types->first()->types->type === 'rent') {
                            $isRent = true;
                        }
                    @endphp
                    <form method="POST" action="{{ $isRent ? route('rent.destroy', $invoice->id) : route('invoice.destroy', $invoice->id) }}" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-light text-danger" onclick="event.stopPropagation();" data-bs-toggle="tooltip" title="{{ __('Delete') }}">
                            <i class="ti ti-trash"></i>
                        </button>
                    </form>
                @endcan
            </div>
        </td>
    @endif
</tr> 