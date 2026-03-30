<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

echo "=== Checking Permissions ===" . PHP_EOL;

// Check if sales.view permission exists
$salesPermission = Permission::where('name', 'sales.view')->first();
if ($salesPermission) {
    echo "✓ Permission 'sales.view' exists" . PHP_EOL;
    $roles = $salesPermission->roles()->pluck('name')->toArray();
    echo "  Assigned to roles: " . implode(', ', $roles) . PHP_EOL;
} else {
    echo "✗ Permission 'sales.view' NOT found" . PHP_EOL;
}

// Check front-desk role permissions
$frontDesk = Role::where('name', 'front-desk')->first();
if ($frontDesk) {
    echo "✓ Role 'front-desk' exists" . PHP_EOL;
    $permissions = $frontDesk->permissions()->pluck('name')->toArray();
    echo "  Permissions: " . implode(', ', $permissions) . PHP_EOL;
} else {
    echo "✗ Role 'front-desk' NOT found" . PHP_EOL;
}

echo PHP_EOL . "=== All Permissions ===" . PHP_EOL;
$allPermissions = Permission::all()->pluck('name')->toArray();
echo "Total permissions: " . count($allPermissions) . PHP_EOL;
echo "Permissions: " . implode(', ', $allPermissions) . PHP_EOL;
