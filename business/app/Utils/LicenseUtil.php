<?php

namespace App\Utils;

use Carbon\Carbon;
use App\Business;
use App\System;
use Illuminate\Support\Facades\Cache;

class LicenseUtil
{
    /**
     * Check comprehensive license validity for a user and business.
     *
     * @param  \App\User|null  $user
     * @param  \App\Business|null  $business
     * @return array
     */
    public static function checkLicense($user = null, $business = null)
    {
        if (empty($user)) {
            $user = auth()->user();
        }

        if (empty($user)) {
            return [
                'valid' => false,
                'status' => 'unauthenticated',
                'message' => 'User not authenticated.',
                'expires_at' => null
            ];
        }

        // Superadmin bypasses normal business licensing
        if (method_exists($user, 'can') && $user->can('superadmin')) {
            return [
                'valid' => true,
                'status' => 'superadmin',
                'message' => 'Superadmin access',
                'expires_at' => null
            ];
        }

        $now = Carbon::now();

        // 1. Anti-Tamper: Detect system clock rollbacks
        $clockCheck = self::verifySystemClock($now);
        if (!$clockCheck['valid']) {
            return $clockCheck;
        }

        // Resolve business
        if (empty($business)) {
            $business = $user->business ?? Business::find($user->business_id);
        }

        $allEndDates = [];

        // 2. User-level Offline POS License Checks
        if (isset($user->allow_offline_pos) && $user->allow_offline_pos == 0) {
            return [
                'valid' => false,
                'status' => 'offline_disabled',
                'message' => 'Offline POS access is disabled for this user. Please contact administrator.',
                'expires_at' => null
            ];
        }

        if (!empty($user->license_start_date)) {
            $userStart = Carbon::parse($user->license_start_date);
            if ($now->lt($userStart)) {
                return [
                    'valid' => false,
                    'status' => 'not_started',
                    'message' => 'Your POS user license starts on ' . $userStart->format('Y-m-d H:i') . '.',
                    'expires_at' => null
                ];
            }
        }

        if (!empty($user->license_end_date)) {
            $userEnd = Carbon::parse($user->license_end_date);
            if ($now->gte($userEnd)) {
                return [
                    'valid' => false,
                    'status' => 'expired',
                    'message' => 'Your POS user license expired on ' . $userEnd->format('Y-m-d H:i') . '. Please contact administrator to renew.',
                    'expires_at' => $userEnd->toIso8601String()
                ];
            }
            $allEndDates[] = $userEnd;
        }

        // 3. Business / Subscription Checks
        if (!empty($business)) {
            if (!$business->is_active) {
                return [
                    'valid' => false,
                    'status' => 'business_inactive',
                    'message' => 'Business account is inactive. Please contact administrator.',
                    'expires_at' => null
                ];
            }

            // Superadmin subscription module check
            if (class_exists('\Modules\Superadmin\Entities\Subscription')) {
                $activeSub = \Modules\Superadmin\Entities\Subscription::active_subscription($business->id);
                if ($activeSub && !empty($activeSub->end_date)) {
                    $subEnd = Carbon::parse($activeSub->end_date);
                    if ($now->gte($subEnd)) {
                        return [
                            'valid' => false,
                            'status' => 'expired',
                            'message' => 'Your business subscription expired on ' . $subEnd->format('Y-m-d H:i') . '. Please renew your package.',
                            'expires_at' => $subEnd->toIso8601String()
                        ];
                    }
                    $allEndDates[] = $subEnd;
                }
            }

            if (!empty($business->license_expires_at)) {
                $bizEnd = Carbon::parse($business->license_expires_at);
                if ($now->gte($bizEnd)) {
                    return [
                        'valid' => false,
                        'status' => 'expired',
                        'message' => 'Your POS license expired on ' . $bizEnd->format('Y-m-d H:i') . '. Please contact administrator to renew.',
                        'expires_at' => $bizEnd->toIso8601String()
                    ];
                }
                $allEndDates[] = $bizEnd;
            }
        }

        // Determine earliest expiry
        $earliestExpiry = null;
        if (!empty($allEndDates)) {
            usort($allEndDates, function ($a, $b) {
                return $a->timestamp <=> $b->timestamp;
            });
            $earliestExpiry = $allEndDates[0]->toIso8601String();
        }

        return [
            'valid' => true,
            'status' => 'active',
            'message' => 'License is active',
            'expires_at' => $earliestExpiry
        ];
    }

    /**
     * Prevent offline clock tampering / backwards date shifts.
     */
    protected static function verifySystemClock(Carbon $now)
    {
        try {
            $lastKnown = Cache::get('pos_last_known_clock');
            if (empty($lastKnown) && class_exists('\App\System')) {
                $lastKnown = System::getProperty('pos_last_known_clock');
            }

            if (!empty($lastKnown)) {
                $lastCarbon = Carbon::parse($lastKnown);
                // Allow up to 10 minutes tolerance for minor clock drift / NTP adjustments
                if ($now->lt($lastCarbon->subMinutes(10))) {
                    return [
                        'valid' => false,
                        'status' => 'clock_tampered',
                        'message' => 'System clock rollback detected. POS access is locked until system clock is corrected.',
                        'expires_at' => null
                    ];
                }
            }

            // Update timestamp
            $nowStr = $now->toDateTimeString();
            Cache::put('pos_last_known_clock', $nowStr, 86400 * 30);
            if (class_exists('\App\System')) {
                System::setProperties(['pos_last_known_clock' => $nowStr]);
            }
        } catch (\Exception $e) {
            // In case of DB transient error, do not fail benign checks
        }

        return ['valid' => true];
    }
}
