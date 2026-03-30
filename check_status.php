<?php
// Script to verify if status column exists in night_audits
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $exists = \Illuminate\Support\Facades\Schema::hasColumn('night_audits', 'status');
    echo $exists ? "COLUMN_EXISTS" : "COLUMN_MISSING";
    
    // Also check content
    $count = \Illuminate\Support\Facades\DB::table('night_audits')->count();
    echo "|AuditCount:{$count}";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
