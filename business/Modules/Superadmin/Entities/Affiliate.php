<?php

namespace Modules\Superadmin\Entities;

use Illuminate\Database\Eloquent\Model;

class Affiliate extends Model
{
    protected $guarded = ['id'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'tc_accepted_at' => 'datetime',
    ];

    /**
     * Scope a query to only include approved affiliates.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Get the user owning the affiliate account.
     */
    public function user()
    {
        return $this->belongsTo(\App\User::class, 'user_id');
    }

    /**
     * Get the clicks recorded on the affiliate's referral link.
     */
    public function clicks()
    {
        return $this->hasMany(AffiliateClick::class, 'affiliate_id');
    }

    /**
     * Get the businesses registered through the affiliate's referral link.
     */
    public function referrals()
    {
        return $this->hasMany(AffiliateReferral::class, 'affiliate_user_id', 'user_id');
    }

    /**
     * Get the commissions earned by the affiliate.
     */
    public function commissions()
    {
        return $this->hasMany(AffiliateCommission::class, 'affiliate_user_id', 'user_id');
    }

    /**
     * Get the payouts made to the affiliate.
     */
    public function payouts()
    {
        return $this->hasMany(AffiliatePayout::class, 'affiliate_user_id', 'user_id');
    }

    /**
     * Returns the list of affiliate statuses, keyed by the value stored in db.
     *
     * @return array
     */
    public static function statuses()
    {
        return [
            'pending' => __('superadmin::lang.affiliate_pending'),
            'approved' => __('superadmin::lang.affiliate_approved'),
            'rejected' => __('superadmin::lang.affiliate_rejected'),
            'suspended' => __('superadmin::lang.affiliate_suspended'),
        ];
    }

    /**
     * Only an approved affiliate may accrue clicks, referrals and commissions.
     *
     * @return bool
     */
    public function isApproved()
    {
        return $this->status == 'approved';
    }

    /**
     * Build the public referral link for an affiliate code.
     *
     * Kept here so the affiliate dashboard, the superadmin detail page and
     * AffiliateUtil all produce byte for byte the same url.
     *
     * @param  string  $code
     * @return string
     */
    public static function linkForCode($code)
    {
        return route('business.getRegister', ['ref' => $code]);
    }

    /**
     * Get the public referral link for the affiliate.
     *
     * @return string
     */
    public function getReferralLinkAttribute()
    {
        return static::linkForCode($this->affiliate_code);
    }
}
