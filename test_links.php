<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== TESTING ACCOUNTING LINKS ===\n\n";

// Test the problematic URLs
$testUrls = [
    'http://hotelapp.test/accounting/receivables',
    'http://hotelapp.test/accounting/ledger/Current Assets',
    'http://hotelapp.test/accounting/ledger/Current Assets',
];

foreach ($testUrls as $url) {
    echo "Testing: {$url}\n";
    
    try {
        // Parse the URL to get the path
        $parsedUrl = parse_url($url);
        $path = $parsedUrl['path'];
        
        // Remove the base path if present
        $path = str_replace('/accounting', '', $path);
        
        // Create a request
        $request = \Illuminate\Http\Request::create($url, 'GET');
        
        // Try to resolve the route
        $route = app('router')->getRoutes()->match($request);
        
        if ($route) {
            echo "   ✅ Route found: " . $route->getName() . "\n";
            echo "   URI: " . $route->uri() . "\n";
        } else {
            echo "   ❌ No route found\n";
        }
        
    } catch (\Exception $e) {
        echo "   ❌ Error: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}

// Test the actual route generation
echo "=== TESTING ROUTE GENERATION ===\n";

try {
    $receivablesUrl = route('accounting.receivables');
    echo "Receivables URL: {$receivablesUrl}\n";
} catch (\Exception $e) {
    echo "Receivables route error: " . $e->getMessage() . "\n";
}

try {
    // Test the Current Assets redirect route
    $currentAssetsUrl = route('accounting.ledger.current-assets');
    echo "Current Assets redirect URL: {$currentAssetsUrl}\n";
} catch (\Exception $e) {
    echo "Current Assets route error: " . $e->getMessage() . "\n";
}

try {
    // Test the Current Liabilities redirect route
    $currentLiabilitiesUrl = route('accounting.ledger.current-liabilities');
    echo "Current Liabilities redirect URL: {$currentLiabilitiesUrl}\n";
} catch (\Exception $e) {
    echo "Current Liabilities route error: " . $e->getMessage() . "\n";
}

echo "\n=== LINK TESTING COMPLETE ===\n";
