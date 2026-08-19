<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ExternalInvoiceIntegration extends Model {
    protected $fillable=['company_id','provider','base_url','api_key','enabled','settings','last_sync_at'];
    protected function casts(): array { return ['api_key'=>'encrypted','enabled'=>'boolean','settings'=>'array','last_sync_at'=>'datetime']; }
    public function company(){ return $this->belongsTo(Company::class); }
}
