<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tables = [
    'accounting_chart_of_accounts',
    'accounting_journal_entries',
    'accounting_journal_lines',
    'accounting_settings',
    'tenants',
];

foreach ($tables as $table) {
    if (!Illuminate\Support\Facades\Schema::hasTable($table)) {
        echo "Missing table: {$table}\n";
        continue;
    }

    $columns = Illuminate\Support\Facades\Schema::getColumnListing($table);
    echo "Columns in {$table}:\n";
    print_r($columns);
    echo "\n";
}
