<?php

namespace Modules\Superadmin\Http\Controllers;

use App\System;
use App\Utils\Util;
use Illuminate\Routing\Controller;
use Menu;
use Modules\Superadmin\Notifications\NewBusinessNotification;
use Modules\Superadmin\Notifications\NewBusinessWelcomNotification;
use Notification;

class DataController extends Controller
{
    /**
     * Parses notification message from database.
     *
     * @return array
     */
    public function parse_notification($notification)
    {
        $notification_data = [];
        if ($notification->type ==
            'Modules\Superadmin\Notifications\SendSubscriptionExpiryAlert') {
            $data = $notification->data;
            $msg = __('superadmin::lang.subscription_expiry_alert', ['days_left' => $data['days_left'], 'app_name' => config('app.name')]);

            $notification_data = [
                'msg' => $msg,
                'icon_class' => 'fas fa-exclamation-triangle bg-yellow',
                'link' => action([\Modules\Superadmin\Http\Controllers\SubscriptionController::class, 'index']),
                'read_at' => $notification->read_at,
                'created_at' => $notification->created_at->diffForHumans(),
            ];
        } elseif ($notification->type ==
            'Modules\Superadmin\Notifications\SuperadminCommunicator') {
            $msg = __('superadmin::lang.new_message_from_superadmin');

            $notification_data = [
                'msg' => $msg,
                'icon_class' => 'fas fa-exclamation-triangle bg-yellow',
                'link' => action([\App\Http\Controllers\HomeController::class, 'showNotification'], [$notification->id]),
                'show_popup' => true,
                'read_at' => $notification->read_at,
                'created_at' => $notification->created_at->diffForHumans(),
            ];
        }

        return $notification_data;
    }

    /**
     * Function to be called after a new business is created.
     *
     * @return null
     */
    /**
     * Create the affiliate commission owed on a newly approved subscription.
     *
     * Fired from every route a subscription can be approved by: the online
     * gateways and superadmin created ones at creation, pesapal from its
     * callback, and an offline payment approved by hand.
     *
     * @return void
     */
    public function after_subscription_approved($data)
    {
        //A commission must never be able to fail a payment.
        try {
            if (! empty($data['subscription'])) {
                app(\Modules\Superadmin\Utils\AffiliateUtil::class)
                    ->createForSubscription($data['subscription']);
            }
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());
        }
    }

    public function after_business_created($data)
    {
        /*
         * Attribute the registration to a referring affiliate.
         *
         * Deliberately ahead of the email handling below, which returns early
         * when no mailer is configured; referral tracking must not depend on
         * that. Kept in its own try so neither concern can break the other,
         * and so a tracking failure can never fail the registration itself.
         */
        try {
            if (! empty($data['business'])) {
                app(\Modules\Superadmin\Utils\AffiliateReferralUtil::class)
                    ->attributeRegistration($data['business'], request());
            }
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
        }

        /*
         * Everything below sends email to real addresses, which a demo install
         * must not do. The attribution above is only a database row, so it
         * stays. This guard used to sit in BusinessController and skipped the
         * whole hook, taking referral tracking down with it.
         */
        if (config('app.env') == 'demo') {
            return null;
        }

        try {
            //Send new registration notification to superadmin
            $is_notif_enabled =
            System::getProperty('enable_new_business_registration_notification');

            $common_util = new Util();

            if (!$common_util->IsMailConfigured()) {
                return null;
            }

            $email = System::getProperty('email');
            $business = $data['business'];

            if (!empty($email) && $is_notif_enabled == 1) {
                Notification::route('mail', $email)
                    ->notify(new NewBusinessNotification($business));
            }

            //Send welcome email to business owner
            $welcome_email_settings = System::getProperties(['enable_welcome_email', 'welcome_email_subject', 'welcome_email_body'], true);

            if (isset($welcome_email_settings['enable_welcome_email']) && $welcome_email_settings['enable_welcome_email'] == 1 && !empty($welcome_email_settings['welcome_email_subject']) && !empty($welcome_email_settings['welcome_email_body'])) {
                $subject = $this->removeTags($welcome_email_settings['welcome_email_subject'], $business);
                $body = $this->removeTags($welcome_email_settings['welcome_email_body'], $business);

                $welcome_email_data = [
                    'subject' => $subject,
                    'body' => $body,
                ];

                Notification::route('mail', $business->owner->email)
                    ->notify(new NewBusinessWelcomNotification($welcome_email_data));
            }
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
        }

        return null;
    }

    private function removeTags($string, $business)
    {
        $string = str_replace('{business_name}', $business->name, $string);
        $string = str_replace('{owner_name}', $business->owner->user_full_name, $string);

        return $string;
    }

    /**
     * Adds Superadmin menus
     *
     * @return null
     */
    public function modifyAdminMenu()
    {
        if (auth()->user()->can('superadmin')) {
            Menu::modify(
                'admin-sidebar-menu',
                function ($menu) {
                    $menu->url(action([\Modules\Superadmin\Http\Controllers\SuperadminController::class, 'index']), __('superadmin::lang.superadmin'), ['icon' => '<svg aria-hidden="true" class="tw-size-5 tw-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                    <path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0"></path>
                    <path d="M6 21v-2a4 4 0 0 1 4 -4h2.5"></path>
                    <path d="M19.001 19m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"></path>
                    <path d="M19.001 15.5v1.5"></path>
                    <path d="M19.001 21v1.5"></path>
                    <path d="M22.032 17.25l-1.299 .75"></path>
                    <path d="M17.27 20l-1.3 .75"></path>
                    <path d="M15.97 17.25l1.3 .75"></path>
                    <path d="M20.733 20l1.3 .75"></path>
                  </svg>', 'style' => config('app.env') == 'demo' ? 'background-color: #12DDDC;color:white' : '', 'active' => request()->segment(1) == 'superadmin'])->order(1);
                }
            );
        }

        if (auth()->user()->can('superadmin.access_package_subscriptions') && auth()->user()->can('business_settings.access')) {
            $menu = Menu::instance('admin-sidebar-menu');
            $menu->whereTitle(__('business.settings'), function ($sub) {
                $sub->url(
                    action([\Modules\Superadmin\Http\Controllers\SubscriptionController::class, 'index']),
                    __('superadmin::lang.subscription'),
                    ['icon' => '', 'active' => request()->segment(1) == 'subscription']
                );
            });
        }

        //Affiliate entry, shown to every user once the superadmin enables the system.
        if (app(\Modules\Superadmin\Utils\AffiliateUtil::class)->isEnabled()) {
            Menu::modify('admin-sidebar-menu', function ($menu) {
                $menu->url(
                    action([\Modules\Superadmin\Http\Controllers\AffiliateController::class, 'index']),
                    __('superadmin::lang.affiliate'),
                    ['icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="tw-size-5 tw-shrink-0" width="24" height="24" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <path d="M6 12m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" />
                        <path d="M18 6m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" />
                        <path d="M18 18m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" />
                        <path d="M8.7 10.7l6.6 -3.4" />
                        <path d="M8.7 13.3l6.6 3.4" />
                    </svg>', 'active' => request()->segment(1) == 'affiliate']
                )->order(89);
            });
        }
    }

    /**
     * Defines user permissions for the module.
     *
     * @return array
     */
    public function user_permissions()
    {
        return [
            [
                'value' => 'superadmin.access_package_subscriptions',
                'label' => __('superadmin::lang.access_package_subscriptions'),
                'default' => false,
            ],
        ];
    }
}
