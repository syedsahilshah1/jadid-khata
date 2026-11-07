<?php

namespace Modules\Superadmin\Utils;

use App\Business;
use App\Utils\Util;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Modules\Superadmin\Entities\Affiliate;
use Modules\Superadmin\Entities\AffiliateClick;
use Modules\Superadmin\Entities\AffiliateReferral;

/**
 * The referral link cookie, and everything that hangs off it.
 *
 * The chain is: link click -> cookie -> business registration -> referral row.
 *
 * Kept apart from AffiliateUtil because this is the one part of the affiliate
 * system that runs on every page request, through the CaptureAffiliateReferral
 * middleware, rather than only where an affiliate is being administered.
 */
class AffiliateReferralUtil extends Util
{
    /**
     * Separates the affiliate code from the cookie's expiry timestamp.
     */
    const COOKIE_SEPARATOR = '|';

    /**
     * @var \Modules\Superadmin\Utils\AffiliateUtil
     */
    protected $affiliateUtil;

    /**
     * @return void
     */
    public function __construct(AffiliateUtil $affiliateUtil)
    {
        $this->affiliateUtil = $affiliateUtil;
    }

    /**
     * Handle a visit carrying an affiliate code: record the click and drop the
     * referral cookie.
     *
     * @param  string  $code
     * @return \Modules\Superadmin\Entities\Affiliate|null  Null when the code
     *                                                      is not usable.
     */
    public function track($code, Request $request)
    {
        if (! $this->affiliateUtil->isEnabled()) {
            return null;
        }

        //Only an approved affiliate may accrue clicks and referrals.
        $affiliate = $this->affiliateUtil->findApprovedByCode($code);

        if (empty($affiliate)) {
            return null;
        }

        /*
         * Re-visiting with the same code already in hand is the same visitor,
         * not a new click. Without this, refreshing the link inflates the count.
         */
        if ($this->codeFromCookie($request) !== $affiliate->affiliate_code) {
            AffiliateClick::create(['affiliate_id' => $affiliate->id]);
        }

        $this->queueCookie($affiliate);

        return $affiliate;
    }

    /**
     * Attribute a newly registered business to the affiliate that referred it.
     *
     * @return \Modules\Superadmin\Entities\AffiliateReferral|null
     */
    public function attributeRegistration(Business $business, Request $request)
    {
        if (! $this->affiliateUtil->isEnabled()) {
            return null;
        }

        $code = $this->codeFromCookie($request);

        $affiliate = $this->affiliateUtil->findApprovedByCode($code);

        if (empty($affiliate)) {
            return null;
        }

        $expiry = $this->expiryFromCookie($request);

        /*
         * The registration has to fall inside the referral window. A browser
         * drops the cookie on its own once it expires, so this only bites when
         * the cookie is replayed or edited by hand; without it the window would
         * be enforced by the visitor rather than by us.
         *
         * A cookie carrying no expiry is honoured, so nothing set before this
         * check existed is thrown away.
         */
        if (! empty($expiry) && $expiry->isPast()) {
            return null;
        }

        /*
         * referred_business_id is unique, so this is belt and braces; checking
         * first keeps a duplicate from raising an exception mid registration.
         */
        if (AffiliateReferral::where('referred_business_id', $business->id)->exists()) {
            return null;
        }

        return AffiliateReferral::create([
            'affiliate_user_id' => $affiliate->user_id,
            'referred_business_id' => $business->id,
            'affiliate_code' => $affiliate->affiliate_code,
            'registered_at' => now(),
            'cookie_expiry' => $expiry,
        ]);
    }

    /**
     * Queue the referral cookie on the outgoing response.
     *
     * The expiry is carried inside the value as well as on the cookie itself,
     * so the registration can record exactly when the attribution window
     * closes rather than re-deriving it from the current settings.
     *
     * @return void
     */
    protected function queueCookie(Affiliate $affiliate)
    {
        $minutes = $this->affiliateUtil->cookieDays() * 24 * 60;
        $expires_at = now()->addMinutes($minutes);

        $value = $affiliate->affiliate_code.self::COOKIE_SEPARATOR.$expires_at->getTimestamp();

        Cookie::queue(Cookie::make($this->affiliateUtil->cookieName(), $value, $minutes));
    }

    /**
     * Affiliate code held in the referral cookie, if any.
     *
     * @return string|null
     */
    public function codeFromCookie(Request $request)
    {
        $value = $request->cookie($this->affiliateUtil->cookieName());

        if (empty($value)) {
            return null;
        }

        $code = explode(self::COOKIE_SEPARATOR, $value)[0];

        return $code === '' ? null : $code;
    }

    /**
     * Expiry held in the referral cookie, if it carries one.
     *
     * @return \Carbon\Carbon|null
     */
    protected function expiryFromCookie(Request $request)
    {
        $value = (string) $request->cookie($this->affiliateUtil->cookieName());

        $parts = explode(self::COOKIE_SEPARATOR, $value);

        if (count($parts) < 2 || ! ctype_digit($parts[1])) {
            return null;
        }

        return \Carbon::createFromTimestamp((int) $parts[1]);
    }
}
