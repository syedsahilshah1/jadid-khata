<?php

namespace Modules\Superadmin\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Superadmin\Utils\AffiliateUtil;

class AffiliateSettingsController extends Controller
{
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
     * Show the form for editing the affiliate settings.
     *
     * @return Response
     */
    public function edit()
    {
        if (! auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        $settings = $this->affiliateUtil->all();

        //Used by the affiliate list card rendered below the settings form.
        $affiliate_statuses = \Modules\Superadmin\Entities\Affiliate::statuses();

        return view('superadmin::affiliate.settings')
            ->with(compact('settings', 'affiliate_statuses'));
    }

    /**
     * Update the affiliate settings.
     *
     * @return Response
     */
    public function update(Request $request)
    {
        if (! auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            //Validated by jQuery; AffiliateUtil::update() casts the values.
            $input = $request->only(['commission_percentage', 'renewal_commission_percentage', 'team_commission_amount', 'cookie_days', 'min_payout', 'terms']); //AFF-RATES
            $input['enabled'] = ! empty($request->input('enabled'));
            $input['commission_recurring'] = ! empty($request->input('commission_recurring'));

            $this->affiliateUtil->update($input);

            $output = ['success' => 1,
                'msg' => __('lang_v1.success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return redirect()
            ->action([\Modules\Superadmin\Http\Controllers\AffiliateSettingsController::class, 'edit'])
            ->with('status', $output);
    }
}
