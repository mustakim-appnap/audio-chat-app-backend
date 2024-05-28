<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\AccountCheckRequest;
use App\Http\Requests\AuthRequest;
use App\Services\v1\AuthServiceV1;
use Illuminate\Http\Response;

class AuthControllerV1 extends Controller
{
    private $authServiceV1;

    public function __construct(AuthServiceV1 $authServiceV1)
    {
        $this->authServiceV1 = $authServiceV1;
    }

    public function authenticate(AuthRequest $request)
    {
        try {
            $response = $this->authServiceV1->authenticate($request->validated());
            if (is_array($response)) {
                if ($response['success'] === true) {
                    return response($response, Response::HTTP_OK);
                } else {
                    return response($response, Response::HTTP_INTERNAL_SERVER_ERROR);
                }
            } else {
                return response($response, Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (\Exception $exception) {
            return response(['success' => false, 'error' => $exception->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function checkAccounts(AccountCheckRequest $request)
    {
        try {
            $response = $this->authServiceV1->checkAccounts($request->validated());

            return response(['success' => true, 'error' => null, 'data' => $response], Response::HTTP_OK);
        } catch (\Exception $exception) {
            return response(['success' => false, 'error' => $exception->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function generateHeaderToken()
    {
        try {
            $response = $this->authServiceV1->generateHeaderToken();

            return response(['token' => $response], Response::HTTP_OK);
        } catch (\Exception $exception) {
            return response(['success' => false, 'error' => $exception->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function logout()
    {
        try {
            $this->authServiceV1->logout();

            return response(['success' => true, 'error' => null], Response::HTTP_OK);
        } catch (\Exception $exception) {
            return response(['success' => false, 'error' => $exception->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
