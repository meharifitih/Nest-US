<?php
    $settings = settings();
?>
<?php $__env->startSection('tab-title'); ?>
    <?php echo e(__('Register')); ?>

<?php $__env->stopSection(); ?>
<?php $__env->startPush('script-page'); ?>
    <?php if($settings['google_recaptcha'] == 'on'): ?>
        <?php echo NoCaptcha::renderJs(); ?>

    <?php endif; ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const phoneInput = document.getElementById('phone_number');
            const phoneError = document.getElementById('phone_error');
            
            phoneInput.addEventListener('input', function(e) {
                let value = e.target.value;
                // Remove any non-digit characters
                value = value.replace(/\D/g, '');
                
                // Check if number starts with 9 (Ethio Telecom) or 7 (Safaricom)
                if (value.length > 0) {
                    const firstDigit = value.charAt(0);
                    if (firstDigit !== '9' && firstDigit !== '7') {
                        phoneError.textContent = 'Phone number must start with 9 (Ethio Telecom) or 7 (Safaricom)';
                        phoneError.style.display = 'block';
                    } else {
                        phoneError.style.display = 'none';
                    }
                }
                
                // Limit to 9 digits
                if (value.length > 9) {
                    value = value.slice(0, 9);
                }
                
                e.target.value = value;
            });
        });
    </script>
<?php $__env->stopPush(); ?>
<?php $__env->startSection('content'); ?>
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="d-flex justify-content-center">
                    <div class="auth-header">
                        <h2 class="text-secondary"><b><?php echo e(__('Sign up')); ?> </b></h2>
                        <p class="f-16 mt-2"><?php echo e(__('Enter your details and create account')); ?></p>
                    </div>
                </div>
            </div>

            <?php echo e(Form::open(['route' => 'register', 'method' => 'post', 'id' => 'register-Form'])); ?>

            <?php if(session('error')): ?>
                <div class="alert alert-danger" role="alert"><?php echo e(session('error')); ?></div>
            <?php endif; ?>
            <?php if(session('success')): ?>
                <div class="alert alert-success" role="alert"><?php echo e(session('success')); ?></div>
            <?php endif; ?>
            <div class="form-floating mb-3">
                <input type="text" class="form-control" id="name" name="name"
                    placeholder="<?php echo e(__('Name')); ?>" value="<?php echo e(old('name')); ?>" required />
                <label for="name"><?php echo e(__('Name')); ?></label>
                <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <span class="invalid-name text-danger" role="alert">
                        <strong><?php echo e($message); ?></strong>
                    </span>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
            <input type="hidden" name="type" value="owner" />
            <div class="form-floating mb-3">
                <input type="email" class="form-control" id="email" name="email"
                    placeholder="<?php echo e(__('Email address')); ?>" value="<?php echo e(old('email')); ?>" required />
                <label for="email"><?php echo e(__('Email address')); ?></label>
                <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <span class="invalid-email text-danger" role="alert">
                        <strong><?php echo e($message); ?></strong>
                    </span>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
            <div class="form-floating mb-3">
                <div class="input-group">
                    <span class="input-group-text">+251</span>
                    <input type="text" class="form-control" id="phone_number" name="phone_number"
                        placeholder="Phone Number (e.g., 912345678)" value="<?php echo e(old('phone_number')); ?>" required />
                </div>
                <small class="text-muted">Enter number starting with 9 (Ethio Telecom) or 7 (Safaricom)</small>
                <span id="phone_error" class="text-danger" style="display: none;"></span>
                <?php $__errorArgs = ['phone_number'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <span class="invalid-phone_number text-danger" role="alert">
                        <strong><?php echo e($message); ?></strong>
                    </span>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
            <div class="form-floating mb-3">
                <input type="password" class="form-control" id="password" name="password"
                    placeholder="<?php echo e(__('Password')); ?>" required />
                <label for="password"><?php echo e(__('Password')); ?></label>
                <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <span class="invalid-password text-danger" role="alert">
                        <strong><?php echo e($message); ?></strong>
                    </span>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
            <div class="form-floating mb-3">
                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation"
                    placeholder="<?php echo e(__('Confirm Password')); ?>" required />
                <label for="password_confirmation"><?php echo e(__('Confirm Password')); ?></label>
                <?php $__errorArgs = ['password_confirmation'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <span class="invalid-password_confirmation text-danger" role="alert">
                        <strong><?php echo e($message); ?></strong>
                    </span>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
            <div class="form-check mt-3">
                <input class="form-check-input input-primary" type="checkbox" id="agree" name="agree" required />
                <label class="form-check-label" for="agree">
                    <span class="h5 mb-0">
                        <?php echo e(__('Agree with')); ?>

                        <span><a
                                href="<?php echo e(!empty($menu->slug) ? route('page', $menu->slug) : '#'); ?>"><?php echo e(__('Terms and conditions')); ?></a>.</span>
                    </span>
                </label>
            </div>
            <?php if($settings['google_recaptcha'] == 'on'): ?>
                <div class="form-group">
                    <label for="email" class="form-label"></label>
                    <?php echo NoCaptcha::display(); ?>

                    <?php $__errorArgs = ['g-recaptcha-response'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <span class="small text-danger" role="alert">
                            <strong><?php echo e($message); ?></strong>
                        </span>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
            <?php endif; ?>
            <div class="d-grid mt-4">
                <button type="submit" class="btn btn-secondary p-2"><?php echo e(__('Sign Up')); ?></button>
            </div>
            <hr />
            <h5 class="d-flex justify-content-center"><?php echo e(__('Already have an account?')); ?> <a class="ms-1 text-secondary"
                    href="<?php echo e(route('login')); ?>"><?php echo e(__('Login in here')); ?></a>
            </h5>
            <?php echo e(Form::close()); ?>

        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.auth', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/chipchip/Downloads/codecanyon-ytuZNl0y-smart-tenant-property-management-system-saas/main_file/resources/views/auth/register.blade.php ENDPATH**/ ?>