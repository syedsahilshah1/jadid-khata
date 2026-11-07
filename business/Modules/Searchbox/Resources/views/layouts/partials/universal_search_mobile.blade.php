<button type="button" id="mobile_search_btn"
    class="tw-fixed tw-bottom-6 tw-right-6 tw-z-[95] tw-inline-flex tw-items-center tw-justify-center tw-rounded-full tw-bg-primary-700 tw-p-3 tw-text-white tw-shadow-xl tw-ring-1 tw-ring-white/20 hover:tw-bg-primary-800 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-primary-300"
    style="position:fixed;right:24px;bottom:24px;z-index:95;width:52px;height:52px;border-radius:9999px;background:#0f766e;color:#fff;display:flex;align-items:center;justify-content:center;box-shadow:0 10px 25px rgba(0,0,0,.25);border:1px solid rgba(255,255,255,.25);"
    title="{{ __('searchbox::lang.search_placeholder') }}" aria-label="{{ __('searchbox::lang.search_placeholder') }}">
    <svg class="tw-h-5 tw-w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:22px;height:22px;stroke:#ffffff;">
        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
    </svg>
</button>

<div id="mobile_search_overlay" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document" style="margin-top: 8vh;">
        <div class="modal-content" style="border-radius:16px;overflow:hidden;border:1px solid #d1d5db;box-shadow:0 20px 50px rgba(2,6,23,.25);">
            <div style="background:linear-gradient(120deg,#0f766e,#0ea5a4);padding:12px 16px;">
                <div class="tw-flex tw-items-center tw-justify-between">
                    <div class="tw-text-white">
                        <div class="tw-text-sm tw-font-semibold">Universal Search</div>
                        <div class="tw-text-xs tw-opacity-80">Products, contacts, invoices, payments and more</div>
                    </div>
                    <div class="tw-text-xs tw-font-semibold tw-text-white/90 tw-border tw-border-white/40 tw-rounded-md tw-px-2 tw-py-0.5">Ctrl+K</div>
                </div>
            </div>
            <div class="tw-flex tw-items-center tw-gap-2 tw-border-b tw-border-gray-200 tw-px-3 tw-py-3 tw-bg-white">
                <div class="tw-relative tw-min-w-0 tw-flex-1">
                    <span class="tw-pointer-events-none tw-absolute tw-left-0 tw-top-0 tw-bottom-0 tw-z-10 tw-flex tw-w-11 tw-items-center tw-justify-center tw-text-gray-400" aria-hidden="true">
                        <svg class="tw-h-[18px] tw-w-[18px] tw-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                    <input type="search" id="mobile_search_input" autocomplete="off"
                        class="tw-w-full tw-rounded-lg tw-border tw-border-gray-200 tw-bg-gray-50 tw-py-2.5 tw-pr-11 tw-text-sm tw-text-gray-900 focus:tw-border-primary-500 focus:tw-outline-none focus:tw-ring-1 focus:tw-ring-primary-500"
                        style="padding-left: 2.75rem;"
                        placeholder="{{ __('searchbox::lang.search_placeholder') }}"
                        aria-label="{{ __('searchbox::lang.search_placeholder') }}" />
                </div>
                <button type="button" id="mobile_search_close"
                    class="tw-shrink-0 tw-rounded-lg tw-bg-gray-100 tw-px-3 tw-py-2 tw-text-sm tw-font-medium tw-text-gray-700 hover:tw-bg-gray-200">
                    @lang('messages.close')
                </button>
            </div>
            <div id="mobile_search_results" class="tw-max-h-[65vh] tw-overflow-y-auto tw-p-2 tw-bg-gray-50"></div>
        </div>
    </div>
</div>
