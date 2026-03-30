<?php

use Spatie\Permission\Models\Role;

$role = Role::where('name', 'front-desk')->first();
if ($role) {
    $role->givePermissionTo(['pos.use', 'pos.view-sales']);
    echo 'POS permissions assigned to front-desk role!';
} else {
    echo 'front-desk role not found, creating it...';
    $role = Role::create(['name' => 'front-desk']);
    $role->givePermissionTo(['pos.use', 'pos.view-sales']);
    echo 'front-desk role created and permissions assigned!';
}
