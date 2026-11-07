<?php

namespace Modules\Superadmin\Entities;

use Illuminate\Database\Eloquent\Model;

class AffiliateClick extends Model
{
    protected $guarded = ['id'];

    /**
     * Get the affiliate whose referral link was clicked.
     */
    public function affiliate()
    {
        return $this->belongsTo(Affiliate::class, 'affiliate_id');
    }
}
