<?php

namespace Modules\WhatsApp\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\WhatsApp\Events\RepairWasCreatedOrUpdate;
use Modules\WhatsApp\Events\SellWasCreatedOrUpdate;
use Modules\WhatsApp\Listeners\SendRepairNotifications;
use Modules\WhatsApp\Listeners\SendSellNotifications;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        RepairWasCreatedOrUpdate::class => [
            SendRepairNotifications::class
        ],
        SellWasCreatedOrUpdate::class => [
            SendSellNotifications::class
        ]
    ];
}