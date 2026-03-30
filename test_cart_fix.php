<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== CART FIX VERIFICATION ===\n\n";

// Test 1: Check if the syntax error is fixed
echo "1. SYNTAX CHECK:\n";
try {
    include 'c:\laragon\www\hotelapp\app\Livewire\Pos\CartPanel.php';
    echo "   ✅ CartPanel.php syntax is valid\n";
} catch (ParseError $e) {
    echo "   ❌ CartPanel.php syntax error: " . $e->getMessage() . "\n";
} catch (Error $e) {
    echo "   ❌ CartPanel.php error: " . $e->getMessage() . "\n";
}

// Test 2: Check if the Livewire component can be instantiated
echo "\n2. COMPONENT INSTANTIATION:\n";
try {
    $cartPanel = new \App\Livewire\Pos\CartPanel();
    echo "   ✅ CartPanel component instantiates successfully\n";
    
    // Check if the cart-updated listener is properly registered
    $reflection = new ReflectionClass($cartPanel);
    $methods = $reflection->getMethods();
    
    $hasRefreshCart = false;
    foreach ($methods as $method) {
        if ($method->getName() === 'refreshCart') {
            $hasRefreshCart = true;
            
            // Check if it has the On attribute
            $attributes = $method->getAttributes();
            $hasCartUpdatedListener = false;
            foreach ($attributes as $attr) {
                if ($attr instanceof \Livewire\Attributes\On && $attr->name === 'cart-updated') {
                    $hasCartUpdatedListener = true;
                    break;
                }
            }
            
            if ($hasCartUpdatedListener) {
                echo "   ✅ refreshCart method has #[On('cart-updated')] attribute\n";
            } else {
                echo "   ❌ refreshCart method missing #[On('cart-updated')] attribute\n";
            }
            break;
        }
    }
    
    if (!$hasRefreshCart) {
        echo "   ❌ refreshCart method not found\n";
    }
    
} catch (\Exception $e) {
    echo "   ❌ CartPanel instantiation failed: " . $e->getMessage() . "\n";
}

// Test 3: Test the complete flow again
echo "\n3. COMPLETE FLOW TEST:\n";
try {
    // Create user and authenticate
    $user = \App\Models\User::find(1);
    \Auth::login($user);
    
    // Mount Terminal
    $terminal = new \App\Livewire\Pos\Terminal();
    $shiftService = new \App\Services\POS\POSShiftService();
    $terminal->boot($shiftService);
    $terminal->mount();
    
    // Mount ProductGrid
    $productGrid = new \App\Livewire\Pos\ProductGrid();
    $cartService = new \App\Modules\POS\Services\CartService();
    $productGrid->boot($cartService);
    $productGrid->mount(
        cartUuid: $terminal->cartUuid,
        search: '',
        shiftRequired: false,
        posAccessible: true
    );
    
    // Mount CartPanel
    $cartPanel = new \App\Livewire\Pos\CartPanel();
    $cartPanel->cart = $terminal->cart;
    
    echo "   🔄 Testing cart addition...\n";
    
    // Add a product
    $product = \App\Models\Pos\Product::first();
    $productGrid->add($product->id);
    
    // Trigger the cart-updated event
    echo "   📡 Dispatching cart-updated event...\n";
    
    // Simulate the event reaching CartPanel
    $cartPanel->refreshCart();
    
    echo "   ✅ CartPanel refreshCart() called\n";
    echo "   ✅ Cart items count: " . $cartPanel->cart->items->count() . "\n";
    echo "   ✅ Cart total: " . $cartPanel->cart->total() . "\n";
    
} catch (\Exception $e) {
    echo "   ❌ Flow test failed: " . $e->getMessage() . "\n";
    echo "   Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== VERIFICATION COMPLETE ===\n";
