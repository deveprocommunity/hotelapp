<?php

$models = [
    'app/Models/Department.php',
    'app/Models/Store.php',
    'app/Models/Inventory.php',
    'app/Models/InventoryCategory.php',
    'app/Models/InventoryStockMovement.php',
    'app/Models/InventoryCharge.php',
    'app/Models/InventoryLedger.php',
    'app/Models/Pos/PosSale.php',
    'app/Models/Pos/PosSaleDetail.php',
    'app/Models/Pos/PosSalePayment.php',
    'app/Models/Reservation.php',
    'app/Models/ReservationLedger.php',
    'app/Models/Room.php',
    'app/Models/RoomType.php',
    'app/Models/RoomCharge.php',
    'app/Models/Guest.php',
    'app/Models/GuestLedger.php',
    'app/Models/MaintenanceRecord.php',
    'app/Models/HousekeepingRecord.php',
    'app/Models/HousekeepingTask.php',
    'app/Models/NightAudit.php',
    'app/Models/DailyAudit.php',
    'app/Models/DailyDepartmentAudit.php',
    'app/Models/DailyDepartmentAuditItem.php',
    'app/Models/FixedAsset.php',
    'app/Models/FixedAssetCategory.php',
    'app/Models/FixedAssetDepreciation.php',
    'app/Models/FixedAssetDisposal.php',
    'app/Models/Expense.php',
    'app/Models/ExpenseCategory.php',
    'app/Models/ProductionBatch.php',
    'app/Models/ProductionBatchMovement.php',
    'app/Models/Recipe.php',
    'app/Models/RecipeIngredient.php',
    'app/Models/RecipeItem.php',
    'app/Models/Service.php',
    'app/Models/ServiceCharge.php',
    'app/Models/CashShift.php',
    'app/Models/Stock.php',
    'app/Models/StockMovement.php',
    'app/Models/Vendor.php',
    'app/Models/Company.php',
    'app/Models/Account.php' // If exists
];

$basePath = 'c:/laragon/www/hotelapp/';

foreach ($models as $modelPath) {
    if (!file_exists($basePath . $modelPath)) continue;

    $content = file_get_contents($basePath . $modelPath);

    // 1. Add Trait Import if missing
    if (strpos($content, 'use App\Traits\BelongsToProperty;') === false) {
        $content = preg_replace('/namespace (.+);/', "namespace $1;\n\nuse App\Traits\BelongsToProperty;", $content, 1);
    }

    // 2. Add use BelongsToProperty; inside class if missing
    if (strpos($content, 'use BelongsToProperty;') === false) {
        // Find class definition and insert after it
        $content = preg_replace('/class (\w+) extends (\w+)\n\{/', "class $1 extends $2\n{\n    use BelongsToProperty;", $content, 1);
        // Also handle cases with other traits
        $content = preg_replace('/use (HasFactory|SoftDeletes|Notifiable)(, |;)/', "use $1, BelongsToProperty$2", $content, 1);
    }

    // 3. Add property_id to $fillable if missing
    if (strpos($content, "'property_id'") === false) {
         $content = preg_replace('/protected \$fillable = \[/', "protected \$fillable = [\n        'property_id',", $content, 1);
    }
    
    // 4. Standardize tenant_id to property_id in fillable/logic
    $content = str_replace("'tenant_id'", "'property_id'", $content);
    $content = str_replace('"tenant_id"', '"property_id"', $content);

    file_put_contents($basePath . $modelPath, $content);
    echo "Updated: $modelPath\n";
}
