```markdown
# Module Specifications

Each module: name, inputs, preconditions, logic (pseudo-code), output.

1. src/inc/db.php
- Module name: DB connection
- Input: config.php values (db_host, db_user, db_pass, db_name)
- Preconditions: MySQL server reachable, credentials valid
- Logic: create mysqli connection, set charset
- Output: global mysqli instance, db() helper returning mysqli

2. src/inc/validators.php
- Module name: Validators
- Input: raw user input strings
- Preconditions: text input provided
- Logic: apply regex and sanitization routines
- Output: boolean/validation messages; helper esc()

3. src/inc/auth.php
- Module name: Authentication & Registration helpers
- Input: username/service_no, password, registration arrays
- Preconditions: DB connection available
- Logic (pseudo):
  - find_user_by_login(login): SELECT ... WHERE username=? OR service_no=?
  - register_user(data): validate, hash password, INSERT INTO customers
  - require_role(role): check session role and redirect if mismatch
- Output: user array or boolean and error list

4. src/inc/billing.php
- Module name: Billing engine & persistence
- Input: service_no, prev_reading, curr_reading, category
- Preconditions: valid customer exists
- Logic (pseudo):
  - calculate_tiered_bill(prev,curr,category): compute units and slab charges
  - create_bill(...): call calculate, find unpaid dues, add fine, create bill row, update customer curr_reading
- Output: created bill array or error

5. api/*.php
- Module name: API endpoints (customers, bills)
- Input: HTTP params, api_key
- Preconditions: valid api_key
- Logic: handle action param, prepared statements, JSON output
- Output: application/json with data or error

Flow diagrams: see diagrams/system.drawio
```