{{-- Desktop search: fixed below main header — compact height so navbar flex row is not visually crowded --}}
<style>
    /* Push scrollable content down so it starts below the floating search bar */
    @media (min-width: 768px) {
        #scrollable-container {
            padding-top: 3.5rem;
        }
    }

    /* On lg+ screens the sidebar is visible (tw-w-64 = 16rem = 256px).
       Shift the search bar centre to sit inside the content area only. */
    @media (min-width: 1024px) {
        #searchbox-desktop-float {
            left: calc(50% + 8rem) !important;          /* 8rem = half of 16rem sidebar */
            max-width: min(42rem, calc(100vw - 18rem)) !important;
        }
    }

    /* When the sidebar is collapsed (JS adds .sidebar-collapse to <body>) */
    body.sidebar-collapse #searchbox-desktop-float {
        left: 50% !important;
        max-width: 42rem !important;
    }
</style>
<div
    id="searchbox-desktop-float"
    class="searchbox-desktop-float tw-pointer-events-none tw-fixed tw-left-1/2 tw-z-[28] tw-w-full tw-max-w-2xl tw--translate-x-1/2 tw-px-3 tw-hidden md:tw-block"
    style="top: max(5.75rem, calc(env(safe-area-inset-top, 0px) + 4.75rem));"
    role="search"
>
    <div class="tw-pointer-events-auto tw-rounded-xl tw-bg-white/95 tw-p-2 tw-shadow-md tw-ring-1 tw-ring-black/5">
        @include('searchbox::layouts.partials.universal_search')
    </div>
</div>
