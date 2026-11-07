<?php

namespace App\Utils;

use App\Business;
use App\Product;
use App\Variation;

class SkuGuard
{
    protected static $seen = [];

    public static function allowed($business_id)
    {
        $business = Business::find($business_id);

        return ! empty($business) && ! empty($business->allow_duplicate_sku);
    }

    protected static function normalized($sku)
    {
        $sku = trim((string) $sku);
        if ($sku !== '' && ctype_digit($sku) && strlen($sku) >= 8) {
            $n = ltrim($sku, '0');

            return $n === '' ? null : $n;
        }

        return null;
    }

    public static function exists($business_id, $sku, $exclude_product_id = null, $exclude_variation_id = null)
    {
        $sku = trim((string) $sku);
        if ($sku === '') {
            return false;
        }
        $norm = self::normalized($sku);

        $pq = Product::where('business_id', $business_id)
            ->where(function ($q) use ($sku, $norm) {
                $q->where('sku', $sku);
                if ($norm !== null) {
                    $q->orWhereRaw("TRIM(LEADING '0' FROM sku) = ?", [$norm]);
                }
            });
        if (! empty($exclude_product_id)) {
            $pq->where('id', '!=', $exclude_product_id);
        }
        if ($pq->exists()) {
            return true;
        }

        $vq = Variation::join('products', 'variations.product_id', '=', 'products.id')
            ->where('products.business_id', $business_id)
            ->where(function ($q) use ($sku, $norm) {
                $q->where('variations.sub_sku', $sku);
                if ($norm !== null) {
                    $q->orWhereRaw("TRIM(LEADING '0' FROM variations.sub_sku) = ?", [$norm]);
                }
            });
        if (! empty($exclude_product_id)) {
            $vq->where('variations.product_id', '!=', $exclude_product_id);
        }
        if (! empty($exclude_variation_id)) {
            $vq->where('variations.id', '!=', $exclude_variation_id);
        }

        return $vq->exists();
    }

    // true if the same SKU already appeared earlier in this request (e.g. twice in one import file)
    public static function seenBefore($business_id, $sku)
    {
        $sku = trim((string) $sku);
        if ($sku === '') {
            return false;
        }
        $key = $business_id.'|'.(self::normalized($sku) ?? $sku);
        if (isset(self::$seen[$key])) {
            return true;
        }
        self::$seen[$key] = true;

        return false;
    }
}
