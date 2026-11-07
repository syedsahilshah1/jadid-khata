<?php

namespace Modules\Superadmin\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\Superadmin\Utils\AffiliateUtil;

class AffiliateCommissionNotification extends Notification
{
    use Queueable;

    /**
     * @var \Modules\Superadmin\Entities\AffiliateCommission
     */
    protected $commission;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($commission)
    {
        $this->commission = $commission;
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
        $amount = app(AffiliateUtil::class)->formatAmount($this->commission->commission_amount);

        $mail = (new MailMessage)
            ->subject(__('superadmin::lang.affiliate_commission_email_subject', ['app_name' => config('app.name')]))
            ->greeting(__('superadmin::lang.affiliate_email_greeting'))
            ->line(__('superadmin::lang.affiliate_commission_email_line', ['amount' => $amount]));

        //The referred business is only named when it is still available.
        $business = $this->commission->business;
        if (! empty($business)) {
            $mail->line(__('superadmin::lang.business').': '.$business->name);
        }

        return $mail
            ->line(__('superadmin::lang.sale_amount').': '.app(AffiliateUtil::class)->formatAmount($this->commission->sale_amount))
            ->line(__('superadmin::lang.commission_percentage').': '.$this->commission->commission_percentage.'%')
            ->line(__('superadmin::lang.affiliate_commission_email_footer'));
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
            'commission_id' => $this->commission->id,
            'commission_amount' => $this->commission->commission_amount,
        ];
    }
}
