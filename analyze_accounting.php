<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Accounting\JournalEntry;
use App\Models\Accounting\JournalLine;
use App\Models\Accounting\ChartOfAccount;

echo "=== DEEP ANALYSIS OF ACCOUNTING RECORDS ===\n\n";

// 1. Check for Inventory Reconciliation Entry
echo "--- 1. Searching for Inventory Synchronization Entry ---\n";
$syncEntry = JournalEntry::where('description', 'like', '%Inventory Opening Balance%')
    ->with('lines.account')
    ->latest()
    ->first();

if ($syncEntry) {
    echo "FOUND Entry ID: {$syncEntry->id}\n";
    echo "Date: {$syncEntry->date}\n";
    echo "Status: {$syncEntry->status}\n";
    echo "Ref: {$syncEntry->reference}\n";
    foreach ($syncEntry->lines as $line) {
        echo "   - {$line->type}: {$line->amount} | Account: {$line->account->name} ({$line->account->code}) | Type: {$line->account->type}\n";
    }
} else {
    echo "ERROR: No Inventory Synchronization Entry found in DB!\n";
}
echo "\n";

// 2. Check Recent Entries (The 5000, 4500 the user sees)
echo "--- 2. Recent 5 Journal Entries ---\n";
$entries = JournalEntry::with('lines.account')->latest()->take(5)->get();

foreach ($entries as $entry) {
    echo "[{$entry->date}] ID:{$entry->id} Ref:{$entry->reference} Desc:{$entry->description} (Status: {$entry->status})\n";
    foreach ($entry->lines as $line) {
        echo "   - {$line->type}: " . number_format($line->amount, 2) . " | {$line->account->name} ({$line->account->code})\n";
    }
    echo "---------------------------\n";
}

// 3. Dashboard KPI Check
echo "\n--- 3. Checking Report Totals (Dashboard Simulation) ---\n";
// Revenue
$revenueAccounts = ChartOfAccount::where('type', 'revenue')->pluck('id');
$revenue = JournalLine::whereIn('account_id', $revenueAccounts)
    ->sum('amount'); // Ideally credit - debit, simplified here

// Expenses
$expenseAccounts = ChartOfAccount::where('type', 'expense')->pluck('id');
$expense = JournalLine::whereIn('account_id', $expenseAccounts)
    ->where('type', 'debit')
    ->sum('amount');

// Inventory Asset
$assetAccounts = ChartOfAccount::where('type', 'asset')->pluck('id');
$assets = JournalLine::whereIn('account_id', $assetAccounts)
    ->where('type', 'debit')
    ->sum('amount');

echo "Total Revenue (Credit): " . number_format($revenue, 2) . "\n";
echo "Total Expenses (Debit): " . number_format($expense, 2) . "\n";
echo "Total Assets (Debit): " . number_format($assets, 2) . "\n";
