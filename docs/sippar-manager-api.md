# Sippar Manager API

Production base URL: `https://<al-waleed-domain>/api/v1`

The real domain must be configured on the public Al Waleed server before Sippar can connect. `localhost` is not a valid production base URL.

All requests use these headers and HTTPS in production:

```http
Authorization: Bearer <API_KEY>
Accept: application/json
Content-Type: application/json
```

The key is created once from the External Invoices integration page. A newly created key includes customer, bank, invoice, and balance scopes. It must be stored only on the Sippar server.

## Customers

`GET /external-customers?page=1&per_page=100`

Returns `customers` and `pagination`. `external_customer_id` is permanent. Customer `balance` is the outstanding amount: positive means the customer owes Al Waleed; negative means the customer has credit. Cancelled invoices do not affect it.

Example response:

```json
{
  "customers": [
    {
      "external_customer_id": "C-155",
      "name": "Al Noor Trading",
      "balance": 12500.50,
      "currency": "USD",
      "is_active": true,
      "updated_at": "2026-08-24T09:30:00Z"
    }
  ],
  "pagination": {
    "current_page": 1,
    "per_page": 100,
    "total": 1,
    "last_page": 1,
    "next_page_url": null,
    "prev_page_url": null
  }
}
```

## Banks / cash accounts

`GET /external-banks?page=1&per_page=100`

Returns `banks` and `pagination`. `external_bank_id` is permanent. A positive balance is money available in the account; a negative balance is an overdraft/deficit. Sippar must treat this endpoint as read-only.

For both list endpoints, `per_page` defaults to and is capped at 100. Pagination contains `current_page`, `per_page`, `total`, `last_page`, `next_page_url`, and `prev_page_url`.

Example response:

```json
{
  "banks": [
    {
      "external_bank_id": "B-7",
      "name": "Main USD Account",
      "balance": 50000.00,
      "currency": "USD",
      "is_active": true,
      "updated_at": "2026-08-24T09:30:00Z"
    }
  ],
  "pagination": {
    "current_page": 1,
    "per_page": 100,
    "total": 1,
    "last_page": 1,
    "next_page_url": null,
    "prev_page_url": null
  }
}
```

## Create or update an invoice

`POST /external-invoices`

```json
{
  "external_invoice_id": "4812",
  "invoice_no": "INV-2026-4812",
  "invoice_name": "Sales Invoice INV-2026-4812",
  "order_no": "ORD-20260819-001",
  "external_customer_id": "C-155",
  "invoice_date": "2026-08-19",
  "currency": "USD",
  "amount": 12500.50,
  "status": "active"
}
```

The same `external_invoice_id` updates the existing record and never creates a duplicate. `description` may be sent instead of `invoice_name`. The invoice currency must match the company's configured three-letter currency. Al Waleed currently keeps one accounting currency per company.

Example create response (`201`):

```json
{
  "message": "Invoice received.",
  "data": {
    "id": 42,
    "external_invoice_id": "4812",
    "invoice_no": "INV-2026-4812",
    "invoice_name": "Sales Invoice INV-2026-4812",
    "order_no": "ORD-20260819-001",
    "invoice_date": "2026-08-19T00:00:00.000000Z",
    "currency": "USD",
    "amount": "12500.50",
    "status": "active",
    "customer_id": 155
  }
}
```

Resending the same `external_invoice_id` returns `200` with the updated values and the message `Invoice replayed and safely updated.`

## Cancel an invoice

Either resend the full invoice payload with `"status": "cancelled"`, or call:

`POST /external-invoices/{external_invoice_id}/cancel`

Cancellation preserves the record for auditing and removes its effect from the customer balance. Resending the full payload with `"status": "active"` reactivates it.

Example cancellation response (`200`):

```json
{
  "message": "Invoice cancelled.",
  "data": {
    "external_invoice_id": "4812",
    "invoice_no": "INV-2026-4812",
    "status": "cancelled"
  }
}
```

Common responses: `200` updated/cancelled, `201` created, `401` invalid key, `403` missing scope or disabled integration, `404` unknown customer/invoice, and `422` invalid payload or unsupported currency.
