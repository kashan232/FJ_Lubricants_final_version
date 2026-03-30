<?php

use App\Http\Controllers\Business_tpyeController;
use App\Http\Controllers\CategoryAndSubCategoryController;
use App\Http\Controllers\CityAndAreaController;
use App\Http\Controllers\CreateBillController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DistributorController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SizeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SalesmanController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\LocalSaleController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PurchaseReturnController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SaleReturnController;
use App\Http\Controllers\VendorController;
use App\Models\Product;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
// Done 
// connected
// Github COnnected to FJ lubricants final version

Route::get('/home', [HomeController::class, 'index'])->middleware('auth')->name('home');

// Route::get('/adminpage', [HomeController::class, 'adminpage'])->middleware(['auth','admin'])->name('adminpage');

//city
Route::get('/city', [CityAndAreaController::class, 'city'])->name('city');
Route::post('/store-city', [CityAndAreaController::class, 'store_city'])->name('store-city');
Route::post('/city/update', [CityAndAreaController::class, 'update'])->name('city.update');

Route::get('/Area', [CityAndAreaController::class, 'Area'])->name('Area');
Route::post('/store-Area', [CityAndAreaController::class, 'store_Area'])->name('store-Area');
Route::post('/Area/update', [CityAndAreaController::class, 'update_area'])->name('Area.update');



Route::get('/create-bill', [CreateBillController::class, 'create_bill'])->name('create-bill');


Route::get('/Distributor', [DistributorController::class, 'Distributor'])->name('Distributor');
Route::post('/store-Distributor', [DistributorController::class, 'store_Distributor'])->name('store-Distributor');
Route::put('/Distributor/update/{id}', [DistributorController::class, 'update_Distributor'])->name('Distributor.update');
// Update Distributor Route
Route::get('/distributor/ledger/{id}', [DistributorController::class, 'getDistributorLedger'])->name('distributor.ledger');
// Route::post('/Distributor/update', [DistributorController::class, 'update_Distributor'])->name('Distributor.update');
Route::get('/Distributor', [DistributorController::class, 'Distributor'])->name('Distributor');
Route::get('/get-areas', [DistributorController::class, 'get_areas'])->name('get-areas');
Route::get('/Distributor-ledger', [DistributorController::class, 'Distributor_ledger'])->name('Distributor-ledger');
Route::post('/recovery-store', [DistributorController::class, 'recovery_store'])->name('recovery-store');
Route::get('/Distributor-recovery', [DistributorController::class, 'Distributor_recovery'])->name('Distributor-recovery');
Route::put('/Distributor-recovery-update/{id}', [DistributorController::class, 'updateDistributorRecovery'])->name('Distributor-recovery-update');

Route::get('/distributor/balance/ledger/{id}', [DistributorController::class, 'getDistributorLedgerbalance'])->name('distributor.balance.ledger');
Route::get('/Distributor-Balance-Transfer', [DistributorController::class, 'Distributor_Balance_Transfer'])->name('Distributor-Balance-Transfer');
Route::post('/distributor/transfer', [DistributorController::class, 'storeTransfer'])->name('distributor.transfer.store');
Route::delete('/transfers/{id}', [DistributorController::class, 'destroyTransfer'])->name('transfers.destroy');
Route::put('/transfers/{id}', [DistributorController::class, 'update'])->name('transfers.update');

Route::get('/category', [CategoryAndSubCategoryController::class, 'category'])->name('category');
Route::post('/store-category', [CategoryAndSubCategoryController::class, 'store_category'])->name('store-category');
Route::post('/category/update', [CategoryAndSubCategoryController::class, 'update_category'])->name('category.update');

Route::get('/sub-category', [CategoryAndSubCategoryController::class, 'sub_category'])->name('sub-category');
Route::post('/store-sub-category', [CategoryAndSubCategoryController::class, 'store_sub_category'])->name('store-sub-category');
Route::post('/sub-category/update', [CategoryAndSubCategoryController::class, 'update_sub_category'])->name('sub-category.update');

//size
Route::get('/size', [SizeController::class, 'size'])->name('size');
Route::post('/store-size', [SizeController::class, 'store_size'])->name('store-size');
Route::post('/size/update', [SizeController::class, 'update'])->name('size.update');
//business_tpye
Route::get('/business-type', [Business_tpyeController::class, 'index'])->name('business_type');
Route::post('/business-type/store', [Business_tpyeController::class, 'store'])->name('business_type.store');
Route::post('/business-type/update', [Business_tpyeController::class, 'update'])->name('business_type.update');


//expense
Route::get('/expense', [ExpenseController::class, 'expense'])->name('expense');
Route::post('/store-expense-category', [ExpenseController::class, 'store_expense_category'])->name('store-expense-category');
Route::post('/expense/update', [ExpenseController::class, 'update'])->name('expense.update');
Route::delete('/delete-expense-category/{id}', [ExpenseController::class, 'delete_Add_ExpenseBtn'])->name('delete-expense-category');

// Expense Management Routes
Route::get('/expenses', [ExpenseController::class, 'expense'])->name('expenses.index'); // Expense list page
Route::get('/add-expenses', [ExpenseController::class, 'addExpenseScreen'])->name('add-expenses'); // Add expense screen
Route::post('/store-expense', [ExpenseController::class, 'store_addexpense'])->name('store-expense'); // Store new expense
Route::post('/update-expense', [ExpenseController::class, 'update_addexpense'])->name('update-expense'); // Update existing expense
Route::delete('/delete-expense/{id}', [ExpenseController::class, 'delete_add_expense'])->name('delete-expense');


//Product
Route::get('/product', [ProductController::class, 'product'])->name('product');
Route::post('/store-product', [ProductController::class, 'store_product'])->name('store-product');
Route::put('/product/update/{id}', [ProductController::class, 'update'])->name('product.update');
Route::get('/fetch-subcategories', [ProductController::class, 'fetchSubCategories'])->name('fetch-subcategories');
Route::get('/products/{id}/edit', [ProductController::class, 'edit'])->name('products.edit');


// purchase 
Route::get('/Purchase', [PurchaseController::class, 'Purchase'])->name('Purchase');
Route::get('/get-subcategories/{categoryname}', [PurchaseController::class, 'getSubcategories'])->name('get.subcategories');
Route::get('/get-items', [PurchaseController::class, 'getItems'])->name('get.items');
Route::post('/store-Purchase', [PurchaseController::class, 'store_Purchase'])->name('store-Purchase');
Route::get('/all-Purchases', [PurchaseController::class, 'all_Purchases'])->name('all-Purchases');
Route::get('/purchase/invoice/{id}', [PurchaseController::class, 'purchaseInvoice'])->name('purchase.invoice');
Route::get('/purchase/edit/{id}', [PurchaseController::class, 'purchaseedit'])->name('purchase.edit');
Route::put('/purchase/update/{id}', [PurchaseController::class, 'update_purchase'])->name('update-Purchase');

Route::get('/purchase-return/{id}', [PurchaseReturnController::class, 'showReturnForm'])->name('purchase.return.form');
Route::post('/purchase-return/store', [PurchaseReturnController::class, 'store'])->name('purchase.return.store');
Route::get('/all-purchase-return', [PurchaseReturnController::class, 'all_purchase_return'])->name('all-purchase-return');

Route::get('/add-sale', [SaleController::class, 'add_sale'])->name('add-sale');
Route::post('/store-sale', [SaleController::class, 'store_sale'])->name('store-sale');
Route::get('/all-sale', [SaleController::class, 'all_sale'])->name('all-sale');
Route::get('/sale/{id}', [SaleController::class, 'show_sale'])->name('show_sale');
Route::get('/sale/invoice/{id}', [SaleController::class, 'saleInvoice'])->name('sale.invoice');
Route::get('/sale/edit/{id}', [SaleController::class, 'saleEdit'])->name('sale.edit');
// web.php
Route::get('/sale/delete/{id}', [SaleController::class, 'delete'])->name('sale.delete');
Route::put('/sale/update/{id}', [SaleController::class, 'saleupdate'])->name('sale.update');


Route::get('/add-sale-return', [SaleReturnController::class, 'add_sale_return'])->name('add-sale-return');
Route::get('/get-sale-invoices', [SaleReturnController::class, 'getSaleInvoices'])->name('get-sale-invoices');
Route::get('/fetch-sale-details', [SaleReturnController::class, 'fetchSaleDetails'])->name('fetch-sale-details');
Route::post('/sale-return', [SaleReturnController::class, 'store'])->name('sale-return.store');
Route::get('/all-sale-return', [SaleReturnController::class, 'all_sale_return'])->name('all-sale-return');

// Salesmen Routes
Route::get('/salesmen', [SalesmanController::class, 'salesmen'])->name('salesmen'); // Displays Salesmen List
Route::post('/store-salesman', [SalesmanController::class, 'store_salesman'])->name('store-salesman'); // Store a new Salesman
Route::post('/salesman/update', [SalesmanController::class, 'update_salesman'])->name('update-salesman'); // Update existing Salesman
Route::get('/fetch-cities', [SalesmanController::class, 'fetchCities'])->name('fetch-cities'); // Fetch list of cities (adjust method to actual logic)
Route::post('/salesman/toggle-status', [SalesmanController::class, 'toggleStatus'])->name('toggle-salesman-status');
Route::get('/fetch-areas', [CustomerController::class, 'fetchAreas'])->name('fetch-areas');
Route::get('/fetch-designation', [CustomerController::class, 'fetchdesignation'])->name('fetch-designation');
Route::get('/fetch-areas-report', [CustomerController::class, 'fetch_areas_report'])->name('fetch-areas-report');


// designation
Route::get('/designation', [SalesmanController::class, 'designation'])->name('designation');
Route::post('/store-designation', [SalesmanController::class, 'store_designation'])->name('designation.store');
Route::post('/designation/update', [SalesmanController::class, 'update_designation'])->name('designation.update');
Route::delete('/designation/delete/{id}', [SalesmanController::class, 'destroy'])->name('designation.delete');

Route::get('/vendors', [VendorController::class, 'vendors'])->name('vendors');
Route::post('/store-vendors', [VendorController::class, 'store_vendors'])->name('store-vendors');
Route::put('/vendors/update/{id}', [VendorController::class, 'update_vendors'])->name('vendors.update');
Route::get('/vendors-ledger', [VendorController::class, 'vendors_ledger'])->name('vendors-ledger');
Route::post('/vendors-payment', [VendorController::class, 'vendors_payment'])->name('vendors-payment');
Route::get('/amount-paid-vendors', [VendorController::class, 'amount_paid_vendors'])->name('amount-paid-vendors');
Route::get('/vendor/ledger/{id}', [VendorController::class, 'getLedger'])->name('vendor.ledger');
Route::post('/update-vendor-payment', [VendorController::class, 'update_vendor_payment'])->name('update-vendor-payment');

Route::get('/vendors-builty', [VendorController::class, 'vendors_builty'])->name('vendors-builty');
Route::post('/store-vendors-builty', [VendorController::class, 'store_vendors_builty'])->name('store-vendors-builty');
Route::put('/vendor-builty/update/{id}', [VendorController::class, 'update'])->name('update-vendors-builty');

//Cutomer create 
Route::get('/customer', [CustomerController::class, 'index'])->name('customer');
Route::post('/customer/store', [CustomerController::class, 'store'])->name('customer.store');
Route::post('/customer/update', [CustomerController::class, 'update'])->name('customers.update');
Route::delete('/customer/delete/{id}', [CustomerController::class, 'destroy'])->name('delete-customer');
Route::get('/fetch-business-types', [CustomerController::class, 'fetchBusinessTypes'])->name('fetch-business-types');
Route::get('/fetch-areas', [CustomerController::class, 'fetchAreas'])->name('fetch-areas');
Route::get('/customer-ledger', [CustomerController::class, 'customer_ledger'])->name('customer-ledger');
Route::post('/customer-recovery-store', [CustomerController::class, 'customer_recovery_store'])->name('customer-recovery-store');
Route::get('/customer-recovery', [CustomerController::class, 'customer_recovery'])->name('customer-recovery');
Route::get('/customer/edit/{id}', [CustomerController::class, 'getCustomerData'])->name('customer.edit');
Route::put('/customer-recovery/{id}', [CustomerController::class, 'updateRecovery'])->name('customer_recovery.update');


Route::get('/local-sale', [LocalSaleController::class, 'local_sale'])->name('local-sale');
Route::post('/store-local-sale', [LocalSaleController::class, 'store_local_sale'])->name('store-local-sale');
Route::get('/all-local-sale', [LocalSaleController::class, 'all_local_sale'])->name('all-local-sale');
Route::get('/show-local-sale/{id}', [LocalSaleController::class, 'show_local_sale'])->name('show-local-sale');
Route::get('/local/sale/invoice/{id}', [LocalSaleController::class, 'localsaleInvoice'])->name('local.sale.invoice');
Route::get('/local/sale/delete/{id}', [LocalSaleController::class, 'delete_localsale'])->name('local.sale.delete');
Route::get('/local/sale/edit/{id}', [LocalSaleController::class, 'localsaleEdit'])->name('local.sale.edit');
Route::put('/local/sale/update/{id}', [LocalSaleController::class, 'localsaleupdate'])->name('local.sale.update');

// Reporting
Route::get('/Distributor-Ledger-Record', [ReportController::class, 'Distributor_Ledger_Record'])->name('Distributor-Ledger-Record');
Route::get('/fetch-distributor-ledger', [ReportController::class, 'fetchDistributorLedger'])->name('fetch-distributor-ledger');
// routes/web.php
Route::get('reports/distributor-ledger/pdf', [ReportController::class, 'DistributorLedgerPdf'])->name('distributor-ledger-pdf');


Route::get('/vendor-Ledger-Record', [ReportController::class, 'vendor_Ledger_Record'])->name('vendor-Ledger-Record');
Route::get('/fetch-vendor-ledger', [ReportController::class, 'fetchvendorLedger'])->name('fetch-vendor-ledger');
Route::get('/vendor-ledger-pdf', [ReportController::class, 'vendorLedgerPdf'])->name('vendor-ledger-pdf');


Route::get('/Customer-Ledger-Record', [ReportController::class, 'Customer_Ledger_Record'])->name('Customer-Ledger-Record');
Route::get('/fetch-Customer-ledger', [ReportController::class, 'fetchCustomerledger'])->name('fetch-Customer-ledger');
Route::get('/customer-ledger-pdf', [ReportController::class, 'customerLedgerPdf'])->name('customer-ledger-pdf');

Route::get('/stock-Record', [ReportController::class, 'stock_Record'])->name('stock-Record');
Route::get('/get-items-report/{subcategory}', [ReportController::class, 'getItems'])->name('get.items.report');
Route::get('/get-item-details', [ReportController::class, 'getItemDetails'])->name('get.item.details');

Route::get('/date-wise-recovery-report', [ReportController::class, 'date_wise_recovery_report'])->name('date-wise-recovery-report');
Route::post('/get-recovery-report', [ReportController::class, 'getRecoveryReport'])->name('get-recovery-report');

Route::get('/date-wise-purcahse-report', [ReportController::class, 'date_wise_purcahse_report'])->name('date-wise-purcahse-report');
Route::post('/fetch-purchase-report', [ReportController::class, 'fetch_purchase_report'])->name('fetch-purchase-report');

Route::get('/vendor-wise-purcahse-report', [ReportController::class, 'vendor_wise_purcahse_report'])->name('vendor-wise-purcahse-report');
Route::post('/fetch-vendor-purchase-report', [ReportController::class, 'fetchVendorPurchaseReport'])->name('fetch.vendor.purchase.report');

Route::get('/Area-wise-Customer-payments', [ReportController::class, 'Area_wise_Customer_payments'])->name('Area-wise-Customer-payments');
Route::get('/receivable-report', [ReportController::class, 'fetchReceivableReport'])->name('fetch.receivable.report');

Route::get('/Area-wise-Sale-Report', [ReportController::class, 'Area_wise_Sale_Report'])->name('Area-wise-Sale-Report');
Route::get('/fetch-area-wise-sale-report', [ReportController::class, 'fetchAreaWiseSaleReport'])->name('fetch.area.wise.sale.report');
Route::get('/Profit-Report', [ReportController::class, 'Profit_Report'])->name('Profit-Report');
Route::get('/fetch-profit-report', [ReportController::class, 'fetchProfitReport'])->name('fetch.profit.report');
Route::get('/Item-Wise-Sale-Report', [ReportController::class, 'Item_Wise_Sale_Report'])->name('Item-Wise-Sale-Report');
Route::get('/fetch-item-wise-sale-report', [ReportController::class, 'fetchItemWiseSaleReport'])->name('fetch.item.wise.sale.report');
Route::get('/Balance-Sheet', [ReportController::class, 'Balance_Sheet'])->name('Balance-Sheet');
Route::get('/fetch-balance-sheet', [ReportController::class, 'fetchBalanceSheet'])->name('fetch.balance.sheet');

Route::get('/Trial-Balance', [ReportController::class, 'Trial_Balance'])->name('Trial-Balance');
Route::get('/fetch-trial-balance', [ReportController::class, 'fetchTrialBalance'])->name('fetch.trial.balance');

Route::get('/Area-wise-salesman-market-payments', [ReportController::class, 'Area_wise_salesman_market_payments'])->name('Area-wise-salesman-market-payments');
Route::get('/receivable-salesman-marketreport', [ReportController::class, 'receivablesalesmanmarketreport'])->name('receivable.salesman.marketreport');


Route::get('/Date-wise-Sales-Report', [ReportController::class, 'Date_wise_Sales_Report'])->name('Date-wise-Sales-Report');
Route::post('/get-sales-report', [ReportController::class, 'getsalesreport'])->name('get-sales-report');

Route::get('/Product-wise-Sales-Report', [ReportController::class, 'Product_wise_Sales_Report'])->name('Product-wise-Sales-Report');
Route::post('/get-Product-sales-report', [ReportController::class, 'getProductsalesreport'])->name('get-Product-sales-report');

Route::get('/Product-wise-Purchase-Report', [ReportController::class, 'Product_wise_purchase_Report'])->name('Product-wise-Purchase-Report');
Route::post('/get-Product-Purchase-report', [ReportController::class, 'getProductPurchasereport'])->name('get-Product-Purchase-report');


Route::get('/vendors-payments', [PaymentController::class, 'vendors_payments'])->name('vendors-payments');
Route::post('/vendor-payment-store', [PaymentController::class, 'storeVendorPayment'])->name('vendor-payment-store');
Route::get('/get-vendor-balance/{id}', [PaymentController::class, 'getVendorBalance'])->name('get-Vendor-balance');
Route::get('/vendor-payment/receipt', [PaymentController::class, 'showVendorPaymentReceipt'])->name('Vendor.payment.receipt');

Route::get('/customer-payments', [PaymentController::class, 'customer_payments'])->name('customer-payments');
Route::get('/get-customer-balance/{id}', [PaymentController::class, 'getCustomerBalance'])->name('get.customer.balance');
Route::post('/customer-payment/store', [PaymentController::class, 'storeCustomerPayment'])->name('customer.payment.store');
Route::get('customer/payment/receipt/{customer_id}/{amount}', [PaymentController::class, 'showCustomerPaymentReceipt'])->name('Customer.payment.receipt');


Route::get('/Distributor-payments', [PaymentController::class, 'Distributor_payments'])->name('Distributor-payments');
Route::get('/get-Distributor-balance/{id}', [PaymentController::class, 'getDistributorBalance'])->name('get.Distributor.balance');
Route::post('/Distributor-payment/store', [PaymentController::class, 'storeDistributorPayment'])->name('Distributor.payment.store');
Route::get('/distributor/payment/receipt/{distributor_id}/{amount}', [PaymentController::class, 'showPaymentReceipt'])->name('Distributor.payment.receipt');

Route::get('/', function () {
    return view('welcome');
});

// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
