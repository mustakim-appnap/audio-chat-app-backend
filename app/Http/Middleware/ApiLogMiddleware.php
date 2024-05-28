<?php

namespace App\Http\Middleware;

use App\Models\ApiLog;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class ApiLogMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $response = ($next($request));
        $user = Auth::user();
        if ($user) {
            DB::table('users')->where('id', Auth::id())->update([
                'last_active_at' => Carbon::now(),
            ]);
        }
        ApiLog::create([
            'uri' => $request->getRequestUri(),
            'method' => $request->method(),
            'user_id' => $user ? $user->id : null,
            'device_token' => $user ? $user->device_token : null,
            'auth_id' => $user ? $user->auth_id : null,
            'params' => json_encode($request->all()),
            'header' => json_encode($request->header()),
            'authorized' => $user ? Config::get('variable_constants.check.yes') : Config::get('variable_constants.check.no'),
            'ip_address' => $request->ip(),
            'user_agent' => $request->header('user_agent'),
            'response_code' => $response->status(),
            'response' => $response->getContent(),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return $response;
    }
}
