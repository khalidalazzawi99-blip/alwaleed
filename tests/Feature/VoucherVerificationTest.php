<?php

namespace Tests\Feature;

use App\Models\Cashbox;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Receipt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class VoucherVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_receipt_gets_company_sequence_token_audit_and_scannable_print_codes(): void
    {
        [$company, $user, $customer, $cashbox] = $this->records();
        $this->actingAs($user)->post('/receipts', $this->receiptData($customer, $cashbox))->assertRedirect('/receipts');
        $receipt = Receipt::sole();

        $this->assertSame('RCP-'.now()->year.'-000001', $receipt->receipt_no);
        $this->assertSame(64, strlen($receipt->verification_token));
        $this->assertSame($user->id, $receipt->created_by);
        $this->assertSame('active', $receipt->status);
        $this->get('/verify/document/'.$receipt->verification_token)->assertOk()
            ->assertSee('سند القبض صحيح وفعال')->assertSee($receipt->receipt_no)->assertSee($company->name)
            ->assertDontSee('الحساب المالي')->assertDontSee($cashbox->name);
        $this->actingAs($user)->get('/receipts/'.$receipt->id.'/print')->assertOk()
            ->assertSee('data:image/png;base64', false)->assertSee('data:image/svg+xml;base64', false)
            ->assertSee('امسح الرمز للتحقق من صحة سند القبض');
    }

    public function test_payment_gets_independent_sequence_and_public_verification(): void
    {
        [$company, $user, $customer, $cashbox] = $this->records();
        $this->actingAs($user)->post('/receipts', $this->receiptData($customer, $cashbox))->assertRedirect();
        $this->actingAs($user)->post('/payments', $this->paymentData($customer, $cashbox))->assertRedirect('/payments');
        $payment = Payment::sole();

        $this->assertSame('PAY-'.now()->year.'-000001', $payment->payment_no);
        $this->assertSame(64, strlen($payment->verification_token));
        $this->get('/verify/document/'.$payment->verification_token)->assertOk()
            ->assertSee('سند الصرف صحيح وفعال')->assertSee($payment->payment_no)
            ->assertDontSee('الحساب المالي')->assertDontSee($cashbox->name);
        $this->actingAs($user)->get('/payments/'.$payment->id.'/print')->assertOk()
            ->assertSee('امسح الرمز للتحقق من صحة سند الصرف');
    }

    public function test_sequences_are_isolated_by_company_and_document_type_and_never_reused(): void
    {
        [$company, $user, $customer, $cashbox] = $this->records('A');
        [$other, $otherUser, $otherCustomer, $otherCashbox] = $this->records('B');
        foreach ([1, 2] as $unused) {
            $this->actingAs($user)->post('/receipts', $this->receiptData($customer, $cashbox));
        }
        $this->actingAs($user)->post('/payments', $this->paymentData($customer, $cashbox));
        $this->actingAs($otherUser)->post('/receipts', $this->receiptData($otherCustomer, $otherCashbox));

        $this->assertSame(['RCP-'.now()->year.'-000001', 'RCP-'.now()->year.'-000002'], Receipt::where('company_id', $company->id)->orderBy('id')->pluck('receipt_no')->all());
        $this->assertSame('PAY-'.now()->year.'-000001', Payment::where('company_id', $company->id)->sole()->payment_no);
        $this->assertSame('RCP-'.now()->year.'-000001', Receipt::where('company_id', $other->id)->sole()->receipt_no);

        $first = Receipt::where('company_id', $company->id)->oldest()->first();
        $this->actingAs($user)->delete('/receipts/'.$first->id, ['cancellation_reason' => 'اختبار'])->assertRedirect('/receipts');
        $this->actingAs($user)->post('/receipts', $this->receiptData($customer, $cashbox));
        $this->assertSame('RCP-'.now()->year.'-000003', Receipt::where('company_id', $company->id)->latest('id')->first()->receipt_no);
    }

    public function test_cancelling_vouchers_reverses_bank_effect_and_keeps_audit_and_verification(): void
    {
        [, $user, $customer, $cashbox] = $this->records();
        $this->actingAs($user)->post('/receipts', $this->receiptData($customer, $cashbox, 250));
        $receipt = Receipt::sole();
        $this->assertSame(1250.0, (float) $cashbox->fresh()->balance);
        $this->actingAs($user)->delete('/receipts/'.$receipt->id, ['cancellation_reason' => 'خطأ إدخال'])->assertRedirect('/receipts');
        $receipt->refresh();
        $this->assertSame(1000.0, (float) $cashbox->fresh()->balance);
        $this->assertSame('cancelled', $receipt->status);
        $this->assertSame($user->id, $receipt->cancelled_by);
        $this->assertNotNull($receipt->cancelled_at);
        $this->assertSame('خطأ إدخال', $receipt->cancellation_reason);
        $this->get('/verify/document/'.$receipt->verification_token)->assertOk()->assertSee('هذا السند ملغي');

        $this->actingAs($user)->post('/payments', $this->paymentData($customer, $cashbox, 100));
        $payment = Payment::sole();
        $this->assertSame(900.0, (float) $cashbox->fresh()->balance);
        $this->actingAs($user)->delete('/payments/'.$payment->id)->assertRedirect('/payments');
        $this->assertSame(1000.0, (float) $cashbox->fresh()->balance);
        $this->assertSame('cancelled', $payment->fresh()->status);
        $this->get('/verify/document/'.$payment->verification_token)->assertOk()->assertSee('هذا السند ملغي');
    }

    public function test_document_number_is_immutable_and_exact_barcode_search_opens_document(): void
    {
        [, $user, $customer, $cashbox] = $this->records();
        $this->actingAs($user)->post('/receipts', $this->receiptData($customer, $cashbox));
        $receipt = Receipt::sole();
        $original = $receipt->receipt_no;
        $receipt->update(['receipt_no' => 'RCP-2099-999999']);
        $this->assertSame($original, $receipt->fresh()->receipt_no);
        $this->actingAs($user)->get('/search?q='.$original)->assertRedirect('/receipts/'.$receipt->id.'/print');

        $this->actingAs($user)->post('/payments', $this->paymentData($customer, $cashbox));
        $payment = Payment::sole();
        $this->actingAs($user)->get('/search?q='.$payment->payment_no)->assertRedirect('/payments/'.$payment->id.'/print');
    }

    public function test_invalid_token_and_cancellation_permissions_are_enforced(): void
    {
        [, $admin, $customer, $cashbox] = $this->records();
        $this->actingAs($admin)->post('/receipts', $this->receiptData($customer, $cashbox));
        $receipt = Receipt::sole();
        $this->get('/verify/document/'.str_repeat('x', 64))->assertNotFound()->assertSee('تعذر التحقق من هذا السند');
        $entry = User::factory()->create(['company_id' => $admin->company_id, 'role' => 'data_entry']);
        $this->actingAs($entry)->delete('/receipts/'.$receipt->id)->assertForbidden();
        $this->assertSame('active', $receipt->fresh()->status);
    }

    private function receiptData(Customer $customer, Cashbox $cashbox, float $amount = 100): array
    {
        return ['receipt_date' => now()->toDateString(), 'party_type' => 'customer', 'party_id' => $customer->id, 'cashbox_id' => $cashbox->id, 'amount' => $amount];
    }

    private function paymentData(Customer $customer, Cashbox $cashbox, float $amount = 100): array
    {
        return ['payment_date' => now()->toDateString(), 'party_type' => 'customer', 'party_id' => $customer->id, 'cashbox_id' => $cashbox->id, 'amount' => $amount];
    }

    private function records(string $suffix = ''): array
    {
        $company = Company::create(['name' => 'شركة التحقق '.$suffix, 'code' => 'VERIFY'.Str::upper(Str::random(8)), 'status' => 'active', 'subscription_start' => now()->subDay(), 'subscription_end' => now()->addYear()]);
        $user = User::factory()->create(['company_id' => $company->id, 'role' => 'admin']);
        $customer = Customer::create(['company_id' => $company->id, 'name' => 'زبون التحقق']);
        $cashbox = Cashbox::create(['company_id' => $company->id, 'name' => 'حساب التحقق', 'account_type' => 'bank', 'bank_name' => 'مصرف الاختبار', 'balance' => 1000, 'is_active' => true]);

        return [$company, $user, $customer, $cashbox];
    }
}
