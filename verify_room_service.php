<?php

// Bootstrap Laravel
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Guest;
use App\Models\Room;
use App\Models\Reservation;
use App\Models\Inventory;
use App\Models\Service;
use App\Models\RoomCharge;
use App\Models\InventoryCategory;
use App\Models\Store;
use App\Livewire\KitchenDashboard;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

// 1. Setup Data
echo "--- Setting up Test Data ---\n";
DB::beginTransaction();

try {
    // Create User for attribution
    $user = User::firstOrCreate(
        ['email' => 'admin@hotel.test'],
        ['name' => 'Admin', 'password' => bcrypt('password')]
    );
    // Force ID 1 if possible or update service to use this user's ID
    // Actually RecipeService uses `auth()->id() ?? 1`. 
    // If User::first() is this user, its ID might not be 1 if ID 1 was deleted.
    // Let's force actingAs if we were in a test, but here we aren't.
    // So we should ensure User 1 exists OR mock auth.
    // Easiest is to ensure User with ID 1 exists.
    if (!User::find(1)) {
        $user1 = new User();
        $user1->id = 1;
        $user1->name = 'System';
        $user1->email = 'system@hotel.test';
        $user1->password = bcrypt('password');
        $user1->save();
    }

    $store = Store::firstOrCreate(['code' => 'MAIN'], ['name' => 'Main Store', 'is_active' => true]);
    $category = InventoryCategory::firstOrCreate(['name' => 'Food'], ['code' => 'FOOD', 'type' => 'product']);

    // Seed Chart of Accounts required by InventoryAccountingService
    $accounts = [
        ['code' => '1200', 'name' => 'Inventory Asset', 'type' => 'asset'],
        ['code' => '5000', 'name' => 'Cost of Goods Sold', 'type' => 'expense'],
        ['code' => '2000', 'name' => 'Accounts Payable', 'type' => 'liability'],
        ['code' => '6310', 'name' => 'Cost of Food Supplies', 'type' => 'expense'],
    ];

    foreach ($accounts as $acc) {
        \App\Models\Accounting\ChartOfAccount::firstOrCreate(
            ['code' => $acc['code']],
            ['name' => $acc['name'], 'type' => $acc['type'], 'is_active' => true]
        );
    }

    // Open Accounting Period for today
    \App\Models\Accounting\AccountingPeriod::firstOrCreate(
        ['start_date' => now()->startOfMonth()->toDateString()],
        [
            'end_date' => now()->endOfMonth()->toDateString(),
            'code' => now()->format('Y-m'),
            'status' => 'open',
            'name' => now()->format('F Y')
        ]
    );

    // Create Ingredient
    $ingredient = Inventory::create([
        'name' => 'Test Ingredient ' . uniqid(),
        'inventory_category_id' => $category->id,
        'store_id' => $store->id,
        'sku' => 'TEST-' . uniqid(),
        'current_stock' => 100,
        'cost_price' => 10.00,
        'unit_price' => 20.00,
        'reorder_level' => 10,
        'is_active' => true
    ]);
    echo "Created Ingredient: {$ingredient->name} (Stock: 100)\n";

    // Create Service
    $service = Service::create([
        'name' => 'Test Dish ' . uniqid(),
        'price' => 50.00,
        'status' => 'active',
        'is_active' => true
    ]);
    echo "Created Service: {$service->name}\n";

    // Link Recipe (2 units required)
    $service->recipes()->create([
        'inventory_id' => $ingredient->id,
        'quantity' => 2,
        'unit' => 'kg' 
        // Note: 'unit' column exists in recipe_items table, so this is valid here
    ]);
    echo "Linked Recipe: 2kg of Ingredient per Dish\n";

    // Create Guest/Room/Reservation
    $room = Room::first() ?? Room::factory()->create();
    $guest = Guest::first() ?? Guest::factory()->create();
    $reservation = Reservation::create([
        'guest_id' => $guest->id,
        'room_id' => $room->id,
        'check_in' => now(),
        'check_out' => now()->addDays(1),
        'status' => 'checked_in',
        'total_amount' => 100
    ]);
    echo "Created Reservation for Room {$room->name}\n";

    // 2. Place Order (Room Charge)
    echo "\n--- Placing Order ---\n";
    $order = RoomCharge::create([
        'reservation_id' => $reservation->id,
        'guest_id' => $guest->id,
        'room_id' => $room->id,
        'service_id' => $service->id,
        'description' => $service->name,
        'amount' => $service->price,
        'quantity' => 1,
        'charge_type' => 'service',
        'status' => 'pending',
        'charge_date' => now()
    ]);
    echo "Order Placed. Status: {$order->status}\n";

    // 3. Verify KDS Visibility
    echo "\n--- Checking KDS ---\n";
    $kds = new KitchenDashboard();
    $orders = $kds->getOrdersProperty();
    $found = $orders->contains('id', $order->id);
    echo "Order visible in KDS? " . ($found ? "YES" : "NO") . "\n";

    if (!$found) throw new Exception("Order not found in KDS");

    // 4. Kitchen Prep (Trigger Stock Deduction)
    echo "\n--- Starting Preparation (Stock Deduction) ---\n";
    // Simulate Livewire call
    $kds->updateStatus($order->id, 'preparing');
    
    $order->refresh();
    echo "Order Status: {$order->status}\n";

    // 5. Verify Inventory
    $ingredient->refresh();
    echo "Ingredient Stock: {$ingredient->current_stock}\n";
    
    if ($ingredient->current_stock == 98) {
        echo "SUCCESS: Stock deducted correctly (100 -> 98)\n";
    } else {
        echo "FAILURE: Stock mismatch. Expected 98, got {$ingredient->current_stock}\n";
        // Check for manual errors (debugging)
        $movement = \App\Models\InventoryMovement::where('item_id', $ingredient->id)->latest()->first();
        if ($movement) {
            echo "Found Movement: Type={$movement->movement_type}, Qty={$movement->quantity}\n";
        } else {
            echo "No Inventory Movement found!\n";
        }
    }

} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
} finally {
    DB::rollBack();
    echo "\n--- Test Complete (Rolled Back) ---\n";
}
