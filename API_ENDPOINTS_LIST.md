# Complete API Endpoints List - PHPNuxBill

## Base URL
**`{APP_URL}/system/api.php?r=<route>&token=<token>`**

---

## Authentication & Authorization

### Admin Authentication
- **`POST r=admin/post`**
  - Login as admin
  - Body: `username`, `password`
  - Returns: `{ result: { token: "a.<uid>.<time>.<sha1>" } }`

### Customer Authentication  
- **`POST r=login/post`**
  - Login as customer
  - Body: `username`, `password`
  - Returns: Customer token

### Token Validation
- **`GET r=isValid`**
  - Check if token is valid
  - Returns: "Token is valid" message

- **`GET r=me`**
  - Get admin information from token
  - Returns: Admin details if token is valid

---

## Dashboard

- **`GET r=dashboard`**
  - Get dashboard statistics and widgets
  - Requires: Admin token
  - Returns: Dashboard data, widgets, statistics

---

## Customers Management

### List & Search
- **`GET r=customers`**
  - List all customers with pagination
  - Query params: `search`, `order`, `filter`, `orderby`, `p` (page)
  - Returns: Paginated customer list

### View Customer
- **`GET r=customers/view/<id>`**
  - View customer details
  - Returns: Customer information

- **`GET r=customers/view/<id>/activation`**
  - View customer activation history
  - Returns: Transaction/activation history

- **`GET r=customers/view/<id>/order`**
  - View customer order history
  - Returns: Order/payment history

- **`GET r=customers/viewu/<username>`**
  - View customer by username
  - Returns: Customer information

### Create & Update
- **`POST r=customers/add-post`**
  - Create new customer
  - Body: `username`, `fullname`, `password`, `email`, `phonenumber`, `address`, `service_type`, `account_type`, `pppoe_username`, `pppoe_password`, `pppoe_ip`, `coordinates`, `city`, `district`, `state`, `zip`, `custom_field_name[]`, `custom_field_value[]`, `csrf_token`
  - Returns: Success message

- **`POST r=customers/edit-post`**
  - Update existing customer
  - Body: `id`, `username`, `fullname`, `password`, `email`, `phonenumber`, `address`, `status`, `service_type`, `account_type`, `pppoe_username`, `pppoe_password`, `pppoe_ip`, `coordinates`, `city`, `district`, `state`, `zip`, `csrf_token`
  - Returns: Success message

### Delete
- **`GET r=customers/delete/<id>`**
  - Delete customer
  - Requires: CSRF token
  - Returns: Success message

### Customer Actions
- **`GET r=customers/login/<id>`**
  - Login as customer (admin impersonation)
  - Requires: CSRF token

- **`GET r=customers/sync/<id>`**
  - Sync customer to router
  - Requires: CSRF token

- **`GET r=customers/recharge/<id>/<plan_id>`**
  - Recharge customer account
  - Requires: CSRF token

- **`GET r=customers/deactivate/<id>/<plan_id>`**
  - Deactivate customer plan
  - Requires: CSRF token

### Export
- **`GET r=customers/csv`**
  - Export customers to CSV
  - Requires: CSRF token

- **`GET r=customers/csv-prepaid`**
  - Export prepaid customers to CSV
  - Requires: CSRF token

---

## Plans Management

### List Plans
- **`GET r=services/hotspot`**
  - List all Hotspot plans
  - Query params: `name` (search), `status` (0/1), `router`, `type1` (prepaid), `type2` (plan_type), `type3` (typebp), `bandwidth`, `valid` (validity_unit), `device`
  - Returns: Paginated Hotspot plans list

- **`GET r=services/pppoe`**
  - List all PPPOE plans
  - Query params: Same as hotspot
  - Returns: Paginated PPPOE plans list

- **`GET r=services/vpn`**
  - List all VPN plans
  - Query params: Same as hotspot
  - Returns: Paginated VPN plans list

### Create & Update Hotspot Plans
- **`GET r=services/add`**
  - View add Hotspot plan form
  - Returns: Plan form data (bandwidths, routers, devices)

- **`POST r=services/add-post`**
  - Create new Hotspot plan
  - Body: Plan details (name_plan, price, type, routers, device, bandwidth, validity, etc.)
  - Returns: Success message

- **`GET r=services/edit/<id>`**
  - View edit Hotspot plan form
  - Returns: Plan data

- **`POST r=services/edit-post`**
  - Update Hotspot plan
  - Body: Plan details
  - Returns: Success message

- **`GET r=services/delete/<id>`**
  - Delete Hotspot plan
  - Returns: Success message

### Create & Update PPPOE Plans
- **`GET r=services/pppoe-add`**
  - View add PPPOE plan form
  - Returns: Plan form data

- **`POST r=services/pppoe-add-post`**
  - Create new PPPOE plan
  - Body: Plan details
  - Returns: Success message

- **`GET r=services/pppoe-edit/<id>`**
  - View edit PPPOE plan form
  - Returns: Plan data

- **`POST r=services/edit-pppoe-post`**
  - Update PPPOE plan
  - Body: Plan details
  - Returns: Success message

- **`GET r=services/pppoe-delete/<id>`**
  - Delete PPPOE plan
  - Returns: Success message

### Create & Update VPN Plans
- **`GET r=services/vpn-add`**
  - View add VPN plan form
  - Returns: Plan form data

- **`POST r=services/vpn-add-post`**
  - Create new VPN plan
  - Body: Plan details
  - Returns: Success message

- **`GET r=services/vpn-edit/<id>`**
  - View edit VPN plan form
  - Returns: Plan data

- **`POST r=services/edit-vpn-post`**
  - Update VPN plan
  - Body: Plan details
  - Returns: Success message

- **`GET r=services/vpn-delete/<id>`**
  - Delete VPN plan
  - Returns: Success message

### Balance Plans
- **`GET r=services/balance-add`**
  - View add balance plan form
  - Returns: Plan form data

- **`POST r=services/balance-add-post`**
  - Create new balance plan
  - Body: Plan details
  - Returns: Success message

- **`GET r=services/balance-edit/<id>`**
  - View edit balance plan form
  - Returns: Plan data

- **`POST r=services/balance-edit-post`**
  - Update balance plan
  - Body: Plan details
  - Returns: Success message

- **`GET r=services/balance-delete/<id>`**
  - Delete balance plan
  - Returns: Success message

### Sync Plans
- **`GET r=services/sync/hotspot`**
  - Sync all Hotspot plans to routers
  - Returns: Sync log

- **`GET r=services/sync/pppoe`**
  - Sync all PPPOE plans to routers
  - Returns: Sync log

### Customer Recharges (Active Plans)
- **`GET r=plan`** (default)
  - List customer recharges/active plans
  - Query params: `search`, `status`, `router`, `plan`
  - Returns: Paginated list of customer active plans/recharges

- **`GET r=plan/recharge`**
  - View recharge form
  - Returns: Recharge form data

- **`POST r=plan/recharge-confirm`**
  - Confirm and process recharge (view confirmation)
  - Body: `id_customer`, `server`, `plan`, `using`
  - Returns: Recharge confirmation page

- **`POST r=plan/recharge-post`**
  - Process recharge transaction
  - Body: `id_customer`, `server`, `plan`, `using`, `svoucher`
  - Returns: Invoice

- **`GET r=plan/sync`**
  - Sync all active plans to routers
  - Returns: Sync log

- **`GET r=plan/view/<id>`**
  - View transaction/invoice
  - Returns: Invoice data

- **`POST r=plan/print`**
  - Print invoice
  - Body: `content` or `id`
  - Returns: Print view

- **`GET r=plan/edit/<id>`**
  - View edit customer recharge form
  - Returns: Customer recharge data

- **`POST r=plan/edit-post`**
  - Update customer recharge
  - Body: `id`, `id_plan`, `expiration`, `time`
  - Returns: Success message

- **`GET r=plan/delete/<id>`**
  - Delete customer recharge
  - Returns: Success message

- **`GET r=plan/extend/<id>/<days>`**
  - Extend customer plan expiration
  - Query params: `svoucher`
  - Returns: Success message

- **`GET r=plan/refill`**
  - View refill account form (voucher activation)
  - Returns: Refill form

- **`POST r=plan/refill-post`**
  - Refill account using voucher
  - Body: `code`, `id_customer`
  - Returns: Invoice

- **`GET r=plan/deposit`**
  - View deposit/balance refill form
  - Returns: Deposit form

- **`POST r=plan/deposit-post`**
  - Deposit balance to customer
  - Body: `id_customer`, `amount`, `id_plan`, `note`, `svoucher`
  - Returns: Invoice

### Voucher Management (Admin)
- **`GET r=plan/voucher`**
  - List vouchers (admin view)
  - Query params: `search`, `router`, `customer`, `plan`, `status`
  - Returns: Paginated voucher list

- **`GET r=plan/add-voucher`**
  - View add voucher form
  - Returns: Voucher form data

- **`POST r=plan/voucher-post`**
  - Create vouchers
  - Body: `type`, `plan`, `voucher_format`, `prefix`, `server`, `numbervoucher`, `lengthcode`, `print_now`, `voucher_per_page`
  - Returns: Success message or print view

- **`GET r=plan/voucher-view/<id>`**
  - View voucher details
  - Returns: Voucher data

- **`GET r=plan/voucher-delete/<id>`**
  - Delete voucher
  - Returns: Success message

- **`POST r=plan/voucher-delete-many`**
  - Delete multiple vouchers
  - Body: `voucherIds` (JSON array)
  - Returns: Success message

- **`GET r=plan/remove-voucher`**
  - Remove old used vouchers (3+ months)
  - Returns: Success message with count

- **`POST r=plan/print-voucher`**
  - Print vouchers
  - Body: `from_id`, `planid`, `pagebreak`, `limit`, `vpl`, `selected_datetime`
  - Returns: Print view

---

## Routers Management

- **`GET r=routers`**
  - List all routers
  - Returns: Router list

- **`GET r=routers/add`**
  - View add router form
  - Returns: Router form data

- **`POST r=routers/add-post`**
  - Create new router
  - Body: Router details
  - Returns: Success message

- **`GET r=routers/edit/<id>`**
  - View edit router form
  - Returns: Router data

- **`POST r=routers/edit-post`**
  - Update router
  - Body: Router details
  - Returns: Success message

- **`GET r=routers/delete/<id>`**
  - Delete router
  - Returns: Success message

---

## Bandwidth Management

- **`GET r=bandwidth`**
  - List all bandwidth profiles
  - Returns: Bandwidth list

- **`GET r=bandwidth/add`**
  - View add bandwidth form
  - Returns: Bandwidth form data

- **`POST r=bandwidth/add-post`**
  - Create new bandwidth profile
  - Body: Bandwidth details
  - Returns: Success message

- **`GET r=bandwidth/edit/<id>`**
  - View edit bandwidth form
  - Returns: Bandwidth data

- **`POST r=bandwidth/edit-post`**
  - Update bandwidth profile
  - Body: Bandwidth details
  - Returns: Success message

- **`GET r=bandwidth/delete/<id>`**
  - Delete bandwidth profile
  - Returns: Success message

---

## Vouchers Management (Customer)

- **`GET r=voucher`** (default)
  - Customer voucher page
  - Returns: Voucher page

- **`GET r=voucher/activation`**
  - View voucher activation form
  - Query params: `code` (pre-filled code)
  - Returns: Activation form

- **`POST r=voucher/activation-post`**
  - Activate voucher
  - Body: `code`
  - Returns: Success message

- **`GET r=voucher/list-activated`**
  - List activated vouchers/transactions
  - Returns: Activation history (paginated)

- **`GET r=voucher/invoice/<id>`**
  - View invoice for voucher activation
  - Query params: `<sign>` (MD5 hash for public access)
  - Returns: Invoice data

---

## Orders & Payments (Customer)

- **`GET r=order/voucher`**
  - View voucher order page
  - Returns: Voucher order page

- **`GET r=order/package`**
  - View available plans to order
  - Returns: Plan list (filtered by account_type, router, enabled, prepaid)

- **`GET r=order/balance`**
  - View balance top-up plans
  - Returns: Balance plans

- **`GET r=order/gateway/<router_id>/<plan_id>`**
  - Select payment gateway
  - Query params: `custom`, `amount`, `discount`
  - Returns: Available gateways and payment options

- **`POST r=order/buy`**
  - Create payment transaction
  - Body: `gateway`, `router_id`, `plan_id`, `custom`, `amount`, `discount`
  - Returns: Payment transaction with payment link

- **`GET r=order/view/<transaction_id>`**
  - View payment transaction
  - Returns: Transaction details including payment URL

- **`GET r=order/view/<transaction_id>/check`**
  - Check payment status
  - Returns: Payment status

- **`GET r=order/view/<transaction_id>/cancel`**
  - Cancel payment transaction
  - Returns: Success message

- **`GET r=order/history`**
  - View order history
  - Returns: Order history list (paginated)

- **`GET r=order/unpaid`**
  - Check for unpaid transactions
  - Returns: Unpaid transaction if exists

- **`GET r=order/pay/<router_id>/<plan_id>`**
  - Pay using balance
  - Returns: Success message

- **`GET r=order/send/<router_id>/<plan_id>`**
  - View send plan form
  - Returns: Send form

---

## Reports

- **`GET r=reports/ajax/<type>`**
  - Get report data via AJAX
  - Types: `type`, `plan`, `method`, `router`, `line`
  - Query params: `sd` (start date), `ed` (end date), `ts` (start time), `te` (end time), `tps[]`, `plns[]`, `mts[]`, `rts[]`
  - Returns: Report data with labels and datas arrays

- **`GET r=reports/by-date`**
  - Get reports by date range
  - Returns: Date-based reports

- **`GET r=reports/activation`**
  - Get activation reports
  - Returns: Activation statistics

- **`GET r=reports/by-period`**
  - Get reports by period
  - Returns: Period-based reports

- **`GET r=reports/period-view`**
  - View period report details
  - Returns: Period report data

- **`GET r=reports/daily-report`**
  - Get daily report
  - Returns: Daily statistics

---

## Settings

- **`GET r=settings/app`**
  - View app settings
  - Query params: `testWa`, `testSms`, `testEmail`, `testTg` (for testing)
  - Returns: Application settings

- **`POST r=settings/app-post`**
  - Update app settings
  - Body: Settings data (CompanyName, logo, login_page settings, etc.)
  - Returns: Success message

- **`GET r=settings/router`**
  - View router settings
  - Returns: Router settings

- **`POST r=settings/router-post`**
  - Update router settings
  - Body: Router settings data
  - Returns: Success message

- **`GET r=settings/devices`**
  - List available device drivers
  - Returns: Device list with descriptions

- **`GET r=settings/docs`**
  - Redirect to documentation
  - Returns: Redirect to docs

- **`GET r=settings/localisation`**
  - View localization settings
  - Returns: Localization form

- **`POST r=settings/localisation-post`**
  - Update localization settings
  - Body: Localization data
  - Returns: Success message

- **`GET r=settings/users`**
  - List admin users
  - Query params: `search`, `p` (page)
  - Returns: Admin user list

- **`GET r=settings/users-add`**
  - View add admin user form
  - Returns: Admin form

- **`GET r=settings/users-view/<id>`**
  - View admin user details
  - Returns: Admin user data

- **`GET r=settings/users-edit/<id>`**
  - View edit admin user form
  - Returns: Admin user data

- **`POST r=settings/users-post`**
  - Create admin user
  - Body: Admin user details
  - Returns: Success message

- **`POST r=settings/users-edit-post`**
  - Update admin user
  - Body: Admin user details
  - Returns: Success message

- **`GET r=settings/users-delete/<id>`**
  - Delete admin user
  - Returns: Success message

- **`GET r=settings/change-password`**
  - View change password form
  - Returns: Password form

- **`POST r=settings/change-password-post`**
  - Change admin password
  - Body: `old_password`, `new_password`, `confirm_password`
  - Returns: Success message

- **`GET r=settings/notifications`**
  - View notification settings
  - Returns: Notification settings form

- **`POST r=settings/notifications-post`**
  - Update notification settings
  - Body: Notification settings
  - Returns: Success message

- **`GET r=settings/dbstatus`**
  - View database status
  - Returns: Database status information

- **`GET r=settings/dbbackup`**
  - Backup database
  - Returns: Database backup file

- **`POST r=settings/dbrestore`**
  - Restore database from backup
  - Body: Database backup file
  - Returns: Success message

- **`GET r=settings/language`**
  - View language management
  - Returns: Language management form

- **`POST r=settings/lang-post`**
  - Update language settings
  - Body: Language data
  - Returns: Success message

- **`GET r=settings/maintenance`**
  - View maintenance mode settings
  - Returns: Maintenance settings form

- **`GET r=settings/miscellaneous`**
  - View miscellaneous settings
  - Returns: Miscellaneous settings form

---

## Payment Gateway

- **`GET r=paymentgateway`**
  - List payment gateways
  - Returns: Available payment gateways

- **`GET r=paymentgateway/audit/<gateway>`**
  - View payment gateway audit
  - Query params: `q` (search)
  - Returns: Transaction audit list

- **`GET r=paymentgateway/auditview/<id>`**
  - View payment gateway transaction details
  - Returns: Transaction details

- **`POST r=paymentgateway`**
  - Save active payment gateways
  - Body: `pgs[]` (array of gateway names)
  - Returns: Success message

---

## Messages

- **`GET r=message/send`**
  - View send message form
  - Query params: `<id>` (customer ID, optional)
  - Returns: Message form

- **`POST r=message/send-post`**
  - Send message to customer
  - Body: `id_customer`, `message`, `via` (sms/wa/both)
  - Returns: Success message

- **`GET r=message/send_bulk`**
  - View bulk message form
  - Returns: Bulk message form

- **`POST r=message/send_bulk_ajax`**
  - Send bulk messages (AJAX endpoint)
  - Body: `group` (all/new/expired/active), `message`, `via`, `batch`, `page`, `router`, `test`, `service`
  - Returns: JSON with `status`, `totalSent`, `totalFailed`, `hasMore`, `page`

---

## Logs

- **`GET r=logs`**
  - View system logs
  - Query params: `search`, `p` (page)
  - Returns: Log list

---

## Export

- **`GET r=export/customers`**
  - Export customers data
  - Returns: CSV file

- **`GET r=export/transactions`**
  - Export transactions
  - Returns: CSV file

---

## Coupons

- **`GET r=coupons`**
  - List coupons
  - Returns: Coupon list

- **`GET r=coupons/add`**
  - View add coupon form
  - Returns: Coupon form

- **`POST r=coupons/add-post`**
  - Create coupon
  - Body: Coupon details
  - Returns: Success message

- **`GET r=coupons/edit/<id>`**
  - View edit coupon form
  - Returns: Coupon data

- **`POST r=coupons/edit-post`**
  - Update coupon
  - Body: Coupon details
  - Returns: Success message

- **`GET r=coupons/delete/<id>`**
  - Delete coupon
  - Returns: Success message

---

## Custom Fields

- **`GET r=customfield`**
  - List custom fields
  - Returns: Custom field list

- **`GET r=customfield/add`**
  - View add custom field form
  - Returns: Custom field form

- **`POST r=customfield/add-post`**
  - Create custom field
  - Body: Custom field details
  - Returns: Success message

- **`GET r=customfield/edit/<id>`**
  - View edit custom field form
  - Returns: Custom field data

- **`POST r=customfield/edit-post`**
  - Update custom field
  - Body: Custom field details
  - Returns: Success message

- **`GET r=customfield/delete/<id>`**
  - Delete custom field
  - Returns: Success message

---

## Pools

- **`GET r=pool`**
  - List IP pools
  - Returns: Pool list

- **`GET r=pool/add`**
  - View add pool form
  - Returns: Pool form

- **`POST r=pool/add-post`**
  - Create IP pool
  - Body: Pool details
  - Returns: Success message

- **`GET r=pool/edit/<id>`**
  - View edit pool form
  - Returns: Pool data

- **`POST r=pool/edit-post`**
  - Update IP pool
  - Body: Pool details
  - Returns: Success message

- **`GET r=pool/delete/<id>`**
  - Delete IP pool
  - Returns: Success message

---

## Admin Management

- **`GET r=admin`**
  - List admins
  - Returns: Admin list

- **`GET r=admin/add`**
  - View add admin form
  - Returns: Admin form

- **`POST r=admin/add-post`**
  - Create admin user
  - Body: Admin details
  - Returns: Success message

- **`GET r=admin/edit/<id>`**
  - View edit admin form
  - Returns: Admin data

- **`POST r=admin/edit-post`**
  - Update admin user
  - Body: Admin details
  - Returns: Success message

- **`GET r=admin/delete/<id>`**
  - Delete admin user
  - Returns: Success message

---

## Accounts (Customer Profile)

- **`GET r=accounts/profile`**
  - View customer profile
  - Returns: Customer profile data

- **`POST r=accounts/profile-post`**
  - Update customer profile
  - Body: Profile data (fullname, email, phonenumber, address, etc.)
  - Returns: Success message

- **`GET r=accounts/password`**
  - View change password form
  - Returns: Password form

- **`POST r=accounts/password-post`**
  - Change password
  - Body: `old_password`, `new_password`, `confirm_password`
  - Returns: Success message

- **`GET r=accounts/language-update-post`**
  - Update customer language preference
  - Query params: `lang` (language code)
  - Returns: Success message

---

## Home (Customer)

- **`GET r=home`**
  - Customer home page/dashboard
  - Query params: `recharge` (recharge ID), `uid` (token for payment link)
  - Returns: Customer dashboard data with active plans

---

## Register

- **`GET r=register`**
  - View registration form
  - Returns: Registration form

- **`POST r=register/post`**
  - Register new customer
  - Body: Customer registration data (username, password, email, fullname, etc.)
  - Returns: Success message

## Forgot Password

- **`GET r=forgot`**
  - View forgot password form
  - Query params: `step` (0=initial, 1=verify, 2=reset, 6=find username, 7=find username post, -1=reset)
  - Returns: Forgot password form

- **`POST r=forgot`** (step=1)
  - Request OTP code
  - Body: `username`
  - Returns: OTP sent confirmation

- **`POST r=forgot`** (step=2)
  - Verify OTP and reset password
  - Body: `username`, `otp_code`
  - Returns: New password

- **`POST r=forgot`** (step=7)
  - Find username by phone/email
  - Body: `find` (phone number or email)
  - Returns: Username sent confirmation

## Logout

- **`GET r=logout`**
  - Logout customer or admin
  - Clears session and cookies
  - Returns: Redirect to login with success message

## Payment Gateway Callback

- **`POST r=callback/<gateway>`**
  - Payment gateway notification callback
  - Body: Gateway-specific payment data
  - Returns: Gateway-specific response
  - Note: This is called by payment gateways, not typically used by API clients

---

## Mail/Inbox (Customer)

- **`GET r=mail`** (default)
  - List customer inbox messages
  - Query params: `q` (search), `p` (page)
  - Returns: Paginated inbox messages

- **`GET r=mail/view/<id>`**
  - View inbox message
  - Returns: Message details (marks as read)

- **`GET r=mail/delete/<id>`**
  - Delete inbox message
  - Returns: Success message

## Hotspot Actions (Customer)

- **`GET r=hotspot_action/enqueue/login/<recharge_id>`**
  - Enqueue hotspot login request
  - Requires: Session with `nux-ip` and `nux-mac`
  - Returns: Connecting page

- **`GET r=hotspot_action/enqueue/logout/<recharge_id>`**
  - Enqueue hotspot logout request
  - Returns: Disconnecting page

- **`GET r=hotspot_action/enqueue_json/login/<recharge_id>`**
  - Enqueue hotspot login (AJAX)
  - Returns: JSON with `ok`, `job_id`, `status_url`

- **`GET r=hotspot_action/enqueue_json/logout/<recharge_id>`**
  - Enqueue hotspot logout (AJAX)
  - Returns: JSON with `ok`, `job_id`, `status_url`

- **`GET r=hotspot_action/status/<job_id>`**
  - Check hotspot action status
  - Returns: Status page (success/wait)

- **`GET r=hotspot_action/status_json/<job_id>`**
  - Check hotspot action status (JSON)
  - Returns: JSON with `ok`, `status`, `action`, `message`, `updated_at`

## Autoload (Admin AJAX Helpers)

- **`GET r=autoload/pool`**
  - Get IP pools list
  - Query params: `routers` (filter by router)
  - Returns: Pool list

- **`GET r=autoload/bw_name/<id>`**
  - Get bandwidth name by ID
  - Returns: Bandwidth name string

- **`GET r=autoload/balance/<customer_id>`**
  - Get customer balance
  - Query params: `<format>` (1 for formatted, 0 for raw)
  - Returns: Balance value

- **`GET r=autoload/server`**
  - Get enabled routers list
  - Returns: Router list

- **`GET r=autoload/pppoe_ip_used`**
  - Check if PPPOE IP is already used
  - Query params: `ip`, `id` (exclude customer ID)
  - Returns: Error message if used, empty if available

- **`GET r=autoload/pppoe_username_used`**
  - Check if PPPOE username is already used
  - Query params: `u` (username), `id` (exclude customer ID)
  - Returns: Error message if used, empty if available

- **`POST r=autoload/plan`**
  - Get plans by server and type
  - Body: `server`, `jenis` (type: Hotspot/PPPOE)
  - Returns: Plan list

- **`GET r=autoload/customer_is_active/<username>/<plan_id>`**
  - Check if customer is online
  - Returns: HTML status indicator (green/yellow/red dot)

- **`GET r=autoload/plan_is_active/<customer_id>`**
  - Get customer's active plans with status
  - Returns: HTML with plan labels and status indicators

- **`GET r=autoload/customer_select2`**
  - Search customers (Select2 format)
  - Query params: `s` (search term)
  - Returns: JSON with `results` array (`id`, `text`)

## Autoload User (Customer AJAX Helpers)

- **`GET r=autoload_user/isLogin/<recharge_id>`**
  - Check login status for recharge
  - Returns: HTML button/link for login/logout

- **`GET r=autoload_user/bw_name/<id>`**
  - Get bandwidth name by ID
  - Returns: Bandwidth name string

- **`GET r=autoload_user/inbox_unread`**
  - Get unread inbox count
  - Returns: Count number or empty

- **`GET r=autoload_user/inbox`**
  - Get recent unread inbox messages
  - Returns: HTML list of messages

- **`GET r=autoload_user/language`**
  - Get available languages list
  - Query params: `select` (current language)
  - Returns: HTML list of languages

## Radius NAS Management

- **`GET r=radius/nas-add`**
  - View add NAS form
  - Returns: NAS form

- **`POST r=radius/nas-add-post`**
  - Create NAS
  - Body: NAS details (nasname, shortname, type, secret, etc.)
  - Returns: Success message

- **`GET r=radius/nas-edit/<id>`**
  - View edit NAS form
  - Returns: NAS data

- **`POST r=radius/nas-edit-post`**
  - Update NAS
  - Body: NAS details
  - Returns: Success message

- **`GET r=radius/nas-delete/<id>`**
  - Delete NAS
  - Returns: Success message

## Pool Management

- **`GET r=pool/list`**
  - List IP pools
  - Query params: `routers` (filter)
  - Returns: Pool list

- **`GET r=pool/add`**
  - View add pool form
  - Returns: Pool form

- **`GET r=pool/edit/<id>`**
  - View edit pool form
  - Returns: Pool data

- **`GET r=pool/delete/<id>`**
  - Delete IP pool
  - Returns: Success message

- **`GET r=pool/sync`**
  - Sync pools to routers
  - Returns: Sync log

- **`POST r=pool/add-post`**
  - Create IP pool
  - Body: Pool details
  - Returns: Success message

- **`POST r=pool/edit-post`**
  - Update IP pool
  - Body: Pool details
  - Returns: Success message

- **`GET r=pool/port`**
  - List port pools
  - Returns: Port pool list

- **`GET r=pool/add-port`**
  - View add port pool form
  - Returns: Port pool form

- **`GET r=pool/edit-port/<id>`**
  - View edit port pool form
  - Returns: Port pool data

- **`GET r=pool/delete-port/<id>`**
  - Delete port pool
  - Returns: Success message

- **`GET r=pool/sync`** (port sync)
  - Sync port pools
  - Returns: Sync log

- **`POST r=pool/add-port-post`**
  - Create port pool
  - Body: Port pool details
  - Returns: Success message

- **`POST r=pool/edit-port-post`**
  - Update port pool
  - Body: Port pool details
  - Returns: Success message

## ODP Management

- **`GET r=odp/add`**
  - View add ODP form
  - Returns: ODP form

- **`GET r=odp/edit/<id>`**
  - View edit ODP form
  - Returns: ODP data

- **`GET r=odp/delete/<id>`**
  - Delete ODP
  - Returns: Success message

- **`POST r=odp/add-post`**
  - Create ODP
  - Body: ODP details
  - Returns: Success message

- **`POST r=odp/edit-post`**
  - Update ODP
  - Body: ODP details
  - Returns: Success message

## Maps

- **`GET r=maps/customer`**
  - View customer map
  - Returns: Customer map view

- **`GET r=maps/routers`**
  - View router map
  - Returns: Router map view

- **`GET r=maps/odp`**
  - View ODP map
  - Returns: ODP map view

## Plugin Manager

- **`GET r=pluginmanager`** (default)
  - List plugins
  - Returns: Plugin list

- **`GET r=pluginmanager/refresh`**
  - Refresh plugin list
  - Returns: Success message

- **`GET r=pluginmanager/dlinstall`**
  - Download and install plugin
  - Query params: Plugin URL
  - Returns: Success message

- **`GET r=pluginmanager/delete/<plugin>`**
  - Delete plugin
  - Returns: Success message

- **`GET r=pluginmanager/install/<plugin>`**
  - Install plugin
  - Returns: Success message

## FreeRADIUS REST API

**Base URL:** `{APP_URL}/radius.php`

### Actions (via header or query parameter)

- **`POST radius.php`** with header `X-Freeradius-Section: authenticate`
  - Authenticate user credentials
  - Body: `username`, `password`, `CHAPassword`, `CHAPchallenge`
  - Returns: `204 No Content` (success) or `401 Unauthorized`

- **`POST radius.php`** with header `X-Freeradius-Section: authorize`
  - Authorize user and get session attributes
  - Body: `username`, `password`, `CHAPassword`, `CHAPchallenge`
  - Returns: JSON with RADIUS attributes or `401 Unauthorized`

- **`POST radius.php`** with header `X-Freeradius-Section: accounting`
  - Store accounting data
  - Body: `username`, `acctSessionId`, `acctOutputOctets`, `acctInputOctets`, `acctOutputGigawords`, `acctInputGigawords`, `acctStatusType`, `nasid`, `macAddr`, etc.
  - Returns: `200 OK` with JSON response

**Alternative:** Use query parameter `?action=authenticate|authorize|accounting`

---

## Notes

1. **Authentication**: Most endpoints require a `token` parameter (admin or customer token)
2. **CSRF Protection**: POST endpoints may require `csrf_token` in the body
3. **Pagination**: List endpoints support `p` parameter (page number, starting from 1)
4. **Search**: Many list endpoints support `search` parameter
5. **Response Format**: All API responses return JSON with structure:
   ```json
   {
     "success": boolean,
     "message": string,
     "result": mixed,
     "meta": mixed (optional)
   }
   ```
6. **Error Handling**: Errors return `success: false` with error message
7. **Permissions**: Some endpoints require specific user types (SuperAdmin, Admin, Agent, Sales)

---

## Quick Reference

- **Base URL**: `{APP_URL}/system/api.php`
- **Route Parameter**: `r=<controller>/<action>/<param1>/<param2>...`
- **Authentication**: `token=<token>` (query parameter)
- **Method**: GET for viewing, POST for creating/updating
- **Response**: Always JSON

