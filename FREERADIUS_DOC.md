# FreeRADIUS in this codebase (how it works)

This document explains how **FreeRADIUS** integrates with PHPNuxBill in this repo, what code paths are involved, what data is stored, and what requests/responses look like.

This guide focuses only on the **REST integration** (FreeRADIUS calls this app over HTTP).

---

## REST mode: FreeRADIUS ↔ `radius.php`

### What it is

FreeRADIUS calls the project’s endpoint:

- **`{APP_URL}/radius.php`**

That file implements 3 sections:

- `authenticate`
- `authorize`
- `accounting`

The section is selected by the request header:

- **`X-Freeradius-Section`** → `$_SERVER['HTTP_X_FREERADIUS_SECTION']`

Fallback:

- `?action=<section>`

### High-level flow

1. NAS sends Access-Request / Accounting to FreeRADIUS
2. FreeRADIUS uses the `rest` module to call `radius.php` with the current section
3. `radius.php`:
   - validates customer/voucher credentials
   - finds an active subscription (`tbl_user_recharges`)
   - generates RADIUS reply attributes (rate-limit, expiry, data limits, etc.)
   - stores accounting snapshots into `rad_acct` (app DB table)

### Where data lives (REST mode)

REST mode uses the **main PHPNuxBill database** tables:

- **`tbl_user_recharges`**: active subscription per username (`status='on'`, `routers='radius'`)
- **`tbl_plans`** + **`tbl_bandwidth`**: plan limits and rate settings
- **`tbl_customers`**: customer password / PPPoE password mapping
- **`tbl_voucher`**: voucher activation flow when `routers='radius'`
- **`rad_acct`** (in the *PHPNuxBill* DB): accounting snapshots used for:
  - online detection
  - remaining data computation for data-limited plans

> Note: `rad_acct` is created by `install/phpnuxbill.sql` (and tracked in `system/updates.json`).

---

## REST mode: the 3 sections

### 1) `authenticate`

Goal: “Are these credentials valid?”

Inputs (read using `_req()`):

- `username`
- `password`
- optionally CHAP:
  - `CHAPassword`
  - `CHAPchallenge`

Rules:

- If CHAP is present, the code checks CHAP against:
  - `tbl_customers.password`
  - `tbl_customers.pppoe_password`
  - voucher-style username-as-password (including “empty password” voucher)
- Non-CHAP: if `username == password`, it treats it like a voucher login attempt.

Success behavior:

- If credentials match an active customer or a voucher, it returns **`204 No Content`**.

Failure behavior:

- Returns **`401 Unauthorized`** with JSON describing the rejection.

### 2) `authorize`

Goal: “Given a valid identity, what attributes/limits should the session have?”

This is where plan logic is enforced:

1. Identify user:
   - `username/password` or CHAP
   - If login used `pppoe_username`, it maps back to the customer’s primary `username`.
2. Find subscription:
   - Look up `tbl_user_recharges` by `username`
   - If not found, it tries mapping `pppoe_username → username` and re-checks.
3. If subscription is active, build reply attributes via `process_radiust_rest($tur, $code)`.
4. If not active:
   - Voucher flow: if `username == password`, it tries to activate a voucher in `tbl_voucher` **only when** `routers='radius'`.
   - Otherwise rejects (plan expired / invalid).

Key reply attributes produced in `process_radiust_rest()`:

- **Rate limiting**
  - `reply:Mikrotik-Rate-Limit`
  - `reply:Ascend-Data-Rate`
  - `reply:Ascend-Xmit-Rate`
  - `reply:WISPr-Bandwidth-Max-Up`
  - `reply:WISPr-Bandwidth-Max-Down`
- **Session/expiry**
  - `reply:expiration` (human readable)
  - `reply:WISPr-Session-Terminate-Time` (ISO-like)
- **Concurrent logins**
  - `Simultaneous-Use` (from plan `shared_users`)
  - Additionally, for Hotspot plans it tries to block excess sessions by counting active `rad_acct` rows with `acctstatustype='Start'`.
- **Data limit enforcement** (when plan is Limited + Data_Limit/Both_Limit)
  - `reply:Mikrotik-Total-Limit` is set to `(plan_limit_bytes - used_bytes)`
  - If remaining is negative, it rejects with “You have exceeded your data limit.”
- **Time limit enforcement**
  - `reply:Max-All-Session` / `reply:Expire-After`
- **PPPoE pool**
  - `reply:Framed-Pool` is set when plan `type == 'PPPOE'`

### 3) `accounting`

Goal: store usage snapshots and optionally respond with updated limits.

Important implementation detail: this code stores *the latest counters* per session, not a running sum.

Accounting writes into **`rad_acct`** (PHPNuxBill DB).

It keys the row on:

- `username`
- `acctsessionid` (`acctSessionId`)
- `macaddr` (`macAddr`)
- `nasid`

It also combines Octets + Gigawords:

- `out = acctOutputOctets + acctOutputGigawords * 4294967296`
- `in  = acctInputOctets  + acctInputGigawords  * 4294967296`

Then:

- It only persists the row if the username has an active recharge with `routers='radius'` and `status='on'` (or matches via PPPoE mapping).
- On `acctStatusType == Start` and for data-limited plans, it calculates a remaining total limit and rejects if exceeded.
- Otherwise it returns Accept + “Saved”.

---

## “Online user” and interim-update cleanup (REST mode)

### Online detection

The device class `system/devices/RadiusRest.php` implements `online_customer()` by looking at the latest `rad_acct` row and considering a user online if:

- `acctstatustype` is `Start` or `Interim-Update`, and
- `dateAdded` is within a window derived from `frrest_interim_update` (default ~10 minutes if unset).

### Cron cleanup

`system/cron.php` will convert stale sessions to `Stop` if:

- `frrest_interim_update != 0`, and
- the last `dateAdded` is older than `frrest_interim_update` minutes.

This keeps “online” state from getting stuck when interim-updates stop.

---

## Pointers in the codebase

- REST endpoint: `radius.php`
- REST accounting table: `rad_acct` (created in `install/phpnuxbill.sql`)
- REST device helper: `system/devices/RadiusRest.php`
