<?php

namespace App\Http\Middleware;
use Closure;
use Illuminate\Support\Facades\Log;
use DB;
class GetOrgUrl
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Set Log
        date_default_timezone_set('Asia/Ho_Chi_Minh');
        $current_date = date('Y-m-d');
        Log::useFiles(base_path() . '/dailyLog/'. $current_date .'-access.log', 'info');
        // End Set Log
        $response = $next($request);
        // Sau khi xử lý xuất excel
        $bodyContent = $response->getOriginalContent();
        // Log::info($bodyContent);
        if (\App::environment('local')) {
            $unsubcribelink = "http://localhost:4200/#/";
        } else {
            $unsubcribelink = "https://bi.acs.vn/#/";
            try {
                $user_id = $request->user_id;
                $orgInfo = DB::table('organizations')->join('users', 'organizations.id', '=', 'users.organization_id')->where('users.id', $user_id)->select('organizations.id', 'organizations.organization_name', 'organizations.subdomain_name')->first();
                if ($orgInfo != null && $orgInfo->subdomain_name != null) {
                    $unsubcribelink = "https://$orgInfo->subdomain_name/#/";
                }
            } catch (\Exception $e) {
                Log::info($e->getMessage());
            }
        }
        $bodyContent['unsubcribeLink'] = $unsubcribelink;
        $jsonContent = json_encode($bodyContent);
        $response->setContent($jsonContent);
        return $response;
    }

    

    public function terminate($request, $response) {
    }
}
