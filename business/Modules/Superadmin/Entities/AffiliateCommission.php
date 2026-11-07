<?php

namespace Modules\Superadmin\Entities;

use Illuminate\Database\Eloquent\Model;

class AffiliateCommission extends Model
{
    protected $guarded = ['id'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'sale_amount' => 'float',
        'commission_percentage' => 'float',
        'commission_amount' => 'float',
    ];


    /**
     * Get the user owning the affiliate account that earned the commission.
     */
    public function affiliateUser()
    {
        return $this->belongsTo(\App\User::class, 'affiliate_user_id');
    }

    /**
     * Get the referred business whose purchase generated the commission.
     */
    public function business()
    {
        return $this->belongsTo(\App\Business::class, 'referred_business_id');
    }

    /**
     * Get the package that was purchased.
     */
    public function package()
    {
        return $this->belongsTo(Package::class, 'package_id')->withTrashed();
    }



    /**
     * Returns the list of commission statuses, keyed by the value stored in db.
     *
     * @return array
     */
    public static function statuses()
    {
        return [
            'pending' => __('superadmin::lang.affiliate_pending'),
            'approved' => __('superadmin::lang.affiliate_approved'),
            'cancelled' => __('superadmin::lang.affiliate_cancelled'),
        ];
    }
}
