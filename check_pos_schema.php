<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== DATABASE SCHEMA CHECK ===\n\n";

// Check pos_sales table
echo "1. pos_sales table columns:\n";
$columns = \Illuminate\Support\Facades\Schema::getColumnListing('pos_sales');
foreach ($columns as $column) {
    echo "   - {$column}\n";
}

// Check pos_sale_details table
echo "\n2. pos_sale_details table columns:\n";
$columns = \Illuminate\Support\Facades\Schema::getColumnListing('pos_sale_details');
foreach ($columns as $column) {
    echo "   - {$column}\n";
}

// Check pos_sale_payments table
echo "\n3. pos_sale_payments table columns:\n";
$columns = \Illuminate\Support\Facades\Schema::getColumnListing('pos_sale_payments');
foreach ($columns as $column) {
    echo "   - {$column}\n";
}

echo "\n=== SCHEMA CHECK COMPLETE ===\n";
