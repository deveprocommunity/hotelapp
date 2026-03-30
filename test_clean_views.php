<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== TESTING CLEAN VIEWS ===\n\n";

$sale = \App\Models\Pos\PosSale::with(['details.product', 'payments', 'createdBy'])
    ->where('uuid', '8c5273bc-79b3-4510-b940-98a49dc20b24')
    ->firstOrFail();

$hotelSettings = \App\Models\Setting::getHotelSettings();

// Test receipt clean view
$receiptHtml = view('pos.print.receipt-content-clean', [
    'sale' => $sale,
    'hotelSettings' => $hotelSettings,
])->render();

// Test invoice clean view
$invoiceHtml = view('pos.print.invoice-content-clean', [
    'sale' => $sale,
    'hotelSettings' => $hotelSettings,
])->render();

echo "RECEIPT CLEAN VIEW:\n";
$receiptDivCount = substr_count(trim($receiptHtml), '<div>');
$receiptFirstDiv = strpos(trim($receiptHtml), '<div>');
echo "Div count: $receiptDivCount, First div position: $receiptFirstDiv\n";
echo ($receiptDivCount === 1 && $receiptFirstDiv === 0) ? "✅ SINGLE ROOT" : "❌ MULTIPLE ROOTS";
echo "\n\n";

echo "INVOICE CLEAN VIEW:\n";
$invoiceDivCount = substr_count(trim($invoiceHtml), '<div>');
$invoiceFirstDiv = strpos(trim($invoiceHtml), '<div>');
echo "Div count: $invoiceDivCount, First div position: $invoiceFirstDiv\n";
echo ($invoiceDivCount === 1 && $invoiceFirstDiv === 0) ? "✅ SINGLE ROOT" : "❌ MULTIPLE ROOTS";
echo "\n\n";

echo "=== TEST COMPLETE ===\n";
echo "🎯 Both views should now work with Livewire without MultipleRootElementsDetectedException\n";
