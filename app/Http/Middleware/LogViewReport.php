<?php

namespace App\Http\Middleware;
use Closure;
use Illuminate\Support\Facades\Log;
use App\PageLog;
use Auth;
use Session;
use Exception;
use Carbon\Carbon;
class LogViewReport
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
        $response = $next($request);
        // $response->withCookie(cookie()->forever('page_id', $request->page_id));
        // try {
            
        // } catch (Exception $e) {

        // }
        return $response;
    }

    

    public function terminate($request, $response) {
    }
}
