<?php

namespace Modules\Superadmin\Http\Middleware;

use Closure;
use Modules\Superadmin\Utils\AffiliateReferralUtil;

/**
 * Captures a ?ref=CODE query parameter on any web page.
 *
 * The tidy /ref/{code} link is the one affiliates are given, but the code is
 * also accepted straight on the registration url, which is the shape people
 * expect and the one most likely to be pasted by hand.
 *
 * The work is skipped entirely unless the parameter is actually present, so
 * ordinary requests pay nothing more than an array lookup.
 */
class CaptureAffiliateReferral
{
    /**
     * @var \Modules\Superadmin\Utils\AffiliateReferralUtil
     */
    protected $affiliateReferralUtil;

    /**
     * @return void
     */
    public function __construct(AffiliateReferralUtil $affiliateReferralUtil)
    {
        $this->affiliateReferralUtil = $affiliateReferralUtil;
    }

    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $code = $request->query('ref');

        if (! empty($code) && is_string($code)) {
            try {
                $this->affiliateReferralUtil->track($code, $request);
            } catch (\Exception $e) {
                //Tracking must never be able to break the page being visited.
                \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());
            }
        }

        return $next($request);
    }
}
