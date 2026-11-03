<?php

use App\Http\Controllers\AdminPortalController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CashierPortalController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\RegistrarPortalController;
use App\Http\Controllers\StudentPortalController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| 1. Public & Guest Routes
|--------------------------------------------------------------------------
*/
Route::get('/', [PublicController::class, 'home'])->name('home');
Route::get('/track-application', [PublicController::class, 'trackApplication'])->name('public.track');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/demo-login', [AuthController::class, 'quickDemoLogin'])->name('login.demo');

Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');

/*
|--------------------------------------------------------------------------
| 2. Protected DMS File Streaming Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::post('/account/change-password', [AuthController::class, 'changePassword'])
        ->name('account.password.update');
    Route::get('/documents/enrollment/{document}', [DocumentController::class, 'streamEnrollmentDocument'])
        ->name('documents.enrollment');
    Route::get('/documents/promissory-note/{note}', [DocumentController::class, 'streamPromissoryNoteFile'])
        ->name('documents.promissory');
    Route::get('/documents/payment-proof/{payment}', [DocumentController::class, 'streamPaymentProof'])
        ->name('documents.payment_proof');
    Route::get('/receipt/{payment}', [StudentPortalController::class, 'receipt'])
        ->name('student.receipt');
    Route::post('/account/notifications/read-all', [StudentPortalController::class, 'markAllNotificationsRead'])
        ->name('notifications.read_all');
});

/*
|--------------------------------------------------------------------------
| 3. Student Portal Routes (/student/*)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:student'])->prefix('student')->group(function () {
    Route::get('/dashboard', [StudentPortalController::class, 'dashboard'])->name('student.dashboard');

    Route::get('/enrollment', [StudentPortalController::class, 'enrollment'])->name('student.enrollment');
    Route::post('/enrollment', [StudentPortalController::class, 'submitEnrollment'])->name('student.enrollment.submit');
    Route::post('/enrollment/document', [StudentPortalController::class, 'uploadDocument'])->name('student.enrollment.document');
    Route::post('/enrollment/scheme', [StudentPortalController::class, 'switchScheme'])->name('student.enrollment.scheme');

    Route::get('/billing', [StudentPortalController::class, 'billing'])->name('student.billing');
    Route::get('/billing/pay/{invoice}', [StudentPortalController::class, 'showPayInvoice'])->name('student.billing.pay');
    Route::post('/billing/pay/{invoice}', [StudentPortalController::class, 'processPayment'])->name('student.billing.pay.submit');
    Route::post('/billing/completion-fee', [StudentPortalController::class, 'requestCompletionFee'])->name('student.billing.completion');
    Route::post('/billing/simulate-overdue/{invoice}', [StudentPortalController::class, 'simulateOverdue'])->name('student.billing.simulate_overdue');

    Route::get('/promissory-note', [StudentPortalController::class, 'promissoryNotes'])->name('student.promissory');
    Route::get('/promissory-note/download-template', [StudentPortalController::class, 'downloadPromissoryTemplate'])->name('student.promissory.template');
    Route::post('/promissory-note/upload', [StudentPortalController::class, 'uploadPromissoryNote'])->name('student.promissory.upload');

    Route::get('/exam-clearance', [StudentPortalController::class, 'examClearance'])->name('student.clearance');

    Route::get('/tela-orders', [StudentPortalController::class, 'telaOrders'])->name('student.tela');
    Route::post('/tela-orders', [StudentPortalController::class, 'storeTelaOrder'])->name('student.tela.store');

    Route::post('/notifications/{notification}/read', [StudentPortalController::class, 'markNotificationRead'])->name('student.notifications.read');
});

/*
|--------------------------------------------------------------------------
| 4. Registrar Portal Routes (/registrar/*)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:registrar,super_admin'])->prefix('registrar')->group(function () {
    Route::get('/dashboard', [RegistrarPortalController::class, 'dashboard'])->name('registrar.dashboard');
    Route::get('/applications', [RegistrarPortalController::class, 'applications'])->name('registrar.applications');
    Route::get('/applications/{enrollment}/review', [RegistrarPortalController::class, 'reviewApplication'])->name('registrar.applications.review');
    Route::post('/applications/{enrollment}/approve', [RegistrarPortalController::class, 'approveApplication'])->name('registrar.applications.approve');
    Route::post('/applications/{enrollment}/return', [RegistrarPortalController::class, 'returnApplication'])->name('registrar.applications.return');
    Route::post('/documents/{document}/review', [RegistrarPortalController::class, 'reviewDocument'])->name('registrar.documents.review');

    Route::get('/promissory-notes', [RegistrarPortalController::class, 'promissoryNotes'])->name('registrar.promissory');
    Route::post('/promissory-notes/{note}/approve', [RegistrarPortalController::class, 'approvePromissoryNote'])->name('registrar.promissory.approve');
    Route::post('/promissory-notes/{note}/reject', [RegistrarPortalController::class, 'rejectPromissoryNote'])->name('registrar.promissory.reject');

    Route::get('/students', [RegistrarPortalController::class, 'students'])->name('registrar.students');
});

/*
|--------------------------------------------------------------------------
| 5. Cashier Portal Routes (/cashier/*)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:cashier,super_admin'])->prefix('cashier')->group(function () {
    Route::get('/dashboard', [CashierPortalController::class, 'dashboard'])->name('cashier.dashboard');
    Route::get('/payments', [CashierPortalController::class, 'payments'])->name('cashier.payments');
    Route::post('/payments/{payment}/verify', [CashierPortalController::class, 'verifyPayment'])->name('cashier.payments.verify');
    Route::post('/payments/{payment}/reject', [CashierPortalController::class, 'rejectPayment'])->name('cashier.payments.reject');

    Route::get('/tela-orders', [CashierPortalController::class, 'telaOrders'])->name('cashier.tela');
    Route::post('/tela-orders/{order}/ready', [CashierPortalController::class, 'markTelaReady'])->name('cashier.tela.ready');
    Route::post('/tela-orders/{order}/claimed', [CashierPortalController::class, 'markTelaClaimed'])->name('cashier.tela.claimed');

    Route::get('/receipts', [CashierPortalController::class, 'receipts'])->name('cashier.receipts');
});

/*
|--------------------------------------------------------------------------
| 6. Super Admin Portal Routes (/admin/*)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:super_admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminPortalController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/users', [AdminPortalController::class, 'users'])->name('admin.users');
    Route::post('/users', [AdminPortalController::class, 'storeUser'])->name('admin.users.store');
    Route::post('/users/{user}/role', [AdminPortalController::class, 'updateUserRole'])->name('admin.users.role');
    Route::post('/users/{user}/toggle', [AdminPortalController::class, 'toggleUserStatus'])->name('admin.users.toggle');

    Route::get('/settings/fees', [AdminPortalController::class, 'feeSettings'])->name('admin.settings.fees');
    Route::get('/settings/terms', [AdminPortalController::class, 'feeSettings'])->name('admin.settings.terms');
    Route::post('/settings/fees', [AdminPortalController::class, 'updateFeeSettings'])->name('admin.settings.fees.update');

    Route::get('/audit-logs', [AdminPortalController::class, 'auditLogs'])->name('admin.audit');
});
