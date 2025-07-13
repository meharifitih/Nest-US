<?php
    $profile = asset(Storage::url('upload/profile/'));
?>
<?php $__env->startSection('page-title'); ?>
    <?php if(\Auth::user()->type == 'super admin'): ?>
        <?php echo e(__('Customer')); ?>

    <?php else: ?>
        <?php echo e(__('User')); ?>

    <?php endif; ?>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item">
        <a href="<?php echo e(route('dashboard')); ?>"><?php echo e(__('Dashboard')); ?></a>
    </li>
    <li class="breadcrumb-item" aria-current="page">
        <?php if(\Auth::user()->type == 'super admin'): ?>
            <?php echo e(__('Customers')); ?>

        <?php else: ?>
            <?php echo e(__('Users')); ?>

        <?php endif; ?>
    </li>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
    <div class="row">
        <div class="col-sm-12">


            <div class="card table-card">
                <div class="card-header">
                    <div class="row align-items-center g-2">
                        <div class="col">
                            <h5> <?php if(\Auth::user()->type == 'super admin'): ?>
                                <?php echo e(__('Customer List')); ?>

                            <?php else: ?>
                                <?php echo e(__('User List')); ?>

                            <?php endif; ?></h5>
                        </div>
                        <div class="col-auto d-flex gap-2">
                            <button type="button" class="btn btn-primary px-3" data-bs-toggle="modal" data-bs-target="#filterModal">
                                <i class="ti ti-filter me-1"></i> <?php echo e(__('Filter')); ?>

                            </button>
                            <?php if(Gate::check('create user')): ?>
                                <a href="#" class="btn btn-secondary customModal" data-size="lg"
                                    data-url="<?php echo e(route('users.create')); ?>" data-title="<?php echo e(__('Create User')); ?>"> <i
                                        class="ti ti-circle-plus align-text-bottom"></i> <?php echo e(__('Create User')); ?></a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <!-- Filter Modal -->
                    <div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="filterModalLabel"><?php echo e(__('Filter Users')); ?></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form method="GET" action="<?php echo e(route('users.index')); ?>">
                                    <div class="modal-body">
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <?php echo e(Form::label('name', __('Name'), ['class' => 'form-label'])); ?>

                                                    <?php echo e(Form::text('name', request('name'), ['class' => 'form-control', 'placeholder' => __('Enter name')])); ?>

                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <?php echo e(Form::label('email', __('Email'), ['class' => 'form-label'])); ?>

                                                    <?php echo e(Form::text('email', request('email'), ['class' => 'form-control', 'placeholder' => __('Enter email')])); ?>

                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <?php echo e(Form::label('approval_status', __('Approval Status'), ['class' => 'form-label'])); ?>

                                                    <select name="approval_status" class="form-select">
                                                        <option value=""><?php echo e(__('All')); ?></option>
                                                        <option value="approved" <?php echo e(request('approval_status') == 'approved' ? 'selected' : ''); ?>><?php echo e(__('Approved')); ?></option>
                                                        <option value="pending" <?php echo e(request('approval_status') == 'pending' ? 'selected' : ''); ?>><?php echo e(__('Pending')); ?></option>
                                                        <option value="rejected" <?php echo e(request('approval_status') == 'rejected' ? 'selected' : ''); ?>><?php echo e(__('Rejected')); ?></option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <?php echo e(Form::label('active_package', __('Active Package'), ['class' => 'form-label'])); ?>

                                                    <?php echo e(Form::text('active_package', request('active_package'), ['class' => 'form-control', 'placeholder' => __('Enter package name')])); ?>

                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <?php echo e(Form::label('package_due_date', __('Package Due Date'), ['class' => 'form-label'])); ?>

                                                    <?php echo e(Form::date('package_due_date', request('package_due_date'), ['class' => 'form-control'])); ?>

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-primary px-4"><?php echo e(__('Apply Filter')); ?></button>
                                        <a href="<?php echo e(route('users.index')); ?>" class="btn btn-light px-4"><?php echo e(__('Reset')); ?></a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="dt-responsive table-responsive">
                        <table class="table table-hover advance-datatable">
                            <thead>
                                <tr>
                                    <th><?php echo e(__('User')); ?></th>
                                    <th><?php echo e(__('Email')); ?></th>
                                    <th><?php echo e(__('Active Package')); ?></th>
                                    <th><?php echo e(__('Package Due Date')); ?></th>
                                    <th><?php echo e(__('Approval Status')); ?></th>
                                    <th><?php echo e(__('Action')); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr class="clickable-customer-row" data-href="<?php echo e(route('users.show', $user->id)); ?>">
                                        <td class="table-user">
                                            <img src="<?php echo e(!empty($user->avatar) ? asset(Storage::url('upload/profile')) . '/' . $user->avatar : asset(Storage::url('upload/profile')) . '/avatar.png'); ?>"
                                                alt="" class="mr-2 avatar-sm rounded-circle user-avatar">
                                            <a href="#" class="text-body font-weight-semibold" onclick="event.stopPropagation();"><?php echo e($user->name); ?></a>
                                        </td>
                                        <td><?php echo e($user->email); ?> </td>
                                        <?php if(\Auth::user()->type == 'super admin'): ?>
                                            <td><?php echo e(!empty($user->subscriptions) ? $user->subscriptions->title : '-'); ?></td>
                                            <td><?php echo e(!empty($user->subscription_expire_date) ? dateFormat($user->subscription_expire_date) : __('Unlimited')); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo e($user->approval_status === 'approved' ? 'success' : ($user->approval_status === 'rejected' ? 'danger' : 'warning')); ?>">
                                                    <?php echo e(ucfirst($user->approval_status)); ?>

                                                </span>
                                            </td>
                                        <?php else: ?>
                                            <td>-</td>
                                            <td>-</td>
                                            <td>-</td>
                                        <?php endif; ?>
                                        <td>
                                            <div class="cart-action">
                                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('show user')): ?>
                                                    <a class="avtar avtar-xs btn-link-warning text-warning" data-bs-toggle="tooltip"
                                                        data-bs-original-title="<?php echo e(__('Show')); ?>"
                                                        href="<?php echo e(route('users.show', $user->id)); ?>"
                                                        data-title="<?php echo e(__('Edit User')); ?>" onclick="event.stopPropagation();"> <i data-feather="eye"></i></a>
                                                <?php endif; ?>
                                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit user')): ?>
                                                    <a class="avtar avtar-xs btn-link-secondary text-secondary" data-bs-toggle="tooltip"
                                                        data-size="lg" data-bs-original-title="<?php echo e(__('Edit')); ?>"
                                                        href="<?php echo e(route('users.edit', $user->id)); ?>"
                                                        data-title="<?php echo e(__('Edit User')); ?>" onclick="event.stopPropagation();"> <i data-feather="edit"></i></a>
                                                <?php endif; ?>
                                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete user')): ?>
                                                    <?php echo Form::open(['method' => 'DELETE', 'route' => ['users.destroy', $user->id], 'class' => 'd-inline']); ?>

                                                        <button type="submit" class="avtar avtar-xs btn-link-danger text-danger confirm_dialog" data-bs-toggle="tooltip"
                                                            data-bs-original-title="<?php echo e(__('Delete')); ?>" onclick="event.stopPropagation();">
                                                            <i data-feather="trash-2"></i>
                                                        </button>
                                                    <?php echo Form::close(); ?>

                                                <?php endif; ?>
                                                <?php if(Auth::user()->canImpersonate()): ?>
                                                    <a class="avtar avtar-xs btn-link-info text-info" data-bs-toggle="tooltip"
                                                        data-bs-original-title="<?php echo e(__('Continue as Customer')); ?>"
                                                        href="<?php echo e(route('impersonate', $user->id)); ?>" target="_blank" onclick="event.stopPropagation();"> <i
                                                            data-feather="log-in"></i></a>
                                                <?php endif; ?>
                                                <?php if(\Auth::user()->type == 'super admin' && $user->type === 'owner' && $user->approval_status === 'pending'): ?>
                                                    <form action="<?php echo e(route('users.approve', $user->id)); ?>" method="POST" class="d-inline" onsubmit="event.stopPropagation();">
                                                        <?php echo csrf_field(); ?>
                                                        <button type="submit" class="avtar avtar-xs btn-link-success text-success" data-bs-toggle="tooltip"
                                                            data-bs-original-title="<?php echo e(__('Approve')); ?>">
                                                            <i data-feather="check"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                                <?php if(\Auth::user()->type == 'super admin' && $user->approval_status === 'rejected'): ?>
                                                    <form action="<?php echo e(route('users.reapprove', $user->id)); ?>" method="POST" class="d-inline" onsubmit="event.stopPropagation();">
                                                        <?php echo csrf_field(); ?>
                                                        <button type="submit" class="avtar avtar-xs btn-link-success text-success" data-bs-toggle="tooltip"
                                                            data-bs-original-title="<?php echo e(__('Re-approve')); ?>">
                                                            <i data-feather="refresh-cw"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                                <?php if(\Auth::user()->type == 'super admin' && $user->type === 'owner' && $user->approval_status === 'pending'): ?>
                                                    <form action="<?php echo e(route('users.reject', $user->id)); ?>" method="POST" class="d-inline" onsubmit="event.stopPropagation();">
                                                        <?php echo csrf_field(); ?>
                                                        <button type="submit" class="avtar avtar-xs btn-link-danger text-danger" data-bs-toggle="tooltip"
                                                            data-bs-original-title="<?php echo e(__('Reject')); ?>">
                                                            <i data-feather="x"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4 d-flex justify-content-center">
                        <?php echo e($users->links('vendor.pagination.bootstrap-5')); ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('script-page'); ?>
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/chipchip/Downloads/codecanyon-ytuZNl0y-smart-tenant-property-management-system-saas/main_file/resources/views/user/index.blade.php ENDPATH**/ ?>