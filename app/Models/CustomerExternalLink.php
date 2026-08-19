<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class CustomerExternalLink extends Model {
    protected $fillable=['company_id','customer_id','provider','external_customer_id'];
    public function customer(){ return $this->belongsTo(Customer::class); }
}
