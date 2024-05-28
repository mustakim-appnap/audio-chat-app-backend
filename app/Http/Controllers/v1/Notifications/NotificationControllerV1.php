<?php

namespace App\Http\Controllers\v1\Notifications;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePushNotificationStatusRequest;
use App\Services\v1\NotificationServiceV1;
use Illuminate\Http\Response;

class NotificationControllerV1 extends Controller
{
    public function __construct(protected NotificationServiceV1 $notificationServiceV1)
    {

    }

    public function index()
    {
        try {
            $response = $this->notificationServiceV1->getUserNotifications();
            if ($response) {
                return response(['success' => true, 'error' => null, 'data' => $response], Response::HTTP_OK);
            } else {
                return response(['success' => false, 'error' => $response], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (\Exception $exception) {
            return response(['success' => false, 'error' => $exception->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getPushNotificationStatus()
    {
        try {
            $response = $this->notificationServiceV1->getPushNotificationStatus();
            if ($response) {
                return response(['success' => true, 'error' => null, 'data' => $response], Response::HTTP_OK);
            } else {
                return response(['success' => false, 'error' => $response], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (\Exception $exception) {
            return response(['success' => false, 'error' => $exception->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function updatePushNotificationStatus(UpdatePushNotificationStatusRequest $request)
    {
        try {
            $response = $this->notificationServiceV1->updatePushNotificationStatus($request->validated());
            if ($response) {
                return response(['success' => true, 'error' => null, 'data' => $response], Response::HTTP_OK);
            } else {
                return response(['success' => false, 'error' => $response], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (\Exception $exception) {
            return response(['success' => false, 'error' => $exception->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
