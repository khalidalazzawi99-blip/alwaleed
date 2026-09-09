# Sippar one-time business data reset

Authorized target: company code SIPPAR (case insensitive), with a name containing Sippar or سيبار.

Migration: 2026_09_09_235000_reset_sippar_business_data_once.php. Production startup runs migrations automatically. Do not run this migration on a populated environment unless its SIPPAR tenant is intended to be reset.

Removes tenant customers, suppliers, financial accounts and transactions, banks/cashboxes and links, receipts/payments, invoices and items where present, integration invoice data/logs, module records, audit logs, notifications, and voucher attachments. Creates one active main cashbox with zero balance and no bank details.

Preserves the company and subscription, users, settings, enabled features, API tokens, and integration configuration. Existing integrations can import new data afterwards.

Before deletion, a transaction saves an encrypted snapshot of removed rows and available attachment contents in company_data_reset_backups, reset_key sippar-business-reset-2026-09-09. Keep the original APP_KEY for recovery. The backup contains private business data; restrict database access. Missing attachment files cannot be backed up.

The completed marker prevents subsequent executions from deleting new business data. Unexpected tenant tables or cross-company relationships abort the transaction. Attachment cleanup occurs after commit. Rollback is intentionally unsupported; recovery requires an operator to decrypt the snapshot with Laravel Crypt, review subsequent activity, and restore parent rows before dependent rows and files.

Verification on production requires checking the migration ledger and completed_at marker, and confirming the SIPPAR account is empty except for the zero-balance main cashbox. A successful Git push alone does not verify deployment or deletion.
