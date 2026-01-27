```markdown
# Electricity Billing System (Modularized)

This repository contains a modularized PHP Electricity Billing System.

Highlights
- Modular includes: src/inc/db.php, validators.php, auth.php, billing.php
- Admin / Employee / User pages in public/
- JSON API endpoints in api/ for interoperability
- Documentation (MODULE_SPECS.md) and test plan (TEST_PLAN.md)
- draw.io diagram (diagrams/system.drawio)

Quick setup
1. Copy `config.sample.php` to `config.php` and edit DB credentials and `api_key`.
2. Import `sql/schema.sql` into MySQL.
3. Place this project in your PHP server root (XAMPP / WAMP / LAMP).
4. Open `public/index.php` in browser.
5. Change admin password after initial login.

Security notes
- Use HTTPS in production.
- Rotate default seeded password hash in `sql/schema.sql`.
- Add CSRF protection, rate limiting, and stronger authentication for production.
```