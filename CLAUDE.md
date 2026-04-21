# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a customized **SuiteCRM 7.13.3** instance for a Vietnamese travel/flight booking agency (bm.vemaybay.website). It extends the standard SuiteCRM CRM framework with ~40 custom `EC_*` modules for flight bookings, invoicing, HR, accounting, and integrations (Zalo, SMS, WebRTC).

## Development Environment

**Run with Docker (recommended):**
```bash
docker-compose up --build
# App available at http://localhost:8080
# Database must be provided externally (db service is commented out in docker-compose.yml)
```

**Install PHP dependencies:**
```bash
composer install
```

**Check PHP syntax (what CI does):**
```bash
find custom modules -type f -name "*.php" -print0 | xargs -0 -n1 php -l
```

**Code style:**
```bash
vendor/bin/php-cs-fixer fix
```

**Run tests:**
```bash
vendor/bin/phpunit
vendor/bin/codecept run   # acceptance tests
```

## Architecture

### Request Flow
`index.php` → `include/entryPoint.php` → `include/MVC/SugarApplication.php::execute()` → dispatches based on `$_REQUEST['module']` and `$_REQUEST['action']`

### Key Directories
- `modules/` — All CRM modules (standard SuiteCRM + custom `EC_*` modules)
- `custom/modules/` — Overrides and extensions for specific modules (takes priority over `modules/`)
- `include/` — Core framework: MVC base classes, utilities, email, search, caching
- `lib/` — PSR-4 autoloaded library classes (`SuiteCRM\` namespace)
- `Api/V8/` — REST API v8 (OAuth2-authenticated JSON:API)
- `custom/` — All project-specific customizations (modules, services, themes, language)
- `themes/SuiteP/` — Primary UI theme (Smarty templates + jQuery)

### Autoloading (PSR-4 via Composer)
- `SuiteCRM\` → `/lib/` and `/include/`
- `SuiteCRM\Custom\` → `/custom/lib/`
- `SuiteCRM\Modules\` → `/modules/`

### Custom EC_ Modules
Domain-specific modules added for this business. Key ones:
- **EC_Flight_Bookings** — Core flight booking records
- **EC_Booking_Details / EC_Booking_Passengers / EC_Booking_Itineraries** — Booking sub-records
- **EC_HoaDonBan / EC_ChiTietHoaDon** — Sales invoices and line items
- **EC_HoanVe / EC_ChiTietHoanVe** — Ticket refunds
- **EC_Receipt_Voucher / EC_Payment_Voucher / EC_Input_Invoices** — Accounting vouchers
- **EC_Revenue / EC_CashFlow / EC_TongHop / EC_Commission** — Financial reports
- **EC_Employee_Salary / EC_Salary_Details / EC_WorkingOverTimes / EC_LeaveAbsences** — HR/payroll
- **EC_Zalo / EC_Zalo_Apps / EC_Zalo_Contacts / EC_Zalo_Messages** — Zalo messaging integration
- **EC_SMS_Logs / EC_Messages / EC_Outbound_Phone** — Communications
- **EC_Banks / EC_Bank_Account / EC_ChiTietTaiKhoan / EC_ChuyenTienNoiBo** — Banking

### Customization Pattern
SuiteCRM uses a layered override system:
1. Base logic in `modules/{Module}/`
2. Custom overrides in `custom/modules/{Module}/` (preferred place for edits)
3. Language strings in `{module}/language/` and `custom/{module}/language/`
4. Metadata/vardefs in `{module}/metadata/` and `custom/{module}/Ext/`

Always prefer editing files under `custom/` rather than modifying core `modules/` files directly, to avoid conflicts when upgrading SuiteCRM.

## CI/CD
- `.github/workflows/dev-ci.yml` — Runs on push to `main` and PRs: validates composer, installs deps, runs PHP syntax check on `custom/` and `modules/`
- `.github/workflows/prod-ci.yml` — Production deployment pipeline
- PHP 7.4 is required (both local and CI)

## Configuration
- `config.php` — Main config (database credentials, site URL, etc.) — not committed
- `config_override.php` — Environment-specific overrides
- `docker/php/timezone.ini` — Sets `Asia/Ho_Chi_Minh` timezone for the container
