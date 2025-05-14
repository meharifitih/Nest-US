<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Tenant')); ?>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>"><?php echo e(__('Dashboard')); ?></a></li>
    <li class="breadcrumb-item" aria-current="page"> <?php echo e(__('Tenant')); ?></li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center g-2">
                        <div class="col">
                            <h5><?php echo e(__('Tenant List')); ?></h5>
                        </div>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create tenant')): ?>
                            <div class="col-auto">
                                <a class="btn btn-secondary" href="<?php echo e(route('tenant.create')); ?>" data-size="md"> <i
                                        class="ti ti-circle-plus align-text-bottom"></i> <?php echo e(__('Create Tenant')); ?></a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <?php $__currentLoopData = $tenants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tenant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="col-xxl-3 col-xl-4 col-md-6">
                                <div class="card follower-card">
                                    <div class="card-body p-3">
                                        <div class="d-flex align-items-start mb-3">
                                            <div class="flex-grow-1 ms-3 mx-2">
                                                <img class="img-fluid wid-70"
                                                    src="<?php echo e(!empty($tenant->user) && !empty($tenant->user->profile) ? asset(Storage::url('upload/profile/' . $tenant->user->profile)) : asset(Storage::url('upload/profile/avatar.png'))); ?>"
                                                    alt="">
                                            </div>
                                            <?php if(Gate::check('edit tenant') || Gate::check('delete tenant') || Gate::check('show tenant')): ?>
                                                <div class="flex-shrink-0">
                                                    <div class="dropdown">
                                                        <a class="dropdown-toggle text-primary opacity-50 arrow-none"
                                                            href="#" data-bs-toggle="dropdown" aria-haspopup="true"
                                                            aria-expanded="false">
                                                            <i class="ti ti-dots f-16"></i>
                                                        </a>
                                                        <div class="dropdown-menu dropdown-menu-end">
                                                            <a class="dropdown-item"
                                                                href="<?php echo e(route('tenant.edit', $tenant->id)); ?>">
                                                                <i class="material-icons-two-tone">edit</i>
                                                                <?php echo e(__('Edit Tenant')); ?>

                                                            </a>

                                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('show tenant')): ?>
                                                                <a class="dropdown-item"
                                                                    href="<?php echo e(route('tenant.show', $tenant->id)); ?>">
                                                                    <i class="material-icons-two-tone">remove_red_eye</i>
                                                                    <?php echo e(__('View Tenant')); ?>

                                                                </a>
                                                            <?php endif; ?>
                                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete tenant')): ?>
                                                                <?php echo Form::open([
                                                                    'method' => 'DELETE',
                                                                    'route' => ['tenant.destroy', $tenant->id],
                                                                    'id' => 'tenant-' . $tenant->id,
                                                                ]); ?>

                                                                <a class="dropdown-item confirm_dialog" href="#">
                                                                    <i class="material-icons-two-tone">delete</i>
                                                                    <?php echo e(__('Delete Tenant')); ?>

                                                                </a>
                                                                <?php echo Form::close(); ?>

                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <a href="<?php echo e(route('tenant.show', $tenant->id)); ?>">
                                            <h4><?php echo e(ucfirst(!empty($tenant->user) ? $tenant->user->first_name : '') . ' ' . ucfirst(!empty($tenant->user) ? $tenant->user->last_name : '')); ?>

                                            </h4>

                                        </a>
                                        <h6 class="text-truncate text-muted d-flex align-items-center mb-2">
                                            <?php echo e(!empty($tenant->user) ? $tenant->user->email : '-'); ?></h6>

                                        <div class="row">
                                            <div class="col-sm-12 mb-2">
                                                
                                                <p class="text-muted"><?php echo e($tenant->address); ?></p>
                                            </div>

                                            <div class="col-sm-6 mb-3">
                                                <p class="mb-0 text-muted text-sm"><?php echo e(__('Phone')); ?> :</p>
                                                <h6 class="mb-0">
                                                    <?php echo e(!empty($tenant->user) ? $tenant->user->phone_number : '-'); ?></h6>
                                            </div>
                                            <div class="col-sm-6 mb-3">
                                                <p class="mb-0 text-muted text-sm"><?php echo e(__('Family Member')); ?> :</p>
                                                <h6 class="mb-0"><?php echo e(!empty($tenant->family_member) ? $tenant->family_member : '-'); ?></h6>
                                            </div>
                                            <div class="col-sm-6 mb-3">
                                                <p class="mb-0 text-muted text-sm"><?php echo e(__('Property')); ?> :</p>
                                                <h6 class="mb-0">
                                                    <?php echo e(!empty($tenant->properties) ? $tenant->properties->name : '-'); ?>

                                                </h6>
                                            </div>
                                            <div class="col-sm-6 mb-3">
                                                <p class="mb-0 text-muted text-sm"><?php echo e(__('Unit')); ?> :</p>
                                                <h6 class="mb-0">
                                                    <?php echo e(!empty($tenant->units) ? $tenant->units->name : '-'); ?>

                                                </h6>
                                            </div>
                                            <div class="col-sm-6 mb-3">
                                                <p class="mb-0 text-muted text-sm"><?php echo e(__('Lease Start Date')); ?> :</p>
                                                <h6 class="mb-0"><?php echo e(dateFormat($tenant->lease_start_date)); ?></h6>
                                            </div>
                                            <div class="col-sm-6 mb-3">
                                                <p class="mb-0 text-muted text-sm"><?php echo e(__('Lease End Date')); ?> :</p>
                                                <h6 class="mb-0"><?php echo e(dateFormat($tenant->lease_end_date)); ?></h6>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </div>

        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/chipchip/Downloads/codecanyon-ytuZNl0y-smart-tenant-property-management-system-saas/main_file/resources/views/tenant/index.blade.php ENDPATH**/ ?>