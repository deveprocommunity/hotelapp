<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== STATUS COLUMN CHECK ===\n\n";

// Check existing sales to see what status values are used
echo "1. Existing pos_sales records:\n";
$sales = \App\Models\Pos\PosSale::limit(5)->get();
if ($sales->isEmpty()) {
    echo "   No existing sales found\n";
} else {
    foreach ($sales as $sale) {
        echo "   ID: {$sale->id}, Status: '{$sale->status}', Type: '{$sale->sale_type}'\n";
    }
}

// Check column definition
echo "\n2. Status column info:\n";
try {
    $columnInfo = \Illuminate\Support\Facades\DB::select("SHOW COLUMNS FROM pos_sales WHERE Field = 'status'");
    if (!empty($columnInfo)) {
        $column = $columnInfo[0];
        echo "   Type: {$column->Type}\n";
        echo "   Null: {$column->Null}\n";
        echo "   Default: {$column->Default}\n";
    }
} catch (\Exception $e) {
    echo "   Error: " . $e->getMessage() . "\n";
}

echo "\n=== STATUS CHECK COMPLETE ===\n";
