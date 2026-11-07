<?php

namespace App\Utils;

use App\User;
use App\Business;
use App\BusinessLocation;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class CloudAuthUtil
{
    protected static $cloudUrl = 'https://business.jadidkhata.com';

    /**
     * Attempt to verify credentials against live cloud server,
     * pull user details, and sync to local database.
     *
     * @param string $username
     * @param string $password
     * @return array ['success' => bool, 'user' => User|null, 'message' => string]
     */
    public static function attemptCloudLoginAndSync($username, $password)
    {
        if (empty($username) || empty($password)) {
            return ['success' => false, 'user' => null, 'message' => 'Username and password required.'];
        }

        // 1. Connectivity check
        try {
            $ping = Http::timeout(4)->get(self::$cloudUrl . '/api/sync/ping');
            if (!$ping->successful()) {
                return ['success' => false, 'user' => null, 'message' => 'Cloud server unreachable.'];
            }
        } catch (\Exception $e) {
            return ['success' => false, 'user' => null, 'message' => 'Offline: No connection to cloud server.'];
        }

        // 2. Try Passport OAuth Password Grant across known clients
        $clientCandidates = [
            ['id' => 2, 'secret' => '2WTVlYbOCdk4WbIeGu9B1sEtMGe5DjLoNE3faCCN'],
            ['id' => 48, 'secret' => 'LlGSF3uElw4uzunYlBlgPnii1FOy1oPeSu5xdWiK'],
            ['id' => 6, 'secret' => 'FZV39yoblqvNfPsf90PaGkCvXZ9sLUHU4XFxclgW'],
            ['id' => 8, 'secret' => '40YJrZ0MvGTp3GcBaarb4lv88LLWPMEWuEujo2gl'],
        ];

        foreach ($clientCandidates as $client) {
            try {
                $response = Http::timeout(8)
                    ->asForm()
                    ->acceptJson()
                    ->post(self::$cloudUrl . '/oauth/token', [
                        'grant_type' => 'password',
                        'client_id' => $client['id'],
                        'client_secret' => $client['secret'],
                        'username' => $username,
                        'password' => $password,
                        'scope' => '',
                    ]);

                if ($response->successful()) {
                    $json = $response->json();
                    if (!empty($json['access_token'])) {
                        $oauthSync = self::syncUserFromConnectorApi($json['access_token'], $username, $password);
                        if ($oauthSync['success']) {
                            return $oauthSync;
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::warning('OAuth attempt error for client ' . $client['id'] . ': ' . $e->getMessage());
            }
        }

        // 3. Fallback: Web login simulation against cloud login form
        return self::attemptWebLoginAndSync($username, $password);
    }

    /**
     * Sync user using OAuth token and UltimatePOS Connector API.
     */
    protected static function syncUserFromConnectorApi($accessToken, $username, $password)
    {
        try {
            $userRes = Http::timeout(8)
                ->withToken($accessToken)
                ->acceptJson()
                ->get(self::$cloudUrl . '/connector/api/user/loggedin');

            if (!$userRes->successful()) {
                return ['success' => false, 'user' => null, 'message' => 'Failed to fetch cloud user profile.'];
            }

            $userData = $userRes->json('data') ?? $userRes->json();
            if (empty($userData) || empty($userData['business_id'])) {
                return ['success' => false, 'user' => null, 'message' => 'Invalid cloud user profile payload.'];
            }

            // Locations
            $locations = [];
            try {
                $locRes = Http::timeout(8)
                    ->withToken($accessToken)
                    ->acceptJson()
                    ->get(self::$cloudUrl . '/connector/api/business-location');
                if ($locRes->successful()) {
                    $locations = $locRes->json('data') ?? [];
                }
            } catch (\Exception $e) {}

            return self::createOrUpdateLocalUserAndBusiness($userData, $password, $locations);
        } catch (\Exception $e) {
            Log::error('Connector API user sync error: ' . $e->getMessage());
            return ['success' => false, 'user' => null, 'message' => $e->getMessage()];
        }
    }

    /**
     * Fallback web session authentication against live login form.
     */
    protected static function attemptWebLoginAndSync($username, $password)
    {
        $cookieFile = storage_path('framework/cache/cloud_login_' . md5($username) . '.txt');
        if (file_exists($cookieFile)) {
            @unlink($cookieFile);
        }

        try {
            // 1. GET login page for CSRF token & initial session cookie
            $ch = curl_init(self::$cloudUrl . '/login');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
            curl_setopt($ch, CURLOPT_TIMEOUT, 6);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $loginHtml = curl_exec($ch);
            curl_close($ch);

            if (!$loginHtml) {
                return ['success' => false, 'user' => null, 'message' => 'Could not connect to cloud login page.'];
            }

            preg_match('/name="_token"\s+value="([^"]+)"/', $loginHtml, $tokenMatches);
            $csrfToken = $tokenMatches[1] ?? '';
            if (empty($csrfToken)) {
                return ['success' => false, 'user' => null, 'message' => 'Could not read cloud CSRF token.'];
            }

            // 2. POST credentials
            $ch = curl_init(self::$cloudUrl . '/login');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
            curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 8);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
                '_token' => $csrfToken,
                'username' => $username,
                'password' => $password,
            ]));
            $loginRes = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            preg_match('/[Ll]ocation:\s*([^\r\n]+)/', $loginRes, $locMatches);
            $redirectUrl = trim($locMatches[1] ?? '');

            // On invalid login, Laravel redirects back to /login
            if ($httpCode !== 302 || empty($redirectUrl) || strpos($redirectUrl, '/login') !== false) {
                @unlink($cookieFile);
                return ['success' => false, 'user' => null, 'message' => 'Cloud credentials do not match records.'];
            }

            // 3. User is valid on cloud! Fetch profile details
            $ch = curl_init(self::$cloudUrl . '/user/profile');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
            curl_setopt($ch, CURLOPT_TIMEOUT, 8);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $profileHtml = curl_exec($ch);
            curl_close($ch);

            // Fetch /home to get business name & info
            $ch = curl_init(self::$cloudUrl . '/home');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
            curl_setopt($ch, CURLOPT_TIMEOUT, 8);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $homeHtml = curl_exec($ch);
            curl_close($ch);

            @unlink($cookieFile);

            // Parse user info
            preg_match('/name="first_name"[^>]*value="([^"]*)"/', $profileHtml, $fn);
            preg_match('/name="last_name"[^>]*value="([^"]*)"/', $profileHtml, $ln);
            preg_match('/name="email"[^>]*value="([^"]*)"/', $profileHtml, $em);

            $firstName = !empty($fn[1]) ? $fn[1] : $username;
            $lastName = $ln[1] ?? '';
            $email = !empty($em[1]) ? $em[1] : ($username . '@jadidkhata.com');

            // Find or determine business
            // Check if user already exists locally
            $existingUser = User::where('username', $username)->first();
            $businessId = $existingUser ? $existingUser->business_id : null;

            if (!$businessId) {
                // Try to extract business name from home HTML
                preg_match('/<title>\s*(.*?)\s*-\s*Home/i', $homeHtml, $bm);
                $businessName = !empty($bm[1]) ? trim($bm[1]) : ($username . ' Business');

                $business = Business::where('name', $businessName)->first();
                if (!$business) {
                    $business = Business::create([
                        'name' => $businessName,
                        'currency_id' => 1,
                        'start_date' => now()->toDateString(),
                        'tax_number_1' => '',
                        'tax_label_1' => '',
                        'default_profit_percent' => 25.00,
                        'owner_id' => 1,
                        'time_zone' => config('app.timezone', 'Asia/Kolkata'),
                        'is_active' => 1,
                    ]);
                }
                $businessId = $business->id;
            }

            $userData = [
                'username' => $username,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
                'business_id' => $businessId,
            ];

            return self::createOrUpdateLocalUserAndBusiness($userData, $password, []);
        } catch (\Exception $e) {
            @unlink($cookieFile);
            Log::error('Web session cloud auth error: ' . $e->getMessage());
            return ['success' => false, 'user' => null, 'message' => $e->getMessage()];
        }
    }

    /**
     * Create or update local user and ensure business / locations / permissions exist.
     */
    protected static function createOrUpdateLocalUserAndBusiness($userData, $password, $locations = [])
    {
        return DB::transaction(function () use ($userData, $password, $locations) {
            $businessId = $userData['business_id'];

            // Ensure business exists locally
            $business = Business::find($businessId);
            if (!$business) {
                $businessName = $userData['business_name'] ?? ('Business ' . $businessId);
                $business = new Business();
                $business->id = $businessId;
                $business->name = $businessName;
                $business->currency_id = 1;
                $business->start_date = now()->toDateString();
                $business->default_profit_percent = 25.00;
                $business->owner_id = 1;
                $business->time_zone = config('app.timezone', 'Asia/Kolkata');
                $business->is_active = 1;
                $business->save();
            }

            // Ensure default invoice scheme exists for business
            $invoiceScheme = \App\InvoiceScheme::where('business_id', $businessId)->first();
            if (!$invoiceScheme) {
                $invoiceScheme = \App\InvoiceScheme::create([
                    'business_id' => $businessId,
                    'name' => 'Default',
                    'scheme_type' => 'blank',
                    'prefix' => '',
                    'start_number' => 1,
                    'total_digits' => 4,
                    'is_default' => 1,
                ]);
            }

            // Ensure default invoice layout exists for business
            $invoiceLayout = \App\InvoiceLayout::where('business_id', $businessId)->first();
            if (!$invoiceLayout) {
                $invoiceLayout = \App\InvoiceLayout::create([
                    'business_id' => $businessId,
                    'name' => 'Default',
                    'invoice_no_prefix' => 'Invoice No.',
                    'invoice_heading' => 'Invoice',
                    'sub_total_label' => 'Subtotal',
                    'discount_label' => 'Discount',
                    'tax_label' => 'Tax',
                    'total_label' => 'Total',
                    'highlight_color' => '#000000',
                    'is_default' => 1,
                ]);
            }

            // Ensure default location exists
            $locationCount = BusinessLocation::where('business_id', $businessId)->count();
            if ($locationCount === 0) {
                if (!empty($locations)) {
                    foreach ($locations as $loc) {
                        BusinessLocation::create([
                            'id' => !empty($loc['id']) && !BusinessLocation::find($loc['id']) ? $loc['id'] : null,
                            'business_id' => $businessId,
                            'name' => $loc['name'] ?? 'Main Location',
                            'city' => !empty($loc['city']) ? $loc['city'] : 'City',
                            'state' => !empty($loc['state']) ? $loc['state'] : 'State',
                            'country' => !empty($loc['country']) ? $loc['country'] : 'Country',
                            'zip_code' => !empty($loc['zip_code']) ? substr($loc['zip_code'], 0, 7) : '00000',
                            'invoice_scheme_id' => $invoiceScheme->id,
                            'invoice_layout_id' => $invoiceLayout->id,
                            'is_active' => 1,
                        ]);
                    }
                } else {
                    BusinessLocation::create([
                        'business_id' => $businessId,
                        'name' => $business->name . ' Store',
                        'city' => 'City',
                        'state' => 'State',
                        'country' => 'Country',
                        'zip_code' => '00000',
                        'invoice_scheme_id' => $invoiceScheme->id,
                        'invoice_layout_id' => $invoiceLayout->id,
                        'is_active' => 1,
                    ]);
                }
            }

            // Create or update User
            $user = User::where('username', $userData['username'])->first();
            if (!$user && !empty($userData['email'])) {
                $user = User::where('email', $userData['email'])->first();
            }

            $userFields = [
                'username' => $userData['username'],
                'first_name' => $userData['first_name'] ?? $userData['username'],
                'last_name' => $userData['last_name'] ?? '',
                'email' => $userData['email'] ?? ($userData['username'] . '@jadidkhata.com'),
                'password' => Hash::make($password),
                'business_id' => $businessId,
                'status' => 'active',
                'allow_offline_pos' => 1,
                'license_start_date' => !empty($userData['license_start_date']) ? $userData['license_start_date'] : now()->toDateString(),
                'license_end_date' => !empty($userData['license_end_date']) ? $userData['license_end_date'] : (!empty($userData['subscription_end']) ? $userData['subscription_end'] : now()->addDays(30)->toDateString()),
                'offline_license_status' => 'active',
            ];

            if ($user) {
                $user->update($userFields);
            } else {
                if (!empty($userData['id']) && !User::find($userData['id'])) {
                    $userFields['id'] = $userData['id'];
                }
                $user = User::create($userFields);
            }

            // Ensure user has admin role
            $roleName = 'Admin#' . $businessId;
            $role = Role::where('name', $roleName)->where('business_id', $businessId)->first();
            if (!$role) {
                $role = Role::create([
                    'name' => $roleName,
                    'business_id' => $businessId,
                    'guard_name' => 'web',
                    'is_default' => 1
                ]);
            }
            if ($role) {
                $user->syncRoles([$role->id]);
            }

            // Ensure Walk-in customer exists
            \App\Contact::firstOrCreate(
                ['business_id' => $businessId, 'type' => 'customer', 'is_default' => 1],
                [
                    'name' => 'Walk-In Customer',
                    'created_by' => $user->id,
                    'credit_limit' => 0,
                ]
            );

            Log::info("Successfully synced cloud user '{$user->username}' to local database.");

            return [
                'success' => true,
                'user' => $user,
                'message' => 'User synced from cloud successfully.',
            ];
        });
    }
}

