{{--
    Referral link field with a copy button.

    Built with tailwind flex rather than a bootstrap .input-group: that markup
    expects a bootstrap .btn, and a tw-dw-btn dropped inside it keeps its own
    height and radius, which leaves the button detached from the field.

    Only utilities present in public/css/tailwind/app.css are used here.

    Expects:  $link
    Optional: $help - explanatory line above the field.
--}}
@php($help = $help ?? null)
<div class="col-xs-12">
    @if (!empty($help))
        <p class="tw-text-sm tw-text-gray-500 tw-mb-3">{{ $help }}</p>
    @endif

    <div class="tw-flex tw-flex-col sm:tw-flex-row tw-gap-2">
        {{-- Field --}}
        <div
            class="tw-flex tw-items-center tw-gap-2 tw-flex-1 tw-min-w-0 tw-h-12 tw-px-4 tw-bg-gray-50 tw-rounded-lg tw-ring-1 tw-ring-gray-200">
            <svg aria-hidden="true" class="tw-w-4 tw-h-4 tw-shrink-0 tw-text-gray-500"
                xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                fill="none" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                <path d="M9 15l6 -6"></path>
                <path d="M11 6l.463 -.536a5 5 0 0 1 7.071 7.072l-.534 .464"></path>
                <path d="M13 18l-.397 .534a5.068 5.068 0 0 1 -7.127 0a4.972 4.972 0 0 1 0 -7.071l.524 -.463"></path>
            </svg>

            <input type="text" id="referral_link" value="{{ $link }}" readonly
                class="tw-w-full tw-h-full tw-p-0 tw-bg-transparent tw-border-0 tw-text-sm tw-font-mono tw-text-gray-700 tw-truncate focus:tw-outline-none">
        </div>

        {{-- Copy button, matched to the field height so the row lines up --}}
        <button type="button" id="copy_referral_link"
            class="tw-dw-btn tw-dw-btn-outline tw-dw-btn-primary tw-h-12 tw-px-4 tw-shrink-0 tw-whitespace-nowrap tw-gap-2">
            <svg aria-hidden="true" class="tw-w-4 tw-h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                <path d="M8 8m0 2a2 2 0 0 1 2 -2h8a2 2 0 0 1 2 2v8a2 2 0 0 1 -2 2h-8a2 2 0 0 1 -2 -2z"></path>
                <path d="M16 8v-2a2 2 0 0 0 -2 -2h-8a2 2 0 0 0 -2 2v8a2 2 0 0 0 2 2h2"></path>
            </svg>
            @lang('superadmin::lang.copy')
        </button>
    </div>
</div>
