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

- **`GET r=plan/list`**
  - List all plans
  - Returns: Plan list

- **`GET r=plan/add`**
  - View add plan form
  - Returns: Plan form data

- **`POST r=plan/add-post`**
  - Create new plan
  - Body: Plan details
  - Returns: Success message

- **`GET r=plan/edit/<id>`**
  - View edit plan form
  - Returns: Plan data

- **`POST r=plan/edit-post`**
  - Update plan
  - Body: Plan details
  - Returns: Success message

- **`GET r=plan/delete/<id>`**
  - Delete plan
  - Returns: Success message

- **`GET r=plan/sync`**
  - Sync all active plans to routers
  - Returns: Sync log

- **`GET r=plan/recharge`**
  - View recharge form
  - Returns: Recharge form data

- **`POST r=plan/recharge-confirm`**
  - Confirm and process recharge
  - Body: `id_customer`, `server`, `plan`, `using`
  - Returns: Success message

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

## Vouchers Management

- **`GET r=voucher`**
  - List vouchers (customer view)
  - Returns: Voucher list

- **`GET r=voucher/activation`**
  - View voucher activation form
  - Returns: Activation form

- **`POST r=voucher/activation-post`**
  - Activate voucher
  - Body: `code`
  - Returns: Success message

- **`GET r=voucher/list-activated`**
  - List activated vouchers
  - Returns: Activation history

- **`GET r=voucher/invoice/<id>`**
  - View invoice for voucher activation
  - Returns: Invoice data

---

## Orders & Payments (Customer)

- **`GET r=order/package`**
  - View available plans to order
  - Returns: Plan list

- **`GET r=order/balance`**
  - View balance top-up plans
  - Returns: Balance plans

- **`GET r=order/gateway/<router_id>/<plan_id>`**
  - Select payment gateway
  - Returns: Available gateways

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
  - Returns: Order history list

- **`GET r=order/unpaid`**
  - Check for unpaid transactions
  - Returns: Unpaid transaction if exists

- **`GET r=order/pay/<router_id>/<plan_id>`**
  - Pay using balance
  - Returns: Success message

- **`GET r=order/send/<router_id>/<plan_id>`**
  - Send plan to another user
  - Returns: Send form

---

## Reports

- **`GET r=reports/ajax`**
  - Get report data via AJAX
  - Query params: `type`, `plan`, `method`, `router`, `line`
  - Returns: Report data

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
  - Returns: Application settings

- **`POST r=settings/app-post`**
  - Update app settings
  - Body: Settings data
  - Returns: Success message

- **`GET r=settings/router`**
  - View router settings
  - Returns: Router settings

- **`POST r=settings/router-post`**
  - Update router settings
  - Body: Router settings data
  - Returns: Success message

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
  - Returns: Message form

- **`POST r=message/send-post`**
  - Send message to customer
  - Body: `id_customer`, `message`, `via` (sms/wa/both)
  - Returns: Success message

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
  - Body: Profile data
  - Returns: Success message

- **`GET r=accounts/password`**
  - View change password form
  - Returns: Password form

- **`POST r=accounts/password-post`**
  - Change password
  - Body: `old_password`, `new_password`, `confirm_password`
  - Returns: Success message

---

## Home (Customer)

- **`GET r=home`**
  - Customer home page
  - Returns: Customer dashboard data

---

## Register

- **`GET r=register`**
  - View registration form
  - Returns: Registration form

- **`POST r=register/post`**
  - Register new customer
  - Body: Customer registration data
  - Returns: Success message

---

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

