<?php

namespace Modules\ProductCatalogue\Http\Controllers;

use App\Business;
use App\BusinessLocation;
use App\Category;
use App\Contact;
use App\Discount;
use App\Product;
use App\SellingPriceGroup;
use App\User;
use App\Variation;
use App\Utils\BusinessUtil;
use App\Utils\ContactUtil;
use App\Utils\ModuleUtil;
use App\Utils\ProductUtil;
use App\Utils\TransactionUtil;
use App\Utils\Util;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;


class ProductCatalogueController extends Controller
{
    /**
     * All Utils instance.
     */
    protected $productUtil;

    protected $moduleUtil;

    protected $transactionUtil;

    protected $businessUtil;

    protected $contactUtil;

    protected $commonUtil;

    /**
     * Constructor
     *
     * @param  ProductUtils  $product
     * @return void
     */
    public function __construct(
        ProductUtil $productUtil,
        ModuleUtil $moduleUtil,
        TransactionUtil $transactionUtil,
        BusinessUtil $businessUtil,
        ContactUtil $contactUtil,
        Util $commonUtil
    )
    {
        $this->productUtil = $productUtil;
        $this->moduleUtil = $moduleUtil;
        $this->transactionUtil = $transactionUtil;
        $this->businessUtil = $businessUtil;
        $this->contactUtil = $contactUtil;
        $this->commonUtil = $commonUtil;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index($business_id, $location_id)
    {
        $business = Business::with(['currency'])->findOrFail($business_id);

        $settings = json_decode($business->productcatalogue_settings, true);

        $is_show = $settings['is_show'] ?? 1;

        $enable_whatsapp_ordering = $settings['enable_whatsapp_ordering'] ?? 0;

        $order_receiving_whatsapp_number = $settings['order_receiving_whatsapp_number'] ?? '';
        $catalogue_banners = collect($settings['catalogue_banners'] ?? [])
            ->filter(function ($banner) {
                return ! empty($banner['image']);
            })
            ->values()
            ->all();
        

        $products = Product::where('business_id', $business_id)
                ->whereHas('product_locations', function ($q) use ($location_id) {
                    $q->where('product_locations.location_id', $location_id);
                })
                ->ProductForSales()
                ->with(['variations', 'variations.product_variation', 'category']);
                if($is_show == 0){
                    $products = $products->havingRaw('
                    (SELECT CASE WHEN enable_stock = 0 THEN 1 
                        ELSE SUM(variation_location_details.qty_available) END
                        FROM variation_location_details 
                        WHERE variation_location_details.product_id = products.id) > 0');
                }

        $products = $products->select('products.*', DB::raw('(SELECT SUM(variation_location_details.qty_available) FROM variation_location_details WHERE variation_location_details.product_id = products.id) as stock'))
                            ->get()
                            ->groupBy('category_id');

        $business = Business::with(['currency'])->findOrFail($business_id);
        $business_location = BusinessLocation::where('business_id', $business_id)->findOrFail($location_id);

        $now = \Carbon::now()->toDateTimeString();
        $discounts = Discount::where('business_id', $business_id)
                                ->where('location_id', $location_id)
                                ->where('is_active', 1)
                                ->where('starts_at', '<=', $now)
                                ->where('ends_at', '>=', $now)
                                ->orderBy('priority', 'desc')
                                ->get();
        foreach ($discounts as $key => $value) {
            $discounts[$key]->discount_amount = $this->productUtil->num_f($value->discount_amount, false, $business);
        }

        $categories = Category::forDropdown($business_id, 'product');

        $catalogue_order_url = action([self::class, 'storeOrder'], [$business_id, $location_id]);
        $customer_dashboard_url = url('/contact/order-request');
        $login_url = url('/login');

        // Pre-fill customer details if the visitor is a logged-in customer
        $prefill = ['name' => '', 'email' => '', 'phone' => '', 'address' => ''];
        if (auth()->check() && auth()->user()->user_type === 'user_customer') {
            $loggedContact = \App\Contact::where('business_id', $business_id)
                ->find(auth()->user()->crm_contact_id);
            if ($loggedContact) {
                $prefill = [
                    'name'    => $loggedContact->name ?? '',
                    'email'   => $loggedContact->email ?? '',
                    'phone'   => $loggedContact->mobile ?? '',
                    'address' => $loggedContact->address_line_1 ?? '',
                ];
            }
        }

        return view('productcatalogue::catalogue.index')->with(compact('products', 'business', 'discounts', 'business_location', 'categories', 'enable_whatsapp_ordering', 'order_receiving_whatsapp_number', 'catalogue_order_url', 'customer_dashboard_url', 'login_url', 'catalogue_banners', 'prefill'));
    }

    /**
     * Show the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($business_id, $id)
    {
        $product = Product::with(['brand', 'unit', 'category', 'sub_category', 'product_tax', 'variations', 'variations.product_variation', 'variations.group_prices', 'variations.media', 'product_locations', 'warranty', 'variations.variation_location_details'])->where('business_id', $business_id)
                    ->select('products.*', DB::raw('(SELECT SUM(variation_location_details.qty_available) FROM variation_location_details WHERE variation_location_details.product_id = products.id) as stock'))
                    ->findOrFail($id);
        

        $price_groups = SellingPriceGroup::where('business_id', $product->business_id)->active()->pluck('name', 'id');

        $allowed_group_prices = [];
        foreach ($price_groups as $key => $value) {
            $allowed_group_prices[$key] = $value;
        }

        $group_price_details = [];
        $discounts = [];
        foreach ($product->variations as $variation) {
            foreach ($variation->group_prices as $group_price) {
                $group_price_details[$variation->id][$group_price->price_group_id] = $group_price->price_inc_tax;
            }

            $discounts[$variation->id] = $this->productUtil->getProductDiscount($product, $product->business_id, request()->input('location_id'), false, null, $variation->id);
        }

        $combo_variations = [];
        if ($product->type == 'combo') {
            $combo_variations = $this->productUtil->__getComboProductDetails($product['variations'][0]->combo_variations, $product->business_id);
        }

        $business = Business::findOrFail($business_id);
        
        $settings = json_decode($business->productcatalogue_settings, true);
        $enable_whatsapp_ordering = $settings['enable_whatsapp_ordering'] ?? 0;

        return view('productcatalogue::catalogue.show')->with(compact(
            'product',
            'allowed_group_prices',
            'group_price_details',
            'combo_variations',
            'discounts',
            'business',
            'enable_whatsapp_ordering'
        ));
    }

    public function generateQr()
    {
        $business_id = request()->session()->get('user.business_id');
        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'productcatalogue_module'))) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $business_locations = BusinessLocation::forDropdown($business_id);
        $business = Business::findOrFail($business_id);

        return view('productcatalogue::catalogue.generate_qr')
                    ->with(compact('business_locations', 'business'));
    }
    
/**
 * update product Catalogue Setting
 * @param Request $request
 */

    public function productCatalogueSetting(Request $request){

        $business_id = request()->session()->get('user.business_id');
        
        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'productcatalogue_module'))) {
            abort(403, 'Unauthorized action.');
        }
        
        try {
            $busines = Business::findOrFail($business_id);

            $settings = json_decode($busines->productcatalogue_settings, true);
            if (!is_array($settings)) {
                $settings = [];
            }

            // Update only the fields that are present in the request
            if ($request->has('is_show')) {
                $settings['is_show'] = $request->post('is_show');
            }
            if ($request->has('enable_whatsapp_ordering') || $request->has('order_receiving_whatsapp_number')) {
                $settings['enable_whatsapp_ordering'] = $request->post('enable_whatsapp_ordering', 0);
                $settings['order_receiving_whatsapp_number'] = $request->post('order_receiving_whatsapp_number', '');
            }
            if ($request->has('catalogue_banner_section')) {
                $settings['catalogue_banners'] = $this->storeCatalogueBanners($request, $settings);
            }

            $busines->productcatalogue_settings = json_encode($settings);
  
            $busines->update();
    
            $output = [
                'success' => 1,
                'msg' => __('lang_v1.success'),
            ];
    
            return redirect()
                ->action([\Modules\ProductCatalogue\Http\Controllers\ProductCatalogueController::class, 'generateQr'])
                ->with('status', $output);
                
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];

            return back()->with('status', $output)->withInput();
        }
    }

    public function storeOrder(Request $request, $business_id, $location_id)
    {
        try {
            $business = Business::findOrFail($business_id);
            $location = BusinessLocation::where('business_id', $business_id)->findOrFail($location_id);

            $request->validate([
                'customer_name' => 'required|string|max:255',
                'customer_email' => 'required|email|max:255',
                'customer_phone' => 'required|string|max:50',
                'delivery_address' => 'required|string|max:1000',
                'products' => 'required|array|min:1',
                'products.*.product_id' => 'required|integer',
                'products.*.variation_id' => 'required|integer',
                'products.*.quantity' => 'required|numeric|min:1',
            ]);

            DB::beginTransaction();

            $customer_payload = $this->findOrCreateCatalogueCustomer($business, $request);
            $products = $this->buildCatalogueOrderProducts($request->input('products', []), $business_id, $location_id);

            if (empty($products)) {
                throw new \Exception('No valid products found for order creation.');
            }

            $invoice_total = $this->productUtil->calculateInvoiceTotal($products, null, null, false);
            $crm_settings = $this->getCrmSettings($business);
            $order_request_prefix = $crm_settings['order_request_prefix'] ?? null;
            $ref_count = $this->productUtil->setAndGetReferenceCount('crm_order_request', $business_id);
            $invoice_no = $this->productUtil->generateReferenceNumber('crm_order_request', $ref_count, $business_id, $order_request_prefix);

            $input = [
                'location_id' => $location->id,
                'contact_id' => $customer_payload['contact']->id,
                'status' => 'ordered',
                'type' => 'sales_order',
                'discount_amount' => 0,
                'discount_type' => null,
                'final_total' => $invoice_total['final_total'],
                'transaction_date' => now(),
                'is_direct_sale' => 1,
                'invoice_no' => $invoice_no,
                'selling_price_group_id' => $location->selling_price_group_id,
                'shipping_address' => $request->input('delivery_address'),
                'shipping_details' => 'Catalogue order request',
                'sale_note' => 'Created from product catalogue',
                'products' => $products,
            ];

            $cg = $this->contactUtil->getCustomerGroup($business_id, $customer_payload['contact']->id);
            $input['customer_group_id'] = (empty($cg) || empty($cg->id)) ? null : $cg->id;

            $transaction = $this->transactionUtil->createSellTransaction($business_id, $input, $invoice_total, $customer_payload['creator_id'], false);
            $transaction->crm_is_order_request = 1;
            $transaction->save();

            $this->transactionUtil->createOrUpdateSellLines($transaction, $products, $location_id, false, null, [], false);
            $this->transactionUtil->activityLog($transaction, 'added');

            DB::commit();

            $login_info = $customer_payload['login_created'] ? [
                'login_url'    => url('/login'),
                'username'     => $customer_payload['username'],
                'password'     => $customer_payload['plain_password'],
                'dashboard_url'=> url('/contact/order-request'),
            ] : null;

            $whatsapp_url = $this->generateWhatsappUrl($business, $transaction, $customer_payload['contact'], $products, $request->input('delivery_address'), $login_info);

            return response()->json([
                'success' => true,
                'message' => 'Order request created successfully.',
                'invoice_no' => $transaction->invoice_no,
                'whatsapp_url' => $whatsapp_url,
                'login_created' => $customer_payload['login_created'],
                'login_url' => url('/login'),
                'dashboard_url' => url('/contact/order-request'),
                'username' => $customer_payload['username'],
                'password' => $customer_payload['plain_password'],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.something_went_wrong'),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => __('messages.something_went_wrong'),
            ], 422);
        }
    }

    protected function findOrCreateCatalogueCustomer(Business $business, Request $request)
    {
        $email = trim((string) $request->input('customer_email'));
        $phone = trim((string) $request->input('customer_phone'));
        $name = trim((string) $request->input('customer_name'));
        $address = trim((string) $request->input('delivery_address'));

        // Only consider it the same person if BOTH email AND phone match.
        // OR-matching risks merging two different customers that share only one field.
        $contactQuery = Contact::where('business_id', $business->id)
            ->whereIn('type', ['customer', 'both'])
            ->where('email', $email);

        if (!empty($phone)) {
            $contactQuery->where('mobile', $phone);
        }

        $contact = $contactQuery->first();

        if (empty($contact)) {
            $contact_result = $this->contactUtil->createNewContact([
                'business_id' => $business->id,
                'type' => 'customer',
                'name' => $name,
                'first_name' => $name,
                'email' => $email,
                'mobile' => $phone,
                'address_line_1' => $address,
                'contact_status' => 'active',
                'created_by' => $business->owner_id,
            ]);

            $contact = $contact_result['data'];
        } else {
            $contact->name = $name;
            $contact->first_name = $name;
            $contact->email = $email;
            $contact->mobile = $phone;
            $contact->address_line_1 = $address;
            $contact->contact_status = 'active';
            $contact->save();
        }

        $user = User::where('business_id', $business->id)
            ->where(function ($q) use ($contact, $email) {
                $q->where('crm_contact_id', $contact->id)
                  ->orWhere('email', $email);
            })
            ->first();

        $login_created = false;
        $plain_password = null;

        if (empty($user)) {
            $plain_password = Str::random(10);
            $user = User::create([
                'business_id' => $business->id,
                'crm_contact_id' => $contact->id,
                'surname' => '',
                'first_name' => $name,
                'last_name' => '',
                'username' => $this->generateCatalogueUsername($business->id, $contact->id, $email),
                'email' => $email,
                'password' => Hash::make($plain_password),
                'user_type' => 'user_customer',
                'status' => 'active',
                'allow_login' => 1,
                'contact_no' => $phone,
            ]);
            $login_created = true;
        } else {
            // Only touch user_type if this is already a customer account — never
            // downgrade a staff/admin user who happens to share the same email.
            $needs_login_credentials = empty($user->username) || empty($user->password) || (int) $user->allow_login !== 1;

            $user->first_name = $name;
            $user->email = $email;
            $user->contact_no = $phone;
            $user->status = 'active';
            $user->allow_login = 1;
            if ($user->user_type === 'user_customer') {
                // Safe to keep/enforce customer type and link contact
                $user->user_type = 'user_customer';
                $user->crm_contact_id = $contact->id;
            }
            // If it's a staff user found by email, leave user_type untouched
            if ($needs_login_credentials) {
                $plain_password = Str::random(10);
                $user->username = $this->generateCatalogueUsername($business->id, $contact->id, $email);
                $user->password = Hash::make($plain_password);
                $login_created = true;
            }
            $user->save();
        }

        return [
            'contact' => $contact,
            'user' => $user,
            'username' => $user->username,
            'plain_password' => $plain_password,
            'login_created' => $login_created,
            'creator_id' => $user->id,
        ];
    }

    protected function buildCatalogueOrderProducts(array $items, $business_id, $location_id)
    {
        $variation_ids = collect($items)
            ->pluck('variation_id')
            ->filter()
            ->map(function ($id) {
                return (int) $id;
            })
            ->unique()
            ->values()
            ->all();

        $variations = Variation::with(['product', 'product_variation', 'variation_location_details'])
            ->whereIn('id', $variation_ids)
            ->whereHas('product', function ($query) use ($business_id) {
                $query->where('business_id', $business_id);
            })
            ->get()
            ->keyBy('id');

        $products = [];
        foreach ($items as $item) {
            $variation = $variations->get((int) $item['variation_id']);
            if (empty($variation) || empty($variation->product)) {
                continue;
            }

            $location_detail = $variation->variation_location_details
                ->firstWhere('location_id', (int) $location_id);

            if ($variation->product->enable_stock && (! $location_detail || (float) $location_detail->qty_available <= 0)) {
                continue;
            }

            $quantity = (float) $item['quantity'];
            $unit_price_inc_tax = (float) $variation->sell_price_inc_tax;
            $unit_price = (float) $variation->default_sell_price;
            $item_tax = $unit_price_inc_tax - $unit_price;

            $products[] = [
                'product_id' => $variation->product_id,
                'variation_id' => $variation->id,
                'quantity' => $quantity,
                'unit_price' => $unit_price,
                'unit_price_inc_tax' => $unit_price_inc_tax,
                'item_tax' => $item_tax,
                'tax_id' => $variation->product->tax ?? null,
                'product_unit_id' => $variation->product->unit_id,
                'line_discount_type' => null,
                'line_discount_amount' => 0,
                'sell_line_note' => '',
            ];
        }

        return $products;
    }

    protected function generateCatalogueUsername($business_id, $contact_id, $email = null)
    {
        // Use just the email prefix (e.g. "john" from "john@gmail.com")
        // Only append a short number if there's a collision — no business/contact IDs
        $base = ! empty($email) ? Str::before($email, '@') : 'customer';
        $base = Str::lower(preg_replace('/[^a-zA-Z0-9._-]/', '', $base));
        $base = $base !== '' ? $base : 'customer';

        $username = $base;
        $counter = 2;

        while (User::withTrashed()->where('username', $username)->exists()) {
            $username = $base . '_' . str_pad($counter, 2, '0', STR_PAD_LEFT);
            $counter++;
        }

        return $username;
    }

    protected function generateWhatsappUrl(Business $business, $transaction, Contact $contact, array $products, $delivery_address, $login_info = null)
    {
        $settings = json_decode($business->productcatalogue_settings, true);
        $whatsapp_number = $settings['order_receiving_whatsapp_number'] ?? '';
        $whatsapp_number = preg_replace('/[^\d+]/', '', (string) $whatsapp_number);

        if (empty($whatsapp_number)) {
            return null;
        }

        $lines = [];
        $lines[] = '*New Order Request*';
        $lines[] = '';
        $lines[] = 'Order ID: ' . $transaction->invoice_no;
        $lines[] = '';
        $lines[] = '*Order Items:*';

        foreach ($products as $index => $product) {
            $variation = Variation::with(['product', 'product_variation'])->find($product['variation_id']);
            if (empty($variation) || empty($variation->product)) {
                continue;
            }

            $name = $variation->product->name;
            if ($variation->product->type === 'variable') {
                $name .= ' - ' . $variation->name;
            }

            $subtotal = $product['quantity'] * $product['unit_price_inc_tax'];
            $lines[] = ($index + 1) . '. ' . $name;
            $lines[] = '   Quantity: ' . $this->commonUtil->roundQuantity($product['quantity']) . ' x ' . $this->productUtil->num_f($product['unit_price_inc_tax'], true, $business) . ' = ' . $this->productUtil->num_f($subtotal, true, $business);
            $lines[] = '';
        }

        $lines[] = '*Total: ' . $this->productUtil->num_f($transaction->final_total, true, $business) . '*';
        $lines[] = '';
        $lines[] = '*Customer Details:*';
        $lines[] = 'Name: ' . $contact->name;
        $lines[] = 'Email: ' . $contact->email;
        $lines[] = 'Phone: ' . $contact->mobile;
        $lines[] = 'Delivery Address: ' . $delivery_address;

        if (!empty($login_info)) {
            $lines[] = '';
            $lines[] = '*Your Account Details:*';
            $lines[] = 'Login URL: ' . $login_info['login_url'];
            $lines[] = 'Username: ' . $login_info['username'];
            $lines[] = 'Password: ' . $login_info['password'];
            $lines[] = 'Track Orders: ' . $login_info['dashboard_url'];
        }

        return 'https://wa.me/' . $whatsapp_number . '?text=' . rawurlencode(implode("\n", $lines));
    }

    protected function getCrmSettings(Business $business)
    {
        return ! empty($business->crm_settings) ? json_decode($business->crm_settings, true) : [];
    }

    protected function storeCatalogueBanners(Request $request, array $settings)
    {
        $previous_banners = collect($settings['catalogue_banners'] ?? [])
            ->values()
            ->mapWithKeys(function ($banner, $index) {
                return [(string) $index => $banner];
            })
            ->all();
        $catalogue_banners = [];
        $banner_keys = $request->input('catalogue_banner_keys', []);

        foreach ($banner_keys as $banner_key) {
            $banner_key = (string) $banner_key;
            $previous_image = $request->input("existing_catalogue_banner_image.$banner_key")
                ?? ($previous_banners[$banner_key]['image'] ?? null);
            $image_name = $previous_image;

            if ($request->hasFile("catalogue_banner_image.$banner_key")) {
                if (! empty($previous_image)) {
                    $this->deleteCatalogueBannerImage($previous_image);
                }
                $image_name = $this->commonUtil->uploadFile($request, "catalogue_banner_image.$banner_key", 'productcatalogue_banners', 'image');
            }

            if ($request->boolean("remove_catalogue_banner.$banner_key")) {
                if (! empty($previous_image)) {
                    $this->deleteCatalogueBannerImage($previous_image);
                }
                $image_name = null;
            }

            if (! empty($image_name)) {
                $catalogue_banners[] = [
                    'image' => $image_name,
                    'title' => $request->input("catalogue_banner_title.$banner_key"),
                    'subtitle' => $request->input("catalogue_banner_subtitle.$banner_key"),
                    'link' => $request->input("catalogue_banner_link.$banner_key"),
                ];
            }
        }

        return $catalogue_banners;
    }

    protected function deleteCatalogueBannerImage($image_name)
    {
        $image_path = public_path('uploads/productcatalogue_banners/' . $image_name);
        if (! empty($image_name) && file_exists($image_path)) {
            @unlink($image_path);
        }
    }
}