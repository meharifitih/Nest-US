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
                    <h4><?php echo e(__('Total Property')); ?></h4>
                </div>
                <div class="card-body progressCounter">
                    <h2>
                        <span class="count"><?php echo e(count($maintainer->properties())); ?></span>
                    </h2>
                </div>
            </div>
        </div>
        <div class="col-xxl-3 col-sm-6 cdx-xxl-50">
            <div class="card sale-revenue">
                <div class="card-header">
                    <h4><?php echo e(__('Total Request')); ?></h4>
                </div>
                <div class="card-body progressCounter">
                    <h2>
                        <span class="count"><?php echo e($result['totalRequest']); ?></span>
                    </h2>
                </div>
            </div>
        </div>
        <div class="col-xxl-3 col-sm-6 cdx-xxl-50">
            <div class="card sale-revenue">
                <div class="card-header">
                    <h4><?php echo e(__('Today Request')); ?></h4>
                </div>
                <div class="card-body progressCounter">
                    <h2>
                        <span class="count"><?php echo e($result['todayRequest']); ?></span>
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
                    <h2>
                        <span class="count"><?php echo e($result['totalNote']); ?></span>
                    </h2>
                </div>
            </div>
        </div>
        <div class="col-xxl-3 col-sm-6 cdx-xxl-50">
            <div class="card sale-revenue">
                <div class="card-header">
                    <h4><?php echo e(__('Property')); ?></h4>
                </div>
                <div class="card-body progressCounter">
                    <h5>
                        <span class="">
                            <?php $__currentLoopData = $maintainer->properties(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $property): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php echo e($property->name); ?><br>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></span>
                    </h5>
                </div>
            </div>
        </div>
    </div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/chipchip/Downloads/codecanyon-ytuZNl0y-smart-tenant-property-management-system-saas/main_file/resources/views/dashboard/maintainer.blade.php ENDPATH**/ ?>