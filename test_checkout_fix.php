<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== CHECKOUT FIX VERIFICATION ===\n\n";

// Test 1: Check model fillable fields
echo "1. MODEL FILLABLE VERIFICATION:\n";

// PosSale
$saleModel = new \App\Models\Pos\PosSale();
echo "   PosSale fillable: " . implode(', ', $saleModel->getFillable()) . "\n";

// PosSaleDetail  
$detailModel = new \App\Models\Pos\PosSaleDetail();
echo "   PosSaleDetail fillable: " . implode(', ', $detailModel->getFillable()) . "\n";

// PosSalePayment
$paymentModel = new \App\Models\Pos\PosSalePayment();
echo "   PosSalePayment fillable: " . implode(', ', $paymentModel->getFillable()) . "\n";

// Test 2: Test checkout service with auth context
echo "\n2. CHECKOUT SERVICE TEST:\n";
try {
    // Authenticate user
    $user = \App\Models\User::find(1);
    if ($user) {
        \Auth::login($user);
        echo "   ✅ Authenticated user: {$user->name} (ID: {$user->id})\n";
    } else {
        echo "   ❌ User ID 1 not found\n";
        exit;
    }
    
    // Create a test cart with items
    $cart = \App\Modules\POS\Models\PosCart::openOrCreate($user->id);
    echo "   ✅ Test cart created: UUID = {$cart->uuid}\n";
    
    // Add a test item to cart
    $product = \App\Models\Pos\Product::first();
    if ($product) {
        $cartService = new \App\Modules\POS\Services\CartService();
        $cartService->addItem($cart->uuid, $product->id, 1);
        echo "   ✅ Added product to cart: {$product->name}\n";
        
        // Reload cart to confirm item
        $cart->load('items.product');
        echo "   ✅ Cart items count: {$cart->items->count()}\n";
        
        // Test checkout
        echo "   🔄 Testing checkout...\n";
        $checkoutService = new \App\Modules\POS\Services\CheckoutService();
        $sale = $checkoutService->checkout($cart->uuid, 'cash');
        
        echo "   ✅ Checkout successful!\n";
        echo "   ✅ Sale ID: {$sale->id}\n";
        echo "   ✅ Sale UUID: {$sale->uuid}\n";
        echo "   ✅ Sale Total: {$sale->total}\n";
        echo "   ✅ Sale Status: {$sale->status}\n";
        
        // Check sale details
        $detailsCount = $sale->details()->count();
        echo "   ✅ Sale details count: {$detailsCount}\n";
        
        // Check sale payment
        $payment = $sale->payments()->first();
        if ($payment) {
            echo "   ✅ Payment method: {$payment->method}\n";
            echo "   ✅ Payment amount: {$payment->amount}\n";
        }
        
        // Check cart status
        $updatedCart = \App\Modules\POS\Models\PosCart::find($cart->id);
        echo "   ✅ Cart status: {$updatedCart->status}\n";
        echo "   ✅ Cart items remaining: {$updatedCart->items()->count()}\n";
        
    } else {
        echo "   ❌ No products found\n";
    }
    
} catch (\Exception $e) {
    echo "   ❌ Checkout failed: " . $e->getMessage() . "\n";
    echo "   ❌ Stack trace: " . $e->getTraceAsString() . "\n";
}

// Test 3: Check database records
echo "\n3. DATABASE VERIFICATION:\n";
try {
    $salesCount = \App\Models\Pos\PosSale::count();
    echo "   Total pos_sales records: {$salesCount}\n";
    
    $detailsCount = \App\Models\Pos\PosSaleDetail::count();
    echo "   Total pos_sale_details records: {$detailsCount}\n";
    
    $paymentsCount = \App\Models\Pos\PosSalePayment::count();
    echo "   Total pos_sale_payments records: {$paymentsCount}\n";
    
    // Check latest sale
    $latestSale = \App\Models\Pos\PosSale::latest()->first();
    if ($latestSale) {
        echo "   Latest sale: ID {$latestSale->id}, Total {$latestSale->total}, Status {$latestSale->status}\n";
    }
    
} catch (\Exception $e) {
    echo "   ❌ Database check failed: " . $e->getMessage() . "\n";
}

echo "\n=== VERIFICATION COMPLETE ===\n";
