<?php
namespace App\Http\Middleware;
use App\Models\CompanyApiToken;
use App\Models\ExternalInvoiceIntegration;
use App\Models\IntegrationLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
class AuthenticateCompanyApiToken {
    public function handle(Request $request, Closure $next, string $scope='*'): Response {
        // Tokens are commonly copied from password managers or chat clients.
        // Ignore surrounding whitespace without changing the token itself.
        $plain=trim((string) $request->bearerToken());
        if(!$plain) return response()->json(['message'=>'Unauthenticated.'],401);
        $token=CompanyApiToken::with('company')->where('token_hash',hash('sha256',$plain))->first();
        if(!$token) return response()->json(['message'=>'Invalid or expired API token.'],401);
        if($token->revoked_at || ($token->expires_at && $token->expires_at->isPast())) {
            IntegrationLog::create(['company_id'=>$token->company_id,'direction'=>'inbound','event_type'=>'authentication','status'=>'credential_rejected','http_status'=>401,'error_message'=>'API credential is revoked or expired.']);
            return response()->json(['message'=>'Invalid or expired API token.'],401);
        }
        if(!$token->company || $token->company->status!=='active') return response()->json(['message'=>'Company is not active.'],403);
        $enabled=ExternalInvoiceIntegration::where('company_id',$token->company_id)->where('provider','default')->where('enabled',true)->exists();
        if(!$enabled) {
            IntegrationLog::create(['company_id'=>$token->company_id,'direction'=>'inbound','event_type'=>'authentication','status'=>'integration_disabled','http_status'=>403,'error_message'=>'External invoice integration is disabled.']);
            return response()->json(['message'=>'External invoice integration is disabled.'],403);
        }
        if($scope!=='*' && !$token->allows($scope)) return response()->json(['message'=>'Token does not have the required scope.'],403);
        $token->forceFill(['last_used_at'=>now()])->saveQuietly();
        $request->attributes->set('api_token',$token); $request->attributes->set('company',$token->company);
        return $next($request);
    }
}
