<?php

namespace Modules\Searchbox\Http\Controllers;

use App\Contact;
use App\Product;
use App\Transaction;
use App\TransactionPayment;
use App\ExpenseCategory;
use App\User;
use App\Utils\TransactionUtil;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Schema;

class UniversalSearchController extends Controller
{
    protected $transactionUtil;

    /**
     * Results limit per category
     */
    protected $limit = 5;

    public function __construct(TransactionUtil $transactionUtil)
    {
        $this->transactionUtil = $transactionUtil;
        $this->middleware('auth');
    }

    /**
     * Get localized transaction status text
     */
    protected function getTxnStatusText($txn_status)
    {
        if (empty($txn_status)) {
            return '';
        }
        if (Lang::has('lang_v1.' . $txn_status)) {
            return __('lang_v1.' . $txn_status);
        }
        if (Lang::has('sale.' . $txn_status)) {
            return __('sale.' . $txn_status);
        }
        if (Lang::has('purchase.' . $txn_status)) {
            return __('purchase.' . $txn_status);
        }
        if (Lang::has('stock_adjustment.' . $txn_status)) {
            return __('stock_adjustment.' . $txn_status);
        }
        return ucfirst($txn_status);
    }

    /**
     * @return list<string>
     */
    protected function parseOnlyFilter(?string $only): array
    {
        if ($only === null || trim($only) === '') {
            return [];
        }
        $allowed = [
            'products', 'contacts', 'users', 'sales', 'purchases', 'payments', 'expenses',
            'sales_orders', 'purchase_orders', 'stock_transfers', 'stock_adjustments',
            'sale_returns', 'purchase_returns', 'repair',
        ];
        $parts = array_filter(array_map('trim', explode(',', $only)));

        return array_values(array_intersect($parts, $allowed));
    }

    /**
     * @param  list<string>  $onlyList
     */
    protected function shouldSearch(string $segment, array $onlyList): bool
    {
        return $onlyList === [] || in_array($segment, $onlyList, true);
    }

    /**
     * Main search endpoint
     */
    public function search(Request $request)
    {
        try {
            $query = $request->input('q');
            $minLen = (int) config('searchbox.min_query_length', 2);
            $onlyList = $this->parseOnlyFilter($request->input('only'));

            if (empty($query) || strlen($query) < $minLen) {
                return response()->json([
                    'results' => [],
                    'message' => __('lang_v1.minimum_search_length'),
                    'min_length' => $minLen,
                ]);
            }

            $t0 = microtime(true);
            $business_id = $request->session()->get('user.business_id');
            $results = [];

            // Search Products FIRST (per user preference)
            if ($this->shouldSearch('products', $onlyList) && auth()->user()->can('product.view')) {
                $results = array_merge($results, $this->searchProducts($query, $business_id));
            }

            // Search Contacts (Customers & Suppliers)
            if ($this->shouldSearch('contacts', $onlyList) && (auth()->user()->can('supplier.view') || auth()->user()->can('customer.view'))) {
                $results = array_merge($results, $this->searchContacts($query, $business_id));
            }

            // Search team / users (Manage users)
            if ($this->shouldSearch('users', $onlyList) && auth()->user()->can('user.view')) {
                $results = array_merge($results, $this->searchUsers($query, $business_id));
            }

            // Search Sales Invoices
            if ($this->shouldSearch('sales', $onlyList) && (auth()->user()->can('sell.view') || auth()->user()->can('direct_sell.view'))) {
                $results = array_merge($results, $this->searchSales($query, $business_id));
            }

            // Search Purchase Invoices
            if ($this->shouldSearch('purchases', $onlyList) && auth()->user()->can('purchase.view')) {
                $results = array_merge($results, $this->searchPurchases($query, $business_id));
            }

            // Search Payments
            if ($this->shouldSearch('payments', $onlyList) && (auth()->user()->can('sell.view') || auth()->user()->can('purchase.view'))) {
                $results = array_merge($results, $this->searchPayments($query, $business_id));
            }

            // Search Expenses
            if ($this->shouldSearch('expenses', $onlyList) && auth()->user()->can('expense.access')) {
                $results = array_merge($results, $this->searchExpenses($query, $business_id));
            }

            // Search Sales Orders
            if ($this->shouldSearch('sales_orders', $onlyList) && (auth()->user()->can('so.view_all') || auth()->user()->can('so.view_own'))) {
                $results = array_merge($results, $this->searchSalesOrders($query, $business_id));
            }

            // Search Purchase Orders
            if ($this->shouldSearch('purchase_orders', $onlyList) && auth()->user()->can('purchase.view')) {
                $results = array_merge($results, $this->searchPurchaseOrders($query, $business_id));
            }

            // Search Stock Transfers
            if ($this->shouldSearch('stock_transfers', $onlyList) && auth()->user()->can('purchase.view')) {
                $results = array_merge($results, $this->searchStockTransfers($query, $business_id));
            }

            // Search Stock Adjustments
            if ($this->shouldSearch('stock_adjustments', $onlyList) && auth()->user()->can('stock_adjustment.view')) {
                $results = array_merge($results, $this->searchStockAdjustments($query, $business_id));
            }

            // Search Sale Returns
            if ($this->shouldSearch('sale_returns', $onlyList) && auth()->user()->can('sell.view')) {
                $results = array_merge($results, $this->searchSaleReturns($query, $business_id));
            }

            // Search Purchase Returns
            if ($this->shouldSearch('purchase_returns', $onlyList) && auth()->user()->can('purchase.view')) {
                $results = array_merge($results, $this->searchPurchaseReturns($query, $business_id));
            }

            // Search Repair Job Sheets (if module exists)
            if ($this->shouldSearch('repair', $onlyList) && class_exists('\\Modules\\Repair\\Entities\\JobSheet')) {
                $results = array_merge($results, $this->searchRepairJobSheets($query, $business_id));
            }

            $tookMs = (int) round((microtime(true) - $t0) * 1000);

            return response()->json([
                'results' => $results,
                'total' => count($results),
                'query' => $query,
                'only' => $onlyList,
                'took_ms' => $tookMs,
            ]);
        } catch (\Exception $e) {
            \Log::error('UniversalSearch Error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            $error_message = __('lang_v1.search_error');
            if ($error_message === 'lang_v1.search_error') {
                $error_message = $e->getMessage();
            }
            return response()->json([
                'results' => [],
                'message' => $error_message
            ], 500);
        }
    }

    /**
     * Search business users (Manage users)
     */
    protected function searchUsers($query, $business_id)
    {
        $results = [];

        $users = User::where('business_id', $business_id)
            ->where(function ($q) use ($query) {
                $q->where('username', 'like', "%{$query}%")
                    ->orWhere('first_name', 'like', "%{$query}%")
                    ->orWhere('surname', 'like', "%{$query}%")
                    ->orWhere('last_name', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%");
            })
            ->limit($this->limit)
            ->get();

        foreach ($users as $user) {
            $display = trim(implode(' ', array_filter([
                $user->first_name ?? '',
                $user->surname ?? '',
                $user->last_name ?? '',
            ])));
            if ($display === '') {
                $display = $user->username ?? (string) $user->id;
            }

            $subtitleParts = [];
            if (! empty($user->username)) {
                $subtitleParts[] = __('lang_v1.username').': '.$user->username;
            }
            if (! empty($user->email)) {
                $subtitleParts[] = $user->email;
            }
            $subtitle = implode(' | ', $subtitleParts);

            $results[] = [
                'category' => __('searchbox::lang.category_users'),
                'type' => __('searchbox::lang.type_user'),
                'title' => $display,
                'subtitle' => $subtitle,
                'url' => action([\App\Http\Controllers\ManageUserController::class, 'show'], [$user->id]),
                'is_modal' => false,
            ];
        }

        return $results;
    }

    /**
     * Search Products
     */
    protected function searchProducts($query, $business_id)
    {
        $results = [];
        $has_product_barcodes = Schema::hasTable('product_barcodes');

        $products = Product::leftJoin('variations', 'products.id', '=', 'variations.product_id')
            ->where('products.business_id', $business_id)
            ->where(function($q) use ($query, $has_product_barcodes) {
                $q->where('products.name', 'like', "%{$query}%")
                  ->orWhere('products.sku', 'like', "%{$query}%")
                  ->orWhere('variations.sub_sku', 'like', "%{$query}%")
                  ->orWhere('products.product_description', 'like', "%{$query}%");

                if ($has_product_barcodes) {
                    $q->orWhereExists(function ($subquery) use ($query) {
                        $subquery->select(DB::raw(1))
                            ->from('product_barcodes')
                            ->whereColumn('product_barcodes.product_id', 'products.id')
                            ->where('product_barcodes.barcode', 'like', "%{$query}%");
                    });
                }
            })
            ->select(
                'products.id',
                'products.name',
                'products.sku',
                'products.type',
                'products.is_inactive',
                'products.image',
                'variations.name as variation_name',
                'variations.sub_sku',
                'variations.default_sell_price'
            )
            ->groupBy('products.id')
            ->limit($this->limit)
            ->get();

        foreach ($products as $product) {
            $subtitle = __('product.sku') . ': ' . ($product->sub_sku ?: $product->sku);
            if ($product->default_sell_price) {
                $subtitle .= ' | ' . __('lang_v1.price') . ': ' . $this->transactionUtil->num_f($product->default_sell_price, true);
            }

            // Status badge for inactive products
            $badge_class = '';
            $status_text = '';
            $status_text_class = '';
            if ($product->is_inactive) {
                $badge_class = 'tw-bg-gray-500 tw-text-white';
                $status_text = __('lang_v1.inactive');
                $status_text_class = 'tw-text-gray-500';
            }

            $title = $product->name;
            if ($product->variation_name && $product->variation_name !== 'DUMMY') {
                $title .= ' - ' . $product->variation_name;
            }

            $results[] = [
                'category' => __('sale.products'),
                'type' => __('sale.product'),
                'title' => $title,
                'subtitle' => $subtitle,
                'url' => action([\App\Http\Controllers\ProductController::class, 'view'], [$product->id]),
                'is_modal' => true,
                'data_container' => '.view_modal',
                'badge_class' => $badge_class,
                'status_text' => $status_text,
                'status_text_class' => $status_text_class,
                'image' => $product->image_url ?? null
            ];
        }

        return $results;
    }

    /**
     * Search Contacts (Customers & Suppliers)
     */
    protected function searchContacts($query, $business_id)
    {
        $results = [];

        $contacts = Contact::where('business_id', $business_id)
            ->where(function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('supplier_business_name', 'like', "%{$query}%")
                  ->orWhere('contact_id', 'like', "%{$query}%")
                  ->orWhere('mobile', 'like', "%{$query}%")
                  ->orWhere('tax_number', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%");
            })
            ->limit($this->limit)
            ->get();

        foreach ($contacts as $contact) {
            // Build contact type label
            $typeParts = [];
            if (!empty($contact->type) && (strpos($contact->type, 'customer') !== false || $contact->type == 'customer')) {
                $typeParts[] = __('role.customer');
            }
            if (!empty($contact->type) && (strpos($contact->type, 'supplier') !== false || $contact->type == 'supplier')) {
                $typeParts[] = __('purchase.supplier');
            }
            $type = !empty($typeParts) ? implode(' | ', $typeParts) : __('contact.contact');

            // Build subtitle with address (if exists) and phone
            $subtitleParts = [];

            // Add address if not null
            $address = trim(implode(', ', array_filter([
                $contact->address_line_1,
                $contact->address_line_2,
                $contact->city,
                $contact->state,
                $contact->country
            ])));
            if (!empty($address)) {
                $subtitleParts[] = $address;
            }

            // Add mobile
            if (!empty($contact->mobile)) {
                $subtitleParts[] = __('contact.mobile') . ': ' . $contact->mobile;
            }

            // Add tax number
            if (!empty($contact->tax_number)) {
                $subtitleParts[] = __('contact.tax_no') . ': ' . $contact->tax_number;
            }

            $subtitle = implode(' | ', $subtitleParts);

            // Calculate balance
            $balance = $contact->balance ?? 0;
            if ((float) $balance == 0) {
                try {
                    $balance = $this->transactionUtil->getContactDue($contact->id, $business_id);
                } catch (\Exception $e) {
                    $balance = $contact->balance ?? 0;
                }
            }

            $badge_class = '';
            $status_text_class = '';
            $status_text = '';
            if ($balance > 0) {
                $status_text = __('lang_v1.due') . ': ' . $this->transactionUtil->num_f($balance, true);
                $badge_class = 'tw-bg-red-600 tw-text-white';
                $status_text_class = 'tw-text-red-600';
            } elseif ($balance < 0) {
                $status_text = __('lang_v1.advance') . ': ' . $this->transactionUtil->num_f(abs($balance), true);
                $badge_class = 'tw-bg-green-600 tw-text-white';
                $status_text_class = 'tw-text-green-600';
            }

            // Build title with contact_id
            $title = '';
            if (!empty($contact->contact_id)) {
                $title .= '(' . $contact->contact_id . ') ';
            }
            $title .= $contact->name;
            if ($contact->supplier_business_name) {
                $title .= ' (' . $contact->supplier_business_name . ')';
            }

            $results[] = [
                'category' => __('contact.contacts'),
                'type' => $type,
                'contact_type' => $type,
                'title' => $title,
                'subtitle' => $subtitle,
                'badge_class' => $badge_class,
                'status_badge_class' => $badge_class,
                'status_text_class' => $status_text_class,
                'status_text' => $status_text,
                'url' => action([\App\Http\Controllers\ContactController::class, 'show'], [$contact->id])
            ];
        }

        return $results;
    }

    /**
     * Search Sales Invoices
     */
    protected function searchSales($query, $business_id)
    {
        $results = [];

        $sales = Transaction::where('business_id', $business_id)
            ->where('type', 'sell')
            ->where(function($q) use ($query) {
                $q->where('invoice_no', 'like', "%{$query}%")
                  ->orWhere('ref_no', 'like', "%{$query}%");
            })
            ->with(['contact', 'payment_lines'])
            ->limit($this->limit)
            ->get();

        // Check for returns
        if ($sales->count() > 0) {
            $sale_ids = $sales->pluck('id')->toArray();
            $sales_with_returns = Transaction::whereIn('return_parent_id', $sale_ids)
                ->where('type', 'sell_return')
                ->pluck('return_parent_id')
                ->toArray();

            foreach ($sales as $sale) {
                $sale->return_exists = in_array($sale->id, $sales_with_returns);
            }
        }

        foreach ($sales as $sale) {
            $total_paid = $sale->payment_lines->sum('amount');
            $pending_amount = max(0, $sale->final_total - $total_paid);
            $payment_status = $sale->payment_status ?? '';

            $contact_display = '';
            if (!empty($sale->contact)) {
                $contact_display = $sale->contact->supplier_business_name ?: $sale->contact->name ?: ($sale->contact->contact_id ?? '');
            }

            $subtitle = __('lang_v1.total_bill') . ': ' . $this->transactionUtil->num_f($sale->final_total, true);
            if ($pending_amount > 0) {
                $subtitle .= ' | ' . __('lang_v1.due') . ': ' . $this->transactionUtil->num_f($pending_amount, true);
            }

            // Payment status styling
            $badge_class = '';
            $status_text_class = '';
            $status_text = '';
            if (!empty($payment_status)) {
                if ($payment_status == 'paid') {
                    $badge_class = 'tw-bg-green-600 tw-text-white';
                    $status_text_class = 'tw-text-green-600';
                    $status_text = __('lang_v1.paid');
                } elseif ($payment_status == 'partial') {
                    $badge_class = 'tw-bg-yellow-500 tw-text-white';
                    $status_text_class = 'tw-text-yellow-500';
                    $status_text = __('lang_v1.partial') . ($pending_amount > 0 ? ' | ' . __('lang_v1.due') . ': ' . $this->transactionUtil->num_f($pending_amount, true) : '');
                } elseif ($payment_status == 'due') {
                    $badge_class = 'tw-bg-red-600 tw-text-white';
                    $status_text_class = 'tw-text-red-600';
                    $status_text = __('lang_v1.due') . ($pending_amount > 0 ? ': ' . $this->transactionUtil->num_f($pending_amount, true) : '');
                }
            }

            // Transaction status
            $txn_status = !empty($sale->sub_status) && in_array($sale->sub_status, ['quotation', 'proforma'])
                ? $sale->sub_status : ($sale->status ?? '');
            $txn_badge_class = '';
            $txn_text_class = '';
            if (!empty($txn_status)) {
                if (in_array($txn_status, ['pending', 'draft'])) {
                    $txn_badge_class = 'tw-bg-blue-600 tw-text-white';
                    $txn_text_class = 'tw-text-blue-600';
                } elseif (in_array($txn_status, ['quotation', 'proforma'])) {
                    $txn_badge_class = 'tw-bg-primary-800 tw-text-white';
                    $txn_text_class = 'tw-text-primary-800';
                } elseif ($txn_status == 'final') {
                    $txn_badge_class = 'tw-bg-green-600 tw-text-white';
                    $txn_text_class = 'tw-text-green-600';
                }
            }

            $title = ($contact_display ? $contact_display . ' | ' : '') . $sale->invoice_no;
            if (!empty($sale->return_exists)) {
                $title .= ' <small class="label bg-red label-round no-print" title="'.__('lang_v1.some_qty_returned_from_sell').'"><i class="fas fa-undo"></i></small>';
            }

            $results[] = [
                'category' => __('sale.sale'),
                'type' => __('sale.sale'),
                'title' => $title,
                'subtitle' => $subtitle,
                'url' => action([\App\Http\Controllers\SellController::class, 'show'], [$sale->id]),
                'is_modal' => true,
                'data_container' => '.view_modal',
                'badge_class' => $badge_class,
                'status_badge_class' => $badge_class,
                'status_text_class' => $status_text_class,
                'status' => $payment_status,
                'status_text' => !empty($status_text) ? $status_text : (!empty($payment_status) ? __('lang_v1.' . $payment_status) : ''),
                'display_date' => $this->transactionUtil->format_date($sale->transaction_date ?? $sale->created_at ?? null, true),
                'txn_status' => $txn_status,
                'txn_status_text' => $this->getTxnStatusText($txn_status),
                'txn_badge_class' => $txn_badge_class,
                'txn_text_class' => $txn_text_class
            ];
        }

        return $results;
    }

    /**
     * Search Purchase Invoices
     */
    protected function searchPurchases($query, $business_id)
    {
        $results = [];

        $purchases = Transaction::where('business_id', $business_id)
            ->where('type', 'purchase')
            ->where(function($q) use ($query) {
                $q->where('ref_no', 'like', "%{$query}%")
                  ->orWhere('invoice_no', 'like', "%{$query}%");
            })
            ->with(['contact', 'payment_lines'])
            ->limit($this->limit)
            ->get();

        // Check for returns
        if ($purchases->count() > 0) {
            $purchase_ids = $purchases->pluck('id')->toArray();
            $purchases_with_returns = Transaction::whereIn('return_parent_id', $purchase_ids)
                ->where('type', 'purchase_return')
                ->pluck('return_parent_id')
                ->toArray();

            foreach ($purchases as $purchase) {
                $purchase->return_exists = in_array($purchase->id, $purchases_with_returns);
            }
        }

        foreach ($purchases as $purchase) {
            $total_paid = $purchase->payment_lines->sum('amount');
            $pending_amount = max(0, $purchase->final_total - $total_paid);
            $payment_status = $purchase->payment_status ?? '';

            $contact_display = '';
            if (!empty($purchase->contact)) {
                $contact_display = $purchase->contact->supplier_business_name ?: $purchase->contact->name ?: ($purchase->contact->contact_id ?? '');
            }

            $subtitle = __('lang_v1.total_purchase') . ': ' . $this->transactionUtil->num_f($purchase->final_total, true);
            if ($pending_amount > 0) {
                $subtitle .= ' | ' . __('lang_v1.due') . ': ' . $this->transactionUtil->num_f($pending_amount, true);
            }

            // Payment status styling
            $badge_class = '';
            $status_text_class = '';
            $status_text = '';
            if (!empty($payment_status)) {
                if ($payment_status == 'paid') {
                    $badge_class = 'tw-bg-green-600 tw-text-white';
                    $status_text_class = 'tw-text-green-600';
                    $status_text = __('lang_v1.paid');
                } elseif ($payment_status == 'partial') {
                    $badge_class = 'tw-bg-yellow-500 tw-text-white';
                    $status_text_class = 'tw-text-yellow-500';
                    $status_text = __('lang_v1.partial') . ($pending_amount > 0 ? ' | ' . __('lang_v1.due') . ': ' . $this->transactionUtil->num_f($pending_amount, true) : '');
                } elseif ($payment_status == 'due') {
                    $badge_class = 'tw-bg-red-600 tw-text-white';
                    $status_text_class = 'tw-text-red-600';
                    $status_text = __('lang_v1.due') . ($pending_amount > 0 ? ': ' . $this->transactionUtil->num_f($pending_amount, true) : '');
                }
            }

            // Transaction status
            $txn_status = !empty($purchase->sub_status) && in_array($purchase->sub_status, ['quotation', 'proforma'])
                ? $purchase->sub_status : ($purchase->status ?? '');
            $txn_badge_class = '';
            $txn_text_class = '';
            if (!empty($txn_status)) {
                if (in_array($txn_status, ['pending', 'draft'])) {
                    $txn_badge_class = 'tw-bg-blue-600 tw-text-white';
                    $txn_text_class = 'tw-text-blue-600';
                } elseif (in_array($txn_status, ['quotation', 'proforma'])) {
                    $txn_badge_class = 'tw-bg-primary-800 tw-text-white';
                    $txn_text_class = 'tw-text-primary-800';
                } elseif ($txn_status == 'received') {
                    $txn_badge_class = 'tw-bg-green-600 tw-text-white';
                    $txn_text_class = 'tw-text-green-600';
                }
            }

            $txn_status_text = $this->getTxnStatusText($txn_status);
            $combined_status_text = !empty($status_text) ? $status_text : (!empty($payment_status) ? __('lang_v1.' . $payment_status) : '');
            if (!empty($txn_status_text)) {
                $combined_status_text = (!empty($combined_status_text) ? $combined_status_text . ' | ' : '') . $txn_status_text;
            }

            $title = ($contact_display ? $contact_display . ' | ' : '') . $purchase->ref_no;
            if (!empty($purchase->return_exists)) {
                $title .= ' <small class="label bg-red label-round no-print" title="'.__('lang_v1.some_qty_returned').'"><i class="fas fa-undo"></i></small>';
            }

            $results[] = [
                'category' => __('purchase.purchases'),
                'type' => __('purchase.purchases'),
                'title' => $title,
                'subtitle' => $subtitle,
                'url' => action([\App\Http\Controllers\PurchaseController::class, 'show'], [$purchase->id]),
                'is_modal' => true,
                'data_container' => '.view_modal',
                'badge_class' => $badge_class,
                'status_badge_class' => $badge_class,
                'status_text_class' => $status_text_class,
                'status' => $payment_status,
                'status_text' => $combined_status_text,
                'display_date' => $this->transactionUtil->format_date($purchase->transaction_date ?? $purchase->created_at ?? null, true)
            ];
        }

        return $results;
    }

    /**
     * Search Payments
     */
    protected function searchPayments($query, $business_id)
    {
        $results = [];

        $payments = TransactionPayment::join('transactions as t', 'transaction_payments.transaction_id', '=', 't.id')
            ->where('t.business_id', $business_id)
            ->where(function($q) use ($query) {
                $q->where('transaction_payments.payment_ref_no', 'like', "%{$query}%");
            })
            ->select('transaction_payments.*')
            ->with(['transaction.contact', 'transaction.payment_lines'])
            ->limit($this->limit)
            ->get();

        foreach ($payments as $payment) {
            $txn_ref = '';
            if ($payment->transaction) {
                $txn_ref = $payment->transaction->type == 'sell'
                    ? $payment->transaction->invoice_no
                    : $payment->transaction->ref_no;
            }

            $payment_status = $payment->transaction ? ($payment->transaction->payment_status ?? '') : '';
            $badge_class = '';
            $status_text_class = '';
            $status_text = '';

            if ($payment_status == 'paid') {
                $badge_class = 'tw-bg-green-600 tw-text-white';
                $status_text_class = 'tw-text-green-600';
                $status_text = __('lang_v1.paid');
            } elseif ($payment_status == 'partial') {
                $badge_class = 'tw-bg-yellow-500 tw-text-white';
                $status_text_class = 'tw-text-yellow-500';
                $status_text = __('lang_v1.partial');
            } elseif ($payment_status == 'due') {
                $badge_class = 'tw-bg-red-600 tw-text-white';
                $status_text_class = 'tw-text-red-600';
                $status_text = __('lang_v1.due');
            }

            $customer_name = '';
            if ($payment->transaction && $payment->transaction->contact) {
                $customer_name = $payment->transaction->contact->name;
            }

            $payment_title = (!empty($txn_ref) ? $txn_ref : $payment->payment_ref_no) . ($customer_name ? ' - ' . $customer_name : '');

            $paid_on_formatted = '';
            try {
                $paid_on_formatted = $this->transactionUtil->format_date($payment->paid_on, true);
            } catch (\Exception $e) {
                $paid_on_formatted = $payment->paid_on ? $payment->paid_on : '';
            }

            $payment_subtitle = ($paid_on_formatted ? $paid_on_formatted . ' | ' : '') . __('lang_v1.total_payment') . ': ' . $this->transactionUtil->num_f($payment->amount, true);

            $results[] = [
                'category' => __('lang_v1.payment'),
                'type' => __('lang_v1.payment'),
                'title' => $payment_title,
                'subtitle' => $payment_subtitle,
                'url' => action([\App\Http\Controllers\TransactionPaymentController::class, 'viewPayment'], [$payment->id]),
                'is_modal' => true,
                'data_container' => '.view_modal',
                'badge_class' => $badge_class,
                'status_badge_class' => $badge_class,
                'status_text_class' => $status_text_class,
                'status' => $payment_status,
                'status_text' => $status_text,
                'display_date' => $paid_on_formatted,
                'raw_type' => 'payment',
                'related_transaction_type' => ($payment->transaction->type ?? ''),
                'related_transaction_label' => (!empty($payment->transaction) && !empty($payment->transaction->type)
                    ? ($payment->transaction->type == 'sell' ? __('sale.sale') : ($payment->transaction->type == 'purchase' ? __('purchase.purchases') : ucfirst($payment->transaction->type)))
                    : ''),
                'related_transaction_ref' => $txn_ref ?? '',
                'related_transaction_url' => (!empty($payment->transaction)
                    ? ($payment->transaction->type == 'sell'
                        ? action([\App\Http\Controllers\SellController::class, 'show'], [$payment->transaction->id])
                        : ($payment->transaction->type == 'purchase'
                            ? action([\App\Http\Controllers\PurchaseController::class, 'show'], [$payment->transaction->id])
                            : ''))
                    : '')
            ];
        }

        return $results;
    }

    /**
     * Search Expenses
     */
    protected function searchExpenses($query, $business_id)
    {
        $results = [];

        $expenses = Transaction::leftJoin('users AS U', 'transactions.expense_for', '=', 'U.id')
            ->where('transactions.business_id', $business_id)
            ->where('transactions.type', 'expense')
            ->where(function($q) use ($query) {
                $q->where('transactions.ref_no', 'like', "%{$query}%")
                  ->orWhere(DB::raw("CONCAT(COALESCE(U.first_name, ''),' ',COALESCE(U.last_name,''))"), 'like', "%{$query}%")
                  ->orWhere('U.username', 'like', "%{$query}%");
            })
            ->select('transactions.*')
            ->limit($this->limit)
            ->get();

        foreach ($expenses as $expense) {
            $category_name = '';
            $expense_for_name = '';
            if (!empty($expense->expense_category_id)) {
                $cat = ExpenseCategory::find($expense->expense_category_id);
                $category_name = $cat ? $cat->name : '';
            }
            if (!empty($expense->expense_for)) {
                $user = User::find($expense->expense_for);
                $expense_for_name = $user ? trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) : '';
            }

            $subtitle = __('lang_v1.total_expense') . ': ' . $this->transactionUtil->num_f($expense->final_total, true);
            if (!empty($category_name)) {
                $subtitle .= ' | ' . __('category.category') . ': ' . $category_name;
            }

            $expense_payment_status = $expense->payment_status ?? '';
            $expense_badge_class = '';
            $status_text_class = '';
            $status_text = '';

            if (!empty($expense_payment_status)) {
                if ($expense_payment_status == 'paid') {
                    $expense_badge_class = 'tw-bg-green-600 tw-text-white';
                    $status_text_class = 'tw-text-green-600';
                    $status_text = __('lang_v1.paid');
                } elseif ($expense_payment_status == 'partial') {
                    $expense_badge_class = 'tw-bg-yellow-500 tw-text-white';
                    $status_text_class = 'tw-text-yellow-500';
                    $status_text = __('lang_v1.partial');
                } elseif ($expense_payment_status == 'due') {
                    $expense_badge_class = 'tw-bg-red-600 tw-text-white';
                    $status_text_class = 'tw-text-red-600';
                    $status_text = __('lang_v1.due');
                }
            }

            $expenseTitle = !empty($expense_for_name) ? $expense_for_name . ' | ' . $expense->ref_no : $expense->ref_no;

            $results[] = [
                'category' => __('expense.expenses'),
                'type' => __('expense.expenses'),
                'title' => $expenseTitle,
                'subtitle' => $subtitle,
                'url' => action([\App\Http\Controllers\ExpenseController::class, 'show'], [$expense->id]),
                'is_modal' => true,
                'data_container' => '.view_modal',
                'badge_class' => $expense_badge_class,
                'status_badge_class' => $expense_badge_class,
                'status' => $expense_payment_status,
                'status_text' => $status_text,
                'status_text_class' => $status_text_class,
                'display_date' => $this->transactionUtil->format_date($expense->transaction_date ?? $expense->created_at ?? null, true),
                'raw_type' => 'expense',
                'expense_for_name' => $expense_for_name
            ];
        }

        return $results;
    }

    /**
     * Search Sales Orders
     */
    protected function searchSalesOrders($query, $business_id)
    {
        $results = [];

        $sales_orders = Transaction::where('business_id', $business_id)
            ->where('type', 'sales_order')
            ->where(function($q) use ($query) {
                $q->where('ref_no', 'like', "%{$query}%")
                  ->orWhere('invoice_no', 'like', "%{$query}%");
            })
            ->with('contact')
            ->limit($this->limit)
            ->get();

        foreach ($sales_orders as $so) {
            $txn_status = !empty($so->sub_status) && in_array($so->sub_status, ['quotation', 'proforma'])
                ? $so->sub_status : ($so->status ?? '');
            $txn_badge_class = '';
            $txn_text_class = '';

            if (!empty($txn_status) && in_array($txn_status, ['pending', 'draft'])) {
                $txn_badge_class = 'tw-bg-blue-600 tw-text-white';
                $txn_text_class = 'tw-text-blue-600';
            } elseif (!empty($txn_status) && in_array($txn_status, ['quotation', 'proforma'])) {
                $txn_badge_class = 'tw-bg-primary-800 tw-text-white';
                $txn_text_class = 'tw-text-primary-800';
            }

            $so_contact_display = '';
            if (!empty($so->contact)) {
                $so_contact_display = $so->contact->supplier_business_name ?: $so->contact->name ?: ($so->contact->contact_id ?? '');
            }

            $results[] = [
                'category' => __('lang_v1.sales_order'),
                'type' => __('lang_v1.sales_order'),
                'title' => ($so_contact_display ? $so_contact_display . ' | ' : '') . ($so->invoice_no ?: $so->ref_no),
                'subtitle' => __('lang_v1.total_amount') . ': ' . $this->transactionUtil->num_f($so->final_total, true),
                'display_date' => $this->transactionUtil->format_date($so->transaction_date ?? $so->created_at ?? null, true),
                'url' => action([\App\Http\Controllers\SalesOrderController::class, 'show'], [$so->id]),
                'is_modal' => true,
                'data_container' => '.view_modal',
                'txn_status' => $txn_status,
                'txn_status_text' => $this->getTxnStatusText($txn_status),
                'txn_badge_class' => $txn_badge_class,
                'txn_text_class' => $txn_text_class
            ];
        }

        return $results;
    }

    /**
     * Search Purchase Orders
     */
    protected function searchPurchaseOrders($query, $business_id)
    {
        $results = [];

        $purchase_orders = Transaction::where('business_id', $business_id)
            ->where('type', 'purchase_order')
            ->where(function($q) use ($query) {
                $q->where('ref_no', 'like', "%{$query}%")
                  ->orWhere('invoice_no', 'like', "%{$query}%");
            })
            ->with('contact')
            ->limit($this->limit)
            ->get();

        foreach ($purchase_orders as $po) {
            $txn_status = !empty($po->sub_status) && in_array($po->sub_status, ['quotation', 'proforma'])
                ? $po->sub_status : ($po->status ?? '');
            $payment_status = $po->payment_status ?? '';

            $po_badge_class = '';
            $status_text_class = '';
            $status_text = '';

            if (!empty($payment_status)) {
                if ($payment_status == 'paid') {
                    $po_badge_class = 'tw-bg-green-600 tw-text-white';
                    $status_text_class = 'tw-text-green-600';
                    $status_text = __('lang_v1.paid');
                } elseif ($payment_status == 'partial') {
                    $po_badge_class = 'tw-bg-yellow-500 tw-text-white';
                    $status_text_class = 'tw-text-yellow-500';
                    $status_text = __('lang_v1.partial');
                } elseif ($payment_status == 'due') {
                    $po_badge_class = 'tw-bg-red-600 tw-text-white';
                    $status_text_class = 'tw-text-red-600';
                    $status_text = __('lang_v1.due');
                }
            }

            $txn_badge_class = '';
            if (!empty($txn_status) && in_array($txn_status, ['pending', 'draft'])) {
                $txn_badge_class = 'tw-bg-blue-600 tw-text-white';
            } elseif (!empty($txn_status) && in_array($txn_status, ['quotation', 'proforma'])) {
                $txn_badge_class = 'tw-bg-primary-800 tw-text-white';
            }

            $po_contact_display = '';
            if (!empty($po->contact)) {
                $po_contact_display = $po->contact->supplier_business_name ?: $po->contact->name ?: ($po->contact->contact_id ?? '');
            }

            $txn_status_text = $this->getTxnStatusText($txn_status);
            $combined_status_text = !empty($status_text) ? $status_text : (!empty($payment_status) ? __('lang_v1.' . $payment_status) : '');
            if (!empty($txn_status_text)) {
                $combined_status_text = (!empty($combined_status_text) ? $combined_status_text . ' | ' : '') . $txn_status_text;
            }

            $results[] = [
                'category' => __('lang_v1.purchase_order'),
                'type' => __('lang_v1.purchase_order'),
                'title' => ($po_contact_display ? $po_contact_display . ' | ' : '') . $po->ref_no,
                'subtitle' => __('lang_v1.total_purchase') . ': ' . $this->transactionUtil->num_f($po->final_total, true),
                'display_date' => $this->transactionUtil->format_date($po->transaction_date ?? $po->created_at ?? null, true),
                'url' => action([\App\Http\Controllers\PurchaseOrderController::class, 'show'], [$po->id]),
                'is_modal' => true,
                'data_container' => '.view_modal',
                'status' => $payment_status,
                'status_text' => $combined_status_text,
                'status_badge_class' => $po_badge_class,
                'status_text_class' => $status_text_class
            ];
        }

        return $results;
    }

    /**
     * Search Stock Transfers
     */
    protected function searchStockTransfers($query, $business_id)
    {
        $results = [];

        $transfers = Transaction::where('business_id', $business_id)
            ->where('type', 'sell_transfer')
            ->where(function($q) use ($query) {
                $q->where('ref_no', 'like', "%{$query}%");
            })
            ->limit($this->limit)
            ->get();

        foreach ($transfers as $transfer) {
            $txn_status = $transfer->status ?? '';
            $txn_badge_class = '';
            $txn_text_class = '';

            if (!empty($txn_status) && in_array($txn_status, ['pending', 'draft'])) {
                $txn_badge_class = 'tw-bg-blue-600 tw-text-white';
                $txn_text_class = 'tw-text-blue-600';
            } elseif (!empty($txn_status) && in_array($txn_status, ['quotation', 'proforma'])) {
                $txn_badge_class = 'tw-bg-primary-800 tw-text-white';
                $txn_text_class = 'tw-text-primary-800';
            }

            $results[] = [
                'category' => __('lang_v1.stock_transfers'),
                'type' => __('lang_v1.stock_transfer'),
                'title' => $transfer->ref_no,
                'subtitle' => __('lang_v1.total_amount') . ': ' . $this->transactionUtil->num_f($transfer->final_total, true),
                'display_date' => $this->transactionUtil->format_date($transfer->transaction_date ?? $transfer->created_at ?? null, true),
                'url' => action([\App\Http\Controllers\StockTransferController::class, 'show'], [$transfer->id]),
                'is_modal' => true,
                'data_container' => '.view_modal',
                'txn_status' => $txn_status,
                'txn_status_text' => $this->getTxnStatusText($txn_status),
                'txn_badge_class' => $txn_badge_class,
                'txn_text_class' => $txn_text_class
            ];
        }

        return $results;
    }

    /**
     * Search Stock Adjustments
     */
    protected function searchStockAdjustments($query, $business_id)
    {
        $results = [];

        $adjustments = Transaction::where('business_id', $business_id)
            ->where('type', 'stock_adjustment')
            ->where(function($q) use ($query) {
                $q->where('ref_no', 'like', "%{$query}%");
            })
            ->limit($this->limit)
            ->get();

        foreach ($adjustments as $adjustment) {
            $txn_status = $adjustment->status ?? '';
            $txn_badge_class = '';
            $txn_text_class = '';

            if (!empty($txn_status) && in_array($txn_status, ['pending', 'draft'])) {
                $txn_badge_class = 'tw-bg-blue-600 tw-text-white';
                $txn_text_class = 'tw-text-blue-600';
            }

            $results[] = [
                'category' => __('stock_adjustment.stock_adjustment'),
                'type' => __('stock_adjustment.stock_adjustment'),
                'title' => $adjustment->ref_no,
                'subtitle' => __('lang_v1.total_amount') . ': ' . $this->transactionUtil->num_f($adjustment->final_total, true),
                'display_date' => $this->transactionUtil->format_date($adjustment->transaction_date ?? $adjustment->created_at ?? null, true),
                'url' => action([\App\Http\Controllers\StockAdjustmentController::class, 'show'], [$adjustment->id]),
                'is_modal' => true,
                'data_container' => '.view_modal',
                'txn_status' => $txn_status,
                'txn_status_text' => $this->getTxnStatusText($txn_status),
                'txn_badge_class' => $txn_badge_class,
                'txn_text_class' => $txn_text_class
            ];
        }

        return $results;
    }

    /**
     * Search Sale Returns
     */
    protected function searchSaleReturns($query, $business_id)
    {
        $results = [];

        $sale_returns = Transaction::where('transactions.business_id', $business_id)
            ->where('transactions.type', 'sell_return')
            ->where(function($q) use ($query) {
                $q->where('transactions.ref_no', 'like', "%{$query}%")
                  ->orWhere('transactions.invoice_no', 'like', "%{$query}%");
            })
            ->with(['contact', 'payment_lines'])
            ->leftJoin('transactions as T1', 'transactions.return_parent_id', '=', 'T1.id')
            ->select('transactions.*', 'T1.invoice_no as parent_invoice_no', 'T1.id as parent_sale_id')
            ->limit($this->limit)
            ->get();

        foreach ($sale_returns as $return) {
            $return_payment_status = $return->payment_status ?? '';
            $return_badge_class = '';
            $status_text_class = '';
            $status_text = '';

            if (!empty($return_payment_status)) {
                if ($return_payment_status == 'paid') {
                    $return_badge_class = 'tw-bg-green-600 tw-text-white';
                    $status_text_class = 'tw-text-green-600';
                    $status_text = __('lang_v1.paid');
                } elseif ($return_payment_status == 'partial') {
                    $return_badge_class = 'tw-bg-yellow-500 tw-text-white';
                    $status_text_class = 'tw-text-yellow-500';
                    $status_text = __('lang_v1.partial');
                } elseif ($return_payment_status == 'due') {
                    $return_badge_class = 'tw-bg-red-600 tw-text-white';
                    $status_text_class = 'tw-text-red-600';
                    $status_text = __('lang_v1.due');
                }
            }

            $contact_display = '';
            if (!empty($return->contact)) {
                $contact_display = $return->contact->supplier_business_name ?: $return->contact->name ?: ($return->contact->contact_id ?? '');
            }

            $subtitle = '';
            if (!empty($return->parent_invoice_no)) {
                $subtitle .= __('lang_v1.parent_sale') . ': ' . $return->parent_invoice_no . ' | ';
            }
            $subtitle .= __('lang_v1.total_return') . ': ' . $this->transactionUtil->num_f($return->final_total, true);

            $results[] = [
                'category' => __('lang_v1.sell_return'),
                'type' => __('lang_v1.sell_return'),
                'title' => ($contact_display ? $contact_display . ' | ' : '') . $return->invoice_no,
                'subtitle' => $subtitle,
                'display_date' => $this->transactionUtil->format_date($return->transaction_date ?? $return->created_at ?? null, true),
                'url' => action([\App\Http\Controllers\SellReturnController::class, 'show'], [!empty($return->parent_sale_id) ? $return->parent_sale_id : $return->id]),
                'is_modal' => true,
                'data_container' => '.view_modal',
                'status' => $return_payment_status,
                'status_text' => $status_text,
                'status_badge_class' => $return_badge_class,
                'status_text_class' => $status_text_class
            ];
        }

        return $results;
    }

    /**
     * Search Purchase Returns
     */
    protected function searchPurchaseReturns($query, $business_id)
    {
        $results = [];

        $purchase_returns = Transaction::where('transactions.business_id', $business_id)
            ->where('transactions.type', 'purchase_return')
            ->where(function($q) use ($query) {
                $q->where('transactions.ref_no', 'like', "%{$query}%")
                  ->orWhere('transactions.invoice_no', 'like', "%{$query}%");
            })
            ->with(['contact', 'payment_lines'])
            ->leftJoin('transactions as T2', 'transactions.return_parent_id', '=', 'T2.id')
            ->select('transactions.*', 'T2.ref_no as parent_purchase_no', 'T2.id as parent_purchase_id')
            ->limit($this->limit)
            ->get();

        foreach ($purchase_returns as $return) {
            $return_payment_status = $return->payment_status ?? '';
            $return_badge_class = '';
            $status_text_class = '';
            $status_text = '';

            if (!empty($return_payment_status)) {
                if ($return_payment_status == 'paid') {
                    $return_badge_class = 'tw-bg-green-600 tw-text-white';
                    $status_text_class = 'tw-text-green-600';
                    $status_text = __('lang_v1.paid');
                } elseif ($return_payment_status == 'partial') {
                    $return_badge_class = 'tw-bg-yellow-500 tw-text-white';
                    $status_text_class = 'tw-text-yellow-500';
                    $status_text = __('lang_v1.partial');
                } elseif ($return_payment_status == 'due') {
                    $return_badge_class = 'tw-bg-red-600 tw-text-white';
                    $status_text_class = 'tw-text-red-600';
                    $status_text = __('lang_v1.due');
                }
            }

            $contact_display = '';
            if (!empty($return->contact)) {
                $contact_display = $return->contact->supplier_business_name ?: $return->contact->name ?: ($return->contact->contact_id ?? '');
            }

            $subtitle = '';
            if (!empty($return->parent_purchase_no)) {
                $subtitle .= __('lang_v1.parent_purchase') . ': ' . $return->parent_purchase_no . ' | ';
            }
            $subtitle .= __('lang_v1.total_return') . ': ' . $this->transactionUtil->num_f($return->final_total, true);

            $results[] = [
                'category' => __('lang_v1.purchase_return'),
                'type' => __('lang_v1.purchase_return'),
                'title' => ($contact_display ? $contact_display . ' | ' : '') . $return->ref_no,
                'subtitle' => $subtitle,
                'display_date' => $this->transactionUtil->format_date($return->transaction_date ?? $return->created_at ?? null, true),
                'url' => action([\App\Http\Controllers\PurchaseReturnController::class, 'show'], [!empty($return->parent_purchase_id) ? $return->parent_purchase_id : $return->id]),
                'is_modal' => true,
                'data_container' => '.view_modal',
                'status' => $return_payment_status,
                'status_text' => $status_text,
                'status_badge_class' => $return_badge_class,
                'status_text_class' => $status_text_class
            ];
        }

        return $results;
    }

    /**
     * Search Repair Job Sheets (if module exists)
     */
    protected function searchRepairJobSheets($query, $business_id)
    {
        $results = [];

        if (!class_exists('\\Modules\\Repair\\Entities\\JobSheet')) {
            return $results;
        }

        $job_sheets = \Modules\Repair\Entities\JobSheet::join('contacts', 'repair_job_sheets.contact_id', '=', 'contacts.id')
            ->join('repair_statuses', 'repair_job_sheets.status_id', '=', 'repair_statuses.id')
            ->leftJoin('brands', 'repair_job_sheets.brand_id', '=', 'brands.id')
            ->leftJoin('repair_device_models', 'repair_job_sheets.device_model_id', '=', 'repair_device_models.id')
            ->where('repair_job_sheets.business_id', $business_id)
            ->where(function($q) use ($query) {
                $q->where('repair_job_sheets.job_sheet_no', 'like', "%{$query}%")
                  ->orWhere('contacts.name', 'like', "%{$query}%")
                  ->orWhere('contacts.mobile', 'like', "%{$query}%")
                  ->orWhere('repair_job_sheets.serial_no', 'like', "%{$query}%");
            })
            ->select(
                'repair_job_sheets.*',
                'contacts.name as customer_name',
                'contacts.mobile as customer_mobile',
                'repair_statuses.name as status_name',
                'repair_statuses.color as status_color',
                'brands.name as brand_name',
                'repair_device_models.name as device_model_name'
            )
            ->limit($this->limit)
            ->get();

        foreach ($job_sheets as $job_sheet) {
            $subtitleParts = [];
            if (!empty($job_sheet->customer_mobile)) {
                $subtitleParts[] = $job_sheet->customer_mobile;
            }
            if (!empty($job_sheet->brand_name)) {
                $subtitleParts[] = $job_sheet->brand_name;
            }
            if (!empty($job_sheet->device_model_name)) {
                $subtitleParts[] = $job_sheet->device_model_name;
            }
            if (!empty($job_sheet->serial_no)) {
                $subtitleParts[] = __('repair::lang.serial_no') . ': ' . $job_sheet->serial_no;
            }
            $subtitle = implode(' | ', $subtitleParts);

            $status_badge_class = '';
            $status_text_class = '';
            if (!empty($job_sheet->status_color)) {
                $color_map = [
                    '#28a745' => 'tw-bg-green-600 tw-text-white',
                    '#dc3545' => 'tw-bg-red-600 tw-text-white',
                    '#007bff' => 'tw-bg-blue-600 tw-text-white',
                    '#ffc107' => 'tw-bg-yellow-500 tw-text-white',
                    '#fd7e14' => 'tw-bg-orange-500 tw-text-white',
                    '#6c757d' => 'tw-bg-gray-600 tw-text-white',
                ];
                $status_badge_class = $color_map[strtolower($job_sheet->status_color)] ?? 'tw-bg-gray-600 tw-text-white';

                $text_color_map = [
                    '#28a745' => 'tw-text-green-600',
                    '#dc3545' => 'tw-text-red-600',
                    '#007bff' => 'tw-text-blue-600',
                    '#ffc107' => 'tw-text-yellow-600',
                    '#fd7e14' => 'tw-text-orange-500',
                    '#6c757d' => 'tw-text-gray-600',
                ];
                $status_text_class = $text_color_map[strtolower($job_sheet->status_color)] ?? 'tw-text-gray-600';
            }

            $results[] = [
                'category' => __('repair::lang.repair'),
                'type' => __('repair::lang.job_sheet'),
                'title' => ($job_sheet->customer_name ? $job_sheet->customer_name . ' | ' : '') . $job_sheet->job_sheet_no,
                'subtitle' => $subtitle,
                'url' => action(['\\Modules\\Repair\\Http\\Controllers\\JobSheetController', 'show'], [$job_sheet->id]),
                'is_modal' => true,
                'data_container' => '.view_modal',
                'status' => $job_sheet->status_name,
                'status_text' => $job_sheet->status_name,
                'status_badge_class' => $status_badge_class,
                'status_text_class' => $status_text_class
            ];
        }

        return $results;
    }
}
