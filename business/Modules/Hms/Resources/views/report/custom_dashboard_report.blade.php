<div class="col-md-{{ round(($dashboard_detail->size / 100) * 12) }}">
    <div
        class="tw-mb-4 tw-transition-all lg:tw-col-span-2 tw-duration-200 tw-bg-white tw-shadow-sm tw-rounded-xl tw-ring-1 hover:tw-shadow-md hover:tw-translate-y-0.5 tw-ring-gray-200">
        <div class="tw-p-2 sm:tw-p-3">
            <div class="box-header text-center">
                <h3 class="box-title pull-left">{{ $dashboard_detail->heading }}</h3>
                <h6 class="text-uppercase"
                    style="font-family: Arial, sans-serif; color: #333; letter-spacing: 2px; margin-top: 5px;">
                    {{ @format_date($dates['date_to']) }} - {{ @format_date($dates['date_from']) }}
                </h6>
            </div>
            <div class="tw-flow-root tw-mt-5  tw-border-gray-200">
                <div class="tw-overflow-x-auto">
                    <div class="tw-inline-block tw-min-w-full tw-py-2 tw-align-middle sm:tw-px-5">
                        @include('hms::partials.hms_report_table')
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
