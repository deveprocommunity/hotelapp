<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== POS SYSTEM VERIFICATION ===\n\n";

// Test 1: Check PosCart model and namespace
echo "1. POSCART MODEL VERIFICATION:\n";
try {
    $cart = new \App\Modules\POS\Models\PosCart();
    echo "   ✅ PosCart model loads correctly\n";
    echo "   ✅ Namespace: " . get_class($cart) . "\n";
    
    // Test total() method
    $testCart = new \App\Modules\POS\Models\PosCart(['id' => 1]);
    $testCart->setRelation('items', collect());
    $total = $testCart->total();
    echo "   ✅ total() method works: {$total}\n";
    
} catch (\Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}

// Test 2: Check CartService
echo "\n2. CART SERVICE VERIFICATION:\n";
try {
    $cartService = new \App\Modules\POS\Services\CartService();
    echo "   ✅ CartService loads correctly\n";
    
    // Test addItem method signature
    $reflection = new ReflectionClass($cartService);
    $addItemMethod = $reflection->getMethod('addItem');
    echo "   ✅ addItem method exists\n";
    echo "   ✅ addItem parameters: " . implode(', ', array_map(fn($p) => $p->getName(), $addItemMethod->getParameters())) . "\n";
    
} catch (\Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}

// Test 3: Check ProductGrid component
echo "\n3. PRODUCT GRID VERIFICATION:\n";
try {
    $productGrid = new \App\Livewire\Pos\ProductGrid();
    echo "   ✅ ProductGrid component loads correctly\n";
    echo "   ✅ cartUuid property: " . (property_exists($productGrid, 'cartUuid') ? 'exists' : 'missing') . "\n";
    
} catch (\Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}

// Test 4: Check Terminal component
echo "\n4. TERMINAL COMPONENT VERIFICATION:\n";
try {
    $terminal = new \App\Livewire\Pos\Terminal();
    echo "   ✅ Terminal component loads correctly\n";
    echo "   ✅ cartUuid property: " . (property_exists($terminal, 'cartUuid') ? 'exists' : 'missing') . "\n";
    echo "   ✅ posAccessible property: " . (property_exists($terminal, 'posAccessible') ? 'exists' : 'missing') . "\n";
    
} catch (\Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}

// Test 5: Check for duplicate PosCart files
echo "\n5. DUPLICATE FILE CHECK:\n";
$canonicalPath = 'c:\laragon\www\hotelapp\app\Modules\POS\Models\PosCart.php';
$oldPath = 'c:\laragon\www\hotelapp\app\Models\Pos\PosCart.php';

if (file_exists($canonicalPath)) {
    echo "   ✅ Canonical PosCart exists: {$canonicalPath}\n";
} else {
    echo "   ❌ Canonical PosCart missing: {$canonicalPath}\n";
}

if (file_exists($oldPath)) {
    echo "   ❌ Duplicate PosCart still exists: {$oldPath}\n";
} else {
    echo "   ✅ Duplicate PosCart removed: {$oldPath}\n";
}

// Test 6: Check database connection and tables
echo "\n6. DATABASE VERIFICATION:\n";
try {
    // Test pos_carts table
    $cartCount = \Illuminate\Support\Facades\DB::table('pos_carts')->count();
    echo "   ✅ pos_carts table accessible ({$cartCount} records)\n";
    
    // Test pos_cart_items table  
    $itemCount = \Illuminate\Support\Facades\DB::table('pos_cart_items')->count();
    echo "   ✅ pos_cart_items table accessible ({$itemCount} records)\n";
    
    // Test products table
    $productCount = \Illuminate\Support\Facades\DB::table('products')->count();
    echo "   ✅ products table accessible ({$productCount} records)\n";
    
} catch (\Exception $e) {
    echo "   ❌ Database error: " . $e->getMessage() . "\n";
}

echo "\n=== VERIFICATION COMPLETE ===\n";
