# 🧪 THUNDER CLIENT TESTING GUIDE

## 🎯 TEST ACCOUNTING LOCKS

### 1. **Test Payment Lock** (Should FAIL)
```
METHOD: POST
URL: http://hotelapp.test/api/pos/sales/d1157522-1901-4ca8-b83e-ee242e067fa6/pay
HEADERS: Content-Type: application/json
BODY:
{
  "method": "cash",
  "amount": 500
}
```

### 2. **Test Void Lock** (Should FAIL)
```
METHOD: POST
URL: http://hotelapp.test/api/pos/sales/d1157522-1901-4ca8-b83e-ee242e067fa6/void
HEADERS: Content-Type: application/json
BODY:
{
  "reason": "test void after night audit"
}
```

### 3. **Test Post to Room Lock** (Should FAIL)
```
METHOD: POST
URL: http://hotelapp.test/api/pos/sales/d1157522-1901-4ca8-b83e-ee242e067fa6/post-to-room
HEADERS: Content-Type: application/json
BODY:
{
  "guest_id": 1,
  "room_id": 101,
  "note": "test post after night audit"
}
```

### 4. **Run Night Audit** (If needed)
```
METHOD: POST
URL: http://hotelapp.test/api/pos/night-audit
HEADERS: Content-Type: application/json
BODY: {}
```

## ✅ EXPECTED RESULTS

All 3 tests should return:
```json
{
  "status": false,
  "message": "Sale locked by accounting (night audit completed)"
}
```

## 🎯 SUCCESS INDICATORS

- ❌ Payment blocked → ✅ LOCK WORKING
- ❌ Void blocked → ✅ LOCK WORKING  
- ❌ Post to room blocked → ✅ LOCK WORKING
