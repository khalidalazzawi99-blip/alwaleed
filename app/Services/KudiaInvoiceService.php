<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\ExternalInvoice;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class KudiaInvoiceService
{
    public function save(Customer $customer, array $data, ?ExternalInvoice $invoice = null): ExternalInvoice
    {
        if ($customer->account_group !== Customer::GROUP_KUDIA) {
            throw ValidationException::withMessages(['customer_id' => 'اختر زبوناً من قائمة زبائن المصنع.']);
        }

        return DB::transaction(function () use ($customer, $data, $invoice) {
            $items = [];
            $subtotal = 0;
            foreach ($data['items'] as $item) {
                $price = $this->scaled($item['unit_price'], 2);
                $quantity = $this->scaled($item['quantity'], 3);
                $total = intdiv($price * $quantity + 500, 1000);
                $subtotal += $total;
                $items[] = [
                    'description' => $item['description'], 'details' => $item['details'] ?? null,
                    'unit' => $item['unit'] ?? null, 'quantity' => $quantity / 1000,
                    'unit_price' => $price / 100, 'total' => $total / 100,
                ];
            }
            if ($subtotal <= 0 || $subtotal > 99999999999999) {
                throw ValidationException::withMessages(['items' => 'يجب أن يكون إجمالي الفاتورة أكبر من صفر وضمن الحد المسموح.']);
            }
            $rate = $this->scaled($data['tax_rate'] ?? 0, 2);
            $tax = intdiv($subtotal * $rate + 5000, 10000);
            $payload = [
                'items' => $items, 'tax_rate' => $rate / 100, 'tax_amount' => $tax / 100,
                'payment_info' => $data['payment_info'] ?? null,
                'client_name' => $customer->name, 'client_address' => $customer->address,
                'client_phone' => $customer->phone, 'client_company' => $customer->company_name,
            ];
            if (! $invoice) {
                $invoice = new ExternalInvoice([
                    'company_id' => $customer->company_id, 'provider' => 'kudia',
                    'external_invoice_id' => 'kudia-'.Str::uuid(),
                    'invoice_no' => app(VoucherNumberService::class)->next(
                        $customer->company_id, 'kudia_invoice', (int) date('Y', strtotime($data['invoice_date']))),
                    'status' => 'active',
                ]);
            }
            $invoice->fill([
                'customer_id' => $customer->id, 'external_customer_id' => $customer->integration_id,
                'invoice_date' => $data['invoice_date'], 'currency' => $data['currency'],
                'subtotal' => $subtotal / 100, 'amount' => ($subtotal + $tax) / 100,
                'notes' => $data['notes'] ?? null, 'payload' => $payload,
            ])->save();

            return $invoice;
        });
    }

    private function scaled(string|int|float $value, int $places): int
    {
        [$whole, $fraction] = array_pad(explode('.', (string) $value, 2), 2, '');

        return (int) $whole * (10 ** $places) + (int) str_pad(substr($fraction, 0, $places), $places, '0');
    }
}
