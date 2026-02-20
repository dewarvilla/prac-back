<?php

namespace App\Http\Resources\V1\Notification;

use Illuminate\Http\Resources\Json\ResourceCollection;

class NotificationCollection extends ResourceCollection
{
    public $collects = NotificationResource::class;
}
