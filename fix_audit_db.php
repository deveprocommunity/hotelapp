<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$result = [];

try {
    // 1. Check Status Column
    if (!Schema::hasColumn('night_audits', 'status')) {
        $result['status_column'] = 'missing';
        try {
            Schema::table('night_audits', function (Blueprint $table) {
                $table->string('status')->default('open')->after('business_date');
            });
            $result['migration'] = 'success';
        } catch (\Exception $e) {
            $result['migration'] = 'failed: ' . $e->getMessage();
        }
    } else {
        $result['status_column'] = 'exists';
    }

    // 2. Seed Permissions
    $permissions = ['audit.view', 'audit.run', 'audit.export'];
    foreach ($permissions as $perm) {
        Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
    }
    
    // Assign to Super Admin
    $role = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
    $role->givePermissionTo($permissions);
    
    $result['permissions'] = 'seeded';

} catch (\Exception $e) {
    $result['error'] = $e->getMessage();
}

file_put_contents(__DIR__ . '/status.json', json_encode($result));
