<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Maintenance Request')); ?>

<?php $__env->stopSection(); ?>
<?php $__env->startPush('script-page'); ?>
<?php $__env->stopPush(); ?>


<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>"><?php echo e(__('Dashboard')); ?></a></li>
    <li class="breadcrumb-item" aria-current="page"> <?php echo e(__('Maintenance Request')); ?></li>
<?php $__env->stopSection(); ?>


<?php $__env->startSection('card-action-btn'); ?>
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create maintenance request')): ?>
        <a class="btn btn-secondary btn-sm ml-20 customModal" href="#" data-size="lg"
            data-url="<?php echo e(route('maintenance-request.create')); ?>" data-title="<?php echo e(__('Create Maintenance Request')); ?>"> <i
                class="ti-plus mr-5"></i><?php echo e(__('Create Maintenance Request')); ?></a>
    <?php endif; ?>
<?php $__env->stopSection(); ?>


<?php $__env->startSection('content'); ?>
    <div class="row">
        <div class="col-sm-12">
            <div class="card table-card">
                <div class="card-header">
                    <div class="row align-items-center g-2">
                        <div class="col">
                            <h5><?php echo e(__('Maintenance Request List')); ?></h5>
                        </div>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create maintenance request')): ?>
                            <div class="col-auto">
                                <a href="#" class="btn btn-secondary customModal" data-size="lg"
                                    data-url="<?php echo e(route('maintenance-request.create')); ?>" data-title="<?php echo e(__('Create Maintenance Request')); ?>"> <i
                                        class="ti ti-circle-plus align-text-bottom"></i> <?php echo e(__('Create Maintenance Request')); ?></a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="dt-responsive table-responsive">
                        <table class="table table-hover advance-datatable">
                            <thead>
                                <tr>
                                    <th><?php echo e(__('Property')); ?></th>
                                    <th><?php echo e(__('Unit')); ?></th>
                                    <th><?php echo e(__('Issue')); ?></th>
                                    <th><?php echo e(__('Maintainer')); ?></th>
                                    <th><?php echo e(__('Request Date')); ?></th>
                                    <th><?php echo e(__('Status')); ?></th>
                                    <th><?php echo e(__('Attachment')); ?></th>
                                    <?php if(Gate::check('edit maintenance request') ||
                                            Gate::check('delete maintenance request') ||
                                            Gate::check('show maintenance request')): ?>
                                        <th class="text-right"><?php echo e(__('Action')); ?></th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $maintenanceRequests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $request): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr role="row">
                                        <td> <?php echo e(!empty($request->properties) ? $request->properties->name : '-'); ?> </td>
                                        <td> <?php echo e(!empty($request->units) ? $request->units->name : '-'); ?> </td>
                                        <td> <?php echo e(!empty($request->types) ? $request->types->title : '-'); ?> </td>
                                        <td> <?php echo e(!empty($request->maintainers) ? $request->maintainers->name : '-'); ?> </td>
                                        <td> <?php echo e(dateFormat($request->request_date)); ?> </td>
                                        <td>
                                            <?php if($request->status == 'pending'): ?>
                                                <span class="badge bg-light-warning">
                                                    <?php echo e(\App\Models\MaintenanceRequest::$status[$request->status]); ?></span>
                                            <?php elseif($request->status == 'in_progress'): ?>
                                                <span class="badge bg-light-info">
                                                    <?php echo e(\App\Models\MaintenanceRequest::$status[$request->status]); ?></span>
                                            <?php else: ?>
                                                <span class="badge bg-light-success">
                                                    <?php echo e(\App\Models\MaintenanceRequest::$status[$request->status]); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if(!empty($request->issue_attachment)): ?>
                                                <a href="<?php echo e(asset(Storage::url('upload/issue_attachment')) . '/' . $request->issue_attachment); ?>"
                                                    download="download"><i class="ti ti-download"></i></a>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <?php if(Gate::check('edit maintenance request') ||
                                                Gate::check('delete maintenance request') ||
                                                Gate::check('show maintenance request')): ?>
                                            <td class="text-right">
                                                <div class="cart-action">
                                                    <?php echo Form::open(['method' => 'DELETE', 'route' => ['maintenance-request.destroy', $request->id]]); ?>

                                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('show maintenance request')): ?>
                                                        <a class="avtar avtar-xs btn-link-warning text-warning customModal" data-size="lg"
                                                            data-bs-toggle="tooltip"
                                                            data-bs-original-title="<?php echo e(__('View')); ?>" href="#"
                                                            data-url="<?php echo e(route('maintenance-request.show', $request->id)); ?>"
                                                            data-title="<?php echo e(__('Maintenance Request Details')); ?>"> <i
                                                                data-feather="eye"></i></a>
                                                    <?php endif; ?>
                                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit maintenance request')): ?>
                                                        <a class="avtar avtar-xs btn-link-secondary text-secondary customModal" data-size="lg"
                                                            data-bs-toggle="tooltip"
                                                            data-bs-original-title="<?php echo e(__('Edit')); ?>" href="#"
                                                            data-url="<?php echo e(route('maintenance-request.edit', $request->id)); ?>"
                                                            data-title="<?php echo e(__('Maintenance Request')); ?>"> <i
                                                                data-feather="edit"></i></a>
                                                    <?php endif; ?>
                                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete maintenance request')): ?>
                                                        <a class="avtar avtar-xs btn-link-danger text-danger confirm_dialog" data-bs-toggle="tooltip"
                                                            data-bs-original-title="<?php echo e(__('Detete')); ?>" href="#"> <i
                                                                data-feather="trash-2"></i></a>
                                                    <?php endif; ?>
                                                    <?php if(\Auth::user()->type == 'maintainer'): ?>
                                                        <a class="avtar avtar-xs btn-link-secondary text-secondary customModal" data-size="lg"
                                                            data-bs-toggle="tooltip"
                                                            data-bs-original-title="<?php echo e(__('Status Update')); ?>"
                                                            href="#"
                                                            data-url="<?php echo e(route('maintenance-request.action', $request->id)); ?>"
                                                            data-title="<?php echo e(__('Maintenance Request Status')); ?>"> <i
                                                                data-feather="check-square"></i></a>
                                                    <?php endif; ?>
                                                    <?php echo Form::close(); ?>

                                                </div>

                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/chipchip/Downloads/codecanyon-ytuZNl0y-smart-tenant-property-management-system-saas/main_file/resources/views/maintenance_request/index.blade.php ENDPATH**/ ?>