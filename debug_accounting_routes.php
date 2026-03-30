<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== ACCOUNTING ROUTES AUDIT ===\n\n";

// Test 1: Check if receivables route exists
echo "1. RECEIVABLES ROUTE TEST:\n";
$routes = app('router')->getRoutes();
$receivablesRoute = null;
$ledgerRoute = null;

foreach ($routes as $route) {
    if ($route->uri() === 'accounting/receivables') {
        $receivablesRoute = $route;
    }
    if ($route->uri() === 'accounting/ledger/{subtype}') {
        $ledgerRoute = $route;
    }
}

if ($receivablesRoute) {
    echo "   ✅ Receivables route found: " . $receivablesRoute->uri() . "\n";
    echo "   Name: " . $receivablesRoute->getName() . "\n";
} else {
    echo "   ❌ Receivables route NOT found\n";
}

// Test 2: Check Chart of Accounts for required accounts
echo "\n2. CHART OF ACCOUNTS CHECK:\n";

$accounts = [
    'Accounts Receivable',
    'Current Assets',
    'Current Liabilities'
];

foreach ($accounts as $accountName) {
    $account = \App\Models\Accounting\ChartOfAccount::where('name', $accountName)->first();
    if ($account) {
        echo "   ✅ Found: {$accountName} (ID: {$account->id})\n";
    } else {
        echo "   ❌ Missing: {$accountName}\n";
    }
}

// Test 3: Test route generation
echo "\n3. ROUTE GENERATION TEST:\n";

try {
    $receivablesUrl = route('accounting.receivables');
    echo "   Receivables URL: {$receivablesUrl}\n";
} catch (\Exception $e) {
    echo "   ❌ Receivables route error: " . $e->getMessage() . "\n";
}

try {
    // Test with a sample account ID
    $sampleAccountId = 1;
    $ledgerUrl = route('accounting.ledger', $sampleAccountId);
    echo "   Ledger URL: {$ledgerUrl}\n";
} catch (\Exception $e) {
    echo "   ❌ Ledger route error: " . $e->getMessage() . "\n";
}

// Test 4: Check if routes are registered correctly
echo "\n4. REGISTERED ACCOUNTING ROUTES:\n";
foreach ($routes as $route) {
    if (str_starts_with($route->uri(), 'accounting/')) {
        echo "   - " . $route->uri() . " [" . ($route->getName() ?? 'NO NAME') . "]\n";
    }
}

echo "\n=== ROUTE AUDIT COMPLETE ===\n";
