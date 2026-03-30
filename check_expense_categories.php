<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Expense Categories: " . App\Models\ExpenseCategory::count() . PHP_EOL;

$categories = App\Models\ExpenseCategory::take(5)->get(['name', 'code', 'active']);
foreach ($categories as $cat) {
    echo $cat->name . ' (' . $cat->code . ') - ' . ($cat->active ? 'Active' : 'Inactive') . PHP_EOL;
}
