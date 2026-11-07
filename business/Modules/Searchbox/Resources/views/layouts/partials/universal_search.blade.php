{{-- Desktop header search (md+) — inner .tw-relative must stay a single row for input + icon; toolbar is injected above via JS --}}
<div class="tw-relative tw-w-full tw-min-w-0">
    <span class="tw-pointer-events-none tw-absolute tw-left-0 tw-top-0 tw-bottom-0 tw-z-10 tw-flex tw-w-11 tw-items-center tw-justify-center tw-text-gray-500" aria-hidden="true">
        <svg class="tw-h-[18px] tw-w-[18px] tw-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
    </span>
    <input type="search" id="universal_search_input" name="universal_q" autocomplete="off"
        class="tw-w-full tw-rounded-xl tw-border tw-border-white/40 tw-bg-white tw-py-2.5 tw-pr-24 tw-text-sm tw-font-medium tw-text-gray-800 tw-shadow-sm placeholder:tw-text-gray-500 focus:tw-border-primary-400 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-primary-300/50"
        style="background-color:#ffffff; color:#1f2937; padding-left: 2.75rem;"
        placeholder="{{ __('searchbox::lang.search_placeholder') }}"
        aria-label="{{ __('searchbox::lang.search_placeholder') }}" />
    <kbd class="tw-pointer-events-none tw-absolute tw-right-2 tw-top-1/2 tw-hidden -tw-translate-y-1/2 tw-rounded-md tw-border tw-border-gray-300 tw-bg-gray-50 tw-px-1.5 tw-py-0.5 tw-text-[10px] tw-font-semibold tw-text-gray-600 sm:tw-inline-block">{{ __('searchbox::lang.search_shortcut_hint') }}</kbd>
    <div id="universal_search_results"
        class="tw-absolute tw-left-0 tw-right-0 tw-top-full tw-z-[60] tw-mt-1 tw-hidden tw-max-h-[min(70vh,28rem)] tw-overflow-y-auto tw-rounded-lg tw-bg-white tw-shadow-xl tw-ring-1 tw-ring-gray-200"></div>
</div>
