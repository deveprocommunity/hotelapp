<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== POS PRINT FUNCTIONALITY TEST ===\n\n";

// Test 1: Check if routes exist
echo "1. ROUTE VERIFICATION:\n";
try {
    $routes = app('router')->getRoutes();
    $receiptRoute = null;
    $invoiceRoute = null;
    
    foreach ($routes as $route) {
        if ($route->getName() === 'pos.receipt') {
            $receiptRoute = $route;
        }
        if ($route->getName() === 'pos.invoice') {
            $invoiceRoute = $route;
        }
    }
    
    if ($receiptRoute) {
        echo "   ✅ Receipt route exists: " . $receiptRoute->uri() . "\n";
    } else {
        echo "   ❌ Receipt route missing\n";
    }
    
    if ($invoiceRoute) {
        echo "   ✅ Invoice route exists: " . $invoiceRoute->uri() . "\n";
    } else {
        echo "   ❌ Invoice route missing\n";
    }
    
} catch (\Exception $e) {
    echo "   ❌ Route check failed: " . $e->getMessage() . "\n";
}

// Test 2: Check if Livewire components exist
echo "\n2. LIVEWIRE COMPONENTS VERIFICATION:\n";
try {
    $receiptComponent = class_exists('App\\Livewire\\Pos\\Receipt');
    $invoiceComponent = class_exists('App\\Livewire\\Pos\\Invoice');
    
    if ($receiptComponent) {
        echo "   ✅ Receipt Livewire component exists\n";
    } else {
        echo "   ❌ Receipt Livewire component missing\n";
    }
    
    if ($invoiceComponent) {
        echo "   ✅ Invoice Livewire component exists\n";
    } else {
        echo "   ❌ Invoice Livewire component missing\n";
    }
    
} catch (\Exception $e) {
    echo "   ❌ Component check failed: " . $e->getMessage() . "\n";
}

// Test 3: Check if print views exist
echo "\n3. PRINT VIEWS VERIFICATION:\n";
try {
    $receiptView = view()->exists('pos.print.receipt');
    $invoiceView = view()->exists('pos.print.invoice');
    $printLayout = view()->exists('layouts.print');
    
    if ($receiptView) {
        echo "   ✅ Receipt view exists: pos.print.receipt\n";
    } else {
        echo "   ❌ Receipt view missing\n";
    }
    
    if ($invoiceView) {
        echo "   ✅ Invoice view exists: pos.print.invoice\n";
    } else {
        echo "   ❌ Invoice view missing\n";
    }
    
    if ($printLayout) {
        echo "   ✅ Print layout exists: layouts.print\n";
    } else {
        echo "   ❌ Print layout missing\n";
    }
    
} catch (\Exception $e) {
    echo "   ❌ View check failed: " . $e->getMessage() . "\n";
}

// Test 4: Check if we have test sales data
echo "\n4. SALES DATA VERIFICATION:\n";
try {
    $salesCount = \App\Models\Pos\PosSale::count();
    echo "   Total sales in database: {$salesCount}\n";
    
    if ($salesCount > 0) {
        $latestSale = \App\Models\Pos\PosSale::latest()->first();
        echo "   Latest sale UUID: " . $latestSale->uuid . "\n";
        echo "   Latest sale ID: " . $latestSale->id . "\n";
        echo "   Test receipt URL: /pos/receipt/" . $latestSale->uuid . "\n";
        echo "   Test invoice URL: /pos/invoice/" . $latestSale->uuid . "\n";
    } else {
        echo "   ⚠️  No sales found - need to create a test sale first\n";
    }
    
} catch (\Exception $e) {
    echo "   ❌ Sales data check failed: " . $e->getMessage() . "\n";
}

echo "\n=== PRINT FUNCTIONALITY TEST COMPLETE ===\n";
