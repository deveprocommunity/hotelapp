<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== ACCOUNTING PERMISSIONS DEBUG ===\n\n";

// Test 1: Check if accounting.access permission exists
echo "1. PERMISSION CHECK:\n";
$permissions = \Spatie\Permission\Models\Permission::where('name', 'like', '%accounting%')->get();
echo "   Accounting permissions found:\n";
foreach ($permissions as $perm) {
    echo "   - {$perm->name}\n";
}

// Test 2: Check user roles and permissions
echo "\n2. USER ROLE TEST:\n";
$accountant = \App\Models\User::where('email', 'like', '%accountant%')->first();
$auditor = \App\Models\User::where('email', 'like', '%auditor%')->first();

if ($accountant) {
    echo "   Accountant user found: {$accountant->name} (ID: {$accountant->id})\n";
    echo "   Roles: " . $accountant->roles->pluck('name')->join(', ') . "\n";
    echo "   Permissions: " . $accountant->permissions->pluck('name')->join(', ') . "\n";
    echo "   Has accounting.access: " . ($accountant->hasPermissionTo('accounting.access') ? 'YES' : 'NO') . "\n";
    echo "   Has accounting.reports.view: " . ($accountant->hasPermissionTo('accounting.reports.view') ? 'YES' : 'NO') . "\n";
}

if ($auditor) {
    echo "   Auditor user found: {$auditor->name} (ID: {$auditor->id})\n";
    echo "   Roles: " . $auditor->roles->pluck('name')->join(', ') . "\n";
    echo "   Permissions: " . $auditor->permissions->pluck('name')->join(', ') . "\n";
    echo "   Has accounting.access: " . ($auditor->hasPermissionTo('accounting.access') ? 'YES' : 'NO') . "\n";
    echo "   Has accounting.reports.view: " . ($auditor->hasPermissionTo('accounting.reports.view') ? 'YES' : 'NO') . "\n";
}

// Test 3: Check gates directly
echo "\n3. GATE CHECK:\n";

// Test accounting.access gate
$gate = Gate::class;
$canAccess = $gate::allows('accounting.access', $accountant);
echo "   accounting.access gate for accountant: " . ($canAccess ? 'ALLOWED' : 'DENIED') . "\n";

$canAccess = $gate::allows('accounting.access', $auditor);
echo "   accounting.access gate for auditor: " . ($canAccess ? 'ALLOWED' : 'DENIED') . "\n";

// Test accounting.reports.view gate
$canViewReports = $gate::allows('accounting.reports.view', $accountant);
echo "   accounting.reports.view gate for accountant: " . ($canViewReports ? 'ALLOWED' : 'DENIED') . "\n";

$canViewReports = $gate::allows('accounting.reports.view', $auditor);
echo "   accounting.reports.view gate for auditor: " . ($canViewReports ? 'ALLOWED' : 'DENIED') . "\n";

// Test 4: Check middleware registration
echo "\n4. MIDDLEWARE REGISTRATION:\n";
$middlewareAliases = config('middleware.middleware', []);
echo "   Registered middleware aliases:\n";
foreach ($middlewareAliases as $alias => $class) {
    if (str_contains($alias, 'permission')) {
        echo "   - {$alias}: {$class}\n";
    }
}
