<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Inventory;
use App\Models\Accounting\JournalLine;
use App\Models\Accounting\ChartOfAccount;

// 1. Calculate Physical Inventory Value
$inventoryItems = Inventory::all();
$physicalValue = $inventoryItems->sum(function($item) {
    return $item->current_stock * $item->cost_price;
});

echo "--- Inventory Status ---\n";
echo "Total Items: " . $inventoryItems->count() . "\n";
echo "Physical Value (Stock * Cost): " . number_format($physicalValue, 2) . "\n";

// 2. Calculate Financial Inventory Value (GL)
$inventoryAccount = ChartOfAccount::where('type', 'asset')
    ->where(function($q) {
        $q->where('name', 'like', '%Inventory%')
          ->orWhere('code', '1200'); // Standard code for inventory
    })->first();

$financialValue = 0;
if ($inventoryAccount) {
    $financialValue = JournalLine::where('account_id', $inventoryAccount->id)
        ->get()
        ->sum(function($line) {
            return $line->type === 'debit' ? $line->amount : -$line->amount;
        });
    echo "GL Account: {$inventoryAccount->name} ({$inventoryAccount->code})\n";
} else {
    echo "GL Account: NOT FOUND\n";
}

echo "Financial Value (GL Balance): " . number_format($financialValue, 2) . "\n";
echo "Discrepancy: " . number_format($physicalValue - $financialValue, 2) . "\n";
