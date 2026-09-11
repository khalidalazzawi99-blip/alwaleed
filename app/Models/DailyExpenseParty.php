<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyExpenseParty extends Model
{
    protected $fillable = ['name', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function expenses()
    {
        return $this->hasMany(DailyExpense::class, 'party_id');
    }
}
