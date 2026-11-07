<?php

namespace Modules\Superadmin\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AffiliateApprovedNotification extends Notification
{
    use Queueable;

    /**
     * @var \Modules\Superadmin\Entities\Affiliate
     */
    protected $affiliate;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($affiliate)
    {
        $this->affiliate = $affiliate;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject(__('superadmin::lang.affiliate_approved_email_subject', ['app_name' => config('app.name')]))
            ->greeting(__('superadmin::lang.affiliate_email_greeting'))
            ->line(__('superadmin::lang.affiliate_approved_email_line'))
            ->action(__('superadmin::lang.affiliate_dashboard'), $this->affiliate->referral_link)
            ->line(__('superadmin::lang.affiliate_approved_email_footer'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'affiliate_id' => $this->affiliate->id,
            'affiliate_code' => $this->affiliate->affiliate_code,
        ];
    }
}
