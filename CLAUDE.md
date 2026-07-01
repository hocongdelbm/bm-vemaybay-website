# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a customized **SuiteCRM 7.13.3** instance for a Vietnamese travel/flight booking agency (bm.vemaybay.website). It extends the standard SuiteCRM CRM framework with ~40 custom `EC_*` modules for flight bookings, invoicing, HR, accounting, and integrations (Zalo, SMS, WebRTC, Telegram, OnePay, MISA, WinInvoice).

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
`index.php` → `include/MVC/preDispatch.php` → `include/entryPoint.php` → `include/MVC/SugarApplication.php::execute()` → dispatches based on `$_REQUEST['module']` and `$_REQUEST['action']`

### Key Directories
- `modules/` — All CRM modules (standard SuiteCRM + custom `EC_*` modules)
- `custom/modules/` — Overrides and extensions for specific modules (takes priority over `modules/`)
- `custom/entrypoints/` — Custom API-like entry points (authenticated & non-authenticated)
- `custom/services/` — Custom service layer (Notification, Location, Widget)
- `custom/include/` — Custom utilities, helpers, and shared logic
- `custom/include/helpers/api/` — Third-party API wrappers (Datacom, MISA, WinInvoice, Zalo, NextCloud, OnePay, Phương Nam)
- `custom/include/helpers/cache/` — Cache abstraction layer (Session, File drivers)
- `custom/include/helpers/modules/` — Module-specific helper classes
- `custom/include/utils/` — Domain-specific utilities (booking, calls, flights, Telegram, etc.)
- `custom/json_files/` — Runtime JSON config files (airports, airlines, MISA tokens, Zalo data)
- `custom/templates_export/` — Generated invoice export files (XLSX)
- `include/` — Core framework: MVC base classes, utilities, email, search, caching
- `lib/` — PSR-4 autoloaded library classes (`SuiteCRM\` namespace)
- `Api/V8/` — REST API v8 (OAuth2-authenticated JSON:API)
- `themes/SuiteP/` — Primary UI theme (Smarty templates + jQuery)

### Autoloading (PSR-4 via Composer)
- `SuiteCRM\` → `/lib/` and `/include/`
- `SuiteCRM\Custom\` → `/custom/lib/`
- `SuiteCRM\Modules\` → `/modules/`
- `custom\` → Custom SPL autoloader in `custom/services/services_autoload.php` (loads `custom/Services/*`)

---

## Custom Entry Point System

The project implements a **Factory-based entry point system** for AJAX/API calls, serving as the primary backend-for-frontend layer.

### How It Works

1. **Entry Point Registration**: SuiteCRM entry points are registered in `custom/application/Ext/Api/` and point to dispatcher files.
2. **Dispatcher Files** (`custom/entrypoints/`):
   - `entryGeneral.php` — **Authenticated** requests (requires SuiteCRM session). No IP/API-key check.
   - `entryGeneralNonAuth.php` — **Non-authenticated** requests. Validates `IP whitelist + Api-Key header`.
3. **Factory Pattern** (`entryFactory.php`):
   - Auto-loads all PHP classes from `entryAuthClass/` and `entryNonAuthClass/` directories.
   - `entryFactory::create($className)` instantiates the requested class.
4. **Request Routing**: Client sends `class` + `method` + `params` → factory creates instance → calls method dynamically.

### Entry Point Class Hierarchy

```
entryClass (abstract base)
├── entryAuthClass/         ← Requires SuiteCRM login session
│   ├── entryAutoBookDatacomClass    — Auto-booking via Datacom API
│   ├── entryAutoBookPhuongNamClass  — Auto-booking via Phương Nam API
│   ├── entryBookingClass            — Booking CRUD operations
│   ├── entryFareSystemClass         — Fare/pricing system
│   ├── entryInputInvoiceClass       — Input (purchase) invoice management
│   ├── entryOutputInvoiceClass      — Output (sales) invoice + e-invoice (WinInvoice/MISA)
│   ├── entryNextCloudPreviewClass   — NextCloud file preview integration
│   └── entryZaloOAClass             — Zalo OA message management
│
└── entryNonAuthClass/      ← Public API (IP + API-Key protected)
    ├── entryEvent020925Class        — Special event/campaign endpoints
    ├── entryEvent080326Class        — Special event/campaign endpoints
    ├── entryExportClass             — Data export endpoints
    ├── entryOutputInvoiceLookupClass — Public invoice lookup
    └── entryZaloMessageClass        — Zalo message webhook handling
```

### Standalone Entry Points (not Factory-based)
- `entryFlightBookings.php` (163KB) — Massive flight booking operations endpoint (legacy, largest file)
- `epVoucher.php` — Voucher management
- `epCallContact.php` — Call center / contact operations
- `epZaloWebhook.php` — Zalo webhook receiver
- `epZaloOA.php` / `epZaloPost.php` — Zalo OA interactions
- `entryTelegramWebhook.php` — Telegram bot webhook (callback queries)
- `entryMisaCallback.php` — MISA AMIS accounting callback (signature-verified)
- `entryOnepayIPN.php` — OnePay payment IPN (Instant Payment Notification)
- `epSMS.php` — SMS sending operations
- `epStatisticsCall.php` — Call statistics & reporting
- `epAnalytics.php` — Analytics endpoints
- `epOverviewDashboard.php` — Dashboard data
- Various `ep*.php` — Other domain-specific endpoints

### Making an API Call (Frontend → Backend)

**Authenticated call:**
```javascript
// POST to index.php?entryPoint=entryGeneral
fetch('index.php?entryPoint=entryGeneral', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
        class: 'entryBookingClass',
        method: 'getBookingDetails',
        params: { booking_id: '...' }
    })
});
```

**Non-authenticated call:**
```javascript
// POST to index.php?entryPoint=entryGeneralNonAuth
fetch('index.php?entryPoint=entryGeneralNonAuth', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'Api-Key': '<api_key_from_config>'
    },
    body: JSON.stringify({
        class: 'entryExportClass',
        method: 'exportData',
        params: { ... }
    })
});
```

---

## Custom Services Layer

### Notification Service (`custom/services/Notification/`)
Multi-channel notification system with strategy pattern:
- **Interface**: `NotificationChannelInterface` — `sendMessage(string, string, array): string`
- **Channels**: `TelegramChannel` (active), `MattermostChannel` (stub)
- **Profiles**: Multiple Telegram bot/chat configs: `default`, `zalo`, `autobook`, `cty`, `thongbao`, `checkin`, `accounting`
- **Thread Support**: Messages can target specific topics/threads via `threadKey` option (`test`, `logs`, `system`)
- **Usage**: `NotificationService::sendMessage($msg, 'default', ['threadKey' => 'logs'])`
- **Severity levels**: `sendMessage()`, `sendWarningMessage()`, `sendErrorMessage()`, `sendFatalMessage()`

### Location Service (`custom/services/Location/`)
Geocoding with failover: `APIOpenCage` → `APINominatim` (tries each until success).
Also provides IP-to-city via `ipinfo.io`.

### Logger Helper (`custom/include/helpers/LoggerHelper.php`)
File-based logging to `secure_sessions/request_logs/YYYY/MM/YYYY-MM-DD.log`.
Levels: `error`, `warning`, `info`, `debug`. Each log entry gets a unique `LOG<timestamp><random>` ID.

### Cache Helper (`custom/include/helpers/cache/`)
Cache abstraction with `CacheHelper` facade and pluggable drivers: `SessionCacheHelper`, `FileCacheHelper`.

---

## Third-Party API Integrations

| Integration | Helper Class | Purpose |
|---|---|---|
| **Datacom** | `APIDatacom` | Flight search & auto-booking (primary GDS) |
| **Phương Nam** | `APIPhuongNam` | Alternative flight booking API |
| **WinInvoice** | `WinInvoice` | E-invoice creation, signing, and TVAN submission |
| **MISA AMIS** | `MisaInvoice` | Accounting integration (dictionary sync, invoice CRUD) |
| **Zalo OA** | `APIZaloOA` | Zalo Official Account messaging (ZNS templates, followers) |
| **NextCloud** | `APINextCloud` | File storage (upload, share, preview via vnbackup.com) |
| **OnePay** | `Onepay` | Online payment processing (IPN verification) |
| **Telegram** | `TelegramChannel` | Bot notifications (multi-profile, multi-thread) |

---

## Custom EC_ Modules

### Business Domain Groups

**Flight Operations (Core)**
- **EC_Flight_Bookings** — Core booking records (~112KB bean, custom controller with 20+ actions, logic hooks for auto-assign/KPI/revenue)
- **EC_Booking_Details** — Booking line items (pricing, fees)
- **EC_Booking_Passengers** — Passenger information per booking
- **EC_Booking_Itineraries** — Flight segments (departure, arrival, flight numbers, checkin status)
- **EC_Airlines** — Airline master data
- **EC_Airports** — Airport master data

**Invoicing & Tax**
- **EC_HoaDonBan** — Sales invoices (output invoices, e-invoice integration)
- **EC_ChiTietHoaDon** — Invoice line items
- **EC_Input_Invoices** — Purchase/input invoices (from suppliers)
- **EC_HoanVe** — Ticket refunds
- **EC_ChiTietHoanVe** — Refund line items
- **EC_Vouchers** — Customer vouchers/promotions

**Accounting & Finance**
- **EC_Receipt_Voucher** — Cash receipt vouchers (phiếu thu)
- **EC_Payment_Voucher** — Cash payment vouchers (phiếu chi)
- **EC_Payment_Types** — Payment type categories
- **EC_Banks** — Bank master data
- **EC_Bank_Account** — Bank accounts
- **EC_ChiTietTaiKhoan** — Account transaction details (monthly balance sheets)
- **EC_ChuyenTienNoiBo** — Internal money transfers
- **EC_CashFlow** — Cash flow records
- **EC_Revenue** — Revenue tracking records
- **EC_TongHop** — Aggregated financial reports
- **EC_Commission** — Sales commissions
- **EC_LyDoThangThua** — Profit/loss reason tracking

**HR & Payroll**
- **EC_Employee_Salary** — Monthly salary records
- **EC_Salary_Details** — Salary detail breakdown
- **EC_WorkingOverTimes** — Overtime records
- **EC_WorkingOverTimeDetails** — Overtime detail breakdown
- **EC_LeaveAbsences** — Leave/absence requests
- **EC_LeaveAbsenceTypes** — Leave type definitions
- **EC_WorkHistory** — Employee work history
- **EC_Working_Process** — Work process tracking

**Communications & Messaging**
- **EC_Zalo** — Zalo chat conversations (with WebSocket real-time)
- **EC_Zalo_Apps** — Zalo app configurations
- **EC_Zalo_Contacts** — Zalo follower/contact sync
- **EC_Zalo_Messages** — Zalo message storage
- **EC_Messages** — Internal messaging
- **EC_SMS_Logs** — SMS sending logs
- **EC_Outbound_Phone** — Outbound phone number management

**Other**
- **EC_Online_Report** — Online user tracking/reporting
- **EC_Contact_Points_Log** — Customer touchpoint logging
- **EC_LoginAudit** — User login audit trail
- **EC_Location** — Location/geolocation records

---

## Logic Hooks

### Global Hooks (`custom/modules/logic_hooks.php`)
- `after_save`: AOD indexing, ElasticSearch indexing, SecurityGroup assignment
- `after_delete` / `after_restore`: AOD & ElasticSearch cleanup
- `after_ui_footer` / `after_ui_frame`: SecurityGroup UI hooks

### EC_Flight_Bookings Hooks (most critical)
- `process_record`: `customDisplay` (formatting for list view), `getRecallValue`
- `before_delete`: `checkBeforeDelete` (validation before deletion)
- `before_save`: `checkBeforeSave` (validation rules)
- `after_save`: `autoAssignBooking` → `updateFields` → `updateKPI` → `saveRevenueBookingHook` (chained post-save operations)

### Other Module Hooks
- **EC_HoaDonBan**: before_save validation, after_save processing
- **EC_Receipt_Voucher**: before_save/after_save for voucher processing
- **EC_HoanVe**: before_save/after_save for refund calculations
- **EC_LeaveAbsences**: before_save/after_save for leave approval workflow

---

## Scheduled Jobs (Cron)

Defined in `custom/modules/Schedulers/_AddJobsHere.php` (~2500 lines, 20+ jobs). Run via `cron.php` (CLI only).

### Financial Jobs
- `TuDongTaoBang` — Auto-create monthly account detail tables
- `KetChuyenTienMatSCK` — Year-end cash balance carry-forward
- `KetChuyenTienGuiNganHangSCK` — Year-end bank balance carry-forward
- `KetChuyenCongNoPhaiThu` — Year-end receivable carry-forward
- `KetChuyenCongNoPhaiTra` — Year-end payable carry-forward
- `calculateCashFlow` — Calculate cash flow for last 3 days
- `saveRevenueBookingJob` — Save booking revenue to ec_revenue table

### HR/Payroll Jobs
- `createMonthSalary` — Auto-create monthly salary records
- `updateWorkingDays` — Update working days count in salary records
- `updateEfforts` — Update effort/KPI metrics in salary records
- `lockSalaryAtEndMonth` — Lock salary records at month-end
- `updateMissingEfforts` — Backfill efforts if salary locked before month-end

### Operations Jobs
- `updateOnlineReport` — Daily online activity report
- `checkBookingHandle` — Check if assigned bookings are being handled
- `checkStatusOnlineUser` — Check if users are still online
- `reAssignBooking` — Re-assign unhandled bookings
- `checkExpirationDateVoucher` — Check voucher expiration dates
- `notifyCheckinJourney` — Notify 24h before flight departure for checkin

### Zalo/Marketing Jobs
- `sendAutoCheapPriceMessageZalo` — Auto-send cheap ticket alerts via Zalo ZBS
- `maintainZaloChat` — Auto-send consultation messages to maintain Zalo engagement
- `resetRewardPoints` — Annual reset of customer loyalty points
- `migrateZaloImagesToNextCloud` — Sync images from Zalo CDN to VN Backup
- `sendPromotionalSummerZBS` — Summer promotional campaign via Zalo

---

## WebRTC Phone Integration

Located in `custom/jssip_webrtc/`:
- Uses **JsSIP 3.9.4** library for SIP-based VoIP calls
- `call.js` (~78KB) — Full call handling (incoming/outgoing, hold, transfer)
- `call.css` — Call panel UI styling
- `service-worker.js` — Browser push notifications for incoming calls ("Cuộc gọi đến")
- Connects to SIP server configured via `$sugar_config['webrtc']`

---

## Customization Pattern

SuiteCRM uses a layered override system:
1. Base logic in `modules/{Module}/`
2. Custom overrides in `custom/modules/{Module}/` (preferred place for edits)
3. Language strings in `{module}/language/` and `custom/{module}/language/`
4. Metadata/vardefs in `{module}/metadata/` and `custom/{module}/Ext/`

Always prefer editing files under `custom/` rather than modifying core `modules/` files directly, to avoid conflicts when upgrading SuiteCRM.

### Adding a New Entry Point Class
1. Create class file in `custom/entrypoints/entryAuthClass/` (authenticated) or `custom/entrypoints/entryNonAuthClass/` (non-auth)
2. Name file `entry<Name>Class.php` (must match pattern `entry*Class.php`)
3. Extend `entryClass` base class
4. The factory will auto-discover and load it

### Adding a New Scheduler Job
1. Add `$job_strings[] = 'functionName';` at top of `custom/modules/Schedulers/_AddJobsHere.php`
2. Define `function functionName()` in the same file
3. Register in SuiteCRM Admin → Schedulers

---

## UI Architecture

- **Module Views**: Smarty-based templates in `modules/{Module}/tpls/` + view classes in `modules/{Module}/views/`
- **Controllers**: `modules/{Module}/controller.php` — extends `SugarController`, maps actions to views
- **JavaScript**: Module-specific JS in `modules/{Module}/js/`, global in `jssource/`
- **Tab Groups**: Defined in `custom/include/tabConfig.php` — groups modules into UI tabs:
  - Sales (Flight Bookings, Calls, Refunds, Zalo, Messages, Vouchers, Accounts)
  - Contacts (TongHop, Contacts, Zalo Contacts)
  - HR (Leave, Overtime, Salary)
  - Accounting (Receipt/Payment Vouchers, Invoices, Bank Accounts)
  - Settings (Location, Documents, Airports, Airlines, Login Audit)

---

## CI/CD

- `.github/workflows/dev-ci.yml` — Runs on push to `main` and PRs: validates composer, installs deps, runs PHP syntax check on `custom/` and `modules/`
- `.github/workflows/prod-ci.yml` — Production deployment: triggers after successful dev-ci on `main`, SSH to server → `git pull origin main`
- PHP 7.4 is required (both local and CI)
- **Branching**: Active branches include feature branches (`hungnh_*`, `dahyvan-tkve`), `dev`, and `main`

## Configuration

- `config.php` — Main config (database credentials, site URL, etc.) — not committed
- `config_override.php` — Environment-specific overrides (not committed, contains API keys/tokens)
- Key config sections in `config_override.php`:
  - `$sugar_config['flight_config']` — VAT percentage
  - `$sugar_config['notification_channel']` — Active notification channel (`Telegram`)
  - `$sugar_config['telegram'][profile]` — Telegram bot tokens/chat IDs (multi-profile)
  - `$sugar_config['api_autobook']` — Datacom flight search/booking API
  - `$sugar_config['win_invoice']` — WinInvoice e-invoice credentials
  - `$sugar_config['misa']` — MISA AMIS accounting credentials
  - `$sugar_config['zalo_config']` — Zalo OA configuration
  - `$sugar_config['next-cloud']` — NextCloud/VNBackup file storage
  - `$sugar_config['webrtc']` — WebRTC/SIP server config
  - `$sugar_config['onepay']` — OnePay payment gateway
  - `$sugar_config['ip_whitelist']` — Allowed IPs for non-auth endpoints
  - `$sugar_config['api_key']['non_auth_entrypoint']` — API key for non-auth entry points
  - `$sugar_config['external_cache']['redis']` — Redis cache config
  - `$sugar_config['chat_widget']` — Chat widget WebSocket/REST config

## Important Conventions

- **Timezone**: Always `Asia/Ho_Chi_Minh` — set in entry points and Docker config
- **Language**: Vietnamese-first UI labels, Vietnamese function/variable names in business logic are common
- **Date format**: `dd-mm-yyyy` for display, `yyyy-mm-dd` for database
- **Error handling**: All entry points wrap in try/catch with JSON error responses
- **Notifications**: Use `NotificationService::send*Message()` for all alerts — do NOT call Telegram API directly
- **Logging**: Use `LoggerHelper::error()` for custom file logging, `$GLOBALS['log']` for SuiteCRM log
- **SQL**: Direct `$db->query()` calls are common (SuiteCRM pattern), always check `deleted = 0`
- **File naming**: Entry point classes must follow `entry*Class.php` pattern for auto-discovery
