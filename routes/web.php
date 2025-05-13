<?php

use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\AuthPageController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\NoticeBoardController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\FAQController;
use App\Http\Controllers\HomePageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OTPController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PaymentController;
use App\Models\User;

use App\Http\Controllers\PropertyController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\TypeController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\InvoicePaymentController;
use App\Http\Controllers\MaintainerController;
use App\Http\Controllers\MaintenanceRequestController;
use App\Http\Controllers\AccountReviewController;
use App\Http\Controllers\AdminController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

require __DIR__ . '/auth.php';

Route::get('/', [HomeController::class,'index'])->middleware(
    [

        'XSS',
    ]
);
Route::get('home', [HomeController::class,'index'])->name('home')->middleware(
    [

        'XSS',
    ]
);
Route::get('dashboard', [HomeController::class,'index'])->name('dashboard')->middleware(
    [

        'XSS',
    ]
);

//-------------------------------User-------------------------------------------

Route::resource('users', UserController::class)->middleware(
    [
        'auth',
        'XSS',
    ]
);


Route::get('login/otp', [OTPController::class, 'show'])->name('otp.show')->middleware(
    [

        'XSS',
    ]
);
Route::post('login/otp', [OTPController::class, 'check'])->name('otp.check')->middleware(
    [

        'XSS',
    ]
);
Route::get('login/2fa/disable', [OTPController::class, 'disable'])->name('2fa.disable')->middleware(['XSS',]);

//-------------------------------Subscription-------------------------------------------

Route::group(
    [
        'middleware' => [
            'auth',
            'XSS',
        ],
    ], function (){

    Route::resource('subscriptions', SubscriptionController::class);
    Route::post('subscriptions/{id}/subscribe', [SubscriptionController::class, 'subscribe'])->name('subscriptions.subscribe');
    Route::get('coupons/history', [CouponController::class,'history'])->name('coupons.history');
    Route::delete('coupons/history/{id}/destroy', [CouponController::class,'historyDestroy'])->name('coupons.history.destroy');
    Route::get('coupons/apply', [CouponController::class, 'apply'])->name('coupons.apply');
    Route::resource('coupons', CouponController::class);
    Route::get('subscription/transaction', [SubscriptionController::class,'transaction'])->name('subscription.transaction');
}
);

//-------------------------------Subscription Payment-------------------------------------------

Route::group(
    [
        'middleware' => [
            'auth',
            'XSS',
        ],
    ], function (){

    Route::post('subscription/{id}/stripe/payment', [SubscriptionController::class,'stripePayment'])->name('subscription.stripe.payment');
}
);
//-------------------------------Settings-------------------------------------------
Route::group(
    [
        'middleware' => [
            'auth',
            'XSS',
        ],
    ], function (){
    Route::get('settings', [SettingController::class,'index'])->name('setting.index');

    Route::post('settings/account', [SettingController::class,'accountData'])->name('setting.account');
    Route::delete('settings/account/delete', [SettingController::class,'accountDelete'])->name('setting.account.delete');
    Route::post('settings/password', [SettingController::class,'passwordData'])->name('setting.password');
    Route::post('settings/general', [SettingController::class,'generalData'])->name('setting.general');
    Route::post('settings/smtp', [SettingController::class,'smtpData'])->name('setting.smtp');
    Route::get('settings/smtp-test', [SettingController::class, 'smtpTest'])->name('setting.smtp.test');
    Route::post('settings/smtp-test', [SettingController::class, 'smtpTestMailSend'])->name('setting.smtp.testing');
    Route::post('settings/payment', [SettingController::class,'paymentData'])->name('setting.payment');
    Route::post('settings/site-seo', [SettingController::class,'siteSEOData'])->name('setting.site.seo');
    Route::post('settings/google-recaptcha', [SettingController::class,'googleRecaptchaData'])->name('setting.google.recaptcha');
    Route::post('settings/company', [SettingController::class,'companyData'])->name('setting.company');
    Route::post('settings/2fa', [SettingController::class, 'twofaEnable'])->name('setting.twofa.enable');
    Route::post('/settings/tutorial-videos', [\App\Http\Controllers\SettingController::class, 'saveTutorialVideos'])->name('setting.tutorial_videos');

    Route::get('footer-setting', [SettingController::class, 'footerSetting'])->name('footerSetting');
    Route::post('settings/footer', [SettingController::class,'footerData'])->name('setting.footer');

    Route::get('language/{lang}', [SettingController::class,'lanquageChange'])->name('language.change');
    Route::post('theme/settings', [SettingController::class,'themeSettings'])->name('theme.settings');
}
);


//-------------------------------Role & Permissions-------------------------------------------
Route::resource('permission', PermissionController::class)->middleware(
    [
        'auth',
        'XSS',
    ]
);

Route::resource('role', RoleController::class)->middleware(
    [
        'auth',
        'XSS',
    ]
);

//-------------------------------Note-------------------------------------------
Route::resource('note', NoticeBoardController::class)->middleware(
    [
        'auth',
        'XSS',
    ]
);

//-------------------------------Contact-------------------------------------------
Route::resource('contact', ContactController::class)->middleware(
    [
        'auth',
        'XSS',
    ]
);

//-------------------------------logged History-------------------------------------------

Route::group(
    [
        'middleware' => [
            'auth',
            'XSS',
        ],
    ], function () {

    Route::get('logged/history', [UserController::class,'loggedHistory'])->name('logged.history');
    Route::get('logged/{id}/history/show', [UserController::class,'loggedHistoryShow'])->name('logged.history.show');
    Route::delete('logged/{id}/history', [UserController::class,'loggedHistoryDestroy'])->name('logged.history.destroy');
});


//-------------------------------Plan Payment-------------------------------------------
Route::group(
    [
        'middleware' => [
            'auth',
            'XSS',
        ],
    ], function (){
    Route::post('subscription/{id}/bank-transfer', [PaymentController::class, 'subscriptionBankTransfer'])->name('subscription.bank.transfer');
    Route::get('subscription/{id}/bank-transfer/action/{status}', [PaymentController::class, 'subscriptionBankTransferAction'])->name('subscription.bank.transfer.action');
    Route::post('subscription/{id}/paypal', [PaymentController::class, 'subscriptionPaypal'])->name('subscription.paypal');
    Route::get('subscription/{id}/paypal/{status}', [PaymentController::class, 'subscriptionPaypalStatus'])->name('subscription.paypal.status');
    Route::post('subscription/{id}/{user_id}/manual-assign-package', [PaymentController::class, 'subscriptionManualAssignPackage'])->name('subscription.manual_assign_package');
}
);

//-------------------------------Notification-------------------------------------------
Route::resource('notification', NotificationController::class)->middleware(
    [
        'auth',
        'XSS',

    ]
 );

 Route::get('email-verification/{token}', [VerifyEmailController::class, 'verifyEmail'])->name('email-verification')->middleware(
    [
        'XSS',
    ]
);

//-------------------------------FAQ-------------------------------------------
Route::resource('FAQ', FAQController::class)->middleware(
    [
        'auth',
        'XSS',
    ]
);

//-------------------------------Home Page-------------------------------------------
Route::resource('homepage', HomePageController::class)->middleware(
    [
        'auth',
        'XSS',
    ]
);
//-------------------------------FAQ-------------------------------------------
Route::resource('pages', PageController::class)->middleware(
    [
        'auth',
        'XSS',
    ]
);

//-------------------------------Auth page-------------------------------------------
Route::resource('authPage', AuthPageController::class)->middleware(
    [
        'auth',
        'XSS',
    ]
);

// Property Excel Upload Routes
Route::post('property/{property}/upload-tenant-excel', [PropertyController::class, 'uploadTenantExcel'])->name('property.upload.tenant.excel');
Route::get('property/{property}/tenant-excel-uploads', [PropertyController::class, 'getTenantExcelUploads'])->name('property.tenant.excel.uploads');
Route::get('property/tenant-excel-template', [PropertyController::class, 'downloadTenantExcelTemplate'])->name('property.tenant.excel.template');

//-------------------------------Property-------------------------------------------
Route::group(
    [
        'middleware' => [
            'auth',
            'XSS',
        ],
    ],
    function () {
        Route::resource('property', PropertyController::class);
        Route::get('property/{pid}/unit/create', [PropertyController::class, 'unitCreate'])->name('unit.create');
        Route::post('property/{pid}/unit/store', [PropertyController::class, 'unitStore'])->name('unit.store');
        Route::get('units/direct-create', [PropertyController::class, 'unitdirectCreate'])->name('unit.direct-create');
        Route::post('unit/direct-store', [PropertyController::class, 'unitdirectStore'])->name('unit.direct-store');
        Route::get('property/{pid}/unit/{id}/edit', [PropertyController::class, 'unitEdit'])->name('unit.edit');
        Route::get('units', [PropertyController::class, 'units'])->name('unit.index');
        Route::put('property/{pid}/unit/{id}/update', [PropertyController::class, 'unitUpdate'])->name('unit.update');
        Route::delete('property/{pid}/unit/{id}/destroy', [PropertyController::class, 'unitDestroy'])->name('unit.destroy');
        Route::get('property/{pid}/unit', [PropertyController::class, 'getPropertyUnit'])->name('property.unit');
    }
);

//-------------------------------Tenant-------------------------------------------
Route::resource('tenant', TenantController::class)->middleware(
    [
        'auth',
        'XSS',
    ]
);

//-------------------------------Type-------------------------------------------
Route::resource('type', TypeController::class)->middleware(
    [
        'auth',
        'XSS',
    ]
);

//-------------------------------Invoice-------------------------------------------

Route::group(
    [
        'middleware' => [
            'auth',
            'XSS',
        ],
    ],
    function () {
        Route::get('invoice/{id}/payment/create', [InvoiceController::class, 'invoicePaymentCreate'])->name('invoice.payment.create');
        Route::post('invoice/{id}/payment/store', [InvoiceController::class, 'invoicePaymentStore'])->name('invoice.payment.store');
        Route::delete('invoice/{id}/payment/{pid}/destroy', [InvoiceController::class, 'invoicePaymentDestroy'])->name('invoice.payment.destroy');
        Route::delete('invoice/type/destroy', [InvoiceController::class, 'invoiceTypeDestroy'])->name('invoice.type.destroy');
        Route::get('invoice/{id}/reminder', [InvoiceController::class, 'invoicePaymentRemind'])->name('invoice.reminder');
        Route::post('invoice/{id}/reminder', [InvoiceController::class, 'invoicePaymentRemindData'])->name('invoice.sendEmail');
        Route::resource('invoice', InvoiceController::class);
    }
);

//-------------------------------Expense-------------------------------------------
Route::resource('expense', ExpenseController::class)->middleware(
    [
        'auth',
        'XSS',
    ]
);

//-------------------------------Maintainer-------------------------------------------
Route::resource('maintainer', MaintainerController::class)->middleware(
    [
        'auth',
        'XSS',
    ]
);

//-------------------------------Maintenance Request-------------------------------------------


Route::group(
    [
        'middleware' => [
            'auth',
            'XSS',
        ],
    ],
    function () {
        Route::get('maintenance-request/pending', [MaintenanceRequestController::class, 'pendingRequest'])->name('maintenance-request.pending');
        Route::get('maintenance-request/in-progress', [MaintenanceRequestController::class, 'inProgressRequest'])->name('maintenance-request.inprogress');
        Route::get('maintenance-request/{id}/action', [MaintenanceRequestController::class, 'action'])->name('maintenance-request.action');
        Route::post('maintenance-request/{id}/action', [MaintenanceRequestController::class, 'actionData'])->name('maintenance-request.action');
        Route::resource('maintenance-request', MaintenanceRequestController::class);
    }
);

//-------------------------------Plan Payment-------------------------------------------

Route::group(
    [
        'middleware' => [
            'auth',
            'XSS',
        ],
    ],
    function () {
        Route::post('subscription/{id}/bank-transfer', [PaymentController::class, 'subscriptionBankTransfer'])->name('subscription.bank.transfer');
        Route::get('subscription/{id}/bank-transfer/action/{status}', [PaymentController::class, 'subscriptionBankTransferAction'])->name('subscription.bank.transfer.action');
        Route::post('subscription/{id}/paypal', [PaymentController::class, 'subscriptionPaypal'])->name('subscription.paypal');
        Route::get('subscription/{id}/paypal/{status}', [PaymentController::class, 'subscriptionPaypalStatus'])->name('subscription.paypal.status');
        Route::get('subscription/flutterwave/{sid}/{tx_ref}', [PaymentController::class, 'subscriptionFlutterwave'])->name('subscription.flutterwave');
    }
);

//-------------------------------Invoice Payment-------------------------------------------

Route::group(
    [
        'middleware' => [
            'auth',
            'XSS',
        ],
    ],
    function () {

        Route::post('invoice/{id}/banktransfer/payment', [InvoicePaymentController::class, 'banktransferPayment'])->name('invoice.banktransfer.payment');
        Route::post('invoice/{id}/stripe/payment', [InvoicePaymentController::class, 'stripePayment'])->name('invoice.stripe.payment');
        Route::post('invoice/{id}/paypal', [InvoicePaymentController::class, 'invoicePaypal'])->name('invoice.paypal');
        Route::get('invoice/{id}/paypal/{status}', [InvoicePaymentController::class, 'invoicePaypalStatus'])->name('invoice.paypal.status');
        Route::get('invoice/flutterwave/{id}/{tx_ref}', [InvoicePaymentController::class, 'invoiceFlutterwave'])->name('invoice.flutterwave');
    }
);


Route::get('page/{slug}', [PageController::class, 'page'])->name('page');
//-------------------------------FAQ-------------------------------------------
Route::impersonate();

// Account Review Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/account/review', [AccountReviewController::class, 'showReviewPage'])->name('account.review');
});

// Admin Approval Routes
Route::middleware(['auth'])->group(function () {
    Route::post('/users/{id}/approve', [AccountReviewController::class, 'approveUser'])->name('users.approve');
    Route::post('/users/{id}/reject', [AccountReviewController::class, 'rejectUser'])->name('users.reject');
});

// Payment Verification Routes
Route::middleware(['auth'])->group(function () {
    Route::get('payment-verification', 'PaymentVerificationController@index')->name('payment.verification.index');
    Route::post('payment-verification/upload', 'PaymentVerificationController@uploadScreenshot')->name('payment.verification.upload');
    Route::get('payment-verification/approve/{id}', 'PaymentVerificationController@approve')->name('payment.verification.approve');
    Route::post('payment-verification/reject/{id}', 'PaymentVerificationController@reject')->name('payment.verification.reject');
});

// Admin routes for managing payments
Route::get('/admin/payments/pending', [AdminController::class, 'showPendingPayments'])->name('admin.payments.pending');
Route::post('/admin/payments/approve/{id}', [AdminController::class, 'approvePayment'])->name('admin.payments.approve');
Route::post('/admin/payments/reject/{id}', [AdminController::class, 'rejectPayment'])->name('admin.payments.reject');
