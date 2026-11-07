<?php

namespace Modules\WhatsApp\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsApp\Events\RepairWasCreatedOrUpdate;
use Modules\WhatsApp\Services\WhatsAppServices;

class SendRepairNotifications
{
    public $whatsAppUtils;
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(WhatsAppServices $whatsAppUtils)
    {
        $this->whatsAppUtils = $whatsAppUtils;
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(RepairWasCreatedOrUpdate $event)
    {
        print_r($event->data);
    }
}
