<?php

namespace App\Http\Middleware;

use App\Models\ApiLog;
use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

class IsHeaderValid
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (env('SECURE_ACCESS')) {
            if (! $request->hasHeader('idToken')) {
                $this->add_to_log($request, 'Invalid Headers Set!');

                return response(['success' => false, 'error' => 'Invalid Headers Set!'], Response::HTTP_UNAUTHORIZED);
            }
            try {
                $private_key = \Illuminate\Support\Facades\File::get(storage_path(env('JWT_PUBLIC_KEY_FILE')));
                $decoded = JWT::decode($request->header('idToken'), new Key($private_key, 'RS256'));
                if (! ($decoded->issuer == env('JWT_ISSUER'))) {
                    $this->add_to_log($request, 'Invalid Issuer!');

                    return response(['success' => false, 'error' => 'Invalid Issuer!'], Response::HTTP_UNAUTHORIZED);
                }
                if (! ($decoded->audience == env('JWT_AUDIENCE'))) {
                    $this->add_to_log($request, 'Invalid Audience!');

                    return response(['success' => false, 'error' => 'Invalid Audience!'], Response::HTTP_UNAUTHORIZED);
                }
                if (! $decoded->iat) {
                    $this->add_to_log($request, 'Issued at not set!');

                    return response(['success' => false, 'error' => 'Issued at not set!'], Response::HTTP_UNAUTHORIZED);
                }
                if (! $decoded->exp) {
                    $this->add_to_log($request, 'Expiration time not set!');

                    return response(['success' => false, 'error' => 'Expiration time not set!'], Response::HTTP_UNAUTHORIZED);
                }
                if (! (($decoded->exp - $decoded->iat) <= env('JWT_EXP'))) {
                    $this->add_to_log($request, 'Token Has Expired!');

                    return response(['success' => false, 'error' => 'Token Has Expired!'], Response::HTTP_UNAUTHORIZED);
                }

                return $next($request);
            } catch (\Exception $exception) {
                $this->add_to_log($request, $exception->getMessage());

                return response(['success' => false, 'error' => $exception->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        return $next($request);
    }

    public function add_to_log($request, $message)
    {
        $user = Auth::user();

        return ApiLog::create([
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
            'response_code' => Response::HTTP_UNAUTHORIZED,
            'response' => $message,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
