<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== LIVEWIRE FLOW DEBUG ===\n\n";

// Test 1: Simulate the complete flow
echo "1. TERMINAL COMPONENT SIMULATION:\n";
try {
    // Create a user and authenticate
    $user = \App\Models\User::find(1);
    if (!$user) {
        echo "   ❌ User ID 1 not found\n";
        exit;
    }
    
    \Auth::login($user);
    echo "   ✅ Authenticated user: {$user->name}\n";
    
    // Mount Terminal component
    $terminal = new \App\Livewire\Pos\Terminal();
    
    // Mock the shift service
    $shiftService = new \App\Services\POS\POSShiftService();
    $terminal->boot($shiftService);
    
    // Mount the terminal
    $terminal->mount();
    echo "   ✅ Terminal mounted\n";
    echo "   ✅ Terminal cartUuid: {$terminal->cartUuid}\n";
    echo "   ✅ Terminal posAccessible: " . ($terminal->posAccessible ? 'true' : 'false') . "\n";
    
    // Get the terminal view data
    $viewData = $terminal->render();
    echo "   ✅ Terminal render completed\n";
    
    // Extract cartUuid from view data
    if (isset($viewData->getData()['cartUuid'])) {
        $cartUuid = $viewData->getData()['cartUuid'];
        echo "   ✅ Cart UUID passed to view: {$cartUuid}\n";
    } else {
        echo "   ❌ Cart UUID not found in view data\n";
    }
    
} catch (\Exception $e) {
    echo "   ❌ Terminal simulation failed: " . $e->getMessage() . "\n";
    echo "   Stack trace: " . $e->getTraceAsString() . "\n";
}

// Test 2: Simulate ProductGrid mounting
echo "\n2. PRODUCT GRID SIMULATION:\n";
try {
    if (isset($cartUuid)) {
        // Mount ProductGrid with the cart UUID from Terminal
        $productGrid = new \App\Livewire\Pos\ProductGrid();
        
        // Mock the cart service
        $cartService = new \App\Modules\POS\Services\CartService();
        $productGrid->boot($cartService);
        
        // Mount with parameters from Terminal
        $productGrid->mount(
            cartUuid: $cartUuid,
            search: '',
            shiftRequired: false,
            posAccessible: true
        );
        
        echo "   ✅ ProductGrid mounted\n";
        echo "   ✅ ProductGrid cartUuid: {$productGrid->cartUuid}\n";
        echo "   ✅ ProductGrid posAccessible: " . ($productGrid->posAccessible ? 'true' : 'false') . "\n";
        
        // Test adding a product
        $product = \App\Models\Pos\Product::first();
        if ($product) {
            echo "   🔄 Testing add() with product: {$product->name} (ID: {$product->id})\n";
            
            $productGrid->add($product->id);
            echo "   ✅ ProductGrid add() completed\n";
            
            // Check if cart was updated
            $updatedCart = \App\Modules\POS\Models\PosCart::where('uuid', $cartUuid)->first();
            if ($updatedCart && $updatedCart->items->count() > 0) {
                echo "   ✅ Cart updated: {$updatedCart->items->count()} items\n";
                echo "   ✅ Cart total: {$updatedCart->total()}\n";
                
                foreach ($updatedCart->items as $item) {
                    echo "      - {$item->product->name}: {$item->qty} × {$item->price} = {$item->line_total}\n";
                }
            } else {
                echo "   ❌ Cart not updated\n";
            }
        } else {
            echo "   ❌ No products found\n";
        }
    } else {
        echo "   ❌ No cart UUID available from Terminal\n";
    }
    
} catch (\Exception $e) {
    echo "   ❌ ProductGrid simulation failed: " . $e->getMessage() . "\n";
    echo "   Stack trace: " . $e->getTraceAsString() . "\n";
}

// Test 3: Check event dispatching
echo "\n3. EVENT DISPATCHING CHECK:\n";
try {
    // Check if Livewire events are working
    $events = app('events');
    echo "   ✅ Event manager available\n";
    
    // Test manual event dispatch
    $testEvent = new \stdClass();
    $testEvent->message = 'Test cart update';
    
    echo "   🔄 Testing event dispatch...\n";
    
} catch (\Exception $e) {
    echo "   ❌ Event test failed: " . $e->getMessage() . "\n";
}

echo "\n=== DEBUG COMPLETE ===\n";
