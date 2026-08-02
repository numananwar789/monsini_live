<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductArchiveController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderAllocationController;
use App\Http\Controllers\OrderAllocationCancelController;
use App\Http\Controllers\OrderCancelController;
use App\Http\Controllers\OrderFinalController;
use App\Http\Controllers\OrderFinalCancelController;
use App\Http\Controllers\OrderHistoryController;
use App\Http\Controllers\OrderHistoryArchiveController;
use App\Http\Controllers\EmailBodyController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\CustOrderController;
use App\Http\Controllers\CustProductController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CustomPasswordResetController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\SubProductController;
use App\Http\Controllers\MigrationController;

Route::get('/logout', function () {
    Auth::logout();
    return redirect()->route('login')->with('status', 'You have been logged out.');
})->name('logout.get');
Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/register-customer', [RegisterController::class, 'showRegistrationForm'])->name('register.customer.get');
Route::post('/register-customer', [RegisterController::class, 'register'])->name('register.customer.post');
Route::get('/migrate-customers-to-users', [MigrationController::class, 'migrateCustomers']);

// Custom OTP Password Reset Routes
Route::get('/custom-password-reset', [CustomPasswordResetController::class, 'showRequestForm'])
    ->name('custom.password.request');

Route::post('/custom-password-email', [CustomPasswordResetController::class, 'sendResetEmail'])
    ->name('custom.password.email');

Route::get('/custom-password-otp', [CustomPasswordResetController::class, 'showOtpForm'])
    ->name('custom.password.otp');

Route::post('/custom-password-verify', [CustomPasswordResetController::class, 'verifyOtp'])
    ->name('custom.password.verify');

Route::post('/custom-password-reset', [CustomPasswordResetController::class, 'resetPassword'])
    ->name('custom.password.update');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/admin-products', [ProductController::class, 'index'])->name('admin-products');
    Route::get('/admin-products/download', [ProductController::class, 'download'])->name('admin-products.download');
    Route::post('/admin-products/action', [ProductController::class, 'action'])->name('admin-products.action');
    Route::post('/admin-products/import', [ProductController::class, 'import'])->name('admin-products.import');
    Route::post('/products/archive', [ProductController::class, 'archive'])->name('products.archive');
    Route::post('/products/update-color', [ProductController::class, 'updateColor'])->name('products.update.color');
    Route::get('/products/datatable', [ProductController::class, 'getProductsData'])->name('admin-products.datatable');
    Route::resource('products', ProductController::class);
    Route::post('/admin-products/toggle-inventory', [ProductController::class, 'toggleInventory'])->name('admin-products.toggle-inventory');
    Route::post('/admin-products/toggle-year', [ProductController::class, 'toggleYear'])->name('admin-products.toggle-year');

    Route::resource('users', UserController::class);
    Route::resource('product-archives', ProductArchiveController::class)->only(['index']);
    Route::post('product-archives/restore', [ProductArchiveController::class, 'restore'])->name('product-archives.restore');
    Route::post('product-archives/{product}/update-status', [ProductArchiveController::class, 'updateStatus'])->name('product-archives.update-status');
    Route::resource('customers', CustomerController::class);
    Route::resource('vendors', VendorController::class);

    Route::get('/orders/datatable', [OrderController::class, 'getOrdersData'])->name('orders.datatable');
    Route::get('/orders/flagged', [OrderController::class, 'getFlaggedOrders'])->name('orders.flagged');
    Route::resource('orders', OrderController::class);
    Route::get('/orders/export/pending', [OrderController::class, 'exportPending'])->name('orders.export.pending');
    Route::get('/orders-refresh', [OrderController::class, 'refresh'])->name('orders.refresh');
    Route::get('/orders-clear-all', [OrderController::class, 'clearAll'])->name('orders.clear-all');

    Route::resource('order-allocations', OrderAllocationController::class);

    // Additional custom routes
    Route::post('order-allocations-confirm-to-customer', [OrderAllocationController::class, 'confirmToCustomer'])->name('order-allocations.confirm-to-customer');
    Route::post('order-allocations-bulk-allocate', [OrderAllocationController::class, 'bulkAllocate'])->name('order-allocations.bulk-allocate');
    Route::post('order-allocations/bulk-stage', [OrderAllocationController::class, 'bulkStage'])->name('order-allocations.bulk-stage');
    Route::post('order-allocations-bulk-unstage', [OrderAllocationController::class, 'bulkUnstage'])->name('order-allocations.bulk-unstage');
    Route::post('order-allocations-allocate-single', [OrderAllocationController::class, 'allocateSingle'])->name('order-allocations.allocate-single');
    Route::get('/order-allocations-download', [OrderAllocationController::class, 'downloadAllocation'])->name('order-allocations.download');
    Route::post('/order-allocations-clear', [OrderAllocationController::class, 'clearAllOrders'])->name('order-allocations.clear');
    Route::post('/order-allocations-cancel', [OrderAllocationController::class, 'cancelOrders'])->name('order-allocations.cancel');
    Route::get('order-allocation/{id}/toggle-staging', [OrderAllocationController::class, 'toggleStaging'])->name('order-allocation.toggle-staging');
    Route::get('order-allocation/{id}/delete', [OrderAllocationController::class, 'deleteAllocated'])->name('order-allocation.delete');
    Route::get('order-allocation/{id}/delete-full', [OrderAllocationController::class, 'deleteFullOrder'])->name('order-allocation.delete-full');
    Route::get('/order-allocations-show/{id}/show', [OrderAllocationController::class, 'show'])->name('order-allocations.show');
    Route::post('/order-allocations-allocate/{id}/allocate', [OrderAllocationController::class, 'allocate'])->name('order-allocations.allocate');
    Route::resource('order-allocation-cancels', OrderAllocationCancelController::class);

    Route::resource('sub-products', SubProductController::class);
    Route::resource('order-final-cancels', OrderFinalCancelController::class);

    Route::get('/cancelled-orders', [OrderCancelController::class, 'index'])->name('cancelled-orders');
    Route::post('/cancelled-orders/restore', [OrderCancelController::class, 'restore'])->name('cancelled-orders.restore');

    Route::resource('order-history-archives', OrderHistoryArchiveController::class)->only(['index']);
    Route::post('order-history-archives/restore', [OrderHistoryArchiveController::class, 'restore'])->name('order-history-archives.restore');

    Route::get('email-templates', [EmailBodyController::class, 'index'])->name('email-templates.index');
    Route::post('email-templates', [EmailBodyController::class, 'update'])->name('email-templates.update');

    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::resource('order-histories', OrderHistoryController::class);
    Route::post('/order-histories/archive', [OrderHistoryController::class, 'archive'])->name('order-histories.archive');
    Route::post('/order-histories/import', [OrderHistoryController::class, 'import'])->name('order-histories.import');

    Route::post('customers/{customer}/approve', [CustomerController::class, 'approve'])->name('customers.approve');
    Route::post('/customers/import', [CustomerController::class, 'import'])->name('customers.import');

    Route::resource('inventories', InventoryController::class);
    Route::post('/inventory/edit-quantity', [InventoryController::class, 'editQuantity'])->name('inventory.edit-quantity');
    Route::post('/inventory/update-quantity', [InventoryController::class, 'updateQuantity'])->name('inventory.update-quantity');
    Route::post('/inventory/delete', [InventoryController::class, 'delete'])->name('inventory.delete');
    Route::post('/inventory/get-colors', [InventoryController::class, 'getColors'])->name('inventory.get-colors');
    Route::post('/inventory/get-sizes', [InventoryController::class, 'getSizes'])->name('inventory.get-sizes');
    Route::post('/inventory/get-colors2', [InventoryController::class, 'getColors'])->name('inventory.get-colors2');
    Route::get('/inventory/get-products/{style}', [InventoryController::class, 'getSubProducts'])->name('inventory.get-products');
    Route::get('/refresh-final-orders', [OrderFinalController::class, 'refresh'])->name('final-orders.refresh');

    Route::resource('/final-orders', OrderFinalController::class);

    Route::post('/admin/final-orders/confirm-customer', [OrderFinalController::class, 'confirmCustomer'])->name('final-orders.confirm-customer');
    Route::post('/admin/final-orders/confirm-vendor', [OrderFinalController::class, 'confirmVendor'])->name('final-orders.confirm-vendor');
    Route::post('/admin/final-orders/bypass', [OrderFinalController::class, 'bypass'])->name('final-orders.bypass');
    Route::post('/admin/final-orders/cancel', [OrderFinalController::class, 'cancel'])->name('final-orders.cancel');

    Route::get('/admin/order-finals/download', [OrderFinalController::class, 'downloadFinalOrders'])->name('order-finals.download');
    Route::get('/admin/order-finals/delete/{id}', [OrderFinalController::class, 'deleteFinalOrder'])->name('order-finals.delete-id');
    Route::post('/admin/order-finals/cancel', [OrderFinalController::class, 'cancelOrders'])->name('order-finals.cancel');
    Route::post('/admin/order-finals/clear', [OrderFinalController::class, 'clearAllOrders'])->name('order-finals.clear');

    Route::get('/admin/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/admin/orders/delete/{id}', [OrderController::class, 'deleteOrder'])->name('orders.delete-id');
    Route::post('/admin/orders/accept', [OrderController::class, 'accept'])->name('orders.accept');
    Route::post('/admin/orders/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
    Route::post('/admin/orders/import', [OrderController::class, 'import'])->name('orders.import');

    Route::get('/admin/orders/create', [OrderController::class, 'create'])->name('orders.create');
    Route::post('/admin/orders', [OrderController::class, 'store'])->name('orders.store');
    Route::get('/admin/orders/get-colors/{style}', [OrderController::class, 'getColors'])->name('orders.get-colors');
    Route::get('/admin/orders/get-sizes/{style}', [OrderController::class, 'getSizes'])->name('orders.get-sizes');
    Route::get('/admin/orders/get-cost/{style}', [OrderController::class, 'getCost'])->name('orders.get-cost');

    Route::post('/get-color', [OrderController::class, 'getColor'])->name('get.color');
    Route::post('/get-cost', [OrderController::class, 'getCost2'])->name('get.cost');
    Route::post('/get-size', [OrderController::class, 'getSize'])->name('get.size');
    Route::post('/get-product-price', [ProductController::class, 'getWholesalePrice'])->name('get.product.price');
});

Route::prefix('admin')->middleware(['auth'])->group(function () {
    Route::post('products/batch', [ProductController::class, 'batchAction'])->name('admin.products.batch');
    Route::post('products/variant-status', [ProductController::class, 'updateVariantStatus'])->name('admin.products.variant.status');
    Route::post('products/import', [ProductController::class, 'import'])->name('admin.products.import');
    Route::get('products/export', [ProductController::class, 'export'])->name('admin.products.export');
    Route::post('products/archive', [ProductController::class, 'archive'])->name('admin.products.archive');
});


Route::prefix('customer')->middleware(['auth'])->group(function () {

    Route::get('/products', [CustProductController::class, 'index'])->name('customer.products.index');
    Route::post('/products', [CustProductController::class, 'store'])->name('customer.products.store');
    Route::post('/products/popup', [CustProductController::class, 'popup'])->name('customer.products.popup');

    Route::get('/history', [HistoryController::class, 'index'])->name('customer.history');
    Route::delete('/history/{order}', [HistoryController::class, 'destroy'])->name('customer.history.destroy');

    Route::get('/orders', [CustOrderController::class, 'index'])->name('customer.orders.index');
    Route::delete('/orders', [CustOrderController::class, 'destroy'])->name('customer.orders.destroy');
    Route::get('/orders/{order}/edit', [CustOrderController::class, 'edit'])->name('customer.orders.edit');
    Route::put('/orders/{order}', [CustOrderController::class, 'update'])->name('customer.orders.update');
});
Route::get('/backup/download', [BackupController::class, 'downloadDatabaseBackup'])->middleware('auth');
Route::get('/backup/download2', [BackupController::class, 'backup'])->middleware('auth');


require __DIR__ . '/auth.php';
