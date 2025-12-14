# PHPNuxBill Developer Guide (Codebase Tour + Customization Handbook)

This guide is meant for **people customizing PHPNuxBill**. After reading it you should know:

- Where requests enter the app and how routing works
- Where business logic lives (controllers + “autoload” classes)
- How the UI is rendered (Smarty templates, themes, and overrides)
- How the database is structured and how it’s queried
- Where to safely add new features (hooks/plugins, widgets, devices, payment gateways, templates)
- How the cron + API work

> File paths below are relative to the project root.

---

## Architecture in one page

PHPNuxBill is a **single-entry / front-controller** style PHP app.

1. Browser hits `index.php`
2. `index.php` bootstraps the system (`system/vendor/autoload.php`, then `system/boot.php`)
3. `system/boot.php`:
   - loads `init.php` (config, DB, helpers, plugins)
   - initializes Smarty (`$ui`)
   - resolves the route into `system/controllers/<handler>.php`
   - includes that controller file
   - controller assigns values to Smarty and renders a `ui/ui/.../*.tpl` template

There are 3 major “execution modes”:

- **Normal Web UI**: `index.php` + `system/boot.php` + Smarty templates.
- **API**: `system/api.php` loads `init.php` and then includes a controller, but uses a dummy `$ui` that collects assigned values and returns JSON.
- **Cron**: `system/cron.php` + `system/cron_reminder.php` load `init.php` and run scheduled maintenance (expiry handling, reminders, router monitoring).

---

## Directory map (what goes where)

### Root files

- **`index.php`**: main entrypoint (also captures hotspot parameters like `nux-mac`, `nux-ip`, `nux-router`, `nux-key`, `nux-hostname` into `$_SESSION`).
- **`admin/index.php`**: redirects to `/?_route=admin/` (admin landing).
- **`init.php`**: bootstrap “foundation”:
  - autoloading for `system/autoload/*.php`
  - `config.php` loading
  - DB connection configuration (Idiorm `ORM`)
  - global helper functions (`_get`, `_post`, `_auth`, `_admin`, `r2`, `_alert`, `_log`, etc.)
  - plugin auto-load: includes `system/plugin/*.php`
  - loads app config from DB (`tbl_appconfig`) into `$config`
  - language loading (`system/lan/*.json`)
- **`config.php`**: your environment config (DB creds, stage, APP_URL, etc.). See `config.sample.php`.

### `system/`

- **`system/boot.php`**: the request router + Smarty setup. If you’re ever asking “how does a URL become a page?”, start here.
- **`system/controllers/`**: “controllers” (page logic). Each file typically:
  - reads `$routes` and `$routes[1]` as action
  - authorizes (`_auth()` for customers, `_admin()` for admin)
  - does DB work with `ORM::for_table(...)`
  - `run_hook('some_action')`
  - `$ui->assign(...)` then `$ui->display('...tpl')`
- **`system/autoload/`**: “service/library” classes used by controllers:
  - `Admin`, `User`, `Package`, `Message`, `Mikrotik`, `Csrf`, `Validator`, etc.
- **`system/devices/`**: “device connectors” for network enforcement (MikroTik hotspot/PPPoE/VPN, Radius, etc.).
- **`system/widgets/`**: widget implementations loaded onto dashboards (admin + customer widgets).
- **`system/paymentgateway/`**: payment gateway modules (installed by Plugin Manager). Each gateway is a PHP file with a specific set of functions.
- **`system/plugin/`**: plugin PHP files (installed by Plugin Manager). Loaded automatically at boot.
- **`system/cron.php`**: expiry processing + router monitoring + auto-renewal.
- **`system/cron_reminder.php`**: sends 7/3/1 day reminders.
- **`system/api.php`**: JSON API entrypoint.
- **`system/lan/`**: language JSON files.

### `ui/`

Smarty templates and front-end assets.

- **`ui/ui/`**: main templates, split into:
  - `ui/ui/admin/...` (admin pages)
  - `ui/ui/customer/...` (customer pages)
  - shared includes: `ui/ui/sections/*`, `ui/ui/pagination.tpl`, etc.
- **`ui/ui_custom/`**: your override folder. This is the safest place to customize templates without conflicts.
  - Smarty searches `ui_custom` before the default templates.
- **`ui/themes/<themeName>/`**: theme overrides (optional). `system/boot.php` adds theme dirs when `$config['theme'] != 'default'`.
- **`ui/ui/scripts/`**, **`ui/ui/styles/`**, **`ui/ui/images/`**: JS/CSS/assets.

### `install/`

- installer + SQL files: `install/phpnuxbill.sql` and `install/radius.sql`.

---

## Request lifecycle (step-by-step)

### 1) Entrypoint: `index.php`

- Starts session.
- Captures hotspot-provided GET params into `$_SESSION`:
  - `nux-mac`, `nux-ip`, `nux-router`, `nux-key`, `nux-hostname`
- Loads Composer autoload and `system/boot.php`.

### 2) Bootstrap: `system/boot.php` and `init.php`

`system/boot.php` first includes `init.php`, which:

- Loads `config.php`
- Sets up global paths like `$DEVICE_PATH`, `$UPLOAD_PATH`, `$CACHE_PATH`, etc.
- Configures the Idiorm DB connection:
  - `ORM::configure("mysql:host=$db_host;dbname=$db_name")`
- Loads dynamic settings from DB table `tbl_appconfig` into `$config`
- Loads language map into `$_L` (from `system/lan/<language>.json`)
- Defines helper functions (`_get`, `_post`, `r2`, `_alert`, `_auth`, `_admin`, etc.)
- Loads plugins: includes all `system/plugin/*.php` (failures are caught and ignored).

### 3) Route parsing (important)

PHPNuxBill uses a `handler/action/...` route format.

- Preferred way: `/?_route=<handler>/<action>/<param1>/<param2>...`
- If `?_route` is missing, `system/boot.php` tries to derive route from the path portion of the URL.

Then:

- `$routes = explode('/', $req)`
- `$handler = $routes[0]` (defaults to `default` if empty)
- Controller file path is `system/controllers/<handler>.php`
- If it exists: `include($sys_render)`

### 4) Controller executes and renders a template

Typical controller responsibilities:

- Authorization:
  - Customer pages: `_auth()`
  - Admin pages: `_admin()`
- Read `$_GET/$_POST` using `_get()` / `_post()` (sanitized) or `_req()`
- DB reads/writes via `ORM::for_table('tbl_*')`
- Call `run_hook(...)` at key points
- Assign variables to Smarty: `$ui->assign('name', $value)`
- Render template: `$ui->display('admin/...tpl')` or `$ui->display('customer/...tpl')`

---

## Routing cheat sheet (controllers you’ll touch a lot)

Controller = `system/controllers/<handler>.php`

- **Auth / Entry**:
  - `login.php`: customer login + voucher activation flows
  - `admin.php`: admin login flows
  - `logout.php`: logout
- **Customer**:
  - `home.php`, `order.php`, `accounts.php`, `voucher.php`
- **Admin**:
  - `dashboard.php`
  - `customers.php`, `plan.php`, `routers.php`, `pool.php`, `bandwidth.php`
  - `settings.php`, `message.php`, `reports.php`
  - `widgets.php`: dashboard widget configuration UI
- **Integration**:
  - `paymentgateway.php`: gateway management + gateway config UI
  - `callback.php`: payment provider webhooks (calls gateway’s `*_payment_notification()`)
  - `pluginmanager.php`: downloads & installs plugins/gateways/devices/themes
- **Static CMS pages**:
  - `pages.php` / `page.php`: public pages / terms / privacy etc.

---

## Smarty templates (UI layer)

### Template search order (override strategy)

Smarty template directories are configured in `system/boot.php`.

- `ui/ui_custom/` is always included and searched first (best for customization).
- If `$config['theme'] != 'default'`, then `ui/themes/<themeName>/` is used.
- Finally, the default `ui/ui/` is used.

### Where templates live

- Admin templates: `ui/ui/admin/...`
- Customer templates: `ui/ui/customer/...`
- Shared sections: `ui/ui/sections/...`
- Widgets templates: `ui/ui/widget/...`

### How controllers pass data to templates

Controllers do:

- `$ui->assign('key', $value)` for template variables
- `$ui->display('path/to/template.tpl')`

Common “global” template vars set in `system/boot.php`:

- `app_url` (APP_URL)
- `_url` (APP_URL + `/?_route=`)
- `_c` (the `$config` array)
- `_routes` (route segments)
- `_admin` / `_user` (current session user, depending on page)
- `_theme` (theme base URL)

---

## Database layer

### ORM

Database access uses Idiorm (`system/orm.php`) through the `ORM` class:

- `ORM::for_table('tbl_customers')->where('username', $u)->find_one()`
- `->find_many()`, `->find_array()`, `->create()`, `->save()`, `->delete()`

### Key tables (from `install/phpnuxbill.sql`)

You will customize features faster if you memorize these:

- **`tbl_appconfig`**: dynamic settings loaded into `$config` at boot.
- **`tbl_users`**: admin/staff users (SuperAdmin/Admin/Report/Agent/Sales) + `login_token` for single-session enforcement.
- **`tbl_customers`**: customer accounts + balance + status + auto-renewal flags.
- **`tbl_customers_fields`**: customer key/value attributes (used heavily for “Bills”, “Invoice”, “Language”, “Expired Date”, custom fields).
- **`tbl_plans`**: plans (Hotspot/PPPOE/Balance), validity, price, router binding, `device`, and plan scripts.
- **`tbl_user_recharges`**: active subscriptions per customer/router/type; includes expiration and status.
- **`tbl_transactions`**: financial transaction ledger (invoice ID, price, expiration, gateway method).
- **`tbl_voucher`**: generated vouchers + status/used_date.
- **`tbl_routers`**: router inventory + status monitoring.
- **`tbl_payment_gateway`**: pending/paid gateway transactions and audit data.
- **`tbl_widgets`**: dashboard widget config.
- **`tbl_logs`**: system logs via `_log()`.
- **`tbl_customers_inbox`**: in-app inbox messages (notifications).
- **`tbl_message_logs`**: logging for SMS/WA/Email send attempts.

There are additional tables for radius and networking features (e.g. `rad_acct`).

---

## Authentication & sessions

### Customer auth

- Customer session key: `$_SESSION['uid']`
- Customer cookie: `uid` with format `id.time.sha1(id.time.db_pass)`
- Helper:
  - `_auth()` redirects to `login` if not logged in
  - `User::getID()` returns current customer ID or 0
  - `User::_info()` fetches current customer ORM record

### Admin auth

- Admin session key: `$_SESSION['aid']`
- Admin cookie: `aid` (signed token) and optional session timeout `$_SESSION['aid_expiration']`
- Helper:
  - `_admin()` redirects to login if not logged in
  - `Admin::_info()` fetches current admin ORM record

### Single-session enforcement (admins)

`Admin::setCookie()` stores a `login_token` hash in `tbl_users.login_token`.
If `$config['single_session'] == 'yes'`, subsequent logins overwrite the token and invalidate older sessions.

### CSRF

CSRF is handled by `system/autoload/Csrf.php` and is enabled when:

- `$config['csrf_enabled'] == 'yes'` and not API mode.

Common pattern:

- On form display: `$csrf_token = Csrf::generateAndStoreToken()` and pass it to template.
- On POST: `Csrf::check(_post('csrf_token'))`.

---

## Business logic: the “core” classes you’ll edit around

### `Package` (billing + activation)

`system/autoload/Package.php` is the heart of subscription logic.

Key method:

- **`Package::rechargeUser($customerId, $routerName, $planId, $gateway, $channel, $note = '')`**
  - Calculates expiry based on `validity` + `validity_unit`
  - Extends expiry when `extend_expiry == yes` and plan matches
  - Calls device connector: `(new $plan['device'])->add_customer(...)` / `remove_customer(...)`
  - Updates `tbl_user_recharges`
  - Inserts into `tbl_transactions`
  - Sends invoice notifications via `Message::sendInvoice(...)`

Other helpers:

- `Package::getDevice($plan)` chooses device implementation file under `system/devices/`.
- `Package::rechargeBalance(...)` for balance top-ups.

If you want to add “new billing rules”, this is usually where you hook in.

### `Message` (notifications)

`system/autoload/Message.php` sends:

- Telegram (`sendTelegram`)
- SMS (`sendSMS`, optionally via MikroTik SMS tool)
- WhatsApp (`sendWhatsapp`)
- Email (`sendEmail`, SMTP or PHP mail)
- It also writes to message logs (`tbl_message_logs`) and inbox (`tbl_customers_inbox`).

It is also heavily “hookable”: calls `run_hook('send_sms')`, `run_hook('send_whatsapp')`, `run_hook('send_email')`, etc.

### `Mikrotik`

`system/autoload/Mikrotik.php` wraps RouterOS API operations for hotspot/pppoe objects.
Device classes typically call into this layer (or directly use RouterOS client).

### `User`

`system/autoload/User.php` handles customer identity, “attributes” in `tbl_customers_fields`, and some helpers:

- `User::getBills()` / `User::billsPaid()` for additional costs
- `User::getAttribute()` / `setAttribute()` / `getAttributes()`
- `User::generateToken()` used for short-lived “payment links”

### `Admin`

`system/autoload/Admin.php` handles admin cookie tokens, session timeout, and token validation.

---

## Extension points (best places to customize)

### 1) Template overrides: `ui/ui_custom/` (recommended)

If you want to:

- Change page layout
- Add/modify UI elements
- Customize branding

Prefer copying a template from `ui/ui/...` into `ui/ui_custom/...` with the **same relative path**, then editing it there.

This avoids conflicts when the upstream code changes.

### 2) Hooks: `register_hook()` + `run_hook()`

Hooks allow you to inject logic without modifying core controllers.

In `system/autoload/Hookers.php`:

- `register_hook($action, $functionName)`
- `run_hook($action, $args = [])`

Controllers and core classes call `run_hook(...)` in many places, e.g.:

- `customer_login`
- `view_dashboard`
- `recharge_user`, `recharge_user_finish`
- `cronjob`, `cronjob_end`, `cronjob_reminder`
- `send_sms`, `send_whatsapp`, `send_email`, `send_telegram`
- `callback_payment_notification`

**How to add a hook**:

1. Create a plugin file: `system/plugin/my_custom.php`
2. In it:
   - define your function `my_custom_handler($args)`
   - call `register_hook('some_hook_name', 'my_custom_handler')`

Your plugin file will be auto-included at boot.

### 3) Plugins & menus: `register_menu()` + `plugin` controller

Plugins can add menu entries without editing templates.

- Plugins register a menu via `register_menu(...)`.
- Menu links go to `/?_route=plugin/<functionName>`
- `system/controllers/plugin.php` then does `call_user_func($routes[1])`.

That means a plugin can implement “pages” as PHP functions.

**Important**: This is powerful but easy to misuse. Treat plugin callable functions like controllers:

- Validate/authorize at the top (`_admin()` / `_auth()`)
- Sanitize inputs (`_get`, `_post`, `alphanumeric`)
- Use `$ui->assign` and `$ui->display` for rendering

### 4) Widgets: `system/widgets/*.php`

Dashboard widgets are stored in `tbl_widgets` and rendered in `system/controllers/dashboard.php`:

- It loads each widget file from `$WIDGET_PATH/<widgetName>.php`
- Then calls `(new <widgetName>)->getWidget($widgetRow)`

To add a new widget:

1. Add a PHP class file under `system/widgets/` (or `system/widgets/customer/` for customer widgets)
2. Ensure the class name matches the file name
3. Return HTML from `getWidget(...)`
4. Add the widget in the UI: `/?_route=widgets`

### 5) Devices: `system/devices/*.php`

Devices implement the integration to enforce plan access:

- `MikrotikHotspot`, `MikrotikPppoe`, `MikrotikVpn`
- `Radius`, `RadiusRest`
- `Dummy` (no-op / dev/testing)

The “active device class” is chosen by:

- `tbl_plans.device` or fallback logic in `Package::getDevice($plan)`

Typical methods you’ll see/care about:

- `add_customer($customer, $plan)`
- `remove_customer($customer, $plan)`
- `connect_customer($customer, $ip, $mac, $routerName)` (for hotspot redirect flows)
- `sync_customer($customer, $plan)` (optional)

If you need a new vendor/router type, creating a new device class is the cleanest approach.

### 6) Payment gateways: `system/paymentgateway/<name>.php`

Gateways are “modules” discovered by scanning `system/paymentgateway/*.php`.

Expected functions (pattern from controllers):

- `<name>_show_config()` — show config UI
- `<name>_save_config()` — save config POST
- `<name>_payment_notification()` — webhook handler (called via `/?_route=callback/<name>`)

Templates for gateways can be placed under:

- `system/paymentgateway/ui/` (added to Smarty as a template dir namespace)

You usually install gateways via `/?_route=pluginmanager` rather than writing them from scratch.

---

## Cron jobs

### `system/cron.php` (expiry + monitoring)

Main responsibilities:

- Finds expired `tbl_user_recharges` and marks them off
- Calls device connector to remove user access
- Sends “expired” notification using `Message::sendPackageNotification(...)`
- Auto-renews using customer balance if enabled and customer has `auto_renewal` on
- Optional router status monitoring (TCP connect to port 8728 by default) and sends alerts

It also writes a lock file in cache (`router_monitor.lock`) to avoid overlapping runs.

### `system/cron_reminder.php` (7/3/1 day reminders)

Sends reminder messages when expiration date is exactly +7/+3/+1 days.

---

## API mode (`system/api.php`)

### How it works

- Sets `$isApi = true`
- Includes `init.php`
- Builds a dummy `$ui` that collects `$ui->assign(...)` values
- Includes the same controller file as web mode: `system/controllers/<handler>.php`
- Returns JSON (`showResult(...)`)

### Auth

Supports a `token` parameter:

- `token == $config['api_key']` (simple static token)
- or token format: `a.<uid>.<time>.<sha1>` (admin) / `c.<uid>.<time>.<sha1>` (customer)
  - sha1 validation uses `$api_secret` (defaults to DB password if not set)
  - token expiration is checked (3 months)

### Practical guidance

Not every web controller is “API-safe” because many assume browser redirects and template rendering.
If you want stable APIs, create “API-specific routes” inside controllers that:

- validate auth
- assign structured arrays
- avoid calling `$ui->display(...)` (or call it knowing API collects assigned vars)

---

## Customization playbook (how to add features safely)

### Customize UI without touching core templates

- Copy target template from `ui/ui/...` → `ui/ui_custom/...` and edit there.

### Add a new admin page (controller style)

1. Create: `system/controllers/myfeature.php`
2. Add templates:
   - `ui/ui/admin/myfeature/list.tpl`
   - `ui/ui/admin/myfeature/add.tpl` etc.
3. Access via:
   - `/?_route=myfeature/list`

To add it to the navigation, either:

- edit the admin menu template in `ui/ui_custom` (direct), or
- register a plugin menu entry (recommended if you want loose coupling).

### Add a new menu item using a plugin (fastest)

1. Create `system/plugin/myfeature.php`
2. In that file:
   - call `register_menu('My Feature', true, 'myfeature_page', 'AFTER_DASHBOARD', 'ion-ios-cog')`
   - implement function `myfeature_page()`:
     - `_admin()`
     - query DB / assign vars
     - `$ui->display('admin/myfeature/list.tpl')`

Then the link becomes `/?_route=plugin/myfeature_page`.

### Add/extend a billing rule

Most billing changes are in:

- `system/autoload/Package.php` (expiry calc, recharge insert, invoice creation)
- `system/controllers/plan.php` (admin recharge flows)
- `system/controllers/order.php` (customer purchase flows)

Prefer adding logic using hooks (`recharge_user`, `recharge_user_finish`) if possible.

### Add a new notification channel

Implement in `system/autoload/Message.php` (and call it from `sendPackageNotification` / `sendInvoice`),
or implement as hook handlers (e.g. `register_hook('send_whatsapp', ...)`).

---

## Debugging & troubleshooting

- **Global error UX**:
  - Web UI errors are caught in `system/boot.php` and shown in `admin/error.tpl` or `customer/error.tpl`.
  - Errors can be sent to Telegram (`Message::sendTelegram(...)`) in several catch blocks.
- **App stage**:
  - `$_app_stage` is used to disable “dangerous” actions in demo mode.
- **Logs**:
  - `_log(...)` writes to `tbl_logs`.
  - Notification send logs go to `tbl_message_logs`.
- **Common gotcha**:
  - Many flows depend on `tbl_appconfig` values being present; missing settings can break UI assumptions. When debugging, check what’s in `$config`.

---

## Recommended workflow for your own custom features

- **Step 1**: Use `ui/ui_custom/` for template changes (safe upgrades).
- **Step 2**: Use plugins + hooks for adding logic without touching core controllers.
- **Step 3**: If you need new “system behavior” (billing rules, new router types, new gateways), implement:
  - a new device class OR
  - a new payment gateway module OR
  - a new controller + templates
- **Step 4**: Keep a small “customization layer”:
  - `system/plugin/my_company_*.php`
  - `ui/ui_custom/...`
  - optional: your own widget files under `system/widgets/`

---

## Where to look for a specific thing (quick index)

- **How does a URL render a page?** `system/boot.php` + `system/controllers/<handler>.php`
- **Where are helper functions?** `init.php`
- **Where is billing + expiry logic?** `system/autoload/Package.php`
- **Where are notifications sent?** `system/autoload/Message.php`
- **How do MikroTik commands happen?** `system/autoload/Mikrotik.php` + `system/devices/*`
- **How do I override the UI?** `ui/ui_custom/`
- **How do I add hooks?** `system/autoload/Hookers.php` + `system/plugin/*.php`
- **How do I add a payment gateway?** `system/paymentgateway/*.php` + `system/controllers/paymentgateway.php` + `system/controllers/callback.php`
- **How do I add cron behavior?** `system/cron.php` + `run_hook('cronjob')`
