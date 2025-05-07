<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Dashboard')); ?>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('breadcrumb'); ?>
    <ul class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="<?php echo e(route('dashboard')); ?>"><?php echo e(__('Dashboard')); ?></a>
        </li>

    </ul>
<?php $__env->stopSection(); ?>
<?php $__env->startPush('script-page'); ?>

<?php $__env->stopPush(); ?>
<?php
$settings=settings();
?>
<?php $__env->startSection('content'); ?>
    <div class="row">
        <div class="col-xxl-3 col-sm-6 cdx-xxl-50">
            <div class="card sale-revenue">
                <div class="card-header">
                    <h4><?php echo e(__('Property')); ?></h4>
                </div>
                <div class="card-body progressCounter">
                    <h2>
                        <span class=""><?php echo e(!empty($tenant->properties)?$tenant->properties->name:'-'); ?></span>
                    </h2>
                </div>
            </div>
        </div>
        <div class="col-xxl-3 col-sm-6 cdx-xxl-50">
            <div class="card sale-revenue">
                <div class="card-header">
                    <h4><?php echo e(__('Unit')); ?></h4>
                </div>
                <div class="card-body progressCounter">
                    <h2>
                        <span class=""><?php echo e(!empty($tenant->units)?$tenant->units->name:'-'); ?></span>
                    </h2>
                </div>
            </div>
        </div>
        <div class="col-xxl-3 col-sm-6 cdx-xxl-50">
            <div class="card sale-revenue">
                <div class="card-header">
                    <h4><?php echo e(__('Rent')); ?></h4>
                </div>
                <div class="card-body progressCounter">
                    <h2>
                        <span class=""><?php echo e($settings['CURRENCY_SYMBOL']); ?><?php echo e(!empty($result['unit'])?$result['unit']->rent:'-'); ?></span>
                    </h2>
                </div>
            </div>
        </div>
        <div class="col-xxl-3 col-sm-6 cdx-xxl-50">
            <div class="card sale-revenue">
                <div class="card-header">
                    <h4><?php echo e(__('Total Invoice')); ?></h4>
                </div>
                <div class="card-body progressCounter">
                    <h2>
                        <span class="count"><?php echo e($result['totalInvoice']); ?></span>
                    </h2>
                </div>
            </div>
        </div>
        <div class="col-xxl-3 col-sm-6 cdx-xxl-50">
            <div class="card sale-revenue">
                <div class="card-header">
                    <h4><?php echo e(__('Total Contact')); ?></h4>
                </div>
                <div class="card-body progressCounter">
                    <h2>
                        <span class="count"><?php echo e($result['totalContact']); ?></span>
                    </h2>
                </div>
            </div>
        </div>

        <div class="col-xxl-3 col-sm-6 cdx-xxl-50">
            <div class="card sale-revenue">
                <div class="card-header">
                    <h4><?php echo e(__('Total Notes')); ?></h4>
                </div>
                <div class="card-body progressCounter">
                    <h2><span class="count"><?php echo e($result['totalNote']); ?></span> </h2>
                </div>
            </div>
        </div>
    </div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/chipchip/Downloads/codecanyon-ytuZNl0y-smart-tenant-property-management-system-saas/main_file/resources/views/dashboard/tenant.blade.php ENDPATH**/ ?>