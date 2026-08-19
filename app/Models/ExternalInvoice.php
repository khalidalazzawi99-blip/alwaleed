<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ExternalInvoice extends Model {
    protected $fillable=['company_id','customer_id','external_invoice_id','external_customer_id','invoice_no','invoice_date','amount'];
    protected function casts(): array { return ['invoice_date'=>'date','amount'=>'decimal:2']; }
    public function customer(){ return $this->belongsTo(Customer::class); }
    public function company(){ return $this->belongsTo(Company::class); }
}
