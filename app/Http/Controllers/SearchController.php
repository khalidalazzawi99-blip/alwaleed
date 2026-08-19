<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Payment;
use App\Models\Receipt;
use App\Models\Supplier;
use App\Models\ExternalInvoice;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $term = $this->term($request);
        $results = $term === '' ? $this->emptyResults() : $this->results($term, 50);

        return view('search.index', $results + ['term' => $term]);
    }

    public function suggestions(Request $request): JsonResponse
    {
        $term = $this->term($request);

        if ($term === '') {
            return response()->json([]);
        }

        $results = $this->results($term, 5);
        $items = collect();

        foreach ($results['customers'] as $customer) {
            $items->push([
                'type' => __('messages.customer'),
                'title' => $customer->name,
                'meta' => $customer->phone ?: $customer->company_name,
                'url' => url('/search?q='.urlencode($term).'#customer-'.$customer->id),
            ]);
        }

        foreach ($results['suppliers'] as $supplier) {
            $items->push([
                'type' => __('messages.supplier'),
                'title' => $supplier->name,
                'meta' => $supplier->phone ?: $supplier->company_name,
                'url' => url('/search?q='.urlencode($term).'#supplier-'.$supplier->id),
            ]);
        }

        foreach ($results['receipts'] as $receipt) {
            $items->push([
                'type' => __('messages.receipts'),
                'title' => $receipt->receipt_no,
                'meta' => ($receipt->party?->name ?: '—').' · '.number_format($receipt->amount, 2),
                'url' => url('/search?q='.urlencode($term).'#receipt-'.$receipt->id),
            ]);
        }

        foreach ($results['payments'] as $payment) {
            $items->push([
                'type' => __('messages.payments'),
                'title' => $payment->payment_no,
                'meta' => ($payment->party?->name ?: '—').' · '.number_format($payment->amount, 2),
                'url' => url('/search?q='.urlencode($term).'#payment-'.$payment->id),
            ]);
        }

        foreach ($results['externalInvoices'] as $invoice) {
            $items->push(['type'=>__('messages.external_invoices'),'title'=>$invoice->invoice_no,
                'meta'=>($invoice->customer?->name ?: __('messages.unlinked')).' · '.number_format($invoice->amount,2),
                'url'=>url('/external-invoices')]);
        }

        return response()->json($items->take(12)->values());
    }

    private function results(string $term, int $limit): array
    {
        $companyId = auth()->user()->company_id;
        $like = '%'.$term.'%';

        $customers = Customer::query()
            ->when($companyId, fn (Builder $query) => $query->where('company_id', $companyId))
            ->where(fn (Builder $query) => $query
                ->where('name', 'like', $like)
                ->orWhere('phone', 'like', $like)
                ->orWhere('company_name', 'like', $like))
            ->orderByRaw('CASE WHEN name LIKE ? THEN 0 ELSE 1 END', [$term.'%'])
            ->limit($limit)->get();

        $suppliers = Supplier::query()
            ->when($companyId, fn (Builder $query) => $query->where('company_id', $companyId))
            ->where(fn (Builder $query) => $query
                ->where('name', 'like', $like)
                ->orWhere('phone', 'like', $like)
                ->orWhere('company_name', 'like', $like))
            ->orderByRaw('CASE WHEN name LIKE ? THEN 0 ELSE 1 END', [$term.'%'])
            ->limit($limit)->get();

        $receipts = Receipt::with(['customer', 'supplier'])
            ->when($companyId, fn (Builder $query) => $query->where('company_id', $companyId))
            ->where(fn (Builder $query) => $query
                ->where('receipt_no', 'like', $like)
                ->orWhere('notes', 'like', $like)
                ->orWhereHas('customer', fn (Builder $query) => $query
                    ->where('name', 'like', $like)->orWhere('phone', 'like', $like))
                ->orWhereHas('supplier', fn (Builder $query) => $query
                    ->where('name', 'like', $like)->orWhere('phone', 'like', $like)))
            ->orderByRaw('CASE WHEN receipt_no LIKE ? THEN 0 ELSE 1 END', [$term.'%'])
            ->latest('receipt_date')->limit($limit)->get();

        $payments = Payment::with(['customer', 'supplier'])
            ->when($companyId, fn (Builder $query) => $query->where('company_id', $companyId))
            ->where(fn (Builder $query) => $query
                ->where('payment_no', 'like', $like)
                ->orWhere('notes', 'like', $like)
                ->orWhereHas('supplier', fn (Builder $query) => $query
                    ->where('name', 'like', $like)->orWhere('phone', 'like', $like))
                ->orWhereHas('customer', fn (Builder $query) => $query
                    ->where('name', 'like', $like)->orWhere('phone', 'like', $like)))
            ->orderByRaw('CASE WHEN payment_no LIKE ? THEN 0 ELSE 1 END', [$term.'%'])
            ->latest('payment_date')->limit($limit)->get();

        $externalInvoices = ExternalInvoice::with('customer')
            ->when($companyId, fn (Builder $query) => $query->where('company_id', $companyId))
            ->where(fn (Builder $query) => $query->where('invoice_no','like',$like)
                ->orWhere('external_invoice_id','like',$like)
                ->orWhereHas('customer',fn(Builder $query)=>$query->where('name','like',$like)->orWhere('phone','like',$like)))
            ->latest('invoice_date')->limit($limit)->get();

        return compact('customers', 'suppliers', 'receipts', 'payments', 'externalInvoices');
    }

    private function emptyResults(): array
    {
        return [
            'customers' => collect(), 'suppliers' => collect(),
            'receipts' => collect(), 'payments' => collect(),
            'externalInvoices' => collect(),
        ];
    }

    private function term(Request $request): string
    {
        $request->validate(['q' => ['nullable', 'string', 'max:100']]);

        return trim((string) $request->query('q', ''));
    }
}
