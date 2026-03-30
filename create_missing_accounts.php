<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== CREATING MISSING CHART OF ACCOUNTS ===\n\n";

// Define the missing accounts with proper accounting structure
$missingAccounts = [
    [
        'name' => 'Accounts Receivable',
        'code' => '1100',
        'type' => 'asset',
        'account_type' => 'current_assets',
        'description' => 'Amounts owed to the hotel by guests and other parties',
        'is_active' => true,
        'is_system' => true,
    ],
    [
        'name' => 'Current Assets',
        'code' => '1000',
        'type' => 'asset',
        'account_type' => 'current_assets',
        'description' => 'Assets that are expected to be converted to cash within one year',
        'is_active' => true,
        'is_system' => true,
    ],
    [
        'name' => 'Current Liabilities',
        'code' => '2000',
        'type' => 'liability',
        'account_type' => 'current_liabilities',
        'description' => 'Liabilities that are due within one year',
        'is_active' => true,
        'is_system' => true,
    ],
];

foreach ($missingAccounts as $accountData) {
    // Check if account already exists
    $existing = \App\Models\Accounting\ChartOfAccount::where('name', $accountData['name'])->first();
    
    if ($existing) {
        echo "✅ Account already exists: {$accountData['name']} (ID: {$existing->id})\n";
        continue;
    }
    
    try {
        // Create the account
        $account = \App\Models\Accounting\ChartOfAccount::create([
            'name' => $accountData['name'],
            'code' => $accountData['code'],
            'type' => $accountData['type'],
            'account_type' => $accountData['account_type'],
            'description' => $accountData['description'],
            'is_active' => $accountData['is_active'],
            'is_system' => $accountData['is_system'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        echo "✅ Created account: {$accountData['name']} (ID: {$account->id})\n";
        
        // Account balances will be created automatically when transactions occur
        
    } catch (\Exception $e) {
        echo "❌ Failed to create {$accountData['name']}: " . $e->getMessage() . "\n";
    }
}

echo "\n=== ACCOUNT CREATION COMPLETE ===\n\n";

// Verify the accounts were created
echo "VERIFICATION:\n";
foreach ($missingAccounts as $accountData) {
    $account = \App\Models\Accounting\ChartOfAccount::where('name', $accountData['name'])->first();
    if ($account) {
        echo "✅ {$accountData['name']} - ID: {$account->id}, Code: {$account->code}\n";
    } else {
        echo "❌ {$accountData['name']} - NOT FOUND\n";
    }
}
