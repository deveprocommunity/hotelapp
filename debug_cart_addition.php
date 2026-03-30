<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== CART ADDITION DEBUG ===\n\n";

// Test 1: Check if products exist
echo "1. PRODUCT VERIFICATION:\n";
$products = \App\Models\Pos\Product::limit(3)->get();
foreach ($products as $product) {
    echo "   Product ID: {$product->id}, Name: {$product->name}, Price: {$product->price}\n";
}

if ($products->isEmpty()) {
    echo "   ❌ NO PRODUCTS FOUND IN DATABASE\n";
}

// Test 2: Create a test cart
echo "\n2. CART CREATION TEST:\n";
try {
    $testUserId = 1; // Assuming user ID 1 exists
    $cart = \App\Modules\POS\Models\PosCart::openOrCreate($testUserId);
    echo "   ✅ Cart created: UUID = {$cart->uuid}, ID = {$cart->id}\n";
    
    // Test 3: Try to add an item
    echo "\n3. ITEM ADDITION TEST:\n";
    if ($products->isNotEmpty()) {
        $testProduct = $products->first();
        echo "   Attempting to add product ID: {$testProduct->id}\n";
        
        $cartService = new \App\Modules\POS\Services\CartService();
        
        // Check cart items before
        $itemsBefore = $cart->items()->count();
        echo "   Items before: {$itemsBefore}\n";
        
        try {
            $updatedCart = $cartService->addItem($cart->uuid, $testProduct->id, 1);
            echo "   ✅ addItem() completed\n";
            
            // Check cart items after
            $itemsAfter = $updatedCart->items()->count();
            echo "   Items after: {$itemsAfter}\n";
            
            // Check the actual cart item
            $cartItem = \App\Modules\POS\Models\PosCartItem::where('cart_id', $cart->id)
                ->where('product_id', $testProduct->id)
                ->first();
                
            if ($cartItem) {
                echo "   ✅ Cart item created: Qty = {$cartItem->qty}, Price = {$cartItem->price}, Total = {$cartItem->line_total}\n";
            } else {
                echo "   ❌ Cart item NOT found in database\n";
            }
            
            // Test cart total
            $total = $updatedCart->total();
            echo "   Cart total: {$total}\n";
            
        } catch (\Exception $e) {
            echo "   ❌ addItem() failed: " . $e->getMessage() . "\n";
            echo "   Stack trace: " . $e->getTraceAsString() . "\n";
        }
    }
    
} catch (\Exception $e) {
    echo "   ❌ Cart creation failed: " . $e->getMessage() . "\n";
}

// Test 4: Check database tables and columns
echo "\n4. DATABASE SCHEMA CHECK:\n";
try {
    // Check pos_carts table
    $cartColumns = \Illuminate\Support\Facades\Schema::getColumnListing('pos_carts');
    echo "   pos_carts columns: " . implode(', ', $cartColumns) . "\n";
    
    // Check pos_cart_items table  
    $itemColumns = \Illuminate\Support\Facades\Schema::getColumnListing('pos_cart_items');
    echo "   pos_cart_items columns: " . implode(', ', $itemColumns) . "\n";
    
    // Check products table
    $productColumns = \Illuminate\Support\Facades\Schema::getColumnListing('products');
    echo "   products columns: " . implode(', ', $productColumns) . "\n";
    
} catch (\Exception $e) {
    echo "   ❌ Schema check failed: " . $e->getMessage() . "\n";
}

// Test 5: Check Livewire component mounting
echo "\n5. LIVEWIRE COMPONENT TEST:\n";
try {
    // Simulate ProductGrid component
    $productGrid = new \App\Livewire\Pos\ProductGrid();
    
    // Set a test cart UUID
    $testCart = \App\Modules\POS\Models\PosCart::openOrCreate(1);
    $productGrid->cartUuid = $testCart->uuid;
    
    echo "   ProductGrid cartUuid: {$productGrid->cartUuid}\n";
    echo "   ProductGrid posAccessible: " . ($productGrid->posAccessible ? 'true' : 'false') . "\n";
    
    // Test the add method directly
    if ($products->isNotEmpty()) {
        $testProduct = $products->first();
        echo "   Testing add() with product ID: {$testProduct->id}\n";
        
        $productGrid->add($testProduct->id);
        echo "   ✅ add() method completed\n";
    }
    
} catch (\Exception $e) {
    echo "   ❌ Livewire test failed: " . $e->getMessage() . "\n";
    echo "   Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== DEBUG COMPLETE ===\n";
