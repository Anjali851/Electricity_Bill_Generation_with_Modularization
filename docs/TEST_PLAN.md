```markdown
# Test Plan

Instructions:
- Run DB schema, create config.php, start server.
- Use the test table below. For each test record Actual Output and Pass/Fail.

Format columns: Test ID | Functionality | Input | Expected Output | Actual Output | Pass/Fail | Notes

Examples:

- Test ID: T001
  - Functionality: DB connection
  - Input: config.php with correct DB creds
  - Expected Output: db() returns mysqli instance, no fatal
  - Actual Output:
  - Pass/Fail:

- Test ID: T002
  - Functionality: Register valid customer
  - Input: POST to admin_dashboard.php {name: "Anjali", username:"anj", contact:"9876543210", address:"x", password:"pass123", role:"user", service_no:"8001"}
  - Expected Output: New customer inserted, redirect or success message
  - Actual Output:
  - Pass/Fail:

- Test ID: T003
  - Functionality: Validate phone number bad
  - Input: contact="12345"
  - Expected Output: Validation error "Contact must be 10 digits."
  - Actual Output:
  - Pass/Fail:

- Test ID: T004
  - Functionality: Bill calculation slabs
  - Input: prev=100, curr=220, category=household
  - Expected Output: units=120, bill_price computed with slabs
  - Actual Output:
    units = 120
    bill_price = 270.00 (calculation: 50*1.5=75, next50*2.5=125 => 200 for 100 units; remaining 20*3.5=70 => total 270)

  - Pass/Fail: set Pass if units==120 and bill_price==270.00 (or matches your rate_slabs if you use DB rates)

- Test ID: T005
  - Functionality: Create bill and carry forward unpaid dues
  - Input: existing unpaid bill total_bill=500, new bill calculation=300
  - Expected Output: prev_dues=500, fine=150, total=950
  - Actual Output:
    bill_no: BILL-XXXXXX
    units: <units_calculated_by_system>
    bill_price: 300.00
    prev_dues: 500.00
    fine: 150.00
    total: 950.00
    due_date: YYYY-MM-DD

  - Pass/Fail:
    Pass — if the values printed by your test script exactly match prev_dues=500.00, fine=150.00 and total=950.00 (and bill_price = 300.00).
    Fail — otherwise (record the actual differing values and reason).


- Test ID: T006
  - Functionality: API auth
  - Input: GET api/customers.php?api_key=wrong
  - Expected Output: 401 unauthorized
  - Actual Output:
  
    HTTP/1.1 401 Unauthorized
    Content-Type: application/json; charset=utf-8
    Body: {"error":"Unauthorized"}

(Or in PowerShell)

    StatusCode: 401
    Content: {"error":"Unauthorized"}

  - Pass/Fail:
    Pass: If the HTTP status code is 401 AND the response body is exactly (or semantically) {"error":"Unauthorized"}.
    Fail: If you receive any other status code (200, 403, 500, etc.) or a different body message (e.g., HTML error page or DB error).


Add more tests for edge cases (zero units, negative readings, role mismatch, SQL error handling).
```