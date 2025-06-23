@extends('layouts.app')
@section('page-title')
    {{ __('Dashboard') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item" aria-current="page">{{ __('Dashboard') }}</li>
@endsection

@push('script-page')
<script>
    // Remove any existing chart before rendering a new one
    var chartContainer = document.querySelector('#incomeExpense');
    if (chartContainer) {
        chartContainer.innerHTML = '';
    }
    var options = {
        chart: {
            type: 'area',
            height: 320,
            toolbar: {
                show: false
            }
        },
        colors: ['#2ca58d', '#0a2342'],
        dataLabels: {
            enabled: false
        },
        legend: {
            show: true,
            position: 'top'
        },
        markers: {
            size: 1,
            colors: ['#fff', '#fff', '#fff'],
            strokeColors: ['#2ca58d', '#0a2342'],
            strokeWidth: 1,
            shape: 'circle',
            hover: {
                size: 4
            }
        },
        stroke: {
            width: 2,
            curve: 'smooth'
        },
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                type: 'vertical',
                inverseColors: false,
                opacityFrom: 0.5,
                opacityTo: 0
            }
        },
        grid: {
            show: false
        },
        series: [{
                name: "{{ __('Total Income') }}",
                data: {!! json_encode($result['incomeExpenseByMonth']['income']) !!}
            },
            {
                name: "{{ __('Total Expense') }}",
                data: {!! json_encode($result['incomeExpenseByMonth']['expense']) !!}
            }
        ],
        xaxis: {
            categories: {!! json_encode($result['incomeExpenseByMonth']['label']) !!},
            tooltip: {
                enabled: false
            },
            labels: {
                hideOverlappingLabels: true
            },
            axisBorder: {
                show: false
            },
            axisTicks: {
                show: false
            }
        }
    };
    var chart = new ApexCharts(document.querySelector('#incomeExpense'), options);
    chart.render();

</script>
@endpush

@php
    $settings = settings();

@endphp
@section('content')
    <div class="d-flex justify-content-center">
        <div class="row mb-2 mt-2 g-4" style="max-width: 950px;">
            <div class="col-lg col-md-4">
                <div class="card h-100 text-center shadow-sm" style="border-radius: 18px; min-height: 120px;">
                    <div class="card-body d-flex flex-column justify-content-center align-items-center py-2">
                        <div class="avtar bg-light-secondary mb-2">
                            <i class="ti ti-building f-24"></i>
                        </div>
                        <p class="mb-1">{{ __('Total Property') }}</p>
                        <h4 class="mb-0">{{ $result['totalProperty'] }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-lg col-md-4">
                <div class="card h-100 text-center shadow-sm" style="border-radius: 18px; min-height: 120px;">
                    <div class="card-body d-flex flex-column justify-content-center align-items-center py-2">
                        <div class="avtar bg-light-warning mb-2">
                            <i class="ti ti-3d-cube-sphere f-24"></i>
                        </div>
                        <p class="mb-1">{{ __('Total Unit') }}</p>
                        <h4 class="mb-0">{{ $result['totalUnit'] }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-lg col-md-4">
                <div class="card h-100 text-center shadow-sm" style="border-radius: 18px; min-height: 120px;">
                    <div class="card-body d-flex flex-column justify-content-center align-items-center py-2">
                        <div class="avtar bg-light-primary mb-2">
                            <i class="ti ti-file-invoice f-24"></i>
                        </div>
                        <p class="mb-1">{{ __('Total Invoice') }}</p>
                        <h4 class="mb-0">{{ $settings['CURRENCY_SYMBOL'] }}<span class="count">{{ $result['totalIncome'] }}</span></h4>
                    </div>
                </div>
            </div>
            <div class="col-lg col-md-4">
                <div class="card h-100 text-center shadow-sm" style="border-radius: 18px; min-height: 120px;">
                    <div class="card-body d-flex flex-column justify-content-center align-items-center py-2">
                        <div class="avtar bg-light-warning mb-2">
                            <i class="ti ti-file-invoice f-24"></i>
                        </div>
                        <p class="mb-1">{{ __('Pending Invoices') }}</p>
                        <h4 class="mb-0">{{ $result['pendingInvoice'] }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-lg col-md-4">
                <div class="card h-100 text-center shadow-sm" style="border-radius: 18px; min-height: 120px;">
                    <div class="card-body d-flex flex-column justify-content-center align-items-center py-2">
                        <div class="avtar bg-light-danger mb-2">
                            <i class="ti ti-exposure f-24"></i>
                        </div>
                        <p class="mb-1">{{ __('Total Expense') }}</p>
                        <h4 class="mb-0">{{ $settings['CURRENCY_SYMBOL'] }}<span class="count">{{ $result['totalExpense'] }}</span></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-12 col-md-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <h5 class="mb-1">{{ __('Analysis Report') }}</h5>
                        <p class="text-muted mb-2">{{ __('Income and Expense Overview') }}</p>
                    </div>

                </div>
                <div id="incomeExpense"></div>
            </div>
        </div>
    </div>


@endsection
