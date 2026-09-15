<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyExpense extends Model
{
    protected $fillable = ['cashbox_id', 'expense_date', 'amount', 'currency', 'party_id', 'notes'];

    protected function casts(): array
    {
        return ['expense_date' => 'date', 'amount' => 'decimal:2'];
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function party()
    {
        return $this->belongsTo(DailyExpenseParty::class, 'party_id');
    }

    public function cashbox()
    {
        return $this->belongsTo(Cashbox::class)->withTrashed();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
