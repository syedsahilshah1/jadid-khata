@extends('layouts.guest')
@section('title', $business->name)

@push('meta')
@php
    $meta_address_parts = array_filter([
        $business_location->landmark ?? null,
        $business_location->city ?? null,
        $business_location->state ?? null,
        ($business_location->zip_code ?? '') . ($business_location->country ?? ''),
    ]);
    $meta_description = 'Browse the product catalogue of ' . $business->name;
    if (!empty($meta_address_parts)) {
        $meta_description .= ' at ' . $business->name . ', ' . implode(', ', $meta_address_parts);
    }
    $meta_description .= '.';
    $meta_logo_url = !empty($business->logo) ? asset('uploads/business_logos/' . $business->logo) : '';
@endphp
<meta name="description" content="{{ $meta_description }}">
<meta property="og:type" content="website">
<meta property="og:site_name" content="{{ $business->name }}">
<meta property="og:title" content="{{ $business->name }} — {{ $business->name }}">
<meta property="og:description" content="{{ $meta_description }}">
<meta property="og:url" content="{{ url()->current() }}">
@if($meta_logo_url)
<meta property="og:image" content="{{ $meta_logo_url }}">
<meta property="og:image:alt" content="{{ $business->name }} logo">
@endif
@endpush

@section('content')
<style>
.cart-icon-link {
    position: relative;
    overflow: visible;
    /* allow badge to sit outside without covering icon */
}

.cart-badge {
    position: absolute;
    top: 0;
    right: 0;
    transform: translate(40%, -40%);
    z-index: 2;
    min-width: 18px;
    padding: 0 5px;
    border-radius: 9999px;
    font-size: 11px;
    height: 18px;
    line-height: 18px;
    text-align: center;
    pointer-events: none;
}

#send-order-whatsapp-btn {
    background-color: #25D366;
    border-color: #25D366;
}

#send-order-whatsapp-btn:hover {
    background-color: #20BA5A;
    border-color: #20BA5A;
}

/* Quantity selector alignment */
/* Cart total amount highlight */
.cart-total-amount {
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    padding: 4px 8px;
    font-weight: 600;
    color: #28a745 !important;
}

/* Hide number input spinners */
.product-quantity::-webkit-inner-spin-button,
.product-quantity::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

.product-quantity[type=number] {
    -moz-appearance: textfield;
}

/* Ensure input-group works with flex and prevents stacking */
.input-group {
    display: inline-flex !important;
    flex-direction: row !important;
    align-items: stretch !important;
}

.input-group .btn-minus,
.input-group .btn-plus,
.input-group .product-quantity {
    display: inline-flex !important;
    vertical-align: top !important;
}

/* Smooth fade-in animation for product cards */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.tw-animate-fade-in {
    animation: fadeIn 0.35s ease-out forwards;
}

/* Premium-calm: avoid stagger/bounce animations */

/* Premium-calm: avoid scaling interactions on steppers */

/* Product card images: fixed-height container, image shown in full (contain) like the POS screen */
.product-box .product-image-wrap {
    height: 9rem !important;
    min-height: 9rem !important;
    max-height: 9rem !important;
    overflow: hidden !important;
    flex: 0 0 auto;
    display: flex !important;
    align-items: center;
    justify-content: center;
    background: #f8f9fa;
}

@media (min-width: 640px) {
    .product-box .product-image-wrap {
        height: 12rem !important;
        min-height: 12rem !important;
        max-height: 12rem !important;
    }
}

.product-box .product-image-wrap>a {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
    padding: 6px;
}

.product-box .product-image-wrap img {
    display: block;
    max-width: 100%;
    max-height: 100%;
    width: auto;
    height: auto;
    object-fit: contain;
}

/* Card hover lift handled via utility classes */

/* Hide number input spinners for cart quantity */
.cart-item-quantity::-webkit-inner-spin-button,
.cart-item-quantity::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

.cart-item-quantity[type=number] {
    -moz-appearance: textfield;
}

/* DaisyUI Input Group Styling - Clear and Visible */
.tw-dw-input-group {
    display: flex;
    align-items: stretch;
    width: 100%;
}

.tw-dw-input-group>span {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0 1.25rem;
    border-radius: 0.75rem 0 0 0.75rem;
    min-width: 3.5rem;
    height: 3rem;
    flex-shrink: 0;
}

.tw-dw-input-group>.tw-dw-input,
.tw-dw-input-group>.tw-dw-textarea {
    border-left: none !important;
    border-radius: 0 0.75rem 0.75rem 0 !important;
    flex: 1;
    padding-left: 1rem;
    padding-right: 1rem;
}

.tw-dw-input-group>.tw-dw-textarea {
    border-top-left-radius: 0 !important;
    padding-top: 0.75rem;
}

.tw-dw-input-group.tw-items-start>span {
    align-items: flex-start;
    padding-top: 0.875rem;
    height: auto;
    min-height: 100px;
}

.tw-dw-input-group.tw-items-start>.tw-dw-textarea {
    border-top-left-radius: 0 !important;
}

/* Ensure input borders are visible */
.tw-dw-input-group .tw-dw-input:focus,
.tw-dw-input-group .tw-dw-textarea:focus {
    border-color: rgb(59 130 246) !important;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1) !important;
}

/* Horizontal chips nav: hide scrollbar (minimal look) */
.catalogue-search-inner {
    width: 100%;
}

@media (min-width: 768px) {
    .catalogue-search-inner {
        width: 50%;
    }
}

.no-scrollbar::-webkit-scrollbar {
    display: none;
}

.no-scrollbar {
    -ms-overflow-style: none;
    /* IE and Edge */
    scrollbar-width: none;
    /* Firefox */
}

/* Chips nav subtle state when scrolled */
#catalogueNav.is-scrolled {
    background: rgba(255, 255, 255, 0.92);
    border-color: rgba(229, 231, 235, 1);
    box-shadow: 0 1px 0 rgba(17, 24, 39, 0.04);
}

/*
      Fixed fallback + z-index discipline:
      Bootstrap modal backdrop is ~1040 and modal is ~1050.
      Keep catalogue nav below those so it never overlays modals.
    */
#catalogueNavWrap.is-fixed {
    position: fixed !important;
    top: 0;
    left: 0;
    right: 0;
    z-index: 1030;
}

/* =========================================================
   Mobile Categories Drawer (off-canvas)
   ========================================================= */
.mobile-cat-drawer {
    position: fixed;
    inset: 0;
    z-index: 1050;
    visibility: hidden;
    pointer-events: none;
}

.mobile-cat-drawer.is-open {
    visibility: visible;
    pointer-events: auto;
}

.mobile-cat-backdrop {
    position: absolute;
    inset: 0;
    background: rgba(17, 24, 39, 0.55);
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.mobile-cat-drawer.is-open .mobile-cat-backdrop {
    opacity: 1;
}

.mobile-cat-panel {
    position: absolute;
    top: 0;
    left: 0;
    height: 100%;
    width: 84%;
    max-width: 340px;
    background: #ffffff;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    border-top-right-radius: 1.25rem;
    border-bottom-right-radius: 1.25rem;
    transform: translateX(-100%);
    transition: transform 0.3s cubic-bezier(0.22, 1, 0.36, 1);
    display: flex;
    flex-direction: column;
    outline: none;
}

.mobile-cat-drawer.is-open .mobile-cat-panel {
    transform: translateX(0);
}

body.mobile-cat-open {
    overflow: hidden;
}

/* =========================================================
   Out-of-stock product card — fully greyed out
   ========================================================= */
.product-box.is-out-of-stock {
    background: #f9fafb;
    /* gray-50 */
    border-color: #e5e7eb !important;
    /* lock border to gray-200, block primary-300 hover */
}

/* Desaturate and mute both image and body, but leave the
   red "Out of stock" badge in its own stacking context */
.product-box.is-out-of-stock .product-image-wrap>a,
.product-box.is-out-of-stock .tw-dw-card-body {
    filter: grayscale(1);
    -webkit-filter: grayscale(1);
    opacity: 0.65;
}

/* Kill the lift + image zoom hover effects when inert */
.product-box.is-out-of-stock:hover {
    transform: none !important;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04) !important;
}

.product-box.is-out-of-stock .product-image-wrap img:hover {
    transform: none !important;
}

.product-box.is-out-of-stock .show-product-details {
    cursor: not-allowed;
}

/* =========================================================
   Cart Drawer (off-canvas, slides from right)
   Mirrors .mobile-cat-drawer so both drawers behave identically.
   ========================================================= */
.cart-drawer {
    position: fixed;
    inset: 0;
    z-index: 1060;
    visibility: hidden;
    pointer-events: none;
}

.cart-drawer.is-open {
    visibility: visible;
    pointer-events: auto;
}

.cart-backdrop {
    position: absolute;
    inset: 0;
    background: rgba(17, 24, 39, 0.55);
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.cart-drawer.is-open .cart-backdrop {
    opacity: 1;
}

.cart-panel {
    position: absolute;
    top: 0;
    right: 0;
    height: 100%;
    width: 100%;
    background: #ffffff;
    box-shadow: -25px 0 50px -12px rgba(0, 0, 0, 0.25);
    transform: translateX(100%);
    transition: transform 0.3s cubic-bezier(0.22, 1, 0.36, 1);
    display: flex;
    flex-direction: column;
    outline: none;
}

@media (min-width: 1024px) {
    .cart-panel {
        width: 50%;
    }
}

.cart-drawer.is-open .cart-panel {
    transform: translateX(0);
}

body.cart-open {
    overflow: hidden;
}
</style>
        <!-- Content Header (Page header) -->
        <section
            class="tw-py-1.5 sm:tw-py-4 tw-px-3 sm:tw-px-4 tw-bg-gradient-to-br tw-from-primary-50 tw-via-white tw-to-gray-50 tw-shadow-sm"
            id="top">
            <div class="tw-max-w-7xl tw-mx-auto">
                {{-- Mobile: logo + two-line text (name over location) --}}
                <div class="sm:tw-hidden tw-rounded-2xl tw-border tw-border-gray-200/70 tw-bg-white/70 tw-backdrop-blur tw-shadow-[0_6px_18px_rgba(0,0,0,0.06)] tw-px-3 tw-py-2">
                    <div class="tw-flex tw-items-center tw-gap-3 tw-min-w-0">
                        @if(!empty($business->logo))
                        <img src="{{ asset('uploads/business_logos/' . $business->logo) }}"
                            alt="{{ $business->name }} logo"
                            class="tw-flex-shrink-0 tw-w-10 tw-h-10 tw-object-contain tw-rounded-full tw-bg-white tw-shadow-sm tw-ring-1 tw-ring-white">
                        @else
                        <div class="tw-flex-shrink-0 tw-w-10 tw-h-10 tw-rounded-full tw-bg-white tw-shadow-sm tw-ring-1 tw-ring-white tw-flex tw-items-center tw-justify-center">
                            <i class="fas fa-store tw-text-lg tw-text-primary-700"></i>
                        </div>
                        @endif
                        <div class="tw-min-w-0 tw-flex-1">
                            <h2 class="tw-m-0 tw-text-base tw-font-extrabold tw-text-gray-900 tw-tracking-tight tw-leading-tight tw-truncate">{{$business->name}}</h2>
                            @php
                                // Replace line-break tags with a space BEFORE strip_tags so "85001<br>USA" becomes "85001 USA"
                                $mobileAddr = preg_replace('/<(br|\/p|\/div)\b[^>]*>/i', ' ', $business_location->location_address ?? '');
                                $mobileAddr = trim(strip_tags($mobileAddr));
                                $mobileAddr = preg_replace('/\s+/', ' ', $mobileAddr);
                            @endphp
                            <div class="tw-flex tw-items-center tw-gap-1 tw-text-[11px] tw-text-gray-600 tw-leading-tight tw-mt-0.5">
                                <i class="fas fa-map-marker-alt tw-text-primary-600 tw-text-[10px] tw-flex-shrink-0"></i>
                                <span class="tw-truncate tw-min-w-0">
                                    <span class="tw-font-semibold tw-text-gray-800">{{$business_location->name}}</span>@if($mobileAddr)<span class="tw-text-gray-500"> · {{ $mobileAddr }}</span>@endif
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Tablet / Desktop: identity (logo + name) left, location right, with hairline divider --}}
                <div class="tw-hidden sm:tw-block tw-rounded-2xl tw-border tw-border-gray-200/70 tw-bg-white/70 tw-backdrop-blur tw-shadow-[0_1px_2px_rgba(0,0,0,0.04)] tw-ring-1 tw-ring-gray-900/5 tw-px-5 tw-py-3">
                    <div class="tw-flex tw-flex-col md:tw-flex-row tw-items-center tw-gap-4 md:tw-gap-6">
                        <!-- Identity: logo + name as one unit -->
                        <div class="tw-flex tw-items-center tw-gap-4 md:tw-gap-5 tw-min-w-0 md:tw-flex-1 tw-w-full md:tw-w-auto tw-justify-center md:tw-justify-start">
                            <div class="tw-flex-shrink-0">
                                @if(!empty($business->logo))
                                <img src="{{ asset('uploads/business_logos/' . $business->logo) }}"
                                    alt="{{ $business->name }} logo"
                                    class="tw-w-16 md:tw-w-20 tw-h-16 md:tw-h-20 tw-object-contain tw-rounded-full tw-bg-white tw-ring-1 tw-ring-gray-200">
                                @else
                                <div
                                    class="tw-w-16 md:tw-w-20 tw-h-16 md:tw-h-20 tw-rounded-full tw-bg-white tw-ring-1 tw-ring-gray-200 tw-flex tw-items-center tw-justify-center">
                                    <i class="fas fa-store tw-text-2xl tw-text-primary-700"></i>
                                </div>
                                @endif
                            </div>
                            <h2
                                class="tw-m-0 tw-text-2xl md:tw-text-3xl lg:tw-text-4xl tw-font-bold tw-text-gray-900 tw-tracking-tight tw-leading-tight tw-truncate">
                                {{$business->name}}
                            </h2>
                        </div>

                        <!-- Vertical hairline divider (md+ only) -->
                        <div class="tw-hidden md:tw-block tw-w-px tw-bg-gray-200 tw-self-stretch tw-flex-shrink-0" aria-hidden="true"></div>

                        <!-- Location block -->
                        <div class="tw-text-center md:tw-text-right tw-min-w-0 md:tw-max-w-[40%]">
                            <div
                                class="tw-inline-flex tw-items-center tw-justify-center md:tw-justify-end tw-gap-2 tw-text-base md:tw-text-lg tw-font-semibold tw-text-gray-800">
                                <i class="fas fa-map-marker-alt tw-text-primary-600"></i>
                                {{$business_location->name}}
                            </div>
                            <div
                                class="tw-mt-0.5 tw-text-xs md:tw-text-sm tw-text-gray-500 tw-leading-snug tw-whitespace-nowrap tw-truncate">
                                {{ $mobileAddr }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <section id="catalogueNavWrap" class="no-print tw-bg-white/70 tw-backdrop-blur"
            style="position: sticky; top: 0; z-index: 1030;">
            <div class="tw-max-w-7xl tw-mx-auto tw-px-4">
                <nav id="catalogueNav"
                    class="tw-transition-all tw-duration-200 tw-rounded-2xl tw-border tw-border-gray-200 tw-bg-white/90 tw-backdrop-blur tw-mt-2 tw-mb-2">
                    <div class="tw-flex tw-items-start tw-gap-3 tw-px-3 tw-py-3">
                        <!-- Categories button (mobile only) -->
                        <button type="button" id="mobileCategoriesBtn"
                            class="md:tw-hidden tw-inline-flex tw-items-center tw-gap-2 tw-h-10 tw-px-3 tw-rounded-xl tw-bg-white tw-border tw-border-gray-200 tw-shadow-sm hover:tw-shadow tw-text-sm tw-font-semibold tw-text-gray-700 tw-transition focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-primary-500 focus:tw-ring-offset-2"
                            aria-label="Open categories" aria-controls="mobileCategoriesDrawer" aria-expanded="false">
                            <i class="fas fa-bars tw-text-gray-700"></i>
                            <span>Categories</span>
                        </button>

                        <!-- Category chips (desktop / tablet) -->
                        <div class="tw-hidden md:tw-block tw-flex-1 tw-min-w-0">
                            <div class="tw-flex tw-flex-wrap tw-items-center tw-gap-2 tw-px-1">
                                @foreach($products as $key => $product_category)
                                @php
                                $category_name = $product_category->first()->category->name ?? null;
                                @endphp
                                @if(!empty($category_name))
                                <a href="#category{{$key}}"
                                    class="menu tw-inline-flex tw-items-center tw-h-9 tw-px-3 tw-rounded-full tw-border tw-border-gray-200 tw-bg-white tw-text-sm tw-font-medium tw-text-gray-700 hover:tw-border-gray-300 hover:tw-bg-gray-50 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-primary-500 focus:tw-ring-offset-2 tw-transition">
                                    {{$category_name}}
                                </a>
                                @endif
                                @endforeach
                                <a href="#category0"
                                    class="menu tw-inline-flex tw-items-center tw-h-9 tw-px-3 tw-rounded-full tw-border tw-border-gray-200 tw-bg-white tw-text-sm tw-font-medium tw-text-gray-700 hover:tw-border-gray-300 hover:tw-bg-gray-50 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-primary-500 focus:tw-ring-offset-2 tw-transition">
                                    Uncategorized
                                </a>
                            </div>
                        </div>

                        <!-- Mobile spacer: pushes cart to the right -->
                        <div class="md:tw-hidden tw-flex-1"></div>

                        <!-- Cart -->
                        @if($enable_whatsapp_ordering == 1)
                        <div class="tw-flex tw-items-center tw-gap-3 tw-pl-1">
                            <span class="cart-total-amount" id="cart-total-amount"
                                style="display: none; font-size: 12px; white-space: nowrap;">0</span>
                            <button type="button"
                                class="cart-icon-link tw-cursor-pointer tw-relative tw-inline-flex tw-items-center tw-justify-center tw-w-10 tw-h-10 tw-rounded-xl tw-bg-white tw-border tw-border-gray-200 tw-shadow-sm hover:tw-shadow tw-transition tw-p-0"
                                id="cart-icon" aria-label="Open cart">
                                <i class="fas fa-shopping-cart tw-text-gray-800"></i>
                                <span class="badge badge-danger cart-badge" id="cart-badge"
                                    style="display: none;">0</span>
                            </button>
                        </div>
                        @endif
                    </div>
                </nav>

                <!-- Search row -->
                <div class="tw-pb-2.5 tw-pt-0 catalogue-search-row" style="display:flex; justify-content:center;">
                    <div class="tw-relative tw-group catalogue-search-inner">
                        <span class="tw-absolute tw-inset-y-0 tw-left-3.5 tw-flex tw-items-center tw-pointer-events-none">
                            <svg class="tw-text-gray-400 tw-transition-colors tw-duration-200 group-focus-within:tw-text-primary-500"
                                xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2.2"
                                stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                            </svg>
                        </span>
                        <input type="search" id="catalogue-search"
                            placeholder="Search by product name or SKU…"
                            autocomplete="off"
                            style="-webkit-appearance: none;"
                            class="tw-w-full tw-h-10 tw-pl-10 tw-pr-9 tw-rounded-xl tw-border tw-border-gray-200 tw-bg-white tw-text-sm tw-text-gray-800 tw-shadow-sm tw-placeholder-gray-400 focus:tw-outline-none focus:tw-border-primary-400 focus:tw-ring-2 focus:tw-ring-primary-100 tw-transition-all tw-duration-200">
                        <button type="button" id="catalogue-search-clear"
                            aria-label="Clear search"
                            style="display:none;"
                            class="tw-absolute tw-inset-y-0 tw-right-2 tw-my-auto tw-flex tw-items-center tw-justify-center tw-w-6 tw-h-6 tw-rounded-md tw-text-gray-400 hover:tw-text-gray-700 hover:tw-bg-gray-100 tw-transition tw-border-0 tw-bg-transparent tw-cursor-pointer">
                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2.5"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path d="M18 6 6 18"/><path d="m6 6 12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </section>
        <div id="catalogueNavSpacer" class="no-print" style="display:none;"></div>

        <!-- Mobile Categories Drawer -->
        <div id="mobileCategoriesDrawer" class="mobile-cat-drawer md:tw-hidden no-print" aria-hidden="true" role="dialog" aria-modal="true" aria-label="Categories">
            <div id="mobileCategoriesBackdrop" class="mobile-cat-backdrop"></div>
            <aside id="mobileCategoriesPanel" class="mobile-cat-panel" tabindex="-1">
                <div class="tw-flex tw-items-center tw-justify-between tw-px-5 tw-py-4 tw-border-b tw-border-gray-100 tw-bg-gradient-to-r tw-from-primary-50 tw-via-white tw-to-white">
                    <div class="tw-flex tw-items-center tw-gap-3">
                        <span class="tw-inline-flex tw-items-center tw-justify-center tw-w-10 tw-h-10 tw-rounded-xl tw-bg-primary-100 tw-text-primary-700">
                            <i class="fas fa-th-large"></i>
                        </span>
                        <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900 tw-m-0">Categories</h3>
                    </div>
                    <button type="button" id="mobileCategoriesClose"
                        class="tw-inline-flex tw-items-center tw-justify-center tw-w-9 tw-h-9 tw-rounded-full tw-bg-gray-100 hover:tw-bg-gray-200 tw-text-gray-700 tw-transition focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-primary-500"
                        aria-label="Close categories">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <nav class="tw-flex-1 tw-overflow-y-auto tw-py-2">
                    @foreach($products as $key => $product_category)
                    @php
                    $mc_name = $product_category->first()->category->name ?? null;
                    @endphp
                    @if(!empty($mc_name))
                    <a href="#category{{$key}}"
                        class="menu mobile-category-link tw-flex tw-items-center tw-gap-3 tw-px-5 tw-py-3 tw-text-gray-700 hover:tw-bg-primary-50 hover:tw-text-primary-700 tw-border-l-4 tw-border-transparent hover:tw-border-primary-500 tw-transition">
                        <span class="tw-flex tw-items-center tw-justify-center tw-w-9 tw-h-9 tw-rounded-lg tw-bg-primary-50 tw-text-primary-600">
                            <i class="fas fa-tag"></i>
                        </span>
                        <span class="tw-font-medium tw-flex-1 tw-truncate">{{$mc_name}}</span>
                        <i class="fas fa-chevron-right tw-text-gray-400"></i>
                    </a>
                    @endif
                    @endforeach
                    <a href="#category0"
                        class="menu mobile-category-link tw-flex tw-items-center tw-gap-3 tw-px-5 tw-py-3 tw-text-gray-700 hover:tw-bg-primary-50 hover:tw-text-primary-700 tw-border-l-4 tw-border-transparent hover:tw-border-primary-500 tw-transition">
                        <span class="tw-flex tw-items-center tw-justify-center tw-w-9 tw-h-9 tw-rounded-lg tw-bg-primary-50 tw-text-primary-600">
                            <i class="fas fa-tag"></i>
                        </span>
                        <span class="tw-font-medium tw-flex-1 tw-truncate">Uncategorized</span>
                        <i class="fas fa-chevron-right tw-text-gray-400"></i>
                    </a>
                </nav>
            </aside>
        </div>

        <!-- Main content -->
        <section class="content tw-pt-0 tw-bg-gray-50">
            <div class="tw-max-w-7xl tw-mx-auto tw-px-4 tw-pt-1 sm:tw-pt-2 tw-pb-3 sm:tw-pb-8">
                @foreach($products as $product_category)
                <div class="product-category-section">
                <!-- Category Header -->
                <div class="tw-mb-3 tw-mt-6 sm:tw-mb-4 sm:tw-mt-8 first:tw-mt-0">
                    <h2 class="tw-text-lg sm:tw-text-2xl md:tw-text-3xl tw-font-bold tw-text-gray-900 tw-border-l-4 tw-border-primary-500 tw-pl-3 tw-leading-tight tw-m-0"
                        id="category{{$product_category->first()->category->id ?? 0}}">
                        {{$product_category->first()->category->name ?? 'Uncategorized'}}
                    </h2>
                </div>

                <!-- Product Grid -->
                <div
                    class="tw-grid tw-grid-cols-1 sm:tw-grid-cols-2 lg:tw-grid-cols-4 xl:tw-grid-cols-4 2xl:tw-grid-cols-4 tw-gap-3 sm:tw-gap-4 lg:tw-gap-5 tw-mb-6 sm:tw-mb-10">
                    @foreach($product_category as $product)
                    @php

                    $max_price = $product->variations->max('sell_price_inc_tax');
                    $min_price = $product->variations->min('sell_price_inc_tax');
                    $is_out_of_stock = ($product->stock < 0 || empty($product->stock)) && $product->enable_stock;
                        @endphp

                        <!-- Product Card -->
                        <div class="product-card-wrapper"
                            data-name="{{ strtolower($product->name) }}"
                            data-sku="{{ strtolower($product->sku ?? '') }}"
                            data-sub-sku="{{ strtolower($product->variations->pluck('sub_sku')->implode(' ')) }}">
                        <div
                            class="tw-dw-card tw-bg-base-100 tw-shadow-sm hover:tw-shadow-md tw-transition-all tw-duration-200 tw-rounded-xl sm:tw-rounded-2xl tw-overflow-hidden tw-border tw-border-gray-200 hover:tw-border-primary-300 product-box tw-flex tw-flex-col tw-animate-fade-in tw-transform hover:-tw-translate-y-0.5 @if($is_out_of_stock) is-out-of-stock @endif">

                            <!-- Product Image -->
                            <div
                                class="product-image-wrap tw-relative tw-bg-gray-50 tw-h-[9rem] sm:tw-h-[12rem] tw-overflow-hidden">
                                <a href="#" class="show-product-details tw-block tw-w-full tw-h-full"
                                    aria-label="{{ $product->name }}"
                                    data-href="{{action([\Modules\ProductCatalogue\Http\Controllers\ProductCatalogueController::class, 'show'],  [$business->id, $product->id])}}?location_id={{$business_location->id}}">
                                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" loading="lazy"
                                        onerror="this.onerror=null;this.src='{{ asset('/img/default.png') }}';"
                                        style="max-height: 100%; max-width: 100%; object-fit: contain; width: auto; height: auto;"
                                        class="tw-block">
                                </a>

                                @if($is_out_of_stock)
                                <span
                                    class="tw-absolute tw-top-3 tw-left-3 tw-inline-flex tw-items-center tw-rounded-full tw-bg-red-600 tw-text-white tw-text-xs tw-font-bold tw-px-3 tw-py-1 tw-shadow">
                                    @lang('productcatalogue::lang.out_of_stock')
                                </span>
                                @endif
                            </div>

                            <!-- Card Body -->
                            <div class="tw-dw-card-body tw-flex-1 tw-flex tw-flex-col tw-p-3 sm:tw-p-4 lg:tw-p-5 tw-bg-white">
                                <!-- Product Title -->
                                <h2
                                    class="tw-dw-card-title tw-text-lg tw-font-semibold tw-text-gray-900 tw-mb-2 sm:tw-mb-3 tw-line-clamp-2 tw-min-h-[2.75rem] sm:tw-min-h-[3.5rem] tw-group/title">
                                    <a href="#"
                                        class="show-product-details tw-text-gray-900 hover:tw-text-primary-600 tw-transition-all tw-duration-200 tw-no-underline tw-inline-block tw-transform hover:tw-translate-x-1"
                                        data-href="{{action([\Modules\ProductCatalogue\Http\Controllers\ProductCatalogueController::class, 'show'],  [$business->id, $product->id])}}?location_id={{$business_location->id}}">
                                        {{$product->name}}
                                    </a>
                                </h2>

                                <!-- Product Info -->
                                <div class="tw-space-y-3 tw-mb-4 tw-pr-1">
                                    <!-- Price -->
                                    <div class="tw-flex tw-items-baseline tw-gap-2 tw-flex-wrap">
                                        <span
                                            class="display_currency tw-text-2xl tw-font-bold tw-text-primary-600 tw-tracking-tight"
                                            data-currency_symbol="true" data-show_code="true">{{($max_price)}}</span>
                                        @if($max_price != $min_price)
                                        <span class="tw-text-gray-300 tw-text-lg">–</span>
                                        <span
                                            class="display_currency tw-text-2xl tw-font-bold tw-text-primary-600 tw-tracking-tight"
                                            data-currency_symbol="true" data-show_code="true">{{($min_price)}}</span>
                                        @endif
                                    </div>

                                    <!-- SKU -->
                                    <div class="tw-hidden md:tw-flex tw-items-center tw-gap-2 tw-text-xs">
                                        <span class="tw-text-gray-400 tw-font-medium">
                                            <i class="fas fa-barcode tw-mr-1 tw-text-gray-400"></i>@lang('product.sku'):
                                        </span>
                                        <span
                                            class="tw-text-gray-700 tw-font-mono tw-bg-white tw-px-2 tw-py-1 tw-rounded tw-border tw-border-gray-200">{{$product->sku}}</span>
                                    </div>

                                    <!-- Variable Product Variations -->
                                    @if($product->type == 'variable')
                                    @php
                                    $variations = $product->variations->groupBy('product_variation_id');
                                    @endphp
                                    @foreach($variations as $product_variation)
                                    <div class="tw-mt-3">
                                        <label
                                            class="tw-text-xs tw-text-gray-600 tw-font-semibold tw-block tw-mb-2 tw-uppercase tw-tracking-wide">
                                            {{$product_variation->first()->product_variation->name}}
                                        </label>
                                        <div class="tw-relative">
                                            <select
                                                class="tw-w-full tw-px-3 tw-py-2 tw-pr-10 tw-text-sm tw-font-medium tw-text-gray-900 tw-bg-white tw-border tw-border-gray-200 tw-rounded-lg tw-shadow-none focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-primary-500 focus:tw-border-primary-500 tw-transition-all tw-cursor-pointer hover:tw-border-gray-300 tw-appearance-none tw-bg-no-repeat tw-bg-right tw-bg-[length:1.25em_1.25em] tw-bg-[right_0.75rem_center]"
                                                style="background-image: url('data:image/svg+xml;charset=UTF-8,%3csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 24 24%27 fill=%27none%27 stroke=%27%23374151%27 stroke-width=%272%27 stroke-linecap=%27round%27 stroke-linejoin=%27round%27%3e%3cpolyline points=%276 9 12 15 18 9%27%3e%3c/polyline%3e%3c/svg%3e');">
                                                @foreach($product_variation as $variation)
                                                <option value="{{$variation->id}}">
                                                    {{$variation->name}} ({{$variation->sub_sku}}) - <span
                                                        class="display_currency"
                                                        data-currency_symbol="true" data-show_code="true">{{($variation->sell_price_inc_tax)}}</span>
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    @endforeach
                                    @endif
                                </div>

                                <!-- Quantity Controls or View Options Button -->
                                @if($product->type == 'single' || $product->type == 'combo')
                                @if($enable_whatsapp_ordering == 1)
                                @php
                                $variation = $product->variations->first();
                                @endphp
                                <div class="add-to-cart-section tw-mt-auto tw-pt-4 tw-flex tw-justify-center">
                                    <!-- Quantity Control Button Group -->
                                    <div
                                        class="input-group tw-inline-flex tw-items-stretch tw-justify-center tw-gap-0 tw-bg-white tw-rounded-lg tw-p-1 tw-border tw-border-gray-200 tw-shadow-sm hover:tw-shadow-md tw-transition-all tw-duration-200">
                                        <button
                                            class="btn-minus tw-inline-flex tw-items-center tw-justify-center tw-w-9 tw-h-9 sm:tw-w-10 sm:tw-h-10 tw-bg-transparent tw-text-gray-700 tw-rounded-md hover:tw-bg-gray-50 hover:tw-text-gray-900 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-primary-500 focus:tw-ring-offset-1 tw-transition disabled:tw-opacity-50 disabled:tw-cursor-not-allowed disabled:hover:tw-bg-transparent"
                                            type="button" data-product-id="{{$product->id}}"
                                            data-variation-id="{{$variation->id}}" aria-label="Decrease quantity"
                                            @if($is_out_of_stock) disabled @endif>
                                            <i class="fas fa-minus"></i>
                                        </button>

                                        <input type="number"
                                            class="product-quantity tw-w-12 tw-h-9 sm:tw-w-14 sm:tw-h-10 tw-text-center tw-font-semibold tw-text-gray-900 tw-bg-transparent tw-border-x tw-border-gray-200 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-primary-500 focus:tw-ring-offset-1 tw-text-sm tw-appearance-none tw-flex-shrink-0 tw-transition"
                                            value="0" min="0" inputmode="numeric" data-product-id="{{$product->id}}"
                                            data-variation-id="{{$variation->id}}"
                                            data-product-name="{{$product->name}}" data-variation-name=""
                                            data-price="{{$variation->sell_price_inc_tax}}"
                                            data-image="{{$product->image_url}}" data-sku="{{$variation->sub_sku}}"
                                            @if($is_out_of_stock) disabled @endif>

                                        <button
                                            class="btn-plus tw-inline-flex tw-items-center tw-justify-center tw-w-9 tw-h-9 sm:tw-w-10 sm:tw-h-10 tw-bg-transparent tw-text-gray-700 tw-rounded-md hover:tw-bg-gray-50 hover:tw-text-gray-900 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-primary-500 focus:tw-ring-offset-1 tw-transition disabled:tw-opacity-50 disabled:tw-cursor-not-allowed disabled:hover:tw-bg-transparent"
                                            type="button" data-product-id="{{$product->id}}"
                                            data-variation-id="{{$variation->id}}" aria-label="Increase quantity"
                                            @if($is_out_of_stock) disabled @endif>
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                                @endif
                                @else
                                <div class="tw-mt-auto tw-pt-4">
                                    <a href="#" role="button" aria-label="View options"
                                        class="show-product-details tw-w-full tw-inline-flex tw-items-center tw-justify-center tw-gap-2 tw-px-4 tw-py-2.5 sm:tw-px-6 sm:tw-py-3.5 tw-bg-gradient-to-r tw-from-primary-600 tw-to-primary-700 tw-text-white tw-font-semibold tw-rounded-xl tw-shadow-md hover:tw-shadow-xl hover:tw-from-primary-700 hover:tw-to-primary-800 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-primary-500 focus:tw-ring-offset-2 tw-transition-all tw-duration-300 tw-no-underline tw-transform hover:tw-scale-[1.02] active:tw-scale-[0.98] tw-group"
                                        data-href="{{action([\Modules\ProductCatalogue\Http\Controllers\ProductCatalogueController::class, 'show'],  [$business->id, $product->id])}}?location_id={{$business_location->id}}">
                                        <i
                                            class="fas fa-eye tw-transition-transform tw-duration-300 group-hover:tw-rotate-12"></i>
                                        <span class="tw-inline-flex tw-items-center tw-gap-2">
                                            <span>@lang('productcatalogue::lang.view_options')</span>
                                            <i
                                                class="fas fa-arrow-right tw-text-sm tw-w-0 tw-overflow-hidden tw-opacity-0 tw-transition-all tw-duration-300 group-hover:tw-w-4 group-hover:tw-opacity-100 group-hover:tw-translate-x-1"></i>
                                        </span>
                                    </a>
                                </div>
                                @endif
                            </div>
                        </div>
                        </div>{{-- .product-card-wrapper --}}
                        @endforeach
                </div>
                </div>{{-- .product-category-section --}}
                @endforeach

                <!-- No results message -->
                <div id="catalogue-no-results" style="display:none;"
                    class="tw-text-center tw-py-16 tw-text-gray-500">
                    <i class="fas fa-search tw-text-4xl tw-text-gray-300 tw-mb-4 tw-block"></i>
                    <p class="tw-text-lg tw-font-medium">No products found.</p>
                    <p class="tw-text-sm">Try a different keyword.</p>
                </div>
            </div>

            <!-- Scroll to Top Button -->
            <div class='scrolltop no-print tw-fixed tw-bottom-8 tw-right-8 tw-z-50'>
                <button
                    class='tw-btn tw-btn-circle tw-btn-primary tw-shadow-lg hover:tw-scale-110 tw-transition-transform tw-duration-300 scroll icon tw-w-14 tw-h-14'>
                    <i class="fas fa-angle-up tw-text-xl"></i>
                </button>
            </div>
        </section>
        <!-- /.content -->
        <!-- Add currency related field-->
        <input type="hidden" id="__code" value="{{$business->currency->code}}">
        <input type="hidden" id="__symbol" value="{{$business->currency->symbol}}">
        <input type="hidden" id="__thousand" value="{{$business->currency->thousand_separator}}">
        <input type="hidden" id="__decimal" value="{{$business->currency->decimal_separator}}">
        <input type="hidden" id="__symbol_placement" value="{{$business->currency_symbol_placement}}">
        <input type="hidden" id="__precision" value="{{$business->currency_precision}}">
        <input type="hidden" id="__quantity_precision" value="{{$business->quantity_precision}}">
        <input type="hidden" id="__whatsapp_number" value="{{$order_receiving_whatsapp_number ?? ''}}">
        <input type="hidden" id="__enable_whatsapp_ordering" value="{{$enable_whatsapp_ordering}}">
        <input type="hidden" id="__customer_field_settings" value='{{ json_encode($customer_field_settings ?? ["name" => "required", "email" => "required", "phone" => "required"]) }}'>

        <!-- Product Catalogue JS translations (from module language files) -->
        <script type="text/javascript">
        window.LANG = window.LANG || {};
        Object.assign(window.LANG, {
            productcatalogue_cart_empty: @json(__('productcatalogue::lang.cart_empty')),
            productcatalogue_whatsapp_not_configured: @json(__(
                'productcatalogue::lang.whatsapp_not_configured')),
            productcatalogue_order_sent_successfully: @json(__(
                'productcatalogue::lang.order_sent_successfully')),
            productcatalogue_please_enter_name: @json(__('productcatalogue::lang.please_enter_name')),
            productcatalogue_please_enter_email: @json(__('productcatalogue::lang.please_enter_email')),
            productcatalogue_please_enter_valid_email: @json(__(
                'productcatalogue::lang.please_enter_valid_email')),
            productcatalogue_please_enter_phone: @json(__('productcatalogue::lang.please_enter_phone')),
            productcatalogue_please_enter_address: @json(__('productcatalogue::lang.please_enter_address')),
            productcatalogue_your_cart_is_empty: @json(__('productcatalogue::lang.your_cart_is_empty')),
            productcatalogue_product: @json(__('productcatalogue::lang.product')),
            productcatalogue_price: @json(__('productcatalogue::lang.price')),
            productcatalogue_quantity: @json(__('productcatalogue::lang.quantity')),
            productcatalogue_subtotal_label: @json(__('productcatalogue::lang.subtotal_label')),
            productcatalogue_sku: @json(__('productcatalogue::lang.sku')),
            productcatalogue_action: @json(__('productcatalogue::lang.action')),
            productcatalogue_confirm_remove_from_cart: @json(__(
                'productcatalogue::lang.productcatalogue_confirm_remove_from_cart')),
            productcatalogue_order_items: @json(__('productcatalogue::lang.productcatalogue_order_items')),
            productcatalogue_customer_details_label: @json(__('productcatalogue::lang.productcatalogue_customer_details_label')),
            productcatalogue_delivery_address: @json(__('productcatalogue::lang.productcatalogue_delivery_address')),
            productcatalogue_whatsapp_new_order: @json(__('productcatalogue::lang.productcatalogue_whatsapp_new_order')),
            productcatalogue_whatsapp_order_id: @json(__('productcatalogue::lang.productcatalogue_whatsapp_order_id')),
            productcatalogue_whatsapp_total: @json(__('productcatalogue::lang.productcatalogue_whatsapp_total')),
            productcatalogue_whatsapp_name: @json(__('productcatalogue::lang.productcatalogue_whatsapp_name')),
            productcatalogue_whatsapp_email: @json(__('productcatalogue::lang.productcatalogue_whatsapp_email')),
            productcatalogue_whatsapp_phone: @json(__('productcatalogue::lang.productcatalogue_whatsapp_phone')),
        });
        </script>

        <div class="modal fade product_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        </div>

        <!-- Cart Drawer (custom off-canvas, mirrors mobile categories drawer) -->
        @if($enable_whatsapp_ordering == 1)
    <div id="cartDrawer" class="cart-drawer no-print" role="dialog" aria-modal="true" aria-hidden="true" aria-label="Cart drawer">
        <div class="cart-backdrop" data-cart-close></div>

        <aside class="cart-panel">
            <!-- Cart view -->
            <div id="cart-drawer-view" class="tw-flex tw-flex-col tw-h-full">
                <div
                    class="tw-flex tw-items-center tw-justify-between tw-px-5 tw-py-4 tw-bg-gradient-to-r tw-from-primary-600 tw-to-primary-700 tw-text-white tw-border-0">
                    <h4 class="tw-m-0 tw-text-white tw-font-bold tw-text-lg tw-flex tw-items-center">
                        <i class="fas fa-shopping-cart tw-mr-2"></i> {{ __('productcatalogue::lang.my_cart') }}
                    </h4>
                    <button type="button" data-cart-close
                        class="tw-inline-flex tw-items-center tw-justify-center tw-w-10 tw-h-10 tw-rounded-lg hover:tw-bg-white/10 tw-transition tw-cursor-pointer tw-bg-transparent tw-border-0 tw-text-white"
                        aria-label="Close cart">
                        <span aria-hidden="true" class="tw-text-2xl">&times;</span>
                    </button>
                </div>

                <div class="tw-flex-1 tw-overflow-y-auto tw-p-5">
                    <div id="cart-items-container">
                        <div class="tw-text-center tw-py-12">
                            <i class="fas fa-shopping-cart tw-text-6xl tw-text-gray-300 tw-mb-4"></i>
                            <p class="tw-text-gray-500 tw-text-lg tw-font-medium">
                                {{ __('productcatalogue::lang.your_cart_is_empty') }}</p>
                        </div>
                    </div>

                    <div id="cart-summary" style="display: none;">
                        <div class="tw-mt-6 tw-pt-6 tw-border-t-2 tw-border-gray-200">
                            <div class="tw-flex tw-justify-end">
                                <div
                                    class="tw-w-full md:tw-w-96 tw-bg-gradient-to-br tw-from-gray-50 tw-to-gray-100 tw-rounded-xl tw-p-6 tw-shadow-md">
                                    <div class="tw-space-y-3">
                                        <div class="tw-flex tw-justify-between tw-items-center tw-text-gray-700">
                                            <span
                                                class="tw-font-semibold">{{ __('productcatalogue::lang.subtotal') }}:</span>
                                            <span class="tw-font-bold tw-text-lg" id="cart-subtotal">0</span>
                                        </div>
                                        <div
                                            class="tw-border-t-2 tw-border-gray-300 tw-pt-3 tw-flex tw-justify-between tw-items-center">
                                            <span
                                                class="tw-font-bold tw-text-lg tw-text-gray-900">{{ __('productcatalogue::lang.total') }}:</span>
                                            <span class="tw-font-bold tw-text-2xl tw-text-primary-600"
                                                id="cart-total">0</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tw-bg-gray-50 tw-border-t tw-border-gray-200 tw-px-5 tw-py-4 tw-flex tw-flex-col sm:tw-flex-row tw-gap-3"
                    id="cart-footer" style="display: none;">
                    <button type="button" data-cart-close
                        class="tw-flex-1 tw-cursor-pointer tw-px-6 tw-py-3 tw-bg-white tw-text-gray-700 tw-border-2 tw-border-gray-300 tw-rounded-xl tw-font-semibold hover:tw-bg-gray-50 hover:tw-border-gray-400 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-gray-500 focus:tw-ring-offset-2 tw-transition-all tw-duration-200 tw-flex tw-items-center tw-justify-center">
                        <i class="fas fa-arrow-left tw-mr-2"></i>{{ __('productcatalogue::lang.continue_shopping') }}
                    </button>
                    <button type="button"
                        class="tw-flex-1 tw-px-6 tw-py-3 tw-bg-gradient-to-r tw-from-primary-600 tw-to-primary-700 tw-text-white tw-rounded-xl tw-font-semibold tw-shadow-md hover:tw-shadow-lg hover:tw-from-primary-700 hover:tw-to-primary-800 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-primary-500 focus:tw-ring-offset-2 tw-transition-all tw-duration-200 tw-transform hover:tw-scale-105 tw-flex tw-items-center tw-justify-center"
                        id="place-order-btn">
                        <i class="fas fa-shopping-bag tw-mr-2"></i>{{ __('productcatalogue::lang.place_order') }}
                    </button>
                </div>
            </div>

            <!-- Order view (inside drawer) -->
            <div id="order-drawer-view" class="tw-hidden tw-flex tw-flex-col tw-h-full">
                <div
                    class="tw-flex tw-items-center tw-justify-between tw-px-5 tw-py-4 tw-bg-gradient-to-r tw-from-green-600 tw-to-green-700 tw-text-white tw-border-0">
                    <div class="tw-flex tw-items-center tw-gap-3">
                        <button type="button"
                            class="tw-inline-flex tw-items-center tw-justify-center tw-w-10 tw-h-10 tw-rounded-lg hover:tw-bg-white/10 tw-transition"
                            id="back-to-cart-btn" aria-label="Back to cart">
                            <i class="fas fa-arrow-left"></i>
                        </button>
                        <h4 class="tw-m-0 tw-text-white tw-font-bold tw-text-lg tw-flex tw-items-center">
                            <i class="fas fa-shopping-bag tw-mr-2"></i>
                            {{ __('productcatalogue::lang.place_your_order') }}
                        </h4>
                    </div>
                    <button type="button" data-cart-close
                        class="tw-inline-flex tw-items-center tw-justify-center tw-w-10 tw-h-10 tw-rounded-lg hover:tw-bg-white/10 tw-transition tw-cursor-pointer tw-bg-transparent tw-border-0 tw-text-white"
                        aria-label="Close order">
                        <span aria-hidden="true" class="tw-text-2xl">&times;</span>
                    </button>
                </div>

                <div class="tw-flex-1 tw-overflow-y-auto tw-p-5">
                    <!-- Order Summary -->
                    <div
                        class="tw-bg-gradient-to-br tw-from-gray-50 tw-to-gray-100 tw-p-5 tw-mb-6 tw-rounded-xl tw-shadow-md tw-border tw-border-gray-200">
                        <h5 class="tw-mb-4 tw-font-bold tw-text-lg tw-text-gray-900">
                            <i class="fas fa-list tw-mr-2 tw-text-primary-600"></i>
                            {{ __('productcatalogue::lang.order_summary') }}
                        </h5>
                        <div id="order-summary-items" class="table-responsive">
                            <!-- Will be populated by JavaScript -->
                        </div>
                        <div class="tw-text-right tw-mt-4 tw-pt-4 tw-border-t-2 tw-border-gray-300">
                            <span
                                class="tw-text-gray-700 tw-font-semibold tw-text-lg tw-mr-2">{{ __('productcatalogue::lang.total') }}:</span>
                            <strong class="tw-text-2xl tw-font-bold tw-text-primary-600"
                                id="order-total-display">0</strong>
                        </div>
                    </div>

                    <div
                        class="tw-h-px tw-bg-gradient-to-r tw-from-transparent tw-via-gray-300 tw-to-transparent tw-my-6">
                    </div>

                    <!-- Customer Details Form -->
                    @php
                        $cf = $customer_field_settings ?? [
                            'name' => 'required',
                            'email' => 'required',
                            'phone' => 'required',
                        ];
                    @endphp
                    <h5 class="tw-mb-4 tw-font-bold tw-text-lg tw-text-gray-900">
                        <i class="fas fa-user tw-mr-2 tw-text-primary-600"></i>
                        {{ __('productcatalogue::lang.customer_details') }}
                    </h5>
                    <form id="order-form">
                        <div class="row">
                            @if($cf['name'] !== 'remove')
                            <div class="col-md-6">
                                <div class="form-group tw-mb-5">
                                    <label for="customer_name" class="tw-dw-label tw-mb-3">
                                        <span
                                            class="tw-dw-label-text tw-text-sm tw-font-bold tw-text-gray-800 tw-uppercase tw-tracking-wide">
                                            {{ __('productcatalogue::lang.your_name') }} @if($cf['name'] === 'required')<span
                                                class="tw-text-red-500">*</span>@endif
                                        </span>
                                    </label>
                                    <label class="tw-dw-input-group tw-shadow-sm">
                                        <span
                                            class="tw-bg-primary-500 tw-text-white tw-border-2 tw-border-primary-500 tw-font-semibold">
                                            <i class="fas fa-user tw-text-base"></i>
                                        </span>
                                        <input type="text"
                                            class="tw-dw-input tw-dw-input-bordered tw-w-full tw-h-12 tw-border-2 tw-border-gray-300 tw-bg-white tw-text-gray-900 tw-placeholder-gray-400 focus:tw-outline-none focus:tw-border-primary-500 focus:tw-ring-2 focus:tw-ring-primary-200 tw-transition-all"
                                            id="customer_name" name="customer_name" @if($cf['name'] === 'required') required @endif
                                            placeholder="{{ __('productcatalogue::lang.enter_your_name') }}">
                                    </label>
                                </div>
                            </div>
                            @endif
                            @if($cf['email'] !== 'remove')
                            <div class="col-md-6">
                                <div class="form-group tw-mb-5">
                                    <label for="customer_email" class="tw-dw-label tw-mb-3">
                                        <span
                                            class="tw-dw-label-text tw-text-sm tw-font-bold tw-text-gray-800 tw-uppercase tw-tracking-wide">
                                            {{ __('productcatalogue::lang.email_address') }} @if($cf['email'] === 'required')<span
                                                class="tw-text-red-500">*</span>@endif
                                        </span>
                                    </label>
                                    <label class="tw-dw-input-group tw-shadow-sm">
                                        <span
                                            class="tw-bg-primary-500 tw-text-white tw-border-2 tw-border-primary-500 tw-font-semibold">
                                            <i class="fas fa-envelope tw-text-base"></i>
                                        </span>
                                        <input type="email"
                                            class="tw-dw-input tw-dw-input-bordered tw-w-full tw-h-12 tw-border-2 tw-border-gray-300 tw-bg-white tw-text-gray-900 tw-placeholder-gray-400 focus:tw-outline-none focus:tw-border-primary-500 focus:tw-ring-2 focus:tw-ring-primary-200 tw-transition-all"
                                            id="customer_email" name="customer_email" @if($cf['email'] === 'required') required @endif
                                            placeholder="{{ __('productcatalogue::lang.enter_your_email') }}">
                                    </label>
                                </div>
                            </div>
                            @endif
                        </div>
                        <div class="row">
                            @if($cf['phone'] !== 'remove')
                            <div class="col-md-6">
                                <div class="form-group tw-mb-5">
                                    <label for="customer_phone" class="tw-dw-label tw-mb-3">
                                        <span
                                            class="tw-dw-label-text tw-text-sm tw-font-bold tw-text-gray-800 tw-uppercase tw-tracking-wide">
                                            {{ __('productcatalogue::lang.phone_number') }} @if($cf['phone'] === 'required')<span
                                                class="tw-text-red-500">*</span>@endif
                                        </span>
                                    </label>
                                    <label class="tw-dw-input-group tw-shadow-sm">
                                        <span
                                            class="tw-bg-primary-500 tw-text-white tw-border-2 tw-border-primary-500 tw-font-semibold">
                                            <i class="fas fa-phone tw-text-base"></i>
                                        </span>
                                        <input type="tel"
                                            class="tw-dw-input tw-dw-input-bordered tw-w-full tw-h-12 tw-border-2 tw-border-gray-300 tw-bg-white tw-text-gray-900 tw-placeholder-gray-400 focus:tw-outline-none focus:tw-border-primary-500 focus:tw-ring-2 focus:tw-ring-primary-200 tw-transition-all"
                                            id="customer_phone" name="customer_phone" @if($cf['phone'] === 'required') required @endif
                                            placeholder="{{ __('productcatalogue::lang.enter_your_phone_number') }}">
                                    </label>
                                </div>
                            </div>
                            @endif
                            <div class="col-md-6">
                                <div class="form-group tw-mb-5">
                                    <label for="delivery_address" class="tw-dw-label tw-mb-3">
                                        <span
                                            class="tw-dw-label-text tw-text-sm tw-font-bold tw-text-gray-800 tw-uppercase tw-tracking-wide">
                                            {{ __('productcatalogue::lang.delivery_address') }} <span
                                                class="tw-text-red-500">*</span>
                                        </span>
                                    </label>
                                    <label class="tw-dw-input-group tw-items-start tw-shadow-sm">
                                        <span
                                            class="tw-bg-primary-500 tw-text-white tw-border-2 tw-border-primary-500 tw-font-semibold tw-py-3">
                                            <i class="fas fa-map-marker-alt tw-text-base"></i>
                                        </span>
                                        <textarea
                                            class="tw-dw-textarea tw-dw-textarea-bordered tw-w-full tw-min-h-[100px] tw-border-2 tw-border-gray-300 tw-bg-white tw-text-gray-900 tw-placeholder-gray-400 focus:tw-outline-none focus:tw-border-primary-500 focus:tw-ring-2 focus:tw-ring-primary-200 tw-transition-all tw-resize-none"
                                            id="delivery_address" name="delivery_address" rows="3" required
                                            placeholder="{{ __('productcatalogue::lang.enter_your_delivery_address') }}"></textarea>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <div
                    class="tw-bg-gray-50 tw-border-t tw-border-gray-200 tw-px-5 tw-py-4 tw-flex tw-flex-col sm:tw-flex-row tw-gap-3">
                    <button type="button"
                        class="tw-flex-1 tw-px-6 tw-py-3 tw-bg-white tw-text-gray-700 tw-border-2 tw-border-gray-300 tw-rounded-xl tw-font-semibold hover:tw-bg-gray-50 hover:tw-border-gray-400 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-gray-500 focus:tw-ring-offset-2 tw-transition-all tw-duration-200 tw-flex tw-items-center tw-justify-center"
                        id="cancel-order-btn">
                        <i class="fas fa-arrow-left tw-mr-2"></i>{{ __('productcatalogue::lang.continue_shopping') }}
                    </button>
                    <button type="button"
                        class="tw-flex-1 tw-px-6 tw-py-3 tw-bg-gradient-to-r tw-from-green-500 tw-to-green-600 tw-text-white tw-rounded-xl tw-font-semibold tw-shadow-md hover:tw-shadow-lg hover:tw-from-green-600 hover:tw-to-green-700 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-green-500 focus:tw-ring-offset-2 tw-transition-all tw-duration-200 tw-transform hover:tw-scale-105 tw-flex tw-items-center tw-justify-center"
                        id="send-order-whatsapp-btn">
                        <i class="fab fa-whatsapp tw-mr-2 tw-text-lg"></i>
                        {{ __('productcatalogue::lang.send_order_via_whatsapp') }}
                    </button>
                </div>
            </div>
        </aside>
    </div>
@endif
@stop
@section('javascript')
<script type="text/javascript">
var catalogue_enable_whatsapp_ordering = ($('#__enable_whatsapp_ordering').val() == '1');
var catalogue_customer_field_settings = (function() {
    var defaults = {name: 'required', email: 'required', phone: 'required'};
    try {
        var raw = $('#__customer_field_settings').val();
        if (!raw) return defaults;
        var parsed = JSON.parse(raw);
        return $.extend({}, defaults, parsed);
    } catch (e) {
        return defaults;
    }
})();

(function($) {
    $(document).ready(function() {
        // Initialize currency variables (common.js not included in guest layout)
        if (typeof __currency_symbol === 'undefined') {
            __currency_symbol = $('input#__symbol').val();
            __currency_thousand_separator = $('input#__thousand').val();
            __currency_decimal_separator = $('input#__decimal').val();
            __currency_symbol_placement = $('input#__symbol_placement').val();
            if ($('input#__precision').length > 0) {
                __currency_precision = $('input#__precision').val();
            } else {
                __currency_precision = 2;
            }
        }

        __currency_convert_recursively($('.content'));

        // Initialize WhatsApp number from backend
        catalogue_cart_whatsapp_number = $('#__whatsapp_number').val() || '';

        if (catalogue_enable_whatsapp_ordering) {
            // Initialize cart
            catalogue_init_cart();

            catalogue_update_cart_badge();
            catalogue_display_cart();

            // Sync quantity inputs with cart
            catalogue_sync_quantities_with_cart();

            // Cart drawer uses the same class-toggle pattern as the mobile categories drawer.
            // No checkbox, no `:has(:checked)` selector, so no scroll jump.
            function catalogueOpenCart() {
                $('#cartDrawer').addClass('is-open').attr('aria-hidden', 'false');
                $('body').addClass('cart-open');
                catalogue_show_cart_drawer_view();
            }
            function catalogueCloseCart() {
                $('#cartDrawer').removeClass('is-open').attr('aria-hidden', 'true');
                $('body').removeClass('cart-open');
                catalogue_show_cart_drawer_view();
            }
            $(document).on('click', '#cart-icon', function(e) {
                e.preventDefault();
                catalogueOpenCart();
            });
            $(document).on('click', '[data-cart-close]', function(e) {
                e.preventDefault();
                catalogueCloseCart();
            });
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape' && $('#cartDrawer').hasClass('is-open')) {
                    catalogueCloseCart();
                }
            });
        }
    });

    $(document).on('click', '.show-product-details', function(e) {
        e.preventDefault();
        var $link = $(this);
        $.ajax({
            url: $link.data('href'),
            dataType: 'html',
            success: function(result) {
                $('.product_modal')
                    .html(result)
                    .modal('show');
                __currency_convert_recursively($('.product_modal'));

                if (catalogue_enable_whatsapp_ordering) {
                    // Sync quantities with cart when modal opens
                    catalogue_update_cart_badge();
                    // Use shown.bs.modal event to ensure modal is fully rendered
                    $('.product_modal').on('shown.bs.modal', function() {
                        catalogue_sync_quantities_with_cart();
                        $(this).off('shown.bs.modal'); // Remove handler after first use
                    });
                }
            },
        });
    });

    if (catalogue_enable_whatsapp_ordering) {
        // Handle modal shown event for cart sync
        $(document).on('shown.bs.modal', '.product_modal', function() {
            catalogue_update_cart_badge();
            catalogue_sync_quantities_with_cart();
        });
    }

    $(document).on('click', '.menu', function(e) {
        e.preventDefault();
        var cat_id = $(this).attr('href');
        if ($(cat_id).length) {
            var navH = ($('#catalogueNav').outerHeight() || 0);
            $('html, body').animate({
                scrollTop: ($(cat_id).offset().top - navH - 12)
            }, 1000);
        }
    });

    // Mobile categories drawer
    var $mobileDrawer = $('#mobileCategoriesDrawer');
    var $mobileDrawerBtn = $('#mobileCategoriesBtn');

    function openMobileCategories() {
        $mobileDrawer.addClass('is-open').attr('aria-hidden', 'false');
        $mobileDrawerBtn.attr('aria-expanded', 'true');
        $('body').addClass('mobile-cat-open');
        // Focus the panel for a11y
        setTimeout(function() { $('#mobileCategoriesPanel').trigger('focus'); }, 60);
    }

    function closeMobileCategories() {
        $mobileDrawer.removeClass('is-open').attr('aria-hidden', 'true');
        $mobileDrawerBtn.attr('aria-expanded', 'false');
        $('body').removeClass('mobile-cat-open');
    }

    $mobileDrawerBtn.on('click', openMobileCategories);
    $('#mobileCategoriesClose, #mobileCategoriesBackdrop').on('click', closeMobileCategories);
    $(document).on('click', '.mobile-category-link', closeMobileCategories);
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape' && $mobileDrawer.hasClass('is-open')) {
            closeMobileCategories();
        }
    });

})(jQuery);

// Sticky fallback: fix the nav once it reaches the top
var catalogueNavWrapTop = null;

function catalogue_update_nav_wrap_top() {
    var $wrap = $('#catalogueNavWrap');
    if (!$wrap.length) return;
    // Ensure we measure in non-fixed state
    $wrap.removeClass('is-fixed');
    $('#catalogueNavSpacer').hide().height(0);
    catalogueNavWrapTop = $wrap.offset() ? $wrap.offset().top : null;
}

$(function() {
    catalogue_update_nav_wrap_top();
    $(window).on('resize', function() {
        catalogue_update_nav_wrap_top();
    });
});

$(window).scroll(function() {
    var height = $(window).scrollTop();
    var $wrap = $('#catalogueNavWrap');
    var $nav = $('#catalogueNav');
    var $spacer = $('#catalogueNavSpacer');

    // If the page layout shifts, recover the measurement lazily
    if (catalogueNavWrapTop === null) {
        catalogue_update_nav_wrap_top();
    }

    if ($wrap.length && catalogueNavWrapTop !== null) {
        if (height >= catalogueNavWrapTop) {
            if (!$wrap.hasClass('is-fixed')) {
                $wrap.addClass('is-fixed');
                $spacer.height($wrap.outerHeight() || 0).show();
            }
            $nav.addClass('is-scrolled');
        } else {
            $wrap.removeClass('is-fixed');
            $spacer.hide().height(0);
            $nav.removeClass('is-scrolled');
        }
    }

    if (height > 180) {
        $('.scrolltop:hidden').stop(true, true).fadeIn();
    } else {
        $('.scrolltop').stop(true, true).fadeOut();
    }
});

$(document).on('click', '.scroll', function(e) {
    $("html,body").animate({
        scrollTop: $("#top").offset().top
    }, "1000");
    return false;
});

// Cart Management JavaScript - Global variables for shared state
var catalogue_cart_whatsapp_number = '';
var catalogue_cart_previous_quantities = {};

// Get cart from localStorage
function catalogue_get_cart() {
    var cart = localStorage.getItem('product_catalogue_cart');
    return cart ? JSON.parse(cart) : [];
}

// Save cart to localStorage
function catalogue_save_cart(cart) {
    localStorage.setItem('product_catalogue_cart', JSON.stringify(cart));
}

// Get item key for tracking
function catalogue_get_item_key(productId, variationId) {
    return productId + '_' + variationId;
}

// Format currency using standard application function (with currency short code, e.g. "$ 50.00 (USD)")
function catalogue_format_currency(amount) {
    return __currency_with_code(amount, true, false, __currency_precision, false);
}

// Initialize cart
function catalogue_init_cart() {
    // Store previous quantities for comparison
    catalogue_cart_previous_quantities = {};

    // Event handlers for product cards
    $(document).on('click', '.btn-plus', catalogue_increase_quantity);
    $(document).on('click', '.btn-minus', catalogue_decrease_quantity);
    $(document).on('change', '.product-quantity', catalogue_quantity_change_handler);

    // Event handlers for modal (simple products)
    $(document).on('click', '.btn-plus-modal', catalogue_increase_quantity_modal);
    $(document).on('click', '.btn-minus-modal', catalogue_decrease_quantity_modal);
    $(document).on('change', '.product-quantity-modal', catalogue_quantity_change_handler);

    // Event handlers for variations in modal
    $(document).on('click', '.btn-plus-variation', catalogue_increase_quantity_variation);
    $(document).on('click', '.btn-minus-variation', catalogue_decrease_quantity_variation);
    $(document).on('change', '.product-quantity-variation', catalogue_quantity_change_handler);

    // Cart management handlers
    $(document).on('click', '.remove-cart-item', catalogue_remove_from_cart);
    $(document).on('change', '.cart-item-quantity', catalogue_update_cart_quantity_from_input);
    $(document).on('click', '#place-order-btn', catalogue_show_order_form);
    $(document).on('click', '#back-to-cart-btn', function() {
        catalogue_show_cart_drawer_view();
    });
    $(document).on('click', '#cancel-order-btn', function() {
        catalogue_show_cart_drawer_view();
    });
    $(document).on('click', '#send-order-whatsapp-btn', catalogue_send_order_via_whatsapp);

    // Initialize previous quantities
    catalogue_init_previous_quantities();
}

// Initialize previous quantities from cart
function catalogue_init_previous_quantities() {
    var cart = catalogue_get_cart();
    catalogue_cart_previous_quantities = {};

    cart.forEach(function(item) {
        var key = catalogue_get_item_key(item.product_id, item.variation_id);
        catalogue_cart_previous_quantities[key] = item.quantity;
    });
}

// Sync quantity inputs with cart
function catalogue_sync_quantities_with_cart() {
    var cart = catalogue_get_cart();

    // Update all quantity inputs based on cart
    $('.product-quantity, .product-quantity-modal, .product-quantity-variation').each(function() {
        var $input = $(this);
        var productId = $input.data('product-id');
        var variationId = $input.data('variation-id');

        // Find matching item in cart
        var cartItem = cart.find(function(item) {
            return item.product_id == productId && item.variation_id == variationId;
        });

        if (cartItem) {
            $input.val(cartItem.quantity);
            var key = catalogue_get_item_key(productId, variationId);
            catalogue_cart_previous_quantities[key] = cartItem.quantity;
        } else {
            $input.val(0);
            var key = catalogue_get_item_key(productId, variationId);
            catalogue_cart_previous_quantities[key] = 0;
        }
    });
}

// Auto update cart based on quantity change
function catalogue_auto_update_cart($input) {
    var productId = $input.data('product-id');
    var variationId = $input.data('variation-id');
    var productName = $input.data('product-name') || '';
    var variationName = $input.data('variation-name') || '';
    var price = __num_uf($input.data('price')) || 0;
    var image = $input.data('image') || '';
    var sku = $input.data('sku') || '';

    var currentQuantity = parseInt($input.val()) || 0;
    var key = catalogue_get_item_key(productId, variationId);
    var previousQuantity = catalogue_cart_previous_quantities[key] || 0;

    // If quantity decreased to 0, remove from cart
    if (currentQuantity === 0 && previousQuantity > 0) {
        catalogue_remove_item_from_cart(productId, variationId);
        catalogue_cart_previous_quantities[key] = 0;
        return;
    }

    // If quantity is > 0, add or update cart
    if (currentQuantity > 0) {
        var cart = catalogue_get_cart();
        var found = false;

        // Check if item already exists in cart
        for (var i = 0; i < cart.length; i++) {
            if (cart[i].product_id == productId && cart[i].variation_id == variationId) {
                // Update quantity directly (don't add)
                cart[i].quantity = currentQuantity;
                cart[i].product_name = productName;
                cart[i].variation_name = variationName;
                cart[i].price = price;
                cart[i].image_url = image;
                cart[i].sku = sku;
                found = true;
                break;
            }
        }

        // If not found, add new item
        if (!found) {
            cart.push({
                product_id: productId,
                variation_id: variationId,
                product_name: productName,
                variation_name: variationName,
                price: price,
                quantity: currentQuantity,
                image_url: image,
                sku: sku
            });
        }

        catalogue_save_cart(cart);
        catalogue_update_cart_badge();
        catalogue_display_cart();
    }

    // Update previous quantity
    catalogue_cart_previous_quantities[key] = currentQuantity;
}

// Remove item from cart by product and variation
function catalogue_remove_item_from_cart(productId, variationId) {
    var cart = catalogue_get_cart();
    var newCart = cart.filter(function(item) {
        return !(item.product_id == productId && item.variation_id == variationId);
    });
    catalogue_save_cart(newCart);
    catalogue_update_cart_badge();
    catalogue_display_cart();
}

// Increase quantity (product cards)
function catalogue_increase_quantity(e) {
    e.preventDefault();
    var $btn = $(e.currentTarget);
    var $input = $btn.closest('.input-group').find('.product-quantity');
    var currentVal = parseInt($input.val()) || 0;
    $input.val(currentVal + 1);
    catalogue_auto_update_cart($input);
}

// Decrease quantity (product cards)
function catalogue_decrease_quantity(e) {
    e.preventDefault();
    var $btn = $(e.currentTarget);
    var $input = $btn.closest('.input-group').find('.product-quantity');
    var currentVal = parseInt($input.val()) || 0;
    if (currentVal > 0) {
        $input.val(currentVal - 1);
        catalogue_auto_update_cart($input);
    }
}

// Increase quantity modal (simple products)
function catalogue_increase_quantity_modal(e) {
    e.preventDefault();
    var $btn = $(e.currentTarget);
    var $input = $btn.closest('.input-group').find('.product-quantity-modal');
    var currentVal = parseInt($input.val()) || 0;
    $input.val(currentVal + 1);
    catalogue_auto_update_cart($input);
}

// Decrease quantity modal (simple products)
function catalogue_decrease_quantity_modal(e) {
    e.preventDefault();
    var $btn = $(e.currentTarget);
    var $input = $btn.closest('.input-group').find('.product-quantity-modal');
    var currentVal = parseInt($input.val()) || 0;
    if (currentVal > 0) {
        $input.val(currentVal - 1);
        catalogue_auto_update_cart($input);
    }
}

// Increase quantity variation
function catalogue_increase_quantity_variation(e) {
    e.preventDefault();
    var $btn = $(e.currentTarget);
    var $input = $btn.closest('.input-group').find('.product-quantity-variation');
    var currentVal = parseInt($input.val()) || 0;
    $input.val(currentVal + 1);
    catalogue_auto_update_cart($input);
}

// Decrease quantity variation
function catalogue_decrease_quantity_variation(e) {
    e.preventDefault();
    var $btn = $(e.currentTarget);
    var $input = $btn.closest('.input-group').find('.product-quantity-variation');
    var currentVal = parseInt($input.val()) || 0;
    if (currentVal > 0) {
        $input.val(currentVal - 1);
        catalogue_auto_update_cart($input);
    }
}

// Quantity change handler
function catalogue_quantity_change_handler(e) {
    var $input = $(e.currentTarget);
    var val = parseInt($input.val()) || 0;
    if (val < 0) {
        $input.val(0);
    }
    // Auto update cart based on quantity change
    catalogue_auto_update_cart($input);
}

// Calculate cart total
function catalogue_calculate_cart_total() {
    var cart = catalogue_get_cart();
    var total = 0;

    for (var i = 0; i < cart.length; i++) {
        var price = __num_uf(cart[i].price) || 0;
        var quantity = parseInt(cart[i].quantity) || 0;
        total += price * quantity;
    }

    return total;
}

// Get cart item count
function catalogue_get_cart_item_count() {
    var cart = catalogue_get_cart();
    var count = 0;

    for (var i = 0; i < cart.length; i++) {
        count += parseInt(cart[i].quantity) || 0;
    }

    return count;
}

// Update cart total amount display in navbar
function catalogue_update_cart_total_display() {
    var total = catalogue_calculate_cart_total();
    var $totalDisplay = $('#cart-total-amount');

    if (total > 0) {
        $totalDisplay.text(catalogue_format_currency(total)).show();
    } else {
        $totalDisplay.hide();
    }
}

// Update cart badge
function catalogue_update_cart_badge() {
    var count = catalogue_get_cart_item_count();
    var $badge = $('#cart-badge');

    if (count > 0) {
        $badge.text(count).show();
    } else {
        $badge.hide();
    }

    // Update cart total amount display
    catalogue_update_cart_total_display();
}

// Display cart
function catalogue_display_cart() {
    var cart = catalogue_get_cart();
    var $container = $('#cart-items-container');
    var $summary = $('#cart-summary');
    var $footer = $('#cart-footer');
    var defaultImg = "{{ asset('/img/default.png') }}";

    if (cart.length === 0) {
        $container.html(
            '<div class="tw-text-center tw-py-12"><i class="fas fa-shopping-cart tw-text-6xl tw-text-gray-300 tw-mb-4"></i><p class="tw-text-gray-500 tw-text-lg tw-font-medium">' +
            LANG.productcatalogue_your_cart_is_empty + '</p></div>');
        $summary.hide();
        $footer.hide();
        return;
    }

    // Build cart items HTML (table layout - mobile friendly)
    var html = '<div class="tw-overflow-x-auto">';
    html += '<table class="table tw-w-full">';
    html += '<thead><tr class="tw-bg-gray-100 tw-border-b-2 tw-border-gray-300">';
    html += '<th class="tw-px-4 tw-py-3 tw-text-left tw-text-sm tw-font-bold tw-text-gray-700 tw-uppercase">' + (LANG
        .productcatalogue_product || 'Product') + '</th>';
    html += '<th class="tw-px-4 tw-py-3 tw-text-center tw-text-sm tw-font-bold tw-text-gray-700 tw-uppercase">' + (LANG
        .productcatalogue_quantity || 'Qty') + '</th>';
    html +=
        '<th class="tw-px-4 tw-py-3 tw-text-right tw-text-sm tw-font-bold tw-text-gray-700 tw-uppercase tw-hidden sm:tw-table-cell">' +
        (LANG.productcatalogue_price || 'Price') + '</th>';
    html += '<th class="tw-px-4 tw-py-3 tw-text-right tw-text-sm tw-font-bold tw-text-gray-700 tw-uppercase">' + (LANG
        .productcatalogue_subtotal_label || 'Subtotal') + '</th>';
    html += '<th class="tw-px-4 tw-py-3 tw-text-right tw-text-sm tw-font-bold tw-text-gray-700 tw-uppercase"> ' + (LANG
        .productcatalogue_action || 'Remove') + '</th>';
    html += '</tr></thead>';
    html += '<tbody class="tw-divide-y tw-divide-gray-200">';

    for (var i = 0; i < cart.length; i++) {
        var item = cart[i];
        var price = __num_uf(item.price) || 0;
        var quantity = parseInt(item.quantity) || 0;
        var subtotal = price * quantity;

        var displayName = item.product_name || '';
        if (item.variation_name) {
            displayName += ' - ' + item.variation_name;
        }

        html += '<tr class="hover:tw-bg-gray-50 tw-transition-colors">';

        // Product cell (image + name + sku)
        html += '<td class="tw-px-4 tw-py-3 tw-align-top">';
        html += '<div class="tw-flex tw-gap-3 tw-items-start">';
        var imageUrl = item.image_url || defaultImg;
        html +=
            '<div class="tw-w-12 tw-h-12 tw-bg-gray-100 tw-rounded-lg tw-border tw-border-gray-200 tw-overflow-hidden tw-flex tw-items-center tw-justify-center tw-flex-shrink-0">';
        html += '<img src="' + imageUrl + '" class="tw-w-full tw-h-full tw-object-contain tw-p-1" alt="' + displayName +
            '" onerror="this.onerror=null;this.src=\'' + defaultImg + '\';">';
        html += '</div>';
        html += '<div class="tw-min-w-0">';
        html += '<div class="tw-text-gray-900 tw-font-medium tw-leading-snug tw-break-words">' + displayName + '</div>';
        if (item.sku) {
            html += '<div class="tw-text-xs tw-text-gray-500 tw-mt-1"><span class="tw-font-semibold">' + (LANG
                .productcatalogue_sku || 'SKU') + ':</span> ' + item.sku + '</div>';
        }
        // On xs: show unit price under name to avoid squished columns
        html += '<div class="tw-text-xs tw-text-gray-600 tw-mt-1 sm:tw-hidden"><span class="tw-font-semibold">' + (LANG
            .productcatalogue_price || 'Price') + ':</span> ' + catalogue_format_currency(price) + '</div>';
        html += '</div>';
        html += '</div>';
        html += '</td>';

        // Quantity cell (controls)
        html += '<td class="tw-px-4 tw-py-3 tw-align-top">';
        html += '<div class="tw-flex tw-justify-center">';
        html +=
            '<div class="input-group tw-inline-flex tw-items-stretch tw-gap-0 tw-bg-gradient-to-r tw-from-gray-50 tw-to-gray-100 tw-rounded-xl tw-p-1 tw-border-2 tw-border-gray-200 tw-shadow-sm">';
        html +=
            '<button class="tw-inline-flex tw-items-center tw-justify-center tw-w-10 tw-h-10 tw-bg-white tw-text-gray-700 tw-border-2 tw-border-gray-300 tw-border-r-0 tw-rounded-l-xl hover:tw-bg-primary-500 hover:tw-text-white hover:tw-border-primary-500 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-primary-500 tw-transition-all tw-font-bold tw-text-base tw-flex-shrink-0" type="button" onclick="catalogue_decrease_cart_quantity(' +
            i + ')"><i class="fas fa-minus tw-text-xs"></i></button>';
        html +=
            '<input type="number" class="cart-item-quantity tw-w-16 tw-h-10 tw-text-center tw-font-bold tw-text-gray-900 tw-bg-white tw-border-2 tw-border-gray-300 tw-border-x-0 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-primary-500 focus:tw-border-primary-500 tw-text-base tw-appearance-none tw-flex-shrink-0" value="' +
            item.quantity + '" min="0" data-index="' + i + '">';
        html +=
            '<button class="tw-inline-flex tw-items-center tw-justify-center tw-w-10 tw-h-10 tw-bg-white tw-text-gray-700 tw-border-2 tw-border-gray-300 tw-border-l-0 tw-rounded-r-xl hover:tw-bg-primary-500 hover:tw-text-white hover:tw-border-primary-500 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-primary-500 tw-transition-all tw-font-bold tw-text-base tw-flex-shrink-0" type="button" onclick="catalogue_increase_cart_quantity(' +
            i + ')"><i class="fas fa-plus tw-text-xs"></i></button>';
        html += '</div>';
        html += '</div>';
        html += '</td>';

        // Price cell (sm+)
        html += '<td class="tw-px-4 tw-py-3 tw-text-right tw-align-top tw-text-gray-700 tw-hidden sm:tw-table-cell">';
        html += catalogue_format_currency(price);
        html += '</td>';

        // Subtotal cell
        html += '<td class="tw-px-4 tw-py-3 tw-text-right tw-align-top tw-text-gray-900 tw-font-bold">';
        html += catalogue_format_currency(subtotal);
        html += '</td>';

        // Remove cell
        html += '<td class="tw-px-4 tw-py-3 tw-text-right tw-align-top">';
        html +=
            '<button class="tw-inline-flex tw-items-center tw-justify-center tw-w-10 tw-h-10 tw-text-red-500 hover:tw-bg-red-50 hover:tw-text-red-700 tw-rounded-lg tw-transition-all remove-cart-item" data-index="' +
            i + '" title="Remove"><i class="fas fa-trash tw-text-base"></i></button>';
        html += '</td>';

        html += '</tr>';
    }

    html += '</tbody></table></div>';
    $container.html(html);

    // Update summary
    var total = catalogue_calculate_cart_total();
    $('#cart-subtotal').text(catalogue_format_currency(total));
    $('#cart-total').text(catalogue_format_currency(total));

    $summary.show();
    $footer.show();
}

// Increase cart quantity (for cart modal)
function catalogue_increase_cart_quantity(index) {
    var cart = catalogue_get_cart();
    if (index >= 0 && index < cart.length) {
        var item = cart[index];
        item.quantity += 1;
        var key = catalogue_get_item_key(item.product_id, item.variation_id);
        catalogue_cart_previous_quantities[key] = item.quantity;
        catalogue_save_cart(cart);
        catalogue_update_cart_badge();
        catalogue_display_cart();
        catalogue_sync_quantities_with_cart();
    }
}

// Decrease cart quantity (for cart modal)
function catalogue_decrease_cart_quantity(index) {
    var cart = catalogue_get_cart();
    if (index < 0 || index >= cart.length) return;
    var item = cart[index];
    if (item.quantity > 1) {
        item.quantity -= 1;
        var key = catalogue_get_item_key(item.product_id, item.variation_id);
        catalogue_cart_previous_quantities[key] = item.quantity;
        catalogue_save_cart(cart);
        catalogue_update_cart_badge();
        catalogue_display_cart();
        catalogue_sync_quantities_with_cart();
    } else {
        // Quantity is 1: confirm before removing item
        swal({
            title: '',
            text: LANG.productcatalogue_confirm_remove_from_cart,
            icon: 'warning',
            buttons: true,
            dangerMode: true,
        }).then(function(confirmed) {
            if (!confirmed) return;
            cart.splice(index, 1);
            var key = catalogue_get_item_key(item.product_id, item.variation_id);
            catalogue_cart_previous_quantities[key] = 0;
            catalogue_save_cart(cart);
            catalogue_update_cart_badge();
            catalogue_display_cart();
            catalogue_sync_quantities_with_cart();
        });
    }
}

// Update cart quantity from input change
function catalogue_update_cart_quantity_from_input(e) {
    var index = parseInt($(e.currentTarget).data('index')) || 0;
    var quantity = parseInt($(e.currentTarget).val()) || 0;

    var cart = catalogue_get_cart();
    if (index >= 0 && index < cart.length) {
        var item = cart[index];

        if (quantity <= 0) {
            // Remove item if quantity is 0
            cart.splice(index, 1);
            var key = catalogue_get_item_key(item.product_id, item.variation_id);
            catalogue_cart_previous_quantities[key] = 0;
        } else {
            // Update quantity
            cart[index].quantity = quantity;
            var key = catalogue_get_item_key(item.product_id, item.variation_id);
            catalogue_cart_previous_quantities[key] = quantity;
        }

        catalogue_save_cart(cart);
        catalogue_update_cart_badge();
        catalogue_display_cart();
        catalogue_sync_quantities_with_cart();
    }
}

// Remove item from cart
function catalogue_remove_from_cart(e) {
    e.preventDefault();
    var index = $(e.currentTarget).data('index');
    var cart = catalogue_get_cart();
    if (index < 0 || index >= cart.length) return;

    swal({
        title: '',
        text: LANG.productcatalogue_confirm_remove_from_cart,
        icon: 'warning',
        buttons: true,
        dangerMode: true,
    }).then(function(confirmed) {
        if (!confirmed) return;
        var removedItem = cart[index];
        cart.splice(index, 1);
        if (removedItem) {
            var key = catalogue_get_item_key(removedItem.product_id, removedItem.variation_id);
            catalogue_cart_previous_quantities[key] = 0;
        }
        catalogue_save_cart(cart);
        catalogue_update_cart_badge();
        catalogue_display_cart();
        catalogue_sync_quantities_with_cart();
    });
}

// Show order form (Step 3)
function catalogue_show_order_form() {
    var cart = catalogue_get_cart();

    if (cart.length === 0) {
        swal({
            title: '',
            text: LANG.productcatalogue_cart_empty,
            icon: 'warning',
            buttons: {
                confirm: {
                    text: 'OK',
                    value: true,
                    visible: true,
                    className: '',
                    closeModal: true
                }
            }
        });
        return;
    }

    // Populate order summary
    catalogue_populate_order_summary();

    // Switch drawer view to order form
    catalogue_show_order_drawer_view();
}

function catalogue_show_cart_drawer_view() {
    $('#order-drawer-view').addClass('tw-hidden');
    $('#cart-drawer-view').removeClass('tw-hidden');
}

function catalogue_show_order_drawer_view() {
    $('#cart-drawer-view').addClass('tw-hidden');
    $('#order-drawer-view').removeClass('tw-hidden');

    // Scroll order view to top
    var $orderScroll = $('#order-drawer-view').find('.tw-overflow-y-auto').first();
    if ($orderScroll.length) {
        $orderScroll.scrollTop(0);
    }
}

// Populate order summary in order form
function catalogue_populate_order_summary() {
    var cart = catalogue_get_cart();
    var html = '<div class="tw-overflow-x-auto">';
    html += '<table class="table tw-w-full">';
    html += '<thead><tr class="tw-bg-gray-100 tw-border-b-2 tw-border-gray-300">';
    html += '<th class="tw-px-4 tw-py-3 tw-text-left tw-text-sm tw-font-bold tw-text-gray-700 tw-uppercase">' + LANG
        .productcatalogue_product + '</th>';
    html += '<th class="tw-px-4 tw-py-3 tw-text-center tw-text-sm tw-font-bold tw-text-gray-700 tw-uppercase">' + LANG
        .productcatalogue_quantity + '</th>';
    html += '<th class="tw-px-4 tw-py-3 tw-text-right tw-text-sm tw-font-bold tw-text-gray-700 tw-uppercase">' + LANG
        .productcatalogue_price + '</th>';
    html += '<th class="tw-px-4 tw-py-3 tw-text-right tw-text-sm tw-font-bold tw-text-gray-700 tw-uppercase">' + LANG
        .productcatalogue_subtotal_label + '</th>';
    html += '</tr></thead>';
    html += '<tbody class="tw-divide-y tw-divide-gray-200">';

    for (var i = 0; i < cart.length; i++) {
        var item = cart[i];
        var price = __num_uf(item.price) || 0;
        var quantity = parseInt(item.quantity) || 0;
        var subtotal = price * quantity;
        var displayName = item.product_name || '';
        if (item.variation_name) {
            displayName += ' - ' + item.variation_name;
        }

        html += '<tr class="hover:tw-bg-gray-50 tw-transition-colors">';
        html += '<td class="tw-px-4 tw-py-3 tw-text-gray-900 tw-font-medium">' + displayName + '</td>';
        html += '<td class="tw-px-4 tw-py-3 tw-text-center tw-text-gray-700 tw-font-semibold">' + quantity + '</td>';
        html += '<td class="tw-px-4 tw-py-3 tw-text-right tw-text-gray-700">' + catalogue_format_currency(price) +
            '</td>';
        html += '<td class="tw-px-4 tw-py-3 tw-text-right tw-text-gray-900 tw-font-bold">' + catalogue_format_currency(
            subtotal) + '</td>';
        html += '</tr>';
    }

    html += '</tbody></table>';
    html += '</div>';
    $('#order-summary-items').html(html);

    // Update total
    var total = catalogue_calculate_cart_total();
    $('#order-total-display').text(catalogue_format_currency(total));
}

// Generate 5-digit order ID
function catalogue_generate_order_id() {
    // Generate a random 5-digit number (10000 to 99999)
    var orderId = Math.floor(10000 + Math.random() * 90000);
    return orderId.toString();
}

// Generate WhatsApp link with order details
function catalogue_generate_whatsapp_link(customerData) {
    var cart = catalogue_get_cart();
    var whatsappNumber = catalogue_cart_whatsapp_number || $('#__whatsapp_number').val() || '';

    if (!whatsappNumber) {
        swal({
            title: '',
            text: LANG.productcatalogue_whatsapp_not_configured,
            icon: 'error',
            buttons: {
                confirm: {
                    text: 'OK',
                    value: true,
                    visible: true,
                    className: '',
                    closeModal: true
                }
            }
        });
        return null;
    }

    // Format WhatsApp number (remove any non-numeric characters except +)
    whatsappNumber = whatsappNumber.replace(/[^\d+]/g, '');

    // Generate 5-digit order ID
    var orderId = catalogue_generate_order_id();

    // Build order message with structured design
    var message = '*' + (LANG.productcatalogue_whatsapp_new_order || 'New Order') + '*\n\n';

    // Order ID
    message += (LANG.productcatalogue_whatsapp_order_id || 'Order ID') + ': ' + orderId + '\n\n';

    // Order Items
    message += '*' + (LANG.productcatalogue_order_items || 'Order Items') + ':*\n';
    for (var i = 0; i < cart.length; i++) {
        var item = cart[i];
        var price = __num_uf(item.price) || 0;
        var quantity = parseInt(item.quantity) || 0;
        var subtotal = price * quantity;
        var displayName = item.product_name || '';
        if (item.variation_name) {
            displayName += ' - ' + item.variation_name;
        }

        var serialNumber = (i + 1);
        message += serialNumber + '. ' + displayName + '\n';
        message += '   ' + (LANG.productcatalogue_quantity || 'Quantity') + ': ' + quantity +
            ' × ' + (LANG.productcatalogue_price || 'Price') + ': ' + catalogue_format_currency(price) +
            ' = ' + catalogue_format_currency(subtotal) + '\n\n';
    }

    // Total
    var total = catalogue_calculate_cart_total();
    var totalFormatted = catalogue_format_currency(total);
    message += '*' + (LANG.productcatalogue_whatsapp_total || 'Total') + ': ' + totalFormatted + '*\n\n';

    // Customer Details
    message += '*' + (LANG.productcatalogue_customer_details_label || 'Customer Details') + ':*\n';
    if (catalogue_customer_field_settings.name !== 'remove' && customerData.name) {
        message += (LANG.productcatalogue_whatsapp_name || 'Name') + ': ' + customerData.name + '\n';
    }
    if (catalogue_customer_field_settings.email !== 'remove' && customerData.email) {
        message += (LANG.productcatalogue_whatsapp_email || 'Email') + ': ' + customerData.email + '\n';
    }
    if (catalogue_customer_field_settings.phone !== 'remove' && customerData.phone) {
        message += (LANG.productcatalogue_whatsapp_phone || 'Phone') + ': ' + customerData.phone + '\n';
    }
    message += (LANG.productcatalogue_delivery_address || 'Delivery Address') + ': ' + customerData.address;

    // Generate WhatsApp URL
    var whatsappUrl = 'https://wa.me/' + whatsappNumber + '?text=' + encodeURIComponent(message);

    return {
        url: whatsappUrl,
        orderId: orderId,
        totalFormatted: totalFormatted
    };
}

// Send order via WhatsApp
function catalogue_send_order_via_whatsapp() {
    // Use HTML5 validation - check if form is valid
    var form = document.getElementById('order-form');
    if (!form.checkValidity()) {
        // Find first invalid field and show custom message
        var firstInvalid = form.querySelector(':invalid');
        if (firstInvalid) {
            firstInvalid.focus();
            var fieldName = firstInvalid.getAttribute('name');
            var message = '';

            switch (fieldName) {
                case 'customer_name':
                    message = LANG.productcatalogue_please_enter_name;
                    break;
                case 'customer_email':
                    if (firstInvalid.validity.typeMismatch) {
                        message = LANG.productcatalogue_please_enter_valid_email;
                    } else {
                        message = LANG.productcatalogue_please_enter_email;
                    }
                    break;
                case 'customer_phone':
                    message = LANG.productcatalogue_please_enter_phone;
                    break;
                case 'delivery_address':
                    message = LANG.productcatalogue_please_enter_address;
                    break;
                default:
                    message = firstInvalid.validationMessage;
            }

            swal({
                title: '',
                text: message,
                icon: 'warning',
                buttons: {
                    confirm: {
                        text: 'OK',
                        value: true,
                        visible: true,
                        className: '',
                        closeModal: true
                    }
                }
            });
        }
        // Trigger HTML5 validation display
        form.reportValidity();
        return;
    }

    // Get form values (fields may be removed by settings)
    var $name = $('#customer_name');
    var $email = $('#customer_email');
    var $phone = $('#customer_phone');
    var $address = $('#delivery_address');

    var customerData = {
        name: $name.length ? $name.val().trim() : '',
        email: $email.length ? $email.val().trim() : '',
        phone: $phone.length ? $phone.val().trim() : '',
        address: $address.length ? $address.val().trim() : ''
    };

    // Generate WhatsApp link
    var orderInfo = catalogue_generate_whatsapp_link(customerData);

    if (!orderInfo) {
        return; // Error already shown in generateWhatsAppLink
    }

    // Open WhatsApp in new window
    window.open(orderInfo.url, '_blank');

    // Close drawer and reset view
    $('#cartDrawer').removeClass('is-open').attr('aria-hidden', 'true');
    $('body').removeClass('cart-open');
    catalogue_show_cart_drawer_view();

    // Clear cart
    catalogue_save_cart([]);
    catalogue_cart_previous_quantities = {};

    // Sync quantities with cart (reset all quantity inputs to 0)
    catalogue_sync_quantities_with_cart();

    // Update cart badge and display
    catalogue_update_cart_badge();
    catalogue_display_cart();

    // Reset form
    $('#order-form')[0].reset();

    // Show success message with SweetAlert (includes Order ID and Total)
    var orderIdLabel = LANG.productcatalogue_whatsapp_order_id || 'Order ID';
    var totalLabel = LANG.productcatalogue_whatsapp_total || 'Total';

    var successContent = document.createElement('div');
    successContent.style.cssText = 'padding-top:10px;text-align:center;';
    successContent.innerHTML =
        '<p style="margin-bottom:14px;font-size:15px;">' + LANG.productcatalogue_order_sent_successfully + '</p>' +
        '<div style="margin-bottom:6px;"><strong>' + orderIdLabel + ': ' + orderInfo.orderId + '</strong></div>' +
        '<div><strong>' + totalLabel + ': ' + orderInfo.totalFormatted + '</strong></div>';

    swal({
        title: '',
        content: successContent,
        icon: 'success',
        buttons: {
            confirm: {
                text: 'OK',
                value: true,
                visible: true,
                className: '',
                closeModal: true
            }
        }
    });
}

// ── Search ────────────────────────────────────────────────────────────────────
function catalogue_apply_search(q) {
    var term = (q || '').toLowerCase().trim();

    var url = new URL(window.location.href);
    if (term) {
        url.searchParams.set('q', q.trim());
    } else {
        url.searchParams.delete('q');
    }
    history.replaceState(null, '', url.toString());

    var anyVisible = false;
    $('.product-category-section').each(function() {
        var $section = $(this);
        var sectionVisible = false;
        $section.find('.product-card-wrapper').each(function() {
            var $card = $(this);
            var match = !term
                || ($card.data('name')    || '').indexOf(term) !== -1
                || ($card.data('sku')     || '').indexOf(term) !== -1
                || ($card.data('sub-sku') || '').indexOf(term) !== -1;
            $card.toggle(match);
            if (match) sectionVisible = true;
        });
        $section.toggle(sectionVisible);
        if (sectionVisible) anyVisible = true;
    });

    $('#catalogue-no-results').toggle(!anyVisible && !!term);
}

$(document).ready(function() {
    var initialQ = new URLSearchParams(window.location.search).get('q') || '';
    if (initialQ) {
        $('#catalogue-search').val(initialQ);
        $('#catalogue-search-clear').show();
        catalogue_apply_search(initialQ);
    }

    var searchTimer;
    $('#catalogue-search').on('input', function() {
        clearTimeout(searchTimer);
        var val = $(this).val();
        $('#catalogue-search-clear').toggle(val.length > 0);
        searchTimer = setTimeout(function() { catalogue_apply_search(val); }, 220);
    });

    $('#catalogue-search-clear').on('click', function() {
        $('#catalogue-search').val('').trigger('input').focus();
    });
});
</script>
@endsection