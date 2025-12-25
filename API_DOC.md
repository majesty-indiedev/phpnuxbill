# API Documentation (PHPNuxBill)

This project exposes two API-style entrypoints:

- **JSON App API**: `system/api.php` (controller-backed JSON responses)
- **FreeRADIUS REST endpoint**: `radius.php` (RADIUS authenticate/authorize/accounting)

---

## JSON App API (`system/api.php`)

### Base URL

- **`{APP_URL}/system/api.php`**

All API requests are routed through this single file.

### Routing

Routes are passed in the query parameter `r`:

- **`GET/POST {APP_URL}/system/api.php?r=<handler>/<action>/<param1>/<param2>...`**

Where:

- **`<handler>`** maps to a controller file: `system/controllers/<handler>.php`
- Controllers receive the route segments via the global `$routes` array (from `explode('/', $_GET['r'])`)

Examples:

- `r=dashboard`
- `r=customers`
- `r=customers/view/123/order`

### Response schema

The API always returns JSON using `showResult()`:

- **`success`**: boolean
- **`message`**: string
- **`result`**: mixed (usually arrays/objects)
- **`meta`**: mixed (optional metadata)

### Authentication

API auth is provided via the `token` request parameter:

- **`token`** can be:
  - The static app key: **`token == $config['api_key']`** (configured in DB app config)
  - A signed token: **`<type>.<uid>.<time>.<sha1>`**

Token types accepted by `system/api.php`:

- **`a`**: admin session token
- **`c`**: customer session token

Signature validation:

- `sha1` must match: `sha1($uid . '.' . $time . '.' . $api_secret)`
- Token expiration: **~3 months** (`7776000` seconds) when `time != 0`

Default secret:

- If `$api_secret` is not set, it defaults to the DB password (`$db_pass`) in `init.php`.

Convenience routes:

- `r=isValid` → returns “Token is valid” if the token checks out
- `r=me` → returns admin information if the token represents an admin

### Notes / gotchas

- This API **reuses web controllers**. Some routes may:
  - Redirect in web mode (API converts redirects into JSON errors via `r2()` / `_alert()`), or
  - Output non-JSON content (e.g., CSV download endpoints) depending on controller logic.
- **Customer login token mismatch warning** (as of this snapshot):
  - `system/controllers/login.php` returns a token prefixed with `u.`
  - `system/api.php` validates customer tokens with prefix `c.`
  - If you plan to rely on customer API auth, confirm/fix this in your version.

### Known working endpoints (from `docs/insomnia.rest.json`)

The repo includes an Insomnia collection (`docs/insomnia.rest.json`) showing these example calls:

#### Admin

- **Login (admin)**: `POST r=admin/post`
  - Body (form): `username`, `password`
  - Returns: `{ result: { token: "a.<uid>.<time>.<sha1>" } }`

- **Dashboard**: `GET r=dashboard`

#### Customers (admin auth required)

- **List customers**: `GET r=customers`
  - Query parameters commonly used in the collection:
    - `search`
    - `order` (e.g. `username`, `fullname`, `lastname`, `created_at`)
    - `filter` (status-based)
    - `orderby` (`asc` / `dsc`)
    - `p` (page, starting from 1)

- **View activation history**: `GET r=customers/view/<id>/activation`
- **View order history**: `GET r=customers/view/<id>/order`
- **Add customer**: `POST r=customers/add-post`
- **Edit customer**: `POST r=customers/edit-post`
- **Delete customer**: `GET r=customers/delete/<id>`

### Discovering more endpoints

Because routing is controller-backed, the full set of potential handlers is the set of files under:

- `system/controllers/*.php`

To discover actions for a handler, inspect its controller file for:

- `switch ($action) { ... }`
- use of `$routes[1]`, `$routes[2]`, etc.

---

## FreeRADIUS REST Endpoint (`radius.php`)

### Purpose

`radius.php` is a JSON endpoint intended for FreeRADIUS REST integration, handling:

- **Authorize** (voucher activation and allowance checks)
- **Authenticate** (credential validation)
- **Accounting** (usage/logging)

### How to select action

The action is derived from:

- Header: **`X-Freeradius-Section`** (available in PHP as `$_SERVER['HTTP_X_FREERADIUS_SECTION']`)
- Fallback: query parameter `action`

Supported actions (from the `switch` statement):

- `authenticate`
- `authorize`
- `accounting`

### Response format

Returns JSON attribute maps for FreeRADIUS and sets HTTP status:

- `200 OK` for success
- `401 Unauthorized` for reject
- `204 No Content` for empty/no-op cases

---

## Quick curl-style examples (pattern only)

### Call JSON App API

- `GET {APP_URL}/system/api.php?r=dashboard&token=<TOKEN>`

### Call RADIUS endpoint

- `POST {APP_URL}/radius.php` with header `X-Freeradius-Section: authenticate`
