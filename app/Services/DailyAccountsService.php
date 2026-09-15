<?php

namespace App\Services;

use App\Models\Company;
use App\Models\DailyExpense;
use App\Models\Setting;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class DailyAccountsService
{
    public readonly Company $company;

    public function __construct(User $user, public readonly array $filters = [])
    {
        $this->company = app(SipparCompany::class)->forUser($user);
    }

    public function query(): Builder
    {
        $f = $this->filters;
        $start = CarbonImmutable::create((int) ($f['year'] ?? now()->year), 1, 1)->startOfDay();
        $query = DailyExpense::query()->where('company_id', $this->company->id)
            ->whereIn('currency', ['IQD', 'USD'])
            ->where('expense_date', '>=', $start->toDateString())
            ->where('expense_date', '<', $start->addYear()->toDateString());
        if (! empty($f['month'])) {
            $month = $start->setMonth((int) $f['month']);
            $query->where('expense_date', '>=', $month->toDateString())->where('expense_date', '<', $month->addMonth()->toDateString());
        }
        foreach (['from' => '>=', 'to' => '<='] as $key => $operator) {
            if (! empty($f[$key])) {
                $query->where('expense_date', $operator, $f[$key]);
            }
        }
        if (! empty($f['party_id'])) {
            $query->where('party_id', $f['party_id']);
        }
        if (! empty($f['notes'])) {
            $query->where('notes', 'like', '%'.$f['notes'].'%');
        }
        foreach (['min_amount' => '>=', 'max_amount' => '<='] as $key => $operator) {
            if (isset($f[$key])) {
                $query->where('amount', $operator, $f[$key]);
            }
        }

        return $query;
    }

    public function record(int $id): DailyExpense
    {
        return $this->company->dailyExpenses()->findOrFail($id);
    }

    public function expensesByPerson(): Collection
    {
        return $this->query()->select('party_id')
            ->selectRaw("COALESCE(SUM(CASE WHEN currency = 'IQD' THEN amount ELSE 0 END), 0) as total_iqd")
            ->selectRaw("COALESCE(SUM(CASE WHEN currency = 'USD' THEN amount ELSE 0 END), 0) as total_usd")
            ->selectRaw('COALESCE(SUM(amount), 0) as total')
            ->groupBy('party_id')->orderByDesc('total_iqd')->orderByDesc('total_usd')->orderBy('party_id')
            ->with(['party' => fn ($q) => $q->where('company_id', $this->company->id)])->get();
    }

    public function expensesByMonth(): Collection
    {
        // Portable conditional aggregation, compatible with PostgreSQL and SQLite.
        $query = $this->query();
        $start = CarbonImmutable::create((int) ($this->filters['year'] ?? now()->year), 1, 1)->startOfDay();
        for ($i = 1; $i <= 12; $i++) {
            $month = $start->setMonth($i);
            $query->selectRaw('COALESCE(SUM(CASE WHEN expense_date >= ? AND expense_date < ? THEN amount ELSE 0 END), 0) AS month_'.$i,
                [$month->toDateString(), $month->addMonth()->toDateString()]);
            $query->selectRaw("COALESCE(SUM(CASE WHEN currency = 'IQD' AND expense_date >= ? AND expense_date < ? THEN amount ELSE 0 END), 0) AS month_iqd_{$i}",
                [$month->toDateString(), $month->addMonth()->toDateString()]);
            $query->selectRaw("COALESCE(SUM(CASE WHEN currency = 'USD' AND expense_date >= ? AND expense_date < ? THEN amount ELSE 0 END), 0) AS month_usd_{$i}",
                [$month->toDateString(), $month->addMonth()->toDateString()]);
        }
        $totals = $query->toBase()->first();

        return collect(range(1, 12))->map(fn ($i) => [
            'month' => $i, 'name' => $start->setMonth($i)->locale('ar')->translatedFormat('F'),
            'total' => $totals->{'month_'.$i}, 'total_iqd' => $totals->{'month_iqd_'.$i},
            'total_usd' => $totals->{'month_usd_'.$i},
        ]);
    }

    public function summary(): array
    {
        $totals = $this->query()->toBase()
            ->selectRaw("COALESCE(SUM(CASE WHEN currency = 'IQD' THEN amount ELSE 0 END), 0) as total_iqd")
            ->selectRaw("COALESCE(SUM(CASE WHEN currency = 'USD' THEN amount ELSE 0 END), 0) as total_usd")
            ->selectRaw("COALESCE(AVG(CASE WHEN currency = 'IQD' THEN amount END), 0) as average_iqd")
            ->selectRaw("COALESCE(AVG(CASE WHEN currency = 'USD' THEN amount END), 0) as average_usd")
            ->selectRaw('COUNT(*) as count, MAX(expense_date) as last_date')->first();
        $people = $this->expensesByPerson();
        $months = $this->expensesByMonth();

        return ['total_iqd' => $totals->total_iqd, 'total_usd' => $totals->total_usd,
            'average_iqd' => $totals->average_iqd, 'average_usd' => $totals->average_usd,
            'total' => (float) $totals->total_iqd + (float) $totals->total_usd,
            'average' => (float) $totals->average_iqd + (float) $totals->average_usd,
            'count' => (int) $totals->count,
            'last_date' => $totals->last_date ? CarbonImmutable::parse($totals->last_date)->toDateString() : null,
            'top_person' => $people->first(),
            'top_month' => $totals->count ? $months->sortByDesc('total')->first() : null,
            'people' => $people, 'months' => $months];
    }

    public function report(): array
    {
        return ['company' => $this->company, 'filters' => $this->filters, 'summary' => $this->summary(),
            'currency' => 'IQD / USD', 'generatedAt' => now(),
            'period' => ($this->filters['year'] ?? now()->year)
                .(! empty($this->filters['month']) ? ' / '.$this->filters['month'] : '')
                .' | '.($this->filters['from'] ?? 'بداية السنة').' — '.($this->filters['to'] ?? 'نهاية السنة'),
            'logoPath' => $this->logoPath()];
    }

    public function logoPath(): ?string
    {
        $path = Setting::where('company_id', $this->company->id)->value('company_logo') ?: $this->company->logo;
        $disk = Storage::disk('public');

        return $path && $disk->exists($path) ? $disk->path($path) : null;
    }

    public function filename(string $extension): string
    {
        $f = $this->filters;

        return 'sippar-daily-accounts-'.($f['year'] ?? now()->year)
            .(! empty($f['month']) ? '-'.sprintf('%02d', $f['month']) : '')
            .(! empty($f['from']) ? '-from-'.$f['from'] : '')
            .(! empty($f['to']) ? '-to-'.$f['to'] : '').'.'.$extension;
    }
}
