<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Accounting\JournalEntry;
use App\Models\Accounting\JournalLine;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

echo "--- Debugging Journal Entry Creation ---\n";

try {
    DB::beginTransaction();

    $entry = JournalEntry::create([
        'reference' => 'DEBUG-' . time(),
        'date' => Carbon::now(),
        'description' => 'Debug Entry',
        'status' => 'draft',
        'created_by' => 1,
        'source_type' => 'Debug',
        'source_id' => 1,
    ]);

    echo "Journal Entry Created: ID {$entry->id}\n";

    $line = JournalLine::create([
        'journal_entry_id' => $entry->id,
        'account_id' => 1, // Assumes account 1 exists, otherwise integrity constraint violation
        'type' => 'debit',
        'amount' => 100,
        'description' => 'Debug Line'
    ]);

    echo "Journal Line Created: ID {$line->id}\n";

    DB::rollBack();
    echo "Transaction Rolled Back (Success)\n";

} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    if (DB::transactionLevel() > 0) DB::rollBack();
}
