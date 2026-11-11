<?php

use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\QuotationController;
use App\Http\Controllers\Admin\ServiceCategoryController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\AssetController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ExpenseController;
use App\Http\Controllers\Admin\GlobalSearchController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\PayrollController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;

// Auth Routes
Route::middleware('guest')->group(function () {
    Route::get('/',       [LoginController::class, 'showLoginForm']);
    Route::get('/login',  [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post')->middleware('throttle:10,1');

    Route::get('/forgot-password',         [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/forgot-password',        [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/reset-password/{token}',  [ForgotPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password',         [ForgotPasswordController::class, 'reset'])->name('password.update');
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Admin Routes
Route::prefix('admin')->name('admin.')->middleware(['auth', 'log.activity'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/global-search', [GlobalSearchController::class, 'index'])->name('global-search');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::post('/notifications/clear-read', [NotificationController::class, 'clearRead'])->name('notifications.clear-read');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::middleware('permission:reports.view')->group(function () {
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export/csv', [ReportController::class, 'exportCsv'])->name('reports.export.csv');
        Route::get('/reports/export/pdf', [ReportController::class, 'exportPdf'])->name('reports.export.pdf');
    });

    // User Profile
    Route::get('/profile',           [UserController::class, 'profile'])->name('profile');
    Route::post('/profile/update',   [UserController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/password', [UserController::class, 'changePassword'])->name('profile.password');

    // User Management
    Route::middleware('permission:users.view')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
    });
    Route::middleware('permission:users.create')->group(function () {
        Route::get('/users/create',  [UserController::class, 'create'])->name('users.create');
        Route::post('/users',        [UserController::class, 'store'])->name('users.store');
    });
    Route::middleware('permission:users.view')->group(function () {
        Route::get('/users/{user}',  [UserController::class, 'show'])->name('users.show');
    });
    Route::middleware('permission:users.edit')->group(function () {
        Route::get('/users/{user}/edit',            [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}',                 [UserController::class, 'update'])->name('users.update');
        Route::post('/users/{user}/status',         [UserController::class, 'updateStatus'])->name('users.status');
        Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
        Route::post('/users/{user}/force-logout',   [UserController::class, 'forceLogout'])->name('users.force-logout');
    });
    Route::middleware('permission:users.delete')->group(function () {
        Route::delete('/users/{user}',  [UserController::class, 'destroy'])->name('users.destroy');
        Route::post('/users/bulk-action', [UserController::class, 'bulkAction'])->name('users.bulk-action');
    });

    // Role Management
    Route::middleware('permission:roles.view')->group(function () {
        Route::get('/roles',             [RoleController::class, 'index'])->name('roles.index');
    });
    Route::middleware('permission:roles.create')->group(function () {
        Route::get('/roles/create',      [RoleController::class, 'create'])->name('roles.create');
        Route::post('/roles',            [RoleController::class, 'store'])->name('roles.store');
    });
    Route::middleware('permission:roles.edit')->group(function () {
        Route::get('/roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
        Route::put('/roles/{role}',      [RoleController::class, 'update'])->name('roles.update');
    });
    Route::middleware('permission:roles.delete')->group(function () {
        Route::delete('/roles/{role}',   [RoleController::class, 'destroy'])->name('roles.destroy');
    });

    // Quotation Module
    Route::middleware('permission:quotations.create')->group(function () {
        Route::get('/quotations/create',              [QuotationController::class, 'create'])->name('quotations.create');
        Route::post('/quotations',                    [QuotationController::class, 'store'])->name('quotations.store');
    });
    Route::middleware('permission:quotations.view')->group(function () {
        Route::get('/quotations',                     [QuotationController::class, 'index'])->name('quotations.index');
        Route::get('/quotations/{quotation}',         [QuotationController::class, 'show'])->name('quotations.show');
        Route::get('/quotations/{quotation}/print',   [QuotationController::class, 'print'])->name('quotations.print');
        Route::get('/quotations/{quotation}/pdf',     [QuotationController::class, 'pdfDownload'])->name('quotations.pdf');
    });
    Route::middleware('permission:quotations.edit')->group(function () {
        Route::get('/quotations/{quotation}/edit',    [QuotationController::class, 'edit'])->name('quotations.edit');
        Route::put('/quotations/{quotation}',         [QuotationController::class, 'update'])->name('quotations.update');
        Route::post('/quotations/{quotation}/status', [QuotationController::class, 'updateStatus'])->name('quotations.status');
        Route::post('/quotations/{quotation}/duplicate', [QuotationController::class, 'duplicate'])->name('quotations.duplicate');
        Route::post('/quotations/{quotation}/send', [QuotationController::class, 'send'])->name('quotations.send');
        Route::post('/quotations/{quotation}/send-email', [QuotationController::class, 'sendEmail'])->name('quotations.send-email');
        Route::post('/quotations/{quotation}/convert-invoice', [QuotationController::class, 'convertInvoice'])->name('quotations.convert-invoice');
    });
    Route::middleware('permission:quotations.delete')->group(function () {
        Route::delete('/quotations/{quotation}',      [QuotationController::class, 'destroy'])->name('quotations.destroy');
    });

    // Invoice Module
    Route::middleware('permission:invoices.create')->group(function () {
        Route::get('/invoices/create',              [InvoiceController::class, 'create'])->name('invoices.create');
        Route::get('/invoices/quotation-data/{quotation}', [InvoiceController::class, 'quotationData'])->name('invoices.quotation-data');
        Route::post('/invoices',                    [InvoiceController::class, 'store'])->name('invoices.store');
    });
    Route::middleware('permission:invoices.view')->group(function () {
        Route::get('/invoices',                     [InvoiceController::class, 'index'])->name('invoices.index');
        Route::get('/invoices/{invoice}',           [InvoiceController::class, 'show'])->name('invoices.show');
    });
    Route::middleware('permission:invoices.edit')->group(function () {
        Route::get('/invoices/{invoice}/edit',      [InvoiceController::class, 'edit'])->name('invoices.edit');
        Route::put('/invoices/{invoice}',           [InvoiceController::class, 'update'])->name('invoices.update');
        Route::post('/invoices/{invoice}/status',   [InvoiceController::class, 'updateStatus'])->name('invoices.status');
        Route::post('/invoices/{invoice}/duplicate', [InvoiceController::class, 'duplicate'])->name('invoices.duplicate');
        Route::post('/invoices/{invoice}/send',     [InvoiceController::class, 'send'])->name('invoices.send');
        Route::post('/invoices/{invoice}/send-email', [InvoiceController::class, 'sendEmail'])->name('invoices.send-email');
    });
    Route::middleware('permission:invoices.view')->group(function () {
        Route::get('/invoices/{invoice}/print',     [InvoiceController::class, 'print'])->name('invoices.print');
        Route::get('/invoices/{invoice}/pdf',       [InvoiceController::class, 'pdfDownload'])->name('invoices.pdf');
    });
    Route::middleware('permission:invoices.delete')->group(function () {
        Route::delete('/invoices/{invoice}',        [InvoiceController::class, 'destroy'])->name('invoices.destroy');
    });

    // Transaction Module
    Route::middleware('permission:transactions.create')->group(function () {
        Route::get('/transactions/create',              [TransactionController::class, 'create'])->name('transactions.create');
        Route::post('/transactions',                    [TransactionController::class, 'store'])->name('transactions.store');
    });
    Route::middleware('permission:transactions.view')->group(function () {
        Route::get('/transactions',                     [TransactionController::class, 'index'])->name('transactions.index');
        Route::get('/transactions/customer-ledger',     [TransactionController::class, 'customerLedger'])->name('transactions.customer-ledger');
        Route::get('/transactions/{transaction}',       [TransactionController::class, 'show'])->name('transactions.show');
        Route::get('/transactions/{transaction}/receipt', [TransactionController::class, 'receipt'])->name('transactions.receipt');
    });
    Route::middleware('permission:transactions.edit')->group(function () {
        Route::get('/transactions/{transaction}/edit',  [TransactionController::class, 'edit'])->name('transactions.edit');
        Route::put('/transactions/{transaction}',       [TransactionController::class, 'update'])->name('transactions.update');
        Route::post('/transactions/{transaction}/status', [TransactionController::class, 'updateStatus'])->name('transactions.status');
        Route::post('/transactions/{transaction}/send-receipt', [TransactionController::class, 'sendReceipt'])->name('transactions.send-receipt');
    });
    Route::middleware('permission:transactions.delete')->group(function () {
        Route::delete('/transactions/{transaction}',    [TransactionController::class, 'destroy'])->name('transactions.destroy');
    });

    // Expense Module
    Route::middleware('permission:expenses.create')->group(function () {
        Route::get('/expenses/create',                  [ExpenseController::class, 'create'])->name('expenses.create');
        Route::post('/expenses',                        [ExpenseController::class, 'store'])->name('expenses.store');
    });
    Route::middleware('permission:expenses.view')->group(function () {
        Route::get('/expenses',                         [ExpenseController::class, 'index'])->name('expenses.index');
        Route::get('/expenses/{expense}',               [ExpenseController::class, 'show'])->name('expenses.show');
    });
    Route::middleware('permission:expenses.edit')->group(function () {
        Route::get('/expenses/{expense}/edit',          [ExpenseController::class, 'edit'])->name('expenses.edit');
        Route::put('/expenses/{expense}',               [ExpenseController::class, 'update'])->name('expenses.update');
        Route::post('/expenses/{expense}/status',       [ExpenseController::class, 'updateStatus'])->name('expenses.status');
    });
    Route::middleware('permission:expenses.delete')->group(function () {
        Route::delete('/expenses/{expense}',            [ExpenseController::class, 'destroy'])->name('expenses.destroy');
    });

    // Asset Management
    Route::middleware('permission:assets.view')->group(function () {
        Route::get('/assets',                            [AssetController::class, 'index'])->name('assets.index');
        Route::get('/asset-assignments',                 [AssetController::class, 'assignments'])->name('asset-assignments.index');
        Route::get('/asset-history',                     [AssetController::class, 'history'])->name('assets.history');
        Route::get('/asset-assignment-groups/{assignmentGroup}', [AssetController::class, 'showAssignmentGroup'])->name('asset-assignment-groups.show');
        Route::get('/assets/{assetItem}/edit-data',      [AssetController::class, 'editData'])->name('assets.edit-data');
    });
    Route::middleware('permission:assets.create')->group(function () {
        Route::get('/assets/create',                    [AssetController::class, 'create'])->name('assets.create');
        Route::post('/assets',                           [AssetController::class, 'store'])->name('assets.store');
        Route::post('/assets/employees',                 [AssetController::class, 'storeEmployee'])->name('assets.employees.store');
    });
    Route::middleware('permission:assets.edit')->group(function () {
        Route::get('/asset-assignments/create',         [AssetController::class, 'createAssignment'])->name('asset-assignments.create');
        Route::put('/assets/{assetItem}',                [AssetController::class, 'update'])->name('assets.update');
        Route::post('/asset-assignments',                [AssetController::class, 'storeAssignmentBatch'])->name('asset-assignments.store');
        Route::post('/asset-assignments/{assetAssignment}/return', [AssetController::class, 'returnAssignment'])->name('asset-assignments.return');
        Route::post('/asset-assignment-groups/{assignmentGroup}/return', [AssetController::class, 'returnAssignmentGroup'])->name('asset-assignment-groups.return');
        Route::post('/assets/{assetItem}/movement',      [AssetController::class, 'movement'])->name('assets.movement');
    });
    Route::middleware('permission:assets.delete')->group(function () {
        Route::delete('/assets/{assetItem}',             [AssetController::class, 'destroy'])->name('assets.destroy');
    });

    // Staff & Employee Management
    Route::middleware('permission:employees.view')->group(function () {
        Route::get('/employees',                   [EmployeeController::class, 'index'])->name('employees.index');
        Route::get('/employees/{employee}/rate',    [EmployeeController::class, 'getRate'])->name('employees.rate');
    });
    Route::middleware('permission:employees.create')->group(function () {
        Route::get('/employees/create',            [EmployeeController::class, 'create'])->name('employees.create');
        Route::post('/employees',                  [EmployeeController::class, 'store'])->name('employees.store');
    });
    Route::middleware('permission:employees.edit')->group(function () {
        Route::get('/employees/{employee}/edit',   [EmployeeController::class, 'edit'])->name('employees.edit');
        Route::put('/employees/{employee}',        [EmployeeController::class, 'update'])->name('employees.update');
    });
    Route::middleware('permission:employees.delete')->group(function () {
        Route::delete('/employees/{employee}',     [EmployeeController::class, 'destroy'])->name('employees.destroy');
    });

    // Attendance & Daily Punching / Overtime
    Route::middleware('permission:attendance.view')->group(function () {
        Route::get('/attendance',                  [AttendanceController::class, 'index'])->name('attendance.index');
        Route::get('/attendance/period-data',      [AttendanceController::class, 'getPeriodData'])->name('attendance.period-data');
    });
    Route::middleware('permission:attendance.create')->group(function () {
        Route::post('/attendance',                 [AttendanceController::class, 'store'])->name('attendance.store');
    });
    Route::middleware('permission:attendance.edit')->group(function () {
        Route::put('/attendance/{attendance}',     [AttendanceController::class, 'update'])->name('attendance.update');
    });
    Route::middleware('permission:attendance.delete')->group(function () {
        Route::delete('/attendance/{attendance}',  [AttendanceController::class, 'destroy'])->name('attendance.destroy');
    });

    // Weekly & Monthly Payroll Processing
    Route::middleware('permission:payroll.create')->group(function () {
        Route::get('/payrolls/create',             [PayrollController::class, 'create'])->name('payrolls.create');
        Route::post('/payrolls',                   [PayrollController::class, 'store'])->name('payrolls.store');
    });
    Route::middleware('permission:payroll.view')->group(function () {
        Route::get('/payrolls',                    [PayrollController::class, 'index'])->name('payrolls.index');
        Route::get('/payrolls/{payroll}',          [PayrollController::class, 'show'])->name('payrolls.show');
        Route::get('/payrolls/{payroll}/print',    [PayrollController::class, 'print'])->name('payrolls.print');
        Route::get('/payrolls/{payroll}/pdf',      [PayrollController::class, 'pdfDownload'])->name('payrolls.pdf');
    });
    Route::middleware('permission:payroll.edit')->group(function () {
        Route::get('/payrolls/{payroll}/edit',     [PayrollController::class, 'edit'])->name('payrolls.edit');
        Route::put('/payrolls/{payroll}',          [PayrollController::class, 'update'])->name('payrolls.update');
        Route::post('/payrolls/{payroll}/status',  [PayrollController::class, 'updateStatus'])->name('payrolls.status');
    });
    Route::middleware('permission:payroll.delete')->group(function () {
        Route::delete('/payrolls/{payroll}',       [PayrollController::class, 'destroy'])->name('payrolls.destroy');
    });

    // Service Categories
    Route::middleware('permission:services.view')->group(function () {
        Route::get('/service-categories', [ServiceCategoryController::class, 'index'])->name('service-categories.index');
    });
    Route::middleware('permission:services.create')->group(function () {
        Route::post('/service-categories', [ServiceCategoryController::class, 'store'])->name('service-categories.store');
    });
    Route::middleware('permission:services.edit')->group(function () {
        Route::put('/service-categories/{serviceCategory}', [ServiceCategoryController::class, 'update'])->name('service-categories.update');
    });
    Route::middleware('permission:services.delete')->group(function () {
        Route::delete('/service-categories/{serviceCategory}', [ServiceCategoryController::class, 'destroy'])->name('service-categories.destroy');
    });

    // Service Master
    Route::middleware('permission:services.view')->group(function () {
        Route::get('/services',              [ServiceController::class, 'index'])->name('services.index');
        Route::get('/services/search',       [ServiceController::class, 'search'])->name('services.search');
        Route::get('/services/{service}/edit-data', [ServiceController::class, 'editData'])->name('services.edit-data');
    });
    Route::middleware('permission:services.create')->group(function () {
        Route::post('/services',        [ServiceController::class, 'store'])->name('services.store');
    });
    Route::middleware('permission:services.edit')->group(function () {
        Route::put('/services/{service}',        [ServiceController::class, 'update'])->name('services.update');
        Route::post('/services/{service}/toggle',[ServiceController::class, 'toggleStatus'])->name('services.toggle');
    });
    Route::middleware('permission:services.delete')->group(function () {
        Route::delete('/services/{service}',     [ServiceController::class, 'destroy'])->name('services.destroy');
    });

    // Customer Management
    Route::middleware('permission:customers.create')->group(function () {
        Route::get('/customers/create',       [CustomerController::class, 'create'])->name('customers.create');
        Route::post('/customers',             [CustomerController::class, 'store'])->name('customers.store');

        Route::post('/customers/quick',       [CustomerController::class, 'quickStore'])->name('customers.quick-store');
    });
    Route::middleware('permission:customers.view')->group(function () {
        Route::get('/customers',              [CustomerController::class, 'index'])->name('customers.index');
        Route::get('/customers/{customer}',   [CustomerController::class, 'show'])->name('customers.show');
    });
    Route::middleware('permission:customers.edit')->group(function () {
        Route::get('/customers/{customer}/edit',    [CustomerController::class, 'edit'])->name('customers.edit');
        Route::put('/customers/{customer}',         [CustomerController::class, 'update'])->name('customers.update');
        Route::post('/customers/{customer}/status', [CustomerController::class, 'updateStatus'])->name('customers.status');
    });
    Route::middleware('permission:customers.delete')->group(function () {
        Route::post('/customers/bulk-action',     [CustomerController::class, 'bulkAction'])->name('customers.bulk-action');
        Route::delete('/customers/{customer}',    [CustomerController::class, 'destroy'])->name('customers.destroy');
    });

    // Activity Logs
    Route::middleware('permission:activity_logs.view')->group(function () {
        Route::get('/activity-logs',  [ActivityLogController::class, 'index'])->name('activity-logs.index');
        Route::get('/login-history',  [ActivityLogController::class, 'loginHistory'])->name('login-history.index');
    });

    // Settings
    Route::middleware('permission:settings.view')->group(function () {
        Route::get('/settings',                    [SettingsController::class, 'index'])->name('settings.index');
        Route::get('/settings/{group}',            [SettingsController::class, 'index'])->name('settings.group');
        Route::post('/settings/{group}/save',      [SettingsController::class, 'save'])->name('settings.save');
        Route::post('/settings/image/delete',      [SettingsController::class, 'deleteImage'])->name('settings.image.delete');
        Route::post('/settings/mail/test',         [SettingsController::class, 'testMail'])->name('settings.mail.test');
        Route::post('/settings/quotation-email/test', [SettingsController::class, 'testQuotationEmail'])->name('settings.quotation-email.test');
        Route::post('/settings/notification/test', [SettingsController::class, 'testNotification'])->name('settings.notification.test');
        Route::post('/settings/cache/clear',       [SettingsController::class, 'clearCache'])->name('settings.cache.clear');
        Route::get('/settings/backup/download',    [SettingsController::class, 'backup'])->name('settings.backup');
    });
});
 