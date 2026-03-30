<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== DASHBOARD FILTER FIX ===\n\n";

// Test 1: Check if date filter Livewire component is working
echo "1. TESTING DATE FILTER COMPONENT:\n";

try {
    // Simulate the Livewire component mounting
    $request = new \Illuminate\Http\Request();
    $dateFilter = new \App\Livewire\Accounting\DateFilter();
    $dateFilter->mount($request);
    
    echo "   Component mounted successfully\n";
    echo "   Filter type: {$dateFilter->filterType}\n";
    echo "   From date: {$dateFilter->fromDate}\n";
    echo "   To date: {$dateFilter->toDate}\n";
    echo "   As at date: {$dateFilter->asAtDate}\n";
    
} catch (\Exception $e) {
    echo "   Error: " . $e->getMessage() . "\n";
}

// Test 2: Check if date filtering service is working
echo "\n2. TESTING DATE FILTER SERVICE:\n";

try {
    $todayRange = \App\Services\Accounting\AccountingDateFilterService::getDateRange('today');
    echo "   Today range: " . json_encode($todayRange) . "\n";
    
    $thisMonthRange = \App\Services\Accounting\AccountingDateFilterService::getDateRange('this_month');
    echo "   This month range: " . json_encode($thisMonthRange) . "\n";
    
} catch (\Exception $e) {
    echo "   Error: " . $e->getMessage() . "\n";
}

// Test 3: Check if apply filter is working
echo "\n3. TESTING FILTER APPLICATION:\n";

// Simulate a filter request
$testRequest = \Illuminate\Http\Request::create('/accounting?date_range=this_month', 'GET');

// Test the controller method
$controller = new \App\Http\Controllers\Accounting\AccountingController(
    app(\App\Services\Accounting\AccountingReportService::class)
);

// Test date range extraction
$reflection = new \ReflectionClass($controller);
$method = $reflection->getMethod('getDateRangeFromRequest');
$method->setAccessible(true);

echo "   Testing getDateRangeFromRequest method...\n";
try {
    $result = $method->invoke($controller, $testRequest);
    echo "   Date range result: " . json_encode($result) . "\n";
} catch (\Exception $e) {
    echo "   Error: " . $e->getMessage() . "\n";
}

echo "\n=== FILTER TEST COMPLETE ===\n";
