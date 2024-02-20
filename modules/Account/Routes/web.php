<?php

$hostname = app(Hyn\Tenancy\Contracts\CurrentHostname::class);

if($hostname) {
    Route::domain($hostname->fqdn)->group(function () {
        Route::middleware(['auth', 'redirect.module', 'locked.tenant'])->group(function() {

            /**
             * account/
             * account/download
             * account/format
             * account/format/download
             * account/summary-report
             * account/summary-report/records
             * account/summary-report/format/download
             */
            Route::prefix('account')->group(function () {
                Route::get('/', 'AccountController@index')->name('tenant.account.index');
                Route::get('download', 'AccountController@download');
                Route::get('format', 'FormatController@index')->name('tenant.account_format.index');
                Route::get('format/download', 'FormatController@download');


                Route::get('summary-report', 'SummaryReportController@index')->name('tenant.account_summary_report.index');
                Route::get('summary-report/records', 'SummaryReportController@records');
                Route::get('summary-report/format/download', 'SummaryReportController@download');

            });

            Route::prefix('company_accounts')->group(function () {
                Route::get('create', 'CompanyAccountController@create')->name('tenant.company_accounts.create')->middleware('redirect.level');
                Route::get('record', 'CompanyAccountController@record');
                Route::post('', 'CompanyAccountController@store');
            });

            Route::prefix('accounting_ledger')->group(function () {
                Route::get('/', 'LedgerAccountController@index')->name('tenant.accounting_ledger.create');
                // accounting_ledger?date_end=2021-10-24&date_start=2021-10-24&month_end=2021-10&month_start=2021-10&period=month
                Route::get('/excel/', 'LedgerAccountController@excel');
                //->middleware('redirect.level')
                Route::post('record', 'LedgerAccountController@record');
            });

            Route::prefix('accounting_reconciliation')->group(function () {
                Route::get('/', 'ReconciliationController@index')->name('tenant.accounting_reconciliation.create');
                Route::get('records', 'ReconciliationController@records');
                Route::get('columns', 'ReconciliationController@columns');
                Route::get('reconciliate/{id}', 'ReconciliationController@reconciliate');
                Route::get('excel', 'ReconciliationController@excel');
            });
            Route::prefix('bank_reconciliation')->group(function () {
                Route::get('/', 'BankReconciliationController@index')->name('tenant.bank_reconciliation.index');
                Route::get('records', 'BankReconciliationController@records');
                Route::get('columns', 'BankReconciliationController@columns');
                Route::get('reconciliate/{id}', 'BankReconciliationController@reconciliate');
                Route::get('excel', 'BankReconciliationController@excel');
            });

            Route::prefix('accounting_audit')->group(function () {
                Route::get('/', 'AuditController@index')->name('tenant.accounting_audit.create');
                Route::get('records', 'AuditController@records');
                Route::get('columns', 'AuditController@columns');
                Route::get('audit/{id}', 'AuditController@audit');
            });

            Route::prefix('cost_centers')->group(function () {
                Route::get('', 'CostCenterController@index')->name('tenant.cost_centers.create');
                Route::post('', 'CostCenterController@store');
                Route::get('records', 'CostCenterController@records');
                Route::get('record/{id}', 'CostCenterController@record');
                Route::get('columns', 'CostCenterController@columns');
                Route::get('level/{level}/{id}', 'CostCenterController@levelRecords');
                Route::delete('{task}', 'CostCenterController@destroy');
            });


        });
    });
}
else {

    $prefix = env('PREFIX_URL',null);
    $prefix = !empty($prefix)?$prefix.".":'';
    $app_url = $prefix. env('APP_URL_BASE');

    Route::domain($app_url)->group(function () {

        Route::middleware('auth:admin')->group(function() {

            Route::prefix('accounting')->group(function () {

                Route::get('', 'System\AccountingController@index')->name('system.accounting.index');
                Route::get('records', 'System\AccountingController@records');
                Route::get('download', 'System\AccountingController@download');

            });


        });
    });

}

