<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== TESTING SINGLE DIV VIEW ===\n\n";

$sale = \App\Models\Pos\PosSale::with(['details.product', 'payments', 'createdBy'])
    ->where('uuid', '8c5273bc-79b3-4510-b940-98a49dc20b24')
    ->firstOrFail();

$hotelSettings = \App\Models\Setting::getHotelSettings();

$receiptHtml = view('pos.print.receipt-single', [
    'sale' => $sale,
    'hotelSettings' => $hotelSettings,
])->render();

echo "SINGLE DIV RECEIPT:\n";
$divCount = substr_count($receiptHtml, '<div>');
$firstDivPos = strpos($receiptHtml, '<div>');
echo "Div count: $divCount\n";
echo "First div position: $firstDivPos\n";
echo ($divCount === 1 && $firstDivPos === 0) ? "✅ SHOULD WORK" : "❌ WILL FAIL";

echo "\n=== TEST COMPLETE ===\n";
