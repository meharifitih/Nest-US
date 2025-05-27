<tr>
    <td>{{ invoicePrefix() . $invoice->invoice_id }} </td>
    <td>{{ !empty($invoice->properties) ? $invoice->properties->name : '-' }} </td>
    <td>{{ !empty($invoice->units) ? $invoice->units->name : '-' }} </td>
    <td>{{ date('F Y', strtotime($invoice->invoice_month)) }} </td>
    <td>{{ dateFormat($invoice->end_date) }} </td>
    <td>{{ priceFormat($invoice->getInvoiceSubTotalAmount()) }}</td>
    <td>
        @if ($invoice->status == 'pending')
            <span class="badge bg-light-warning">Pending</span>
        @elseif ($invoice->status == 'open')
            <span class="badge bg-light-info">{{ \App\Models\Invoice::$status[$invoice->status] }}</span>
        @elseif($invoice->status == 'paid')
            <span class="badge bg-light-success">{{ \App\Models\Invoice::$status[$invoice->status] }}</span>
        @elseif($invoice->status == 'partial_paid')
            <span class="badge bg-light-warning">{{ \App\Models\Invoice::$status[$invoice->status] }}</span>
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
        <td>
            <div class="cart-action">
                @if ($invoice->status == 'open')
                    <a href="{{ route('invoice.show', $invoice->id) }}" class="btn btn-primary btn-sm">Pay Now</a>
                @else
                    <a class="avtar avtar-xs btn-link-warning text-warning"
                        href="{{ route('invoice.show', $invoice->id) }}"
                        data-bs-toggle="tooltip"
                        data-bs-original-title="{{ __('View') }}"> <i data-feather="eye"></i></a>
                @endif
            </div>
        </td>
    @else
        <td>
            <div class="cart-action">
                {!! Form::open(['method' => 'DELETE', 'route' => ['invoice.destroy', $invoice->id]]) !!}
                @can('show invoice')
                    <a class="avtar avtar-xs btn-link-warning text-warning"
                        href="{{ route('invoice.show', $invoice->id) }}"
                        data-bs-toggle="tooltip"
                        data-bs-original-title="{{ __('View') }}"> <i
                            data-feather="eye"></i></a>
                @endcan
                @can('edit invoice')
                    <a class="avtar avtar-xs btn-link-secondary text-secondary" data-bs-original-title="{{ __('Edit') }}"
                        href="{{ route('invoice.edit', $invoice->id) }}" data-bs-toggle="tooltip"
                        data-bs-original-title="{{ __('Edit') }}"> <i
                            data-feather="edit"></i></a>
                @endcan
                @can('delete invoice')
                    <a class="avtar avtar-xs btn-link-danger text-danger confirm_dialog" data-bs-toggle="tooltip"
                        data-bs-original-title="{{ __('Detete') }}" href="#"> <i
                            data-feather="trash-2"></i></a>
                @endcan
                {!! Form::close() !!}
            </div>
        </td>
    @endif
</tr> 