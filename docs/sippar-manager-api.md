# Sippar API contract

Production base URL: `https://alwaleediq.com/api/v1`

Al Waleed is the master for customers, customer balances, banks/cash accounts, receipts, payments, and financial transactions. Sippar is the master for orders, invoices, and inventory. Sippar caches customer and bank feeds read-only and sends Al Waleed's `customer_id` with invoices. Names are never used for matching.

## Authentication and reliability

All communication is server-to-server over HTTPS:

```http
Authorization: Bearer <API_KEY>
Accept: application/json
Content-Type: application/json
```

The key remains on the Sippar API server, never in desktop/mobile clients. Al Waleed stores only its SHA-256 hash. Keys support expiry/revocation, integration enable/disable, and company isolation. Scopes: `customers:read`, `banks:read`, `invoices:read`, `invoices:write`, `balances:read`. Rate limit: 120 requests/minute.

Lists accept `page` and `per_page` (default/max 100):

```json
{"data": [], "meta": {"current_page": 1, "per_page": 100, "last_page": 1, "total": 0}}
```

## Customers

`GET /external-customers?page=1&per_page=100`

Optional incremental sync: `updated_since=<ISO8601 timestamp>` (inclusive). `customer_id` is owned by Al Waleed, formatted as `C-` plus a 26-character ULID, is not a database ID, and is immutable.

```json
{
  "data": [{
    "customer_id": "C-01K3J8M4N6Q7R8S9T0V1W2X3Y4",
    "name": "شركة النور التجارية",
    "balance": 1250000,
    "currency": "IQD",
    "is_active": true,
    "updated_at": "2026-08-25T12:00:00+00:00"
  }],
  "meta": {"current_page": 1, "per_page": 100, "last_page": 1, "total": 1}
}
```

Positive balance means the customer owes Al Waleed; negative means customer credit. Balance equals active external invoices minus customer receipts.

## Banks / cash accounts

`GET /external-banks?page=1&per_page=100`

This feed is read-only and accepts `updated_since=<ISO8601 timestamp>`. `bank_id` is immutable and formatted as `B-` plus a 26-character ULID.

```json
{
  "data": [{
    "bank_id": "B-01K3J8N5P7Q8R9S0T1V2W3X4Y5",
    "name": "الصندوق الرئيسي",
    "balance": 8500000,
    "currency": "IQD",
    "is_active": true,
    "updated_at": "2026-08-25T12:05:00+00:00"
  }],
  "meta": {"current_page": 1, "per_page": 100, "last_page": 1, "total": 1}
}
```

## Create/update invoice

`POST /external-invoices`

```json
{
  "external_invoice_id": "4812",
  "invoice_no": "INV-2026-4812",
  "order_no": "ORD-2026-1005",
  "customer_id": "C-01K3J8M4N6Q7R8S9T0V1W2X3Y4",
  "invoice_date": "2026-08-25",
  "currency": "IQD",
  "amount": 1250000,
  "status": "active"
}
```

`invoice_name` is optional; `description` is its accepted alias. Currency must match the company's configured currency. `customer_id` is canonical. Temporarily, `external_customer_id` is accepted as an alias; if both are sent they must match.

Idempotency identity is `company_id + external_invoice_id`: first send returns `201`; a retry or changed replay updates the same row and returns `200`. It creates no receipt or duplicate financial row.

```json
{
  "message": "Invoice received.",
  "data": {
    "external_invoice_id": "4812",
    "invoice_no": "INV-2026-4812",
    "invoice_name": null,
    "order_no": "ORD-2026-1005",
    "customer_id": "C-01K3J8M4N6Q7R8S9T0V1W2X3Y4",
    "invoice_date": "2026-08-25",
    "currency": "IQD",
    "amount": 1250000,
    "status": "active",
    "created_at": "2026-08-25T12:10:00+00:00",
    "updated_at": "2026-08-25T12:10:00+00:00"
  }
}
```

## Invoice audit feed

`GET /external-invoices?page=1&per_page=100`

```json
{
  "data": [{
    "external_invoice_id": "4812",
    "invoice_no": "INV-2026-4812",
    "invoice_name": null,
    "order_no": "ORD-2026-1005",
    "customer_id": "C-01K3J8M4N6Q7R8S9T0V1W2X3Y4",
    "invoice_date": "2026-08-25",
    "currency": "IQD",
    "amount": 1250000,
    "status": "active",
    "created_at": "2026-08-25T12:10:00+00:00",
    "updated_at": "2026-08-25T12:10:00+00:00"
  }],
  "meta": {"current_page": 1, "per_page": 100, "last_page": 1, "total": 1}
}
```

## Cancellation

`POST /external-invoices/{external_invoice_id}/cancel`

The invoice is preserved, marked `cancelled`, and excluded from balance. Repeating cancellation is safe and returns `200`.

```json
{"message": "Invoice cancelled.", "data": {"external_invoice_id": "4812", "invoice_no": "INV-2026-4812", "status": "cancelled"}}
```

## Customer balance

`GET /customers/{customer_id}/balance`

```json
{
  "data": {
    "customer_id": "C-01K3J8M4N6Q7R8S9T0V1W2X3Y4",
    "name": "شركة النور التجارية",
    "total_invoices": 1500000,
    "total_receipts": 250000,
    "balance": 1250000,
    "currency": "IQD",
    "updated_at": "2026-08-25T12:00:00+00:00"
  }
}
```

## Responses and retries

- `200`: list/balance/update/cancellation succeeded.
- `201`: invoice first created.
- `401`: missing, invalid, expired, or revoked key.
- `403`: missing scope, inactive company, or disabled integration.
- `404`: customer/invoice absent from the authenticated company.
- `422`: invalid payload, timestamp, pagination, aliases, or currency.
- `429`: rate limit exceeded.
- `500`: generic temporary server/database failure.

Sippar can safely retry timeouts, `429`, and temporary `5xx` responses using the same external invoice ID and exponential backoff. Do not send `company_id`; it is prohibited and the key is the only company source.
