<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== DEBUG ROOT ELEMENTS ===\n\n";

$sale = \App\Models\Pos\PosSale::with(['details.product', 'payments', 'createdBy'])
    ->where('uuid', '8c5273bc-79b3-4510-b940-98a49dc20b24')
    ->firstOrFail();

$hotelSettings = \App\Models\Setting::getHotelSettings();

$receiptHtml = view('pos.print.receipt-content', [
    'sale' => $sale,
    'hotelSettings' => $hotelSettings,
])->render();

echo "RECEIPT HTML (first 200 chars):\n";
echo substr($receiptHtml, 0, 200) . "...\n\n";

echo "TRIMMED HTML:\n";
echo "'" . trim($receiptHtml) . "'\n\n";

$divCount = substr_count(trim($receiptHtml), '<div>');
echo "Number of <div> tags: $divCount\n";

$firstDivPos = strpos(trim($receiptHtml), '<div>');
echo "First <div> position: $firstDivPos\n";

if ($divCount === 1 && $firstDivPos === 0) {
    echo "✅ SINGLE ROOT ELEMENT DETECTED\n";
} else {
    echo "❌ MULTIPLE ROOT ELEMENTS DETECTED\n";
    echo "This will cause: MultipleRootElementsDetectedException\n";
}

echo "\n=== DEBUG COMPLETE ===\n";
