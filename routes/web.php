<?php

Route::redirect('/', 'dashboard');
Auth::routes();

Route::middleware(['auth'])->group(function () {
    Route::get('dashboard', 'DashboardController@index')->name('dashboard');

    // Address
    Route::prefix('address')->name('address.')->group(function () {
        Route::post('{id}/get-sub-addresses', 'AddressController@getSubAddresses')->name('get_sub_addresses');
    });

    // Client
    Route::post('client/save/{client?}', 'ClientController@save')->name('client.save');
    Route::resource('client', 'ClientController')->only(resourceRouteMethods());

    // Branch
    Route::prefix('branch')->name('branch.')->group(function () {
        Route::post('save/{branch?}', 'BranchController@save')->name('save');
        Route::get('{branch}/product', 'BranchController@productList')->name('list_product');
    });
    Route::resource('branch', 'BranchController')->only(resourceRouteMethods());

    // Staff
    Route::prefix('staff')->name('staff.')->group(function () {
        Route::post('save/{staff?}', 'StaffController@save')->name('save');
        Route::post('ajax/{branchId}/get-agents', 'StaffController@getAgents')->name('get_agents');
        Route::get('{staff}/commission', 'StaffController@commission')->name('commission');
        Route::post('{staff}/save-commission', 'StaffController@saveCommission')->name('save_commission');
    });
    Route::resource('staff', 'StaffController')->only(resourceRouteMethods());

    // Position
    Route::post('position/save', 'PositionController@save')->name('position.save');
    Route::resource('position', 'PositionController')->only(resourceRouteMethods(false));

    // Product
    Route::prefix('product')->name('product.')->group(function () {
        Route::post('save/{product?}', 'ProductController@save')->name('save');
        Route::get('{product}/warehouse', 'ProductController@warehouseList')->name('list_warehouse');
        Route::get('/stock', 'ProductController@stockLevel')->name('product_stock');
    });
    Route::resource('product', 'ProductController')->only(resourceRouteMethods());

    // Product category
    Route::post('product-category/save/{productCategory?}', 'ProductCategoryController@save')->name('product_category.save');
    Route::resource('product-category', 'ProductCategoryController')->only(resourceRouteMethods(false))->names(setResourceRouteNames('product_category'));

    // Brand
    Route::post('brand/save', 'BrandController@save')->name('brand.save');
    Route::resource('brand', 'BrandController')->only(resourceRouteMethods(false));

    // Stock transfer
    Route::prefix('transfer')->name('transfer.')->group(function () {
        Route::post('save', 'TransferController@save')->name('save');
        Route::post('ajax/{warehouseId}/get-products', 'TransferController@getProducts')->name('get_products');
    });
    Route::resource('transfer', 'TransferController')->only(resourceRouteMethods());

    // Stock Adjustment
    Route::prefix('adjustment')->name('adjustment.')->group(function () {
        Route::post('save', 'AdjustmentController@save')->name('save');
        Route::post('ajax/{warehouseId}/{productId}/stock-qty', 'AdjustmentController@getStockQuantity')->name('get_stock_quantity');
    });
    Route::resource('adjustment', 'AdjustmentController')->only(resourceRouteMethods(false));

    // Purchase
    Route::prefix('purchase')->name('purchase.')->group(function () {
        Route::post('save', 'PurchaseController@save')->name('save');
    });
    Route::resource('purchase', 'PurchaseController')->only(resourceRouteMethods());

    // Sale
    Route::prefix('sale')->name('sale.')->group(function () {
        Route::post('{saleType}/save', 'SaleController@save')->name('save');
    });
    Route::resource('sale', 'SaleController')->only(resourceRouteMethods());

    // Loan
    Route::prefix('loan')->name('loan.')->group(function () {
        Route::post('save/{loan?}', 'LoanController@save')->name('save');
        Route::post('ajax/payment-schedule', 'LoanController@getPaymentSchedule')->name('get_payment_schedule');
        Route::post('change-status/{loan}/{status}', 'LoanController@changeStatus')->name('change_status');

        Route::get('{loan}/disburse', 'LoanController@disburse')->name('disburse');
        Route::get('{loan}/contract', 'LoanController@printContract')->name('print_contract');
        Route::get('{loan}/payment-schedule', 'LoanController@printPaymentSchedule')->name('print_payment_schedule');

        Route::post('{loan}/update-note', 'LoanController@updateNote')->name('update_note');
    });
    Route::resource('loan', 'LoanController')->only(resourceRouteMethods());

    // Repayment
    Route::prefix('repayment')->name('repayment.')->group(function () {
        Route::get('', 'RepaymentController@index')->name('index');
        Route::get('{id}/{repayType}', 'RepaymentController@show')->name('show');
        Route::post('{id}/save', 'RepaymentController@save')->name('save');
    });

    // Agent commission payment
    Route::prefix('commission-payment')->name('commission-payment.')->group(function () {
        Route::get('', 'CommissionPaymentController@index')->name('index');
        Route::post('save', 'CommissionPaymentController@save')->name('save');
        Route::post('{staffId}/get-commission', 'CommissionPaymentController@getAgentCommissionInfo')->name('get_agent_commission_info');
    });

    // User
    Route::get('profile/{user}', 'UserController@showProfile')->name('user.show_profile');
    Route::post('save-profile/{user}', 'UserController@saveProfile')->name('user.save_profile');
    Route::post('user/save/{user?}', 'UserController@save')->name('user.save');
    Route::resource('user', 'UserController')->only(resourceRouteMethods(false));
    Route::resource('role', 'RoleController');

    // Report
    Route::prefix('report')->name('report.')->group(function () {
        Route::get('loan/{status}', 'ReportController@loan')->name('loan');
        Route::get('disbursed-loan', 'ReportController@disbursedLoan')->name('disbursed_loan');
        Route::get('overdue-loan', 'ReportController@overdueLoan')->name('overdue_loan');
        Route::get('financial-statement', 'ReportController@financialStatement')->name('financial_statement');
        Route::get('client-payment', 'ReportController@clientPayment')->name('client_payment');
        Route::get('client-payment/{invoice}/receipt', 'ReportController@clientPaymentReceipt')->name('client_payment_receipt');
        Route::get('client-registration', 'ReportController@clientRegistration')->name('client_registration');
        Route::get('loan-portfolio/{client}', 'ReportController@loanPortfolio')->name('loan_portfolio');
        Route::get('commission-payment', 'ReportController@commissionPayment')->name('commission_payment');

        Route::get('agent', 'ReportController@agent')->name('agent');
        Route::get('agent/{agent}', 'ReportController@agentDetail')->name('agent_detail');
        Route::get('agent-commission', 'ReportController@agentCommission')->name('agent_commission');
    });

    // Setting
    Route::prefix('setting')->group(function () {
        Route::get('general', 'GeneralSettingController@index')->name('general_setting.index');
        Route::post('general/save', 'GeneralSettingController@save')->name('general_setting.save');
    });
});
