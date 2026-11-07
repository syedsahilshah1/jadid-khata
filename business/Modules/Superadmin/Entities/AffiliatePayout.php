<?php

namespace Modules\Superadmin\Entities;

use Illuminate\Database\Eloquent\Model;

class AffiliatePayout extends Model
{
    protected $guarded = ['id'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'amount' => 'float',
        'paid_at' => 'datetime',
    ];

    /**
     * Get the user owning the affiliate account being paid.
     */
    public function affiliateUser()
    {
        return $this->belongsTo(\App\User::class, 'affiliate_user_id');
    }

    /**
     * Returns the list of payout statuses, keyed by the value stored in db.
     *
     * @return array
     */
    public static function statuses()
    {
        return [
            'paid' => __('superadmin::lang.affiliate_paid'),
        ];
    }
}
