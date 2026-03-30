<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Modules\POS\Models\PosCart;
use App\Models\Pos\Product;
use App\Models\PosSale;
use Illuminate\Support\Str;

echo "Creating test sale for post-to-room lock verification...\n\n";

// Create a new cart
$cart = PosCart::create([
    'uuid' => Str::uuid(),
    'status' => 'open',
    'created_by' => 1
]);

// Get a product (assuming product ID 1 exists)
$product = Product::find(1);
if (!$product) {
    echo "No product found with ID 1. Please ensure you have products in pos_products table.\n";
    exit;
}

// Add item to cart
$cart->items()->create([
    'product_id' => $product->id,
    'qty' => 2,
    'price' => 50.00,
    'line_total' => 100.00
]);

echo "Cart created with UUID: " . $cart->uuid . "\n";
echo "Added 2 x " . $product->name . " at \$50 each = \$100 total\n\n";

// Now checkout the cart to create a sale
use App\Modules\POS\Services\CheckoutService;

$checkoutService = new CheckoutService();
$sale = $checkoutService->checkout($cart->uuid);

echo "Sale created with UUID: " . $sale->uuid . "\n";
echo "Sale total: \$" . number_format($sale->total, 2) . "\n";
echo "Sale balance: \$" . number_format($sale->balance, 2) . "\n";
echo "Sale status: " . $sale->status . "\n\n";

echo "You can now use this sale UUID for post-to-room testing:\n";
echo "SALE_UUID: " . $sale->uuid . "\n";
