<?php

namespace App\Http\Middleware;
use Closure;
use Illuminate\Support\Facades\Log;

class CheckAcess
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
        Log::info(\Request::ip());
        // Log::info($request->user());
        return $next($request);
    }
}
