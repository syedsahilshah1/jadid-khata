<?php

namespace Modules\Superadmin\Utils;

use App\System;
use App\User;
use App\Utils\Util;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Modules\Superadmin\Entities\Affiliate;
use Modules\Superadmin\Entities\AffiliateClick;
use Modules\Superadmin\Entities\AffiliateCommission;
use Modules\Superadmin\Entities\AffiliatePayout;
use Modules\Superadmin\Entities\AffiliateReferral;
use Modules\Superadmin\Entities\Subscription;
use Modules\Superadmin\Notifications\AffiliateApprovedNotification;
use Modules\Superadmin\Notifications\AffiliateCommissionNotification;
use Modules\Superadmin\Notifications\AffiliatePayoutPaidNotification;
use Notification;

/**
 * The affiliate program: settings, accounts, statistics, commissions, payouts
 * and the notifications for them. The referral link cookie lives in
 * AffiliateReferralUtil.
 *
 * The settings are read from the database on first use and kept for the
 * life of the instance, so a caller asking for several pays for one query.
 */
class AffiliateUtil extends Util
{
    //Namespaced keys, because the `system` table is shared.
    const DEFAULTS = [
        'affiliate_enabled' => 0,
        'affiliate_commission_percentage' => 10,
        //1: every paid subscription earns commission. 0: the first one only.
        'affiliate_commission_recurring' => 1,
        //AFF-RATES: % on renewals; empty = follow the old recurring switch.
        'affiliate_renewal_commission_percentage' => '',
        //AFF-TEAM: fixed amount for every upline affiliate. 0 = off.
        'affiliate_team_commission_amount' => 0,
        'affiliate_cookie_days' => 30,
        'affiliate_min_payout' => 0,
        'affiliate_terms' => '',
    ];

    //Changing this would strand every cookie already issued.
    const COOKIE_NAME = 'affiliate_ref';

    //I, O, 0 and 1 omitted: they are the pairs people misread.
    const CODE_ALPHABET = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    const CODE_LENGTH = 6;

    const CODE_ATTEMPTS = 20;

    /**
     * Settings as read from the database, or null before the first read.
     *
     * @var \Illuminate\Support\Collection|null
     */
    protected $settings = null;

    //AFF-RATES: 'first' or 'renewal' for the commission being written.
    protected $aff_commission_type = null;

    /**
     * Whether the affiliate system is switched on by the superadmin.
     *
     * @return bool
     */
    public function isEnabled()
    {
        return filter_var($this->get('affiliate_enabled'), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @return float
     */
    public function commissionPercentage()
    {
        return (float) $this->get('affiliate_commission_percentage');
    }

    /**
     * Whether every paid subscription of a referred business earns commission,
     * rather than only its first.
     *
     * @return bool
     */
    public function isRecurring()
    {
        return filter_var($this->get('affiliate_commission_recurring'), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * AFF-RATES: % paid on every later subscription of a referred business
     * (renewals). Never saved yet: old "recurring" = same %, "first only" = 0.
     *
     * @return float
     */
    public function renewalPercentage()
    {
        $v = $this->get('affiliate_renewal_commission_percentage');
        if ($v === '' || $v === null) {
            return $this->isRecurring() ? $this->commissionPercentage() : 0.0;
        }

        return (float) $v;
    }

    /**
     * AFF-RATES: rates used for one affiliate: its own rates where set,
     * otherwise the program defaults.
     *
     * @return array ['first' => float, 'renewal' => float, 'custom' => bool]
     */

    //AFF-TEAM: how far up the tree team commission goes (also stops loops).
    const TEAM_MAX_LEVELS = 20;

    /**
     * AFF-TEAM: default fixed amount an upline affiliate gets on each
     * commission earned below it in the tree.
     *
     * @return float
     */
    public function teamAmount()
    {
        return (float) $this->get('affiliate_team_commission_amount');
    }

    /**
     * AFF-TEAM: team amount for one affiliate: its own amount, else the default.
     *
     * @return float
     */
    public function teamAmountFor(Affiliate $affiliate)
    {
        $v = $affiliate->team_commission_amount;

        return ($v === null || $v === '') ? $this->teamAmount() : (float) $v;
    }

    /**
     * AFF-TEAM2: team commission an upline gets on one sale. Only affiliates the
     * superadmin switched on (own value set) get anything. Percent of the sale or fixed.
     *
     * @return array [float amount, float percent (0 for fixed)]
     */
    public function teamCommissionFor(Affiliate $affiliate, $sale_amount)
    {
        $v = $affiliate->team_commission_amount;

        if ($v === null || $v === '' || (float) $v <= 0) {
            return [0.0, 0.0];
        }

        if ($affiliate->team_commission_type === 'percent') {
            $pct = min(100, (float) $v);

            return [(float) $sale_amount * $pct / 100, $pct];
        }

        return [(float) $v, 0.0];
    }

    /**
     * AFF-TEAM: the affiliate whose link brought this user's business, if any.
     *
     * @return int|null
     */
    public function findParentFor(User $user)
    {
        if (empty($user->business_id)) {
            return null;
        }

        $referral = AffiliateReferral::where('referred_business_id', $user->business_id)->first();

        if (empty($referral) || (int) $referral->affiliate_user_id === (int) $user->id) {
            return null;
        }

        return (int) $referral->affiliate_user_id;
    }

    /**
     * AFF-TEAM3: user ids above an affiliate in the tree (its uplines).
     *
     * @return int[]
     */
    public function uplinesOf(Affiliate $affiliate)
    {
        $out = [];
        $current = $affiliate;

        while (! empty($current) && ! empty($current->parent_affiliate_user_id) && count($out) < self::TEAM_MAX_LEVELS) {
            $up = (int) $current->parent_affiliate_user_id;
            if (in_array($up, $out, true) || $up === (int) $affiliate->user_id) {
                break;
            }
            $out[] = $up;
            $current = Affiliate::where('user_id', $up)->first();
        }

        return $out;
    }

    /**
     * AFF-TEAM: everyone below an affiliate in the tree.
     *
     * @return array of ['affiliate' => Affiliate, 'level' => int]
     */
    public function teamOf(Affiliate $affiliate)
    {
        $out = [];
        $seen = [(int) $affiliate->user_id => true];
        $frontier = [(int) $affiliate->user_id];
        $level = 1;

        while (! empty($frontier) && $level <= self::TEAM_MAX_LEVELS) {
            $children = Affiliate::with('user')->whereIn('parent_affiliate_user_id', $frontier)->orderBy('id')->get();
            $frontier = [];

            foreach ($children as $child) {
                if (isset($seen[(int) $child->user_id])) {
                    continue;
                }
                $seen[(int) $child->user_id] = true;
                $out[] = ['affiliate' => $child, 'level' => $level];
                $frontier[] = (int) $child->user_id;
            }
            $level++;
        }

        return $out;
    }

    /**
     * AFF-TEAM: after a direct commission, give every approved upline its
     * fixed team amount. Safe to run again: one row per subscription per affiliate.
     *
     * @return void
     */
    protected function createTeamCommissions(Subscription $subscription, AffiliateReferral $referral, $sale_amount)
    {
        $direct = (int) $referral->affiliate_user_id;
        $seen = [$direct => true];
        $current = Affiliate::where('user_id', $direct)->first();
        $level = 2;

        while (! empty($current) && ! empty($current->parent_affiliate_user_id) && $level <= self::TEAM_MAX_LEVELS + 1) {
            $up_id = (int) $current->parent_affiliate_user_id;

            if (isset($seen[$up_id])) {
                break;
            }
            $seen[$up_id] = true;

            $up = Affiliate::where('user_id', $up_id)->first();

            if (empty($up)) {
                break;
            }

            [$amount, $team_pct] = $this->teamCommissionFor($up, $sale_amount); //AFF-TEAM2
            $amount = round($amount, session('business.currency_precision', 2));

            if ($up->isApproved() && $amount > 0
                && ! AffiliateCommission::where('subscription_id', $subscription->id)->where('affiliate_user_id', $up_id)->exists()) {
                try {
                    AffiliateCommission::create([
                        'affiliate_user_id' => $up_id,
                        'referred_business_id' => $referral->referred_business_id,
                        'package_id' => $subscription->package_id,
                        'subscription_id' => $subscription->id,
                        'transaction_id' => $subscription->payment_transaction_id,
                        'sale_amount' => $sale_amount,
                        'commission_percentage' => $team_pct,
                        'commission_amount' => $amount,
                        'status' => 'pending',
                        'commission_type' => 'team',
                        'via_user_id' => $direct,
                        'level' => $level,
                    ]);
                } catch (QueryException $e) {
                    if ((string) $e->getCode() !== '23000') {
                        throw $e;
                    }
                }
            }

            //AFF-TEAM4: only the direct team head earns, no chain further up.
            break;
        }
    }

    /**
     * AFF-RATES (moved below AFF-TEAM helpers).
     */
    public function ratesFor(Affiliate $affiliate)
    {
        $first = $affiliate->first_commission_percentage;
        $renewal = $affiliate->renewal_commission_percentage;
        $has_first = ! ($first === null || $first === '');
        $has_renewal = ! ($renewal === null || $renewal === '');

        return [
            'first' => $has_first ? (float) $first : $this->commissionPercentage(),
            'renewal' => $has_renewal ? (float) $renewal : $this->renewalPercentage(),
            'custom' => $has_first || $has_renewal,
        ];
    }

    /**
     * @return int
     */
    public function cookieDays()
    {
        return (int) $this->get('affiliate_cookie_days');
    }

    /**
     * @return float
     */
    public function minPayout()
    {
        return (float) $this->get('affiliate_min_payout');
    }

    /**
     * Terms & conditions html shown on the affiliate application form.
     *
     * @return string
     */
    public function terms()
    {
        return (string) $this->get('affiliate_terms');
    }

    /**
     * @return string
     */
    public function cookieName()
    {
        return self::COOKIE_NAME;
    }

    /**
     * All settings, shaped for the settings form.
     *
     * @return array
     */
    public function all()
    {
        return [
            'enabled' => $this->isEnabled(),
            'commission_percentage' => $this->commissionPercentage(),
            'commission_recurring' => $this->isRecurring(),
            'renewal_commission_percentage' => $this->renewalPercentage(), //AFF-RATES
            'team_commission_amount' => $this->teamAmount(), //AFF-TEAM
            'cookie_days' => $this->cookieDays(),
            'min_payout' => $this->minPayout(),
            'terms' => $this->terms(),
        ];
    }

    /**
     * Persist the settings.
     *
     * @param  array  $input  Input from the settings form.
     * @return void
     */
    public function update(array $input)
    {
        $values = [
            'affiliate_enabled' => ! empty($input['enabled']) ? 1 : 0,
            'affiliate_commission_percentage' => (float) ($input['commission_percentage'] ?? 0),
            //AFF-RATES: renewals are now set by their own %; the old switch follows it.
            'affiliate_commission_recurring' => (array_key_exists('renewal_commission_percentage', $input) ? ((float) $input['renewal_commission_percentage'] > 0) : ! empty($input['commission_recurring'])) ? 1 : 0,
            'affiliate_renewal_commission_percentage' => array_key_exists('renewal_commission_percentage', $input) ? (float) $input['renewal_commission_percentage'] : '',
            'affiliate_team_commission_amount' => array_key_exists('team_commission_amount', $input) ? max(0, (float) $input['team_commission_amount']) : $this->get('affiliate_team_commission_amount'), //AFF-TEAM
            'affiliate_cookie_days' => (int) ($input['cookie_days'] ?? 0),
            //num_uf: a money field arrives in the business's number format.
            'affiliate_min_payout' => $this->num_uf($input['min_payout'] ?? 0),
            //Stored as html: the column is TEXT, so nothing needs encoding.
            'affiliate_terms' => (string) ($input['terms'] ?? ''),
        ];

        foreach ($values as $key => $value) {
            System::addProperty($key, $value);
        }

        //Anything read later through this instance must see what was just written.
        $this->settings = null;
    }

    /**
     * Read one setting, falling back to its default when never saved.
     *
     * @param  string  $key
     * @return mixed
     */
    protected function get($key)
    {
        if ($this->settings === null) {
            $this->settings = System::getProperties(array_keys(self::DEFAULTS), true);
        }

        $value = $this->settings[$key] ?? null;

        //A row that exists but holds an empty string still counts as unset.
        return ($value === null || $value === '') ? self::DEFAULTS[$key] : $value;
    }

    /**
     * Get the affiliate account belonging to a user, if any.
     *
     * @param  int  $user_id
     * @return \Modules\Superadmin\Entities\Affiliate|null
     */
    public function forUser($user_id)
    {
        return Affiliate::where('user_id', $user_id)->first();
    }

    /**
     * Find an approved affiliate by its public code.
     *
     * Referral tracking uses this: a pending, rejected or suspended affiliate
     * must not accrue clicks, referrals or commissions.
     *
     * @param  string  $code
     * @return \Modules\Superadmin\Entities\Affiliate|null
     */
    public function findApprovedByCode($code)
    {
        if (empty($code)) {
            return null;
        }

        return Affiliate::where('affiliate_code', $code)->approved()->first();
    }

    /**
     * Create a pending affiliate application for a user.
     *
     * No code is issued here: an application that is never approved should not
     * consume one, and a pending applicant has nothing to refer with anyway.
     *
     * @param  \App\User  $user
     * @return \Modules\Superadmin\Entities\Affiliate
     */
    public function apply(User $user)
    {
        return Affiliate::create([
            'user_id' => $user->id,
            'status' => 'pending',
            'tc_accepted_at' => now(),
            'parent_affiliate_user_id' => $this->findParentFor($user), //AFF-TEAM
        ]);
    }

    /**
     * Statuses an affiliate account may be moved to by the superadmin.
     *
     * @return array
     */
    public static function assignableAffiliateStatuses()
    {
        return ['approved', 'rejected', 'suspended'];
    }

    /**
     * Move an affiliate account to a new status.
     *
     * @param  \Modules\Superadmin\Entities\Affiliate  $affiliate
     * @param  string  $status
     * @return \Modules\Superadmin\Entities\Affiliate
     *
     * @throws \InvalidArgumentException  When the status is not assignable.
     */
    public function updateAffiliateStatus(Affiliate $affiliate, $status)
    {
        if (! in_array($status, self::assignableAffiliateStatuses(), true)) {
            throw new \InvalidArgumentException('Invalid affiliate status: '.$status);
        }

        //Nothing to do, and it must not look like a fresh approval.
        if ($affiliate->status === $status) {
            return $affiliate;
        }

        /*
         * The code is issued on approval, and only once: an affiliate that is
         * suspended and later reinstated keeps the code it has been sharing.
         */
        if ($status === 'approved' && empty($affiliate->affiliate_code)) {
            $affiliate->affiliate_code = $this->generateCode();
        }

        $affiliate->status = $status;
        $affiliate->save();

        /*
         * Only on the transition into approved, which the equality check above
         * guarantees: re-approving an already approved affiliate returns early
         * and cannot send a second welcome.
         */
        if ($status === 'approved' && ! empty($affiliate->user->email) && $this->IsMailConfigured()) {
            Notification::route('mail', $affiliate->user->email)
                ->notify(new AffiliateApprovedNotification($affiliate));
        }

        return $affiliate;
    }

    /**
     * Build an unused affiliate code. Random rather than sequential, so it
     * leaks nothing about how many affiliates exist.
     *
     * @return string
     *
     * @throws \RuntimeException  When no unused code could be found.
     */
    public function generateCode()
    {
        $alphabet_size = strlen(self::CODE_ALPHABET);

        for ($attempt = 1; $attempt <= self::CODE_ATTEMPTS; $attempt++) {
            $code = '';

            for ($i = 0; $i < self::CODE_LENGTH; $i++) {
                $code .= self::CODE_ALPHABET[random_int(0, $alphabet_size - 1)];
            }

            if (! Affiliate::where('affiliate_code', $code)->exists()) {
                return $code;
            }
        }

        //32^6 combinations: 20 collisions means something is wrong.
        throw new \RuntimeException('Could not generate an unused affiliate code.');
    }

    /**
     * Headline figures for an affiliate.
     *
     * The superadmin's affiliate detail page and the affiliate's own dashboard
     * show the same numbers, so they are produced in one place to stop the two
     * drifting apart.
     *
     * @return array
     */
    public function forAffiliate(Affiliate $affiliate)
    {
        $user_id = $affiliate->user_id;

        //Summed once each: payableAmount() would re-run both.
        $approved = $this->approvedTotal($user_id);
        $paid = $this->paidTotal($user_id);

        return [
            'click_count' => AffiliateClick::where('affiliate_id', $affiliate->id)->count(),
            'registration_count' => AffiliateReferral::where('affiliate_user_id', $user_id)->count(),

            //Balance based: what is approved, less what has gone out.
            'payable_commission' => round($approved - $paid, session('business.currency_precision', 2)),
            'paid_commission' => $paid,
        ];
    }

    /**
     * Create the commission owed on a subscription, if one is owed.
     *
     * Safe to call repeatedly for the same subscription: it is the only way the
     * observer can stay simple, since a subscription is saved several times
     * over its life.
     *
     * @return \Modules\Superadmin\Entities\AffiliateCommission|null  Null
     *         whenever no commission is due, which is the common case.
     */
    public function createForSubscription(Subscription $subscription)
    {
        //Only a finally approved purchase earns anything.
        if (! $this->isEnabled() || $subscription->status !== 'approved') {
            return null;
        }

        /*
         * package_price is what the business actually paid, after any coupon.
         * original_price is the list price and would over pay the affiliate on
         * every discounted sale.
         */
        $sale_amount = (float) $subscription->package_price;
        $percentage = $this->commissionPercentage();

        //AFF-RATES: first subscription of the business or a renewal, and the affiliate's own rates.
        $aff_referral = AffiliateReferral::where('referred_business_id', $subscription->business_id)->first();
        $aff_account = $aff_referral ? Affiliate::where('user_id', $aff_referral->affiliate_user_id)->first() : null;
        $aff_rates = $aff_account
            ? $this->ratesFor($aff_account)
            : ['first' => $percentage, 'renewal' => $this->renewalPercentage()];
        $aff_is_first = ! AffiliateCommission::where('referred_business_id', $subscription->business_id)->exists();
        $percentage = $aff_is_first ? $aff_rates['first'] : $aff_rates['renewal'];
        $this->aff_commission_type = $aff_is_first ? 'first' : 'renewal';

        if ($sale_amount <= 0 || $percentage <= 0) {
            return null;
        }

        $referral = AffiliateReferral::where('referred_business_id', $subscription->business_id)->first();

        if (empty($referral)) {
            return null;
        }

        if (true) { //AFF-RATES: every subscription is checked; a 0% renewal rate stops renewals above.
            /*
             * Every paid subscription earns, but each one only once: the same
             * subscription can be approved again after being unapproved.
             */
            $already_earned = AffiliateCommission::where('subscription_id', $subscription->id)->exists();
        } else {
            /*
             * First purchase only: later renewals and upgrades earn nothing.
             * Deliberately without a status filter, so a commission cancelled
             * by hand is not quietly replaced by the next purchase.
             */
            $already_earned = AffiliateCommission::where('referred_business_id', $referral->referred_business_id)->exists();
        }

        if ($already_earned) {
            return null;
        }

        //A rejected or suspended affiliate stops earning.
        if (! Affiliate::where('user_id', $referral->affiliate_user_id)->approved()->exists()) {
            return null;
        }

        $commission = $this->storeCommission($subscription, $referral, $sale_amount, $percentage);

        //AFF-TEAM: upline affiliates get their fixed team amount.
        if (! empty($commission)) {
            $this->createTeamCommissions($subscription, $referral, $sale_amount);
        }

        //Told only once the commission is actually on the books.
        if (! empty($commission) && ! empty($commission->affiliateUser->email) && $this->IsMailConfigured()) {
            Notification::route('mail', $commission->affiliateUser->email)
                ->notify(new AffiliateCommissionNotification($commission));
        }

        return $commission;
    }

    /**
     * Statuses a superadmin may set on a commission. "paid" is absent: that is
     * reached by paying the affiliate, never by editing the row.
     *
     * @return array
     */
    public static function assignableCommissionStatuses()
    {
        return ['pending', 'approved', 'cancelled'];
    }

    /**
     * Verb for the button that moves a commission into a status.
     *
     * @param  string  $status
     * @return string
     */
    public static function statusActionLabel($status)
    {
        $labels = [
            'approved' => __('superadmin::lang.approve'),
            'cancelled' => __('superadmin::lang.cancel_commission'),
            'pending' => __('superadmin::lang.reset_to_pending'),
        ];

        return $labels[$status] ?? $status;
    }

    /**
     * Change a commission's status.
     *
     * @param  string  $status
     * @return \Modules\Superadmin\Entities\AffiliateCommission
     *
     * @throws \InvalidArgumentException  When the status is not assignable.
     * @throws \RuntimeException  When the commission is not free to change.
     */
    public function updateCommissionStatus(AffiliateCommission $commission, $status)
    {
        if (! in_array($status, self::assignableCommissionStatuses(), true)) {
            throw new \InvalidArgumentException('Invalid commission status: '.$status);
        }

        //Un-approving below what has already been paid would owe money back.
        if ($commission->status === 'approved' && $status !== 'approved') {
            $remaining = $this->approvedTotal($commission->affiliate_user_id) - (float) $commission->commission_amount;

            if (round($remaining, session('business.currency_precision', 2)) < $this->paidTotal($commission->affiliate_user_id)) {
                throw new \RuntimeException(__('superadmin::lang.commission_already_paid_out'));
            }
        }

        $commission->status = $status;
        $commission->save();

        return $commission;
    }

    /**
     * Write the commission row.
     *
     * @param  float  $sale_amount
     * @param  float  $percentage
     * @return \Modules\Superadmin\Entities\AffiliateCommission|null
     */
    protected function storeCommission(Subscription $subscription, AffiliateReferral $referral, $sale_amount, $percentage)
    {
        try {
            $commission = AffiliateCommission::create([
                'affiliate_user_id' => $referral->affiliate_user_id,
                'referred_business_id' => $referral->referred_business_id,
                'package_id' => $subscription->package_id,
                'subscription_id' => $subscription->id,
                'transaction_id' => $subscription->payment_transaction_id,
                'sale_amount' => $sale_amount,
                //Both rates are stored so a later settings change cannot
                //silently restate what an affiliate already earned.
                'commission_percentage' => $percentage,
                'commission_amount' => $this->calculate($sale_amount, $percentage),
                'commission_type' => $this->aff_commission_type, //AFF-RATES
                'status' => 'pending',
            ]);

            return $commission;
        } catch (QueryException $e) {
            //23000: the unique index kept out a second commission for this subscription.
            if ((string) $e->getCode() === '23000') {
                return null;
            }

            throw $e;
        }
    }

    /**
     * Commission owed on an amount.
     *
     * @param  float  $sale_amount
     * @param  float  $percentage
     * @return float
     */
    public function calculate($sale_amount, $percentage)
    {
        //Stored at display precision: a finer amount could never be settled.
        return round($sale_amount * $percentage / 100, session('business.currency_precision', 2));
    }

    /**
     * Commission approved for an affiliate, whether or not it has been paid.
     *
     * @param  int  $affiliate_user_id
     * @return float
     */
    public function approvedTotal($affiliate_user_id)
    {
        return round(
            AffiliateCommission::where('affiliate_user_id', $affiliate_user_id)
                ->where('status', 'approved')
                ->sum('commission_amount'),
            session('business.currency_precision', 2)
        );
    }

    /**
     * Total already paid out to an affiliate.
     *
     * @param  int  $affiliate_user_id
     * @return float
     */
    public function paidTotal($affiliate_user_id)
    {
        return round(
            AffiliatePayout::where('affiliate_user_id', $affiliate_user_id)
                ->where('status', 'paid')
                ->sum('amount'),
            session('business.currency_precision', 2)
        );
    }

    /**
     * Amount an affiliate is still owed.
     *
     * @param  int  $affiliate_user_id
     * @return float
     */
    public function payableAmount($affiliate_user_id)
    {
        //Both sides are already rounded, so the difference lands on the same scale.
        return round(
            $this->approvedTotal($affiliate_user_id) - $this->paidTotal($affiliate_user_id),
            session('business.currency_precision', 2)
        );
    }

    /**
     * Record a payment to an affiliate.
     *
     * @param  int  $affiliate_user_id
     * @param  float|null  $amount  Defaults to everything owed.
     * @param  string|null  $transaction_id
     * @param  int|null  $created_by
     * @return \Modules\Superadmin\Entities\AffiliatePayout
     *
     * @throws \RuntimeException  When the amount is not payable.
     */
    public function pay($affiliate_user_id, $amount = null, $transaction_id = null, $created_by = null)
    {
        DB::beginTransaction();

        try {
            $owed = $this->payableAmount($affiliate_user_id);

            if ($owed <= 0) {
                throw new \RuntimeException(__('superadmin::lang.no_payable_commission'));
            }

            $amount = $amount === null ? $owed : round((float) $amount, session('business.currency_precision', 2));

            if ($amount <= 0) {
                throw new \RuntimeException(__('superadmin::lang.payout_amount_required'));
            }

            //Paying more than is owed would create a balance that cannot exist.
            if ($amount > $owed) {
                throw new \RuntimeException(__('superadmin::lang.payout_exceeds_owed', [
                    'owed' => $this->formatAmount($owed),
                ]));
            }

            $minimum = $this->minPayout();

            if ($amount < $minimum) {
                throw new \RuntimeException(__('superadmin::lang.below_minimum_payout', [
                    'minimum' => $this->formatAmount($minimum),
                ]));
            }

            $payout = AffiliatePayout::create([
                'affiliate_user_id' => $affiliate_user_id,
                'amount' => $amount,
                'transaction_id' => $transaction_id,
                'status' => 'paid',
                'paid_at' => now(),
                'created_by' => $created_by,
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            throw $e;
        }

        //Told only once the payment is committed, not before.
        if (! empty($payout->affiliateUser->email) && $this->IsMailConfigured()) {
            Notification::route('mail', $payout->affiliateUser->email)
                ->notify(new AffiliatePayoutPaidNotification($payout));
        }

        return $payout;
    }

    /**
     * Render an amount with the system currency, for use in email bodies.
     *
     * @param  float  $amount
     * @return string
     */
    public function formatAmount($amount)
    {
        $system_currency = System::getCurrency();

        return $system_currency->symbol.number_format($amount, 2, $system_currency->decimal_separator, $system_currency->thousand_separator);
    }
}
