<?php

use App\Http\Controllers\AccountSettingsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\POSController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductPriceController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\StockInController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DatabaseController;
use App\Http\Controllers\FinancialReportController;
use App\Http\Controllers\InventoryReportController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReturnController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SalesReportController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\StockAdjustmentController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', function () {
    return redirect('/login');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected routes - require login
Route::middleware(['auth.simple'])->group(function () {
    
    // Admin only routes - use the new 'admin' middleware
    Route::middleware(['role:Administrator'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/sales-data', [DashboardController::class, 'getSalesData'])->name('dashboard.sales-data');
        Route::get('/dashboard/sales-chart-data', [DashboardController::class, 'getSalesChartData']);
        
        Route::resource('users', UserController::class);
        Route::resource('categories', CategoryController::class);
        Route::resource('products', ProductController::class);
        Route::resource('suppliers', SupplierController::class);

        Route::post('/users/{user}/archive', [UserController::class, 'archive'])->name('users.archive');
        Route::post('/users/{user}/restore', [UserController::class, 'restore'])->name('users.restore');
        Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');

        Route::post('/suppliers/{supplier}/archive', [SupplierController::class, 'archive'])->name('suppliers.archive');
        Route::post('/suppliers/{supplier}/restore', [SupplierController::class, 'restore'])->name('suppliers.restore');

        Route::post('/products/{product}/archive', [ProductController::class, 'archive'])->name('products.archive');
        Route::post('/products/{product}/restore', [ProductController::class, 'restore'])->name('products.restore');
        Route::get('/products/suggest-sku/{categoryId}', [ProductController::class, 'suggestSku']);
        Route::post('/suppliers/quick-store', [SupplierController::class, 'quickStore'])->name('suppliers.quick-store');
        Route::post('/suppliers/quick-add', [SupplierController::class, 'quickAdd'])->name('suppliers.quick-add');

        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/sales', [SalesReportController::class, 'index'])->name('reports.sales.index');
        Route::get('/reports/sales/export-summary', [SalesReportController::class, 'exportSummaryPDF']);
        Route::get('/reports/sales/export-detailed', [SalesReportController::class, 'exportDetailedCSV']);
        Route::get('/reports/sales/export-payment-methods', [SalesReportController::class, 'exportPaymentMethodsCSV']);

        Route::get('/reports/inventory', [InventoryReportController::class, 'index'])->name('reports.inventory.index');
        // Inventory Report CSV Exports
        Route::get('/reports/inventory/export-low-stock-csv', [InventoryReportController::class, 'exportLowStockCSV']);
        Route::get('/reports/inventory/export-stock-movement-csv', [InventoryReportController::class, 'exportStockMovementCSV']);
        Route::get('/reports/inventory/export-valuation-csv', [InventoryReportController::class, 'exportValuationCSV']);
        Route::get('/reports/inventory/export-adjustments-csv', [InventoryReportController::class, 'exportAdjustmentsCSV']);
        Route::get('/reports/inventory/export-returns-csv', [InventoryReportController::class, 'exportReturnsCSV']);
        Route::get('/reports/inventory/export-best-sellers-csv', [InventoryReportController::class, 'exportBestSellersCSV']);
        Route::get('/reports/inventory/export-dead-stock-csv', [InventoryReportController::class, 'exportDeadStockCSV']);
        Route::get('/reports/inventory/export-stock-levels-csv', [InventoryReportController::class, 'exportStockLevelsCSV']);

        Route::get('/reports/financial', [FinancialReportController::class, 'index'])->name('reports.financial.index');
        Route::get('/reports/financial/export-full', [FinancialReportController::class, 'exportFullReport']);
        Route::get('/reports/financial/export-profit-loss', [FinancialReportController::class, 'exportProfitLossCSV']);
        Route::get('/reports/financial/export-cogs-analysis', [FinancialReportController::class, 'exportCogsAnalysisCSV']);
        Route::get('/reports/financial/export-payment-analysis', [FinancialReportController::class, 'exportPaymentAnalysisCSV']);

        Route::resource('stock-ins', StockInController::class);
        Route::get('/api/suppliers/{supplier}/products', function($supplier) {
            $supplier = App\Models\Supplier::find($supplier);
            if (!$supplier) {
                return response()->json([]);
            }
            
            $products = $supplier->products()->active()->get();
            return response()->json($products);
        });

        Route::resource('stock-adjustments', StockAdjustmentController::class);
        Route::get('stock-adjustments/{id}/show', [StockAdjustmentController::class, 'show'])->name('stock-adjustments.show');
        
        Route::get('/sales', [SaleController::class, 'index'])->name('sales.index');
        Route::get('/sales/export-csv', [SaleController::class, 'exportCsv'])->name('sales.export.csv');
        Route::get('/sales/{id}', [SaleController::class, 'show'])->name('sales.show');
        Route::get('/sales/{id}/receipt', [SaleController::class, 'receipt'])->name('sales.receipt');
        Route::get('/sales/{id}/details', [SaleController::class, 'details'])->name('sales.details');
        
        Route::get('/sales/receipt/print/{id}', [SaleController::class, 'printReceipt'])->name('sales.receipt.print');

        Route::get('/product-prices', [ProductPriceController::class, 'index'])->name('product-prices.index');
        Route::post('/product-prices/update', [ProductPriceController::class, 'update'])->name('product-prices.update');
        Route::get('/api/product-prices/{product}/history', [ProductPriceController::class, 'priceHistory']);

        Route::resource('returns', ReturnController::class);
        Route::get('/returns/get-sale/{saleId}', [ReturnController::class, 'getSaleDetails'])->name('returns.get-sale-details');

        Route::post('/settings/backup', [DatabaseController::class, 'backup'])->name('database.backup');
        Route::post('/settings/restore', [DatabaseController::class, 'restore'])->name('database.restore');
    });

    // Both admin and employee can access these
    Route::get('/pos', [POSController::class, 'index'])->name('pos.index');
    Route::post('/clear-password-modal-flag', function() {
        session()->forget('show_default_password_modal');
        return response()->json(['success' => true]);
    })->name('clear-password-modal-flag');

    // Shared POS functionality routes
    Route::prefix('pos')->group(function () {
        Route::post('/initialize-sale', [POSController::class, 'initializeSale']);
        Route::post('/search-product', [POSController::class, 'searchProduct']);
        Route::post('/add-item', [POSController::class, 'addItem']);
        Route::put('/update-item/{itemId}', [POSController::class, 'updateItem']);
        Route::delete('/remove-item/{itemId}', [POSController::class, 'removeItem']);
        Route::get('/sale-items/{saleId}', [POSController::class, 'getSaleItems']);
        Route::post('/process-payment', [POSController::class, 'processPayment']);
        Route::get('/receipt/{sale}/pdf', [POSController::class, 'downloadReceiptPDF']);
        Route::get('/receipt/{sale}/pdf', [POSController::class, 'downloadReceiptPDF'])->name('pos.receipt.pdf');
        Route::post('/complete-sale', [POSController::class, 'completeSale'])->name('pos.completeSale');
    });

    Route::get('/pos/my-transactions', [POSController::class, 'myTransactions'])->name('pos.my-transactions');
    Route::get('/pos/sale/{id}/details', [POSController::class, 'saleDetails'])->name('pos.sale.details');
    Route::get('/receipt/print/{id}', [POSController::class, 'printReceipt'])->name('receipt.print');

    // Account settings - both can access
    Route::get('/account/settings', [AccountSettingsController::class, 'edit'])->name('account.settings');
    Route::put('/account/settings', [AccountSettingsController::class, 'update'])->name('account.settings.update');
    Route::put('/account/settings/password', [AccountSettingsController::class, 'updatePassword'])->name('account.settings.password');

    // Add this with your other routes (after auth routes)
Route::post('/session/timeout/update', [SessionController::class, 'updateTimeout'])
    ->name('session.timeout.update');
});