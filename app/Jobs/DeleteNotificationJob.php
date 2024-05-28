<?php

namespace App\Jobs;

use App\Repositories\v1\NotificationRepositoryV1;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DeleteNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $requestId;

    private $notificationType;

    /**
     * Create a new job instance.
     */
    public function __construct($requestId, $notificationType)
    {
        $this->requestId = $requestId;
        $this->notificationType = $notificationType;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $notificationRepositoryV1 = new NotificationRepositoryV1();
        $notificationRepositoryV1->setDataId($this->requestId)->setNotificationType($this->notificationType)->delete();
    }
}
