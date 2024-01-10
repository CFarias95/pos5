<?php

$current_hostname = app(Hyn\Tenancy\Contracts\CurrentHostname::class);

if($current_hostname) {
    Route::domain($current_hostname->fqdn)->group(function () {
        Route::middleware(['auth', 'locked.tenant'])->group(function () {

            Route::redirect('/', '/dashboard');

            Route::prefix('dashboard')->group(function () {
                Route::get('/', 'DashboardController@index')->name('tenant.dashboard.index');
                Route::get('filter', 'DashboardController@filter');
                Route::post('data', 'DashboardController@data');
                //Route::post('data', 'DashboardController@graph_sale_noteSP');
                Route::post('data_aditional', 'DashboardController@data_aditional');
                // Route::post('unpaid', 'DashboardController@unpaid');
                // Route::get('unpaidall', 'DashboardController@unpaidall')->name('unpaidall');
                Route::get('stock-by-product/records', 'DashboardController@stockByProduct');
                Route::get('product-of-due/records', 'DashboardController@productOfDue');
                Route::post('utilities', 'DashboardController@utilities');
                Route::post('comprobantes', 'DashboardController@comprobantes');
                Route::get('global-data', 'DashboardController@globalData');
                Route::get('sales-by-product', 'DashboardController@salesByProduct');
                Route::get('sale_note_data', 'DashboardController@saleNoteSP');
                Route::get('comprobantes_data', 'DashboardController@comprobantesSP');
                Route::get('ventas_prodcuto_data', 'DashboardController@ventas_productoSP');
            });

            //Commands
            Route::get('command/df', 'DashboardController@df')->name('command.df');

        });
    });
}
