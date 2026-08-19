<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class CompanyApiToken extends Model {
    protected $fillable=['company_id','name','token_hash','token_prefix','scopes','last_used_at','expires_at','revoked_at'];
    protected function casts(): array { return ['scopes'=>'array','last_used_at'=>'datetime','expires_at'=>'datetime','revoked_at'=>'datetime']; }
    public function company(){ return $this->belongsTo(Company::class); }
    public function allows(string $scope): bool { return in_array('*',$this->scopes??[],true)||in_array($scope,$this->scopes??[],true); }
}
