<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Dashboard')); ?>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item" aria-current="page"><?php echo e(__('Dashboard')); ?></li>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('script-page'); ?>
<script>
    // Remove any existing chart before rendering a new one
    var chartContainer = document.querySelector('#incomeExpense');
    if (chartContainer) {
        chartContainer.innerHTML = '';
    }
    var options = {
        chart: {
            type: 'area',
            height: 450,
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
                name: "<?php echo e(__('Total Income')); ?>",
                data: <?php echo json_encode($result['incomeExpenseByMonth']['income']); ?>

            },
            {
                name: "<?php echo e(__('Total Expense')); ?>",
                data: <?php echo json_encode($result['incomeExpenseByMonth']['expense']); ?>

            }
        ],
        xaxis: {
            categories: <?php echo json_encode($result['incomeExpenseByMonth']['label']); ?>,
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
<?php $__env->stopPush(); ?>

<?php
    $settings = settings();

?>
<?php $__env->startSection('content'); ?>
    <div class="row">
        <div class="col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avtar bg-light-secondary">
                                <i class="ti ti-building f-24"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="mb-1"><?php echo e(__('Total Property')); ?></p>
                            <div class="d-flex align-items-center justify-content-between">
                                <h4 class="mb-0"><?php echo e($result['totalProperty']); ?></h4>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avtar bg-light-warning">
                                <i class="ti ti-3d-cube-sphere f-24"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="mb-1"><?php echo e(__('Total Unit')); ?></p>
                            <div class="d-flex align-items-center justify-content-between">
                                <h4 class="mb-0"><?php echo e($result['totalUnit']); ?></h4>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avtar bg-light-primary">
                                <i class="ti ti-file-invoice f-24"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="mb-1"><?php echo e(__('Total Invoice')); ?></p>
                            <div class="d-flex align-items-center justify-content-between">
                                <h4 class="mb-0"><?php echo e($settings['CURRENCY_SYMBOL']); ?><span
                                        class="count"><?php echo e($result['totalIncome']); ?></h4>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avtar bg-light-danger">
                                <i class="ti ti-exposure f-24"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="mb-1"><?php echo e(__('Total Expense')); ?></p>
                            <div class="d-flex align-items-center justify-content-between">
                                <h4 class="mb-0"><?php echo e($settings['CURRENCY_SYMBOL']); ?><span
                                        class="count"><?php echo e($result['totalExpense']); ?></h4>

                            </div>
                        </div>
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
                        <h5 class="mb-1"><?php echo e(__('Analysis Report')); ?></h5>
                        <p class="text-muted mb-2"><?php echo e(__('Income and Expense Overview')); ?></p>
                    </div>

                </div>
                <div id="incomeExpense"></div>
            </div>
        </div>
    </div>


<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/chipchip/Downloads/codecanyon-ytuZNl0y-smart-tenant-property-management-system-saas/main_file/resources/views/dashboard/index.blade.php ENDPATH**/ ?>