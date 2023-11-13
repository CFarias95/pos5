<?php

use Illuminate\Support\Facades\Route;

$current_hostname = app(Hyn\Tenancy\Contracts\CurrentHostname::class);

if ($current_hostname) {
    Route::domain($current_hostname->fqdn)->group(function () {
        Route::middleware(['auth', 'redirect.module', 'locked.tenant'])->group(function () {

            Route::prefix('report_configurations')->group(function () {

                Route::get('records', 'ReportConfigurationController@records');
                Route::post('', 'ReportConfigurationController@store');
            });


            Route::prefix('reports')->group(function () {

                Route::get('data-table/persons/{type}', 'ReportController@dataTablePerson');
                Route::get('data-table/items', 'ReportController@dataTableItem');

                Route::get('retentions','ReportsFinancesController@reportRetentionIndex')->name('tenant.reports.retentions.statement.index');
                Route::get('retentions/records', 'ReportsFinancesController@reportRetentionRecords');
                Route::get('retentions/excel','ReportsFinancesController@excelRetentions');
                Route::get('retentions/tables','ReportsFinancesController@tablesStatement');

                Route::get('payable','ReportsFinancesController@reportPayableIndex')->name('tenant.reports.payable.statement.index');
                Route::get('payable/records', 'ReportsFinancesController@reportPayableRecords');
                Route::get('payable/excel','ReportsFinancesController@excelPayable');
                Route::get('payable/tables','ReportsFinancesController@tablesStatement');

                Route::get('receivable','ReportsFinancesController@reportReceivableIndex')->name('tenant.reports.receivable.statement.index');
                Route::get('receivable/records', 'ReportsFinancesController@reportReceivableRecords');
                Route::get('receivable/excel','ReportsFinancesController@excelReceivable');
                Route::get('receivable/tables','ReportsFinancesController@tablesStatement');

                Route::get('topay','ReportsFinancesController@reportToPayIndex')->name('tenant.reports.topay.statement.index');
                Route::get('topay/records', 'ReportsFinancesController@reportToPayRecords');
                Route::get('topay/excel','ReportsFinancesController@excelToPay');
                Route::get('topay/tables','ReportsFinancesController@tablesStatement');

                Route::get('tocollect','ReportsFinancesController@reportToCollectIndex')->name('tenant.reports.tocollect.statement.index');
                Route::get('tocollect/records', 'ReportsFinancesController@reportToCollectRecords');
                Route::get('tocollect/excel','ReportsFinancesController@excelToCollect');
                Route::get('tocollect/tables','ReportsFinancesController@tablesStatement');

                Route::prefix('purchases')->group(function () {
                    /**
                     * reports/purchases/
                     * reports/purchases/items
                     * reports/purchases/pdf
                     * reports/purchases/excel
                     * reports/purchases/filter
                     * reports/purchases/records
                     */
                    Route::get('/', 'ReportPurchaseController@index')->name('tenant.reports.purchases.index');

                    Route::prefix('statement')->group(
                        function () {
                            Route::get('/', 'ReportPurchaseController@reportStatementIndex')->name('tenant.reports.purchases.statement.index');
                            Route::get('records', 'ReportPurchaseController@reportStatementRecords');
                            Route::get('excel', 'ReportPurchaseController@excelStatement');
                            Route::get('tables', 'ReportPurchaseController@tablesStatement');
                        }
                    );

                    Route::prefix('orders')->group(function () {
                    }); //end orders prefix
                    /** Nuevo */

                    Route::get('items', 'ReportPurchaseItemController@index')->name('tenant.reports.purchases.items.index');

                    /*
                         * reports/purchases/general_items/
                         * reports/purchases/general_items/records
                         * reports/purchases/general_items/excel
                         * reports/purchases/general_items/pdf
                         * reports/purchases/general_items/filter
                         */
                    Route::prefix('general_items')->group(function () {
                        Route::get('/', 'ReportPurchaseItemController@general_items')->name('tenant.reports.purchases.general_items.index');
                        Route::get('records', 'ReportPurchaseItemController@records');
                        Route::get('excel', 'ReportPurchaseItemController@general_items');
                        Route::get('pdf', 'ReportPurchaseItemController@general_items');
                        Route::get('filter', 'ReportPurchaseItemController@filter');
                    });
                    /** Nuevo */
                    Route::get('pdf', 'ReportPurchaseController@pdf')->name('tenant.reports.purchases.pdf');
                    Route::get('excel', 'ReportPurchaseController@excel')->name('tenant.reports.purchases.excel');
                    Route::get('filter', 'ReportPurchaseController@filter')->name('tenant.reports.purchases.filter');
                    Route::get('records', 'ReportPurchaseController@records')->name('tenant.reports.purchases.records');

                    /* Quotations report */

                    Route::prefix('quotations')->group(function () {
                        Route::get('/', 'ReportPurchaseController@orders')->name('tenant.reports.purchases.quotations');
                        Route::get('records', 'ReportPurchaseController@orderRecords');
                        Route::get('excel', 'ReportPurchaseController@orderExcel');
                        Route::get('pdf', 'ReportPurchaseController@general_items');
                        Route::get('filter', 'ReportPurchaseController@orderFilter');
                    });

                    Route::prefix('base_impuestos')->group(function () {
                        Route::get('/', 'ReportBaseImpuestosController@index')->name('tenant.reports.purchases.base_impuestos.index');
                        Route::get('records', 'ReportBaseImpuestosController@records');
                        Route::get('excel', 'ReportBaseImpuestosController@excel');
                        Route::get('pdf', 'ReportBaseImpuestosController@pdf');
                        Route::get('datosSP', 'ReportBaseImpuestosController@datosSP');
                    });
                });
                /**
                 * /reports/sales
                 * /reports/sales/pdf
                 * /reports/sales/filter
                 * /reports/sales/excel
                 * /reports/sales/records
                 */
                Route::prefix('sales')->group(function () {
                    Route::get('', 'ReportDocumentController@index')
                        ->name('tenant.reports.sales.index')
                        ->middleware('tenant.internal.mode');
                    Route::get('/pdf', 'ReportDocumentController@pdf')
                        ->name('tenant.reports.sales.pdf');
                    Route::get('/excel', 'ReportDocumentController@excel')
                        ->name('tenant.reports.sales.excel');
                    Route::get('/filter', 'ReportDocumentController@filter')
                        ->name('tenant.reports.sales.filter');
                    Route::get('/records', 'ReportDocumentController@records')
                        ->name('tenant.reports.sales.records');
                    Route::get('/pdf-simple', 'ReportDocumentController@pdfSimple')
                        ->name('tenant.reports.sales.pdfSimple');
                });
                /**
                 * /reports/sale-notes
                 * /reports/sale-notes/pdf
                 * /reports/sale-notes/excel
                 * /reports/sale-notes/filter
                 * /reports/sale-notes/records
                 */
                Route::prefix('sale-notes')->group(function () {
                    Route::get('', 'ReportSaleNoteController@index')->name('tenant.reports.sale_notes.index');
                    Route::get('/pdf', 'ReportSaleNoteController@pdf')->name('tenant.reports.sale_notes.pdf');
                    Route::get('/excel', 'ReportSaleNoteController@excel')
                        ->name('tenant.reports.sale_notes.excel');
                    Route::get('/filter', 'ReportSaleNoteController@filter')
                        ->name('tenant.reports.sale_notes.filter');
                    Route::get('/records', 'ReportSaleNoteController@records')
                        ->name('tenant.reports.sale_notes.records');
                });

                Route::prefix('quotations')->group(function () {
                    Route::get('', 'ReportQuotationController@index')
                        ->name('tenant.reports.quotations.index');
                    Route::get('pdf', 'ReportQuotationController@pdf')
                        ->name('tenant.reports.quotations.pdf');
                    Route::get('excel', 'ReportQuotationController@excel')
                        ->name('tenant.reports.quotations.excel');
                    Route::get('filter', 'ReportQuotationController@filter')
                        ->name('tenant.reports.quotations.filter');
                    Route::get('records', 'ReportQuotationController@records')
                        ->name('tenant.reports.quotations.records');
                });

                Route::prefix('cash')->group(function(){
                    Route::get('', 'ReportCashController@index')->name('tenant.reports.cash.index');
                    Route::get('pdf', 'ReportCashController@pdf')->name('tenant.reports.cash.pdf');
                    Route::get('excel', 'ReportCashController@excel')->name('tenant.reports.cash.excel');
                    Route::get('filter', 'ReportCashController@filter')->name('tenant.reports.cash.filter');
                    Route::get('records', 'ReportCashController@records')->name('tenant.reports.cash.records');
                });

                /**
                 * reports/document-hotels
                 * reports/document-hotels/pdf
                 * reports/document-hotels/excel
                 * reports/document-hotels/filter
                 * reports/document-hotels/records
                 */
                Route::prefix('document-hotels')->group(function () {

                    Route::get('', 'ReportDocumentHotelController@index')->name('tenant.reports.document_hotels.index');
                    Route::get('/pdf', 'ReportDocumentHotelController@pdf')->name('tenant.reports.document_hotels.pdf');
                    Route::get('/excel', 'ReportDocumentHotelController@excel')->name('tenant.reports.document_hotels.excel');
                    Route::get('/filter', 'ReportDocumentHotelController@filter')->name('tenant.reports.document_hotels.filter');
                    Route::get('/records', 'ReportDocumentHotelController@records')->name('tenant.reports.document_hotels.records');
                });
                /**
                 * reports/report_hotels
                 * reports/report_hotels/pdf
                 * reports/report_hotels/excel
                 * reports/report_hotels/filter
                 * reports/report_hotels/records
                 */
                Route::prefix('report_hotels')->group(function () {

                    Route::get('', 'ReportHotelController@index')->name('tenant.reports.report_hotel.index');
                    Route::get('/pdf', 'ReportHotelController@pdf')->name('tenant.reports.report_hotel.pdf');
                    Route::get('/excel', 'ReportHotelController@excel')->name('tenant.reports.report_hotel.excel');
                    Route::get('/filter', 'ReportHotelController@filter')->name('tenant.reports.report_hotel.filter');
                    Route::get('/records', 'ReportHotelController@records')->name('tenant.reports.report_hotel.records');
                });


                Route::get('commercial-analysis', 'ReportCommercialAnalysisController@index')
                    ->name('tenant.reports.commercial_analysis.index');
                Route::get('commercial-analysis/pdf', 'ReportCommercialAnalysisController@pdf')
                    ->name('tenant.reports.commercial_analysis.pdf');
                Route::get('commercial-analysis/excel', 'ReportCommercialAnalysisController@excel')
                    ->name('tenant.reports.commercial_analysis.excel');
                Route::get('commercial-analysis/filter', 'ReportCommercialAnalysisController@filter')
                    ->name('tenant.reports.commercial_analysis.filter');
                Route::get('commercial-analysis/records', 'ReportCommercialAnalysisController@records')
                    ->name('tenant.reports.commercial_analysis.records');
                Route::get('commercial-analysis/data_table', 'ReportCommercialAnalysisController@data_table');
                Route::get('commercial-analysis/columns', 'ReportCommercialAnalysisController@columns');
                Route::get('no_paid/excel', 'ReportUnpaidController@excel')->name('tenant.reports.no_paid.excel');
                Route::get('retention/excel', 'ReportRetentionController@excel')->name('tenant.reports.retention.excel');

                Route::get('document-detractions', 'ReportDocumentDetractionController@index')
                    ->name('tenant.reports.document_detractions.index');
                Route::get('document-detractions/pdf', 'ReportDocumentDetractionController@pdf')
                    ->name('tenant.reports.document_detractions.pdf');
                Route::get('document-detractions/excel', 'ReportDocumentDetractionController@excel')
                    ->name('tenant.reports.document_detractions.excel');
                Route::get('document-detractions/filter', 'ReportDocumentDetractionController@filter')
                    ->name('tenant.reports.document_detractions.filter');
                Route::get('document-detractions/records', 'ReportDocumentDetractionController@records')
                    ->name('tenant.reports.document_hotels.records');


                /**
                 * reports/commissions
                 * reports/commissions/pdf
                 * reports/commissions/excel
                 * reports/commissions/filter
                 * reports/commissions/records
                 */
                Route::prefix('stock')->group(function () {
                    Route::get('/', 'ReportStockAlmacenController@index')
                        ->name('tenant.reports.stock.index');
                    Route::get('records', 'ReportStockAlmacenController@records');
                    Route::get('excel', 'ReportStockAlmacenController@excel');
                    Route::get('pdf', 'ReportStockAlmacenController@pdf');
                    Route::get('datosSP', 'ReportStockAlmacenController@datosSP');
                    Route::get('tables', 'ReportStockAlmacenController@tables');
                });

                Route::get('commissions', 'ReportCommissionController@index')
                    ->name('tenant.reports.commissions.index');
                Route::get('commissions/pdf', 'ReportCommissionController@pdf')
                    ->name('tenant.reports.commissions.pdf');
                Route::get('commissions/excel', 'ReportCommissionController@excel')
                    ->name('tenant.reports.commissions.excel');
                Route::get('commissions/filter', 'ReportCommissionController@filter')
                    ->name('tenant.reports.commissions.filter');
                Route::get('commissions/records', 'ReportCommissionController@records')
                    ->name('tenant.reports.commissions.records');

                Route::get('customers', 'ReportCustomerController@index')->name('tenant.reports.customers.index');
                Route::get('customers/excel', 'ReportCustomerController@excel')
                    ->name('tenant.reports.customers.excel');
                Route::get('customers/filter', 'ReportCustomerController@filter')
                    ->name('tenant.reports.customers.filter');
                Route::get('customers/records', 'ReportCustomerController@records')
                    ->name('tenant.reports.customers.records');

                /**
                 * reports/items
                 * reports/items/excel
                 * reports/items/filter
                 * reports/items/records
                 * */
                Route::get('items', 'ReportItemController@index')->name('tenant.reports.items.index');
                Route::get('items/excel', 'ReportItemController@excel')->name('tenant.reports.items.excel');
                Route::get('items/filter', 'ReportItemController@filter')->name('tenant.reports.items.filter');
                Route::get('items/records', 'ReportItemController@records')->name('tenant.reports.items.records');

                Route::prefix('order-notes-consolidated')->group(function () {
                    Route::get('', 'ReportOrderNoteConsolidatedController@index')
                        ->name('tenant.reports.order_notes_consolidated.index');
                    Route::get('pdf', 'ReportOrderNoteConsolidatedController@pdf');
                    // Route::get('/excel', 'ReportOrderNoteConsolidatedController@excel');
                    Route::get('filter', 'ReportOrderNoteConsolidatedController@filter');
                    Route::get('records', 'ReportOrderNoteConsolidatedController@records');
                    Route::get('totals-by-item', 'ReportOrderNoteConsolidatedController@totalsByItem');
                    Route::get('pdf-totals', 'ReportOrderNoteConsolidatedController@pdfTotals');
                    Route::get('excel-totals', 'ReportOrderNoteConsolidatedController@excelTotals');
                });
                Route::prefix('guides')->group(function () {
                    Route::get('general-items', 'ReportGuideController@index')->name('tenant.reports.guides.index');
                    Route::post('filter', 'ReportGuideController@filter');
                    Route::get('records', 'ReportGuideController@records');
                    Route::get('totals-by-item', 'ReportGuideController@totalsByItem');
                    Route::get('pdf', 'ReportGuideController@pdf');
                    Route::get('pdf-totals', 'ReportGuideController@pdfTotals');
                    Route::get('excel', 'ReportGuideController@excel');
                    Route::get('excel-totals', 'ReportGuideController@excelTotals');
                });
                /**
                 * reports/general-items/
                 * reports/general-items/excel
                 * reports/general-items/pdf
                 * reports/general-items/filter
                 * reports/general-items/records
                 */
                Route::prefix('general-items')->group(function () {
                    Route::get('', 'ReportGeneralItemController@index')->name('tenant.reports.general_items.index');
                    Route::get('/excel', 'ReportGeneralItemController@excel');
                    Route::get('/pdf', 'ReportGeneralItemController@pdf');
                    Route::get('/filter', 'ReportGeneralItemController@filter');
                    Route::get('/records', 'ReportGeneralItemController@records');
                });

                /**
                 *reports/extra-general-items/
                 *reports/extra-general-items/items
                 *reports/extra-general-items/items/excel
                 *reports/extra-general-items/items/filter
                 *reports/extra-general-items/items/records
                 */
                Route::prefix('extra-general-items')->group(function () {
                    Route::get('items', 'ReportItemExtraController@index')->name('tenant.reports.extra.items.index');
                    Route::get('items/excel', 'ReportItemExtraController@excel')->name('tenant.reports.extra.items.excel');
                    Route::get('items/filter', 'ReportItemExtraController@filter')->name('tenant.reports.extra.items.filter');
                    Route::get('records', 'ReportItemExtraController@records')->name('tenant.reports.extra.items.records');
                    Route::get('items/records', 'ReportItemExtraController@records')->name('tenant.reports.extra.items.records');
                    Route::post('items/records', 'ReportItemExtraController@records')->name('tenant.reports.extra.items.records');
                    // /reports/extra-general-items/records
                });

                /**
                 * /reports/state-account
                 * /reports/state-account/pdf
                 * /reports/state-account/filter
                 * /reports/state-account/excel
                 * /reports/state-account/records
                 */
                Route::prefix('state-account')->group(function () {
                    Route::get('', 'ReportStateAccountController@index')
                        ->name('tenant.reports.state_account.index')
                        ->middleware('tenant.internal.mode');
                    Route::get('/pdf', 'ReportStateAccountController@pdf')
                        ->name('tenant.reports.state_account.pdf');
                    Route::get('/excel', 'ReportStateAccountController@excel')
                        ->name('tenant.reports.state_account.excel');
                    Route::get('/filter', 'ReportStateAccountController@filter')
                        ->name('tenant.reports.state_account.filter');
                    Route::get('/records', 'ReportStateAccountController@records')
                        ->name('tenant.reports.state_account.records');
                    Route::get('/pdf-simple', 'ReportStateAccountController@pdfSimple')
                        ->name('tenant.reports.state_account.pdfSimple');
                });

                Route::prefix('reporte_ventas')->group(function () {
                    Route::get('', 'ReporteVentasController@index')
                        ->name('tenant.reports.reporte_ventas.index')
                        ->middleware('tenant.internal.mode');
                    Route::get('/pdf', 'ReporteVentasController@pdf')
                        ->name('tenant.reports.reporte_ventas.pdf');
                    Route::get('/excel', 'ReporteVentasController@excel')
                        ->name('tenant.reports.reporte_ventas.excel');
                    Route::get('/filter', 'ReporteVentasController@filter')
                        ->name('tenant.reports.reporte_ventas.filter');
                    Route::get('/records', 'ReporteVentasController@records')
                        ->name('tenant.reports.reporte_ventas.records');
                    Route::get('/pdf-simple', 'ReporteVentasController@pdfSimple')
                        ->name('tenant.reports.reporte_ventas.pdfSimple');
                    Route::get('/recuperarCategorias', 'ReporteVentasController@recuperarCategorias')
                        ->name('tenant.reports.reporte_ventas.recuperarCategorias');
                });

                Route::prefix('plan_cuentas')->group(function () {
                    Route::get('', 'PlanCuentasController@index')
                        ->name('tenant.plan_cuentas.index')
                        ->middleware('tenant.internal.mode');
                    Route::get('/pdf', 'PlanCuentasController@pdf')
                        ->name('tenant.plan_cuentas.pdf');
                    Route::get('/excel', 'PlanCuentasController@excel')
                        ->name('tenant.plan_cuentas.excel');
                    Route::get('/records', 'PlanCuentasController@records')
                        ->name('tenant.plan_cuentas.records');
                    Route::get('/filter', 'PlanCuentasController@filter')
                        ->name('tenant.plan_cuentas.filter');
                    Route::get('/item/tables', 'PlanCuentasController@item_tables')
                        ->name('tenant.plan_cuentas.item_tables');
                    Route::get('/tables', 'PlanCuentasController@tables')
                        ->name('tenant.plan_cuentas.tables');
                    Route::get('/columns', 'PlanCuentasController@columns');
                    Route::get('/datosSP', 'PlanCuentasController@datosSP');
                });

                Route::get('order-notes-general', 'ReportOrderNoteGeneralController@index')
                    ->name('tenant.reports.order_notes_general.index');

                Route::get('order-notes-general/excel', 'ReportOrderNoteGeneralController@excel');
                Route::get('order-notes-general/pdf', 'ReportOrderNoteGeneralController@pdf');
                Route::get('order-notes-general/filter', 'ReportOrderNoteGeneralController@filter');
                Route::get('order-notes-general/records', 'ReportOrderNoteGeneralController@records');

                Route::get('order-notes-report', 'ReportOrderNoteGeneralController@indexReport')
                    ->name('tenant.reports.order_notes_general.report');

                Route::get('order-notes-report/excel', 'ReportOrderNoteGeneralController@excelReport');
                Route::get('order-notes-report/pdf', 'ReportOrderNoteGeneralController@pdfReport');
                Route::get('order-notes-report/filter', 'ReportOrderNoteGeneralController@filterReport');
                Route::get('order-notes-report/records', 'ReportOrderNoteGeneralController@recordsReport');

                Route::get('sales-consolidated', 'ReportSaleConsolidatedController@index')
                    ->name('tenant.reports.sales_consolidated.index');
                Route::get('sales-consolidated/pdf', 'ReportSaleConsolidatedController@pdf');
                Route::get('sales-consolidated/ticket-totals', 'ReportSaleConsolidatedController@pdfTicketsTotal');
                Route::get('sales-consolidated/ticket80-totals', 'ReportSaleConsolidatedController@pdfTicketsTotal80');
                Route::get('sales-consolidated/excel', 'ReportSaleConsolidatedController@excel');
                Route::get('sales-consolidated/filter', 'ReportSaleConsolidatedController@filter');
                Route::get('sales-consolidated/records', 'ReportSaleConsolidatedController@records');
                Route::get('sales-consolidated/totals-by-item', 'ReportSaleConsolidatedController@totalsByItem');
                Route::get('sales-consolidated/pdf-totals', 'ReportSaleConsolidatedController@pdfTotals');
                Route::get('sales-consolidated/excel-totals', 'ReportSaleConsolidatedController@excelTotals');


                Route::prefix('user-commissions')->group(function () {

                    Route::get('', 'ReportUserCommissionController@index')
                        ->name('tenant.reports.user_commissions.index');
                    Route::get('/pdf', 'ReportUserCommissionController@pdf')
                        ->name('tenant.reports.user_commissions.pdf');
                    Route::get('/excel', 'ReportUserCommissionController@excel')
                        ->name('tenant.reports.user_commissions.excel');
                    Route::get('/filter', 'ReportUserCommissionController@filter')
                        ->name('tenant.reports.user_commissions.filter');
                    Route::get('/records', 'ReportUserCommissionController@records')
                        ->name('tenant.reports.user_commissions.records');
                });

                Route::prefix('commissions-detail')->group(function () {

                    Route::get('', 'ReportCommissionDetailController@index')
                        ->name('tenant.reports.commissions_detail.index');
                    Route::get('/pdf', 'ReportCommissionDetailController@pdf')
                        ->name('tenant.reports.commissions_detail.pdf');
                    Route::get('/excel', 'ReportCommissionDetailController@excel')
                        ->name('tenant.reports.commissions_detail.excel');
                    Route::get('/filter', 'ReportCommissionDetailController@filter')
                        ->name('tenant.reports.commissions_detail.filter');
                    Route::get('/records', 'ReportCommissionDetailController@records')
                        ->name('tenant.reports.commissions_detail.records');
                });


                Route::prefix('fixed-asset-purchases')->group(function () {

                    Route::get('', 'ReportFixedAssetPurchaseController@index')
                        ->name('tenant.reports.fixed-asset-purchases.index');
                    Route::get('pdf', 'ReportFixedAssetPurchaseController@pdf');
                    Route::get('excel', 'ReportFixedAssetPurchaseController@excel');
                    Route::get('filter', 'ReportFixedAssetPurchaseController@filter');
                    Route::get('records', 'ReportFixedAssetPurchaseController@records');
                });

                /**
                 * reports/massive-downloads/
                 * reports/massive-downloads/filter
                 * reports/massive-downloads/pdf
                 * reports/massive-downloads/records
                 */
                Route::prefix('massive-downloads')->group(function () {

                    Route::get('', 'ReportMassiveDownloadController@index')
                        ->name('tenant.reports.massive-downloads.index');
                    Route::get('filter', 'ReportMassiveDownloadController@filter');
                    Route::get('pdf', 'ReportMassiveDownloadController@pdf');
                    Route::get('records', 'ReportMassiveDownloadController@records');
                });


                Route::prefix('download-tray')->group(function () {

                    Route::get('', 'DownloadTrayController@index')->name('tenant.reports.download-tray.index');
                    Route::get('records', 'DownloadTrayController@records');
                    Route::get('download/{id}', 'DownloadTrayController@download');
                });


                Route::prefix('tips')->group(function () {

                    Route::get('', 'ReportTipController@index')->name('tenant.reports.tips.index');
                    Route::get('pdf', 'ReportTipController@pdf');
                    Route::get('excel', 'ReportTipController@excel');
                    Route::get('records', 'ReportTipController@records');
                });
            });

            Route::get('cash/report/income-summary/{cash}', 'ReportIncomeSummaryController@pdf')
                ->name('tenant.reports.income_summary.pdf');
        });
    });
} else {
    $prefix = env('PREFIX_URL', null);
    $prefix = !empty($prefix) ? $prefix . "." : '';
    $app_url = $prefix . env('APP_URL_BASE');

    Route::domain($app_url)->group(function () {
        Route::middleware('auth:admin')->group(function () {

            Route::prefix('reports')->group(function () {

                Route::get('list', 'System\ReportController@listReports')->name('system.list-reports');

                Route::get('clients', 'System\ReportController@clients');

                Route::prefix('report-login-lockout')->group(function () {

                    Route::get('', 'System\ReportLoginLockoutController@index')->name('system.report_login_lockout.index');
                    Route::get('columns', 'System\ReportLoginLockoutController@columns');
                    Route::get('records', 'System\ReportLoginLockoutController@records');
                    // Route::get('report/{type}', 'System\ReportLoginLockoutController@exportReport');

                });
            });

            Route::get('cash/report/income-summary/{cash}', 'ReportIncomeSummaryController@pdf')
                ->name('tenant.reports.income_summary.pdf');
        });
    });
}
