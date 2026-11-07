<?php

namespace Modules\Superadmin\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\Superadmin\Utils\AffiliateUtil;

class AffiliatePayoutPaidNotification extends Notification
{
    use Queueable;

    /**
     * @var \Modules\Superadmin\Entities\AffiliatePayout
     */
    protected $payout;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($payout)
    {
        $this->payout = $payout;
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
        $amount = app(AffiliateUtil::class)->formatAmount($this->payout->amount);

        $mail = (new MailMessage)
            ->subject(__('superadmin::lang.affiliate_payout_email_subject', ['app_name' => config('app.name')]))
            ->greeting(__('superadmin::lang.affiliate_email_greeting'))
            ->line(__('superadmin::lang.affiliate_payout_email_line', ['amount' => $amount]));

        //Carries the payment reference the superadmin recorded, when there is one.
        if (! empty($this->payout->notes)) {
            $mail->line(__('superadmin::lang.notes').': '.$this->payout->notes);
        }

        return $mail->line(__('superadmin::lang.affiliate_payout_email_footer'));
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
            'payout_id' => $this->payout->id,
            'amount' => $this->payout->amount,
        ];
    }
}
