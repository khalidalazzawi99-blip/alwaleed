# External Invoice API

Al Waleed receives invoice summaries only. The external application remains responsible for invoices, invoice items, sales, and inventory.

## Endpoint

`POST /api/v1/external-invoices`

Send JSON over HTTPS with these headers:

```http
Authorization: Bearer <company-api-key>
Accept: application/json
Content-Type: application/json
```

The API key identifies the Al Waleed company. Do not send `company_id`; it is rejected and is never used for authorization.

## Request

```json
{
  "external_invoice_id": "4812",
  "invoice_no": "INV-2026-4812",
  "external_customer_id": "C-155",
  "invoice_date": "2026-08-19",
  "amount": 1250000
}
```

| Field | Description |
|---|---|
| `external_invoice_id` | Stable invoice ID from the external system. |
| `invoice_no` | Human-readable invoice number. |
| `external_customer_id` | Stable customer identifier previously linked in Al Waleed. Names are not used for matching. |
| `invoice_date` | Invoice date in `YYYY-MM-DD` format. |
| `amount` | Positive invoice total with at most two decimal places. |

Example:

```bash
curl -X POST "https://example.com/api/v1/external-invoices" \
  -H "Authorization: Bearer aw_live_REPLACE_WITH_GENERATED_KEY" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  --data '{"external_invoice_id":"4812","invoice_no":"INV-2026-4812","external_customer_id":"C-155","invoice_date":"2026-08-19","amount":1250000}'
```

## Responses

New invoice (`201 Created`):

```json
{
  "message": "Invoice received successfully.",
  "data": {
    "external_invoice_id": "4812",
    "invoice_no": "INV-2026-4812",
    "customer_id": 15,
    "amount": "1250000.00"
  }
}
```

A replay of an existing invoice returns `200 OK`. Other responses are:

- `401 Unauthorized`: missing, invalid, expired, or revoked API key.
- `403 Forbidden`: integration disabled or credential lacks the required scope.
- `404 Not Found`: no customer with that external ID exists in the credential's company.
- `422 Unprocessable Entity`: invalid or malformed fields.
- `500 Internal Server Error`: safe generic database/server failure response.

## Idempotency

`company_id + external_invoice_id` is unique. Retrying the same ID never creates another row. If the same ID is sent with changed summary data, Al Waleed updates the customer link, invoice number, date, and amount and returns `200 OK`. Receipts are never linked to or applied against individual invoices, and receiving or replaying an invoice never edits receipts.

API keys are shown only immediately after generation or regeneration. Store the generated key securely; Al Waleed stores only its SHA-256 hash. Regeneration revokes the previous active key.
