<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== FIXING ACCOUNTING PERMISSIONS ===\n\n";

// Find users with accountant and auditor roles
$accountants = \App\Models\User::whereHas('roles', function($query) {
    $query->where('name', 'accountant');
})->get();

$auditors = \App\Models\User::whereHas('roles', function($query) {
    $query->where('name', 'auditor');
})->get();

echo "Found " . count($accountants) . " accountants and " . count($auditors) . " auditors\n\n";

// Create permissions if they don't exist
$permissions = [
    'accounting.access',
    'accounting.reports.view'
];

foreach ($permissions as $permissionName) {
    $permission = \Spatie\Permission\Models\Permission::where('name', $permissionName)->first();
    
    if (!$permission) {
        echo "Creating permission: {$permissionName}\n";
        \Spatie\Permission\Models\Permission::create([
            'name' => $permissionName,
            'guard_name' => 'web',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}

// Assign permissions to roles
foreach ($accountants as $accountant) {
    echo "Assigning permissions to accountant: {$accountant->name}\n";
    
    foreach ($permissions as $permissionName) {
        $permission = \Spatie\Permission\Models\Permission::where('name', $permissionName)->first();
        if ($permission && !$accountant->hasPermissionTo($permissionName)) {
            $accountant->givePermissionTo($permissionName);
            echo "  - Added: {$permissionName}\n";
        }
    }
}

foreach ($auditors as $auditor) {
    echo "Assigning permissions to auditor: {$auditor->name}\n";
    
    foreach ($permissions as $permissionName) {
        $permission = \Spatie\Permission\Models\Permission::where('name', $permissionName)->first();
        if ($permission && !$auditor->hasPermissionTo($permissionName)) {
            $auditor->givePermissionTo($permissionName);
            echo "  - Added: {$permissionName}\n";
        }
    }
}

echo "\n=== PERMISSIONS FIXED ===\n";
