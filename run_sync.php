<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\Accounting\InventoryReconciliationService;

try {
    echo "Starting Inventory Sync...\n";
    $service = app(InventoryReconciliationService::class);
    $result = $service->syncOpeningBalances();
    echo "Sync Completion Result:\n";
    print_r($result);
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
