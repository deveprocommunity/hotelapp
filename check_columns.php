<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;

$tables = [
    'accounting_journal_entries',
    'accounting_journal_lines',
    'accounting_chart_of_accounts',
    'accounting_periods',
    'fiscal_periods',
    'accounting_ledgers',
    'period_approvals',
    'z_reports',
    'pos_sales',
    'reservations',
    'guests',
    'rooms',
    'invoices',
    'accounting_transactions'
];

foreach ($tables as $table) {
    if (Schema::hasTable($table)) {
        echo "$table: " . implode(', ', Schema::getColumnListing($table)) . "\n\n";
    } else {
        echo "$table: MISSING\n\n";
    }
}
