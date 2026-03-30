# POS System Testing Guide

## Overview
This document provides comprehensive test cases for the Laravel 12.x / Livewire 3.x POS system.

## Prerequisites
1. ✅ Laravel 12.x installed
2. ✅ Livewire 3.x configured
3. ✅ PHP 8.2+
4. ✅ Database migrations executed:
   - `pos_carts` table
   - `pos_cart_items` table
   - `pos_sales` table
   - `pos_sale_details` table
   - `pos_sale_payments` table
   - `accounting_settings` table
5. ✅ Seeders executed:
   - `AccountingSettingsSeeder` (creates POS accounts)

## Test Cases

### 1. Cart Initialization
**Objective:** Verify cart is automatically created/loaded for logged-in user

**Preconditions:**
- User is logged in
- No open cart exists for user

**Steps:**
1. Navigate to `/pos` or load the Terminal component
2. Observe cart UUID is displayed
3. Add a product to the cart
4. Refresh page
5. Verify same cart UUID is restored

**Expected Results:**
- ✅ Cart UUID is displayed in the UI
- ✅ Cart persists across page refreshes
- ✅ User can add items to cart

**Pass Criteria:** Cart UUID is visible and items persist after refresh

---

### 2. Add Items to Cart
**Objective:** Verify products can be added to cart without page refresh

**Preconditions:**
- Terminal component loaded
- Cart is initialized

**Steps:**
1. Click on a product in ProductGrid
2. Observe item appears in CartPanel
3. Add another different product
4. Increase quantity of first product
5. Add same product again

**Expected Results:**
- ✅ Item appears instantly in cart (no refresh)
- ✅ Quantity increments when adding same product
- ✅ Cart total updates immediately
- ✅ Item count updates

**Pass Criteria:** All cart updates are reactive without page refresh

---

### 3. Remove Items from Cart
**Objective:** Verify items can be removed from cart

**Preconditions:**
- Cart has at least 2 items

**Steps:**
1. Click remove button (X) on an item
2. Verify item is removed
3. Verify cart total updates
4. Test decrease quantity button
5. Test increase quantity button

**Expected Results:**
- ✅ Item removed instantly
- ✅ Cart total recalculates
- ✅ Quantity controls work correctly

**Pass Criteria:** All cart modifications are reactive

---

### 4. Clear Cart
**Objective:** Verify cart can be cleared

**Preconditions:**
- Cart has items

**Steps:**
1. Click "Clear Cart" button
2. Confirm dialog
3. Verify cart is empty

**Expected Results:**
- ✅ Cart items removed
- ✅ Cart total shows ₦0.00
- ✅ Empty cart state displayed

**Pass Criteria:** Cart clears without errors

---

### 5. Hold Order
**Objective:** Verify orders can be held for later

**Preconditions:**
- Cart has items

**Steps:**
1. Click "HOLD" button
2. Observe notification
3. Verify new empty cart is created
4. Click "HELD" button
5. Verify held orders modal shows held order

**Expected Results:**
- ✅ Order status changes to 'held'
- ✅ New empty cart created automatically
- ✅ Held order visible in Held Orders modal
- ✅ Notification confirms hold

**Pass Criteria:** Hold functionality works without errors

---

### 6. Restore Held Order
**Objective:** Verify held orders can be restored

**Preconditions:**
- At least one held order exists

**Steps:**
1. Click "HELD" button
2. Click "Restore" on a held order
3. Verify cart is restored with items
4. Verify held order removed from list
5. Add new items to restored cart

**Expected Results:**
- ✅ Held order restored with all items
- ✅ Cart total correct
- ✅ Held order removed from held list
- ✅ New items can be added

**Pass Criteria:** Restore works without errors

---

### 7. Delete Held Order
**Objective:** Verify held orders can be deleted

**Preconditions:**
- At least one held order exists

**Steps:**
1. Click "HELD" button
2. Click "Delete" on a held order
3. Confirm dialog
4. Verify held order removed from list

**Expected Results:**
- ✅ Held order deleted
- ✅ Held orders list updates
- ✅ Notification confirms deletion

**Pass Criteria:** Delete works without errors

---

### 8. Checkout - Cash Payment
**Objective:** Verify complete checkout flow with cash payment

**Preconditions:**
- Cart has items
- Accounting settings configured

**Steps:**
1. Click "CHECKOUT" button
2. Payment modal opens
3. Select "Cash" payment method
4. Verify amount tendered = total
5. Click "Pay"
6. Observe success notification
7. Verify receipt opens/prints
8. Verify new empty cart created

**Expected Results:**
- ✅ Checkout completes successfully
- ✅ Sale persisted to `pos_sales` table
- ✅ Sale details persisted to `pos_sale_details` table
- ✅ Payment persisted to `pos_sale_payments` table
- ✅ Accounting journal entry created
- ✅ Cart cleared
- ✅ Receipt generated

**Pass Criteria:** Complete checkout flow works

**Database Verification:**
```sql
SELECT * FROM pos_sales ORDER BY id DESC LIMIT 1;
SELECT * FROM pos_sale_details WHERE sale_id = <latest_id>;
SELECT * FROM pos_sale_payments WHERE sale_id = <latest_id>;
SELECT * FROM accounting_journal_entries WHERE reference LIKE 'POS-%';
```

---

### 9. Checkout - Card Payment
**Objective:** Verify checkout with card payment

**Preconditions:**
- Cart has items
- Card terminal configured (optional for testing)

**Steps:**
1. Click "CHECKOUT" button
2. Select "Card" payment method
3. Complete payment (simulated)
4. Verify success

**Expected Results:**
- ✅ Checkout completes
- ✅ Payment method recorded as 'card'
- ✅ Journal entry uses card account

**Pass Criteria:** Card payment works

---

### 10. Charge to Room
**Objective:** Verify room charge functionality

**Preconditions:**
- Cart has items
- At least one checked-in guest/reservation exists

**Steps:**
1. Click "Charge to Room" button
2. Room Charge modal opens
3. Select a room/reservation
4. Add optional note
5. Click "Charge"
6. Observe success notification

**Expected Results:**
- ✅ Sale created with 'room_charge' payment method
- ✅ Reservation linked to sale
- ✅ Room charge created
- ✅ Guest ledger entry created (if applicable)
- ✅ Cart cleared

**Pass Criteria:** Room charge works correctly

**Database Verification:**
```sql
SELECT * FROM pos_sales WHERE sale_type = 'room_charge';
SELECT * FROM room_charges WHERE sale_id = <sale_id>;
```

---

### 11. Receipt Printing
**Objective:** Verify receipt generation

**Preconditions:**
- Checkout completed successfully

**Steps:**
1. Complete a checkout
2. Verify receipt page opens
3. Verify all items listed
4. Verify totals correct
5. Verify print dialog opens

**Expected Results:**
- ✅ Receipt displays correct data
- ✅ Auto-print dialog opens
- ✅ All line items present
- ✅ Payment method shown
- ✅ Total matches cart total

**Pass Criteria:** Receipt generates correctly

---

### 12. Multiple Concurrent Users
**Objective:** Verify cart isolation between users

**Preconditions:**
- Two or more users logged in

**Steps:**
1. User A adds items to cart
2. User B opens Terminal
3. User B's cart is empty
4. User B adds different items
5. Verify each user sees only their cart

**Expected Results:**
- ✅ Each user has separate cart
- ✅ Cart UUIDs are different
- ✅ Items don't mix between users

**Pass Criteria:** User isolation works

---

### 13. Error Handling - Empty Cart Checkout
**Objective:** Verify checkout prevented for empty cart

**Steps:**
1. Ensure cart is empty
2. Try to checkout (button should be disabled)
3. Click checkout anyway

**Expected Results:**
- ✅ Checkout button disabled when cart empty
- ✅ Warning notification shown if attempted

**Pass Criteria:** Empty cart checkout prevented

---

### 14. Error Handling - Payment Mismatch
**Objective:** Verify payment validation

**Steps:**
1. Add items to cart (total: ₦500)
2. Open checkout
3. Set amount tendered to ₦300
4. Attempt checkout

**Expected Results:**
- ✅ Validation error shown
- ✅ Checkout prevented

**Pass Criteria:** Payment validation works

---

### 15. Error Handling - Room Charge Without Selection
**Objective:** Verify room charge validation

**Steps:**
1. Add items to cart
2. Click "Charge to Room"
3. Try to submit without selecting room

**Expected Results:**
- ✅ Validation error shown
- ✅ Checkout prevented

**Pass Criteria:** Room validation works

---

## Testing Checklist

### Database Setup
- [ ] Run migrations: `php artisan migrate`
- [ ] Seed accounting settings: `php artisan db:seed --class=AccountingSettingsSeeder`
- [ ] Seed chart of accounts: `php artisan db:seed --class=AccountingChartOfAccountsSeeder`
- [ ] Verify tables exist:
  - [ ] `pos_carts`
  - [ ] `pos_cart_items`
  - [ ] `pos_sales`
  - [ ] `pos_sale_details`
  - [ ] `pos_sale_payments`
  - [ ] `accounting_settings`
  - [ ] `accounting_journal_entries`

### Core Functionality
- [ ] Cart initialization
- [ ] Add items (single, multiple, same product)
- [ ] Remove items
- [ ] Update quantities
- [ ] Clear cart
- [ ] Hold order
- [ ] Restore held order
- [ ] Delete held order
- [ ] Checkout (cash)
- [ ] Checkout (card)
- [ ] Charge to room

### Edge Cases
- [ ] Empty cart checkout prevented
- [ ] Invalid payment amount prevented
- [ ] Room charge without selection prevented
- [ ] Multiple users (cart isolation)
- [ ] Page refresh (cart persistence)
- [ ] Browser back/forward

### UI/UX
- [ ] Toast notifications appear
- [ ] Loading states display
- [ ] Disabled states correct
- [ ] Modal opens/closes correctly
- [ ] Responsive layout

### Performance
- [ ] Cart updates < 100ms
- [ ] No page refresh for cart operations
- [ ] Modal loads < 200ms

## Troubleshooting

### Common Issues

#### "Cart not initialized" error
**Cause:** User not logged in or cart creation failed
**Solution:** Ensure user is authenticated

#### "Payment total must equal cart total"
**Cause:** Amount tendered doesn't match cart total
**Solution:** Set amount tendered = cart total for cash

#### "Accounting settings are missing"
**Cause:** `accounting_settings` table empty
**Solution:** Run `php artisan db:seed --class=AccountingSettingsSeeder`

#### "One or more POS accounting accounts are invalid"
**Cause:** Chart of accounts not properly seeded
**Solution:** Run chart of accounts seeder and verify account IDs in settings

#### Checkout fails with "Room charge payment not found"
**Cause:** Room charge selected but no reservation ID
**Solution:** Ensure reservation is selected in RoomChargeModal

#### Held orders not showing
**Cause:** User ID mismatch or status filter
**Solution:** Verify held cart has correct `user_id` and `status = 'held'`

### Database Queries for Debugging

```sql
-- View all open carts
SELECT * FROM pos_carts WHERE status = 'open';

-- View held orders for user
SELECT * FROM pos_carts WHERE user_id = <user_id> AND status = 'held';

-- View recent sales
SELECT * FROM pos_sales ORDER BY id DESC LIMIT 10;

-- View accounting entries for POS
SELECT * FROM accounting_journal_entries 
WHERE reference LIKE 'POS-%' 
ORDER BY id DESC LIMIT 10;

-- Check accounting settings
SELECT * FROM accounting_settings;
```

## Test Data

### Sample Products
Create test products with:
- Name: "Test Product 1", Price: 100
- Name: "Test Product 2", Price: 250
- Name: "Test Product 3", Price: 500

### Sample Users
Ensure at least 2 users exist for concurrent testing.

### Sample Reservation
Ensure at least one checked-in reservation exists for room charge testing.

## Automated Testing (Optional)

Create PHPUnit tests for critical paths:

```php
// tests/Feature/PosCartTest.php
public function test_add_item_to_cart()
{
    $user = User::factory()->create();
    $product = Product::factory()->create(['price' => 100]);
    
    $response = $this->actingAs($user)
        ->post('/pos/cart/add', [
            'product_id' => $product->id,
            'qty' => 2,
        ]);
    
    $response->assertStatus(200);
    $this->assertDatabaseHas('pos_cart_items', [
        'product_id' => $product->id,
        'qty' => 2,
    ]);
}
```

## Summary

| Test Category | Tests | Pass Rate |
|--------------|-------|-----------|
| Cart Operations | 5 | /5 |
| Hold/Restore | 3 | /3 |
| Checkout | 4 | /4 |
| Room Charge | 1 | /1 |
| Error Handling | 3 | /3 |
| **Total** | **16** | **/16** |

**Target:** 100% pass rate for all critical paths

---

**Last Updated:** 2024-02-06
**Version:** 1.0
