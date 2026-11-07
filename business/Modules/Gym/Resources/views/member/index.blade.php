@extends('layouts.app')
@section('title', __('gym::lang.members'))
@section('content')
    @include('gym::layouts.nav')
    <section class="content-header">
        <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black"> @lang('gym::lang.members')
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
       
        @component('components.widget')
            <div class="box-tools tw-flex tw-justify-end tw-gap-2.5 tw-mb-4">
                @can('hms.add_booking')
                    <a class="tw-dw-btn tw-bg-gradient-to-r tw-from-indigo-600 tw-to-blue-500 tw-font-bold tw-text-white tw-border-none tw-rounded-full pull-right"
                        href="{{ action([\Modules\Gym\Http\Controllers\MemberController::class, 'create']) }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="icon icon-tabler icons-tabler-outline icon-tabler-plus">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <path d="M12 5l0 14" />
                            <path d="M5 12l14 0" />
                        </svg> @lang('messages.add')
                    </a>
                @endcan
            </div>
            <table class="table table-bordered table-striped" id="member_table">
                <thead>
                    <tr>
                        <th>@lang('contact.name')</th>
                        <th>@lang('gym::lang.age')</th>
                        <th>@lang('business.email')</th>
                        <th>@lang('contact.mobile')</th>
                        <th>@lang('gym::lang.package')</th>
                        <th>
                            @lang('lang_v1.created_at')
                        </th>
                        <th>
                            @lang('messages.action')
                        </th>
                    </tr>
                </thead>
            </table>
        @endcomponent

    </section>
    <div class="modal fade view_modal_in" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel"></div>
    </div>
    <!-- /.content -->
@endsection

@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            member_table = $('#member_table').DataTable({
                processing: true,
                serverSide: true,
                fixedHeader: false,
                ajax: {
                    url: "{{ action([\Modules\Gym\Http\Controllers\MemberController::class, 'index']) }}",
                },
                aaSorting: [
                    [5, 'desc']
                ],
                columns: [{
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'age',
                        name: 'age'
                    },
                    {
                        data: 'email',
                        name: 'email'
                    },
                    {
                        data: 'mobile',
                        name: 'mobile'
                    },
                    {
                        data: 'package',
                        name: 'package',
                        orderable: false
                    },
                    {
                        data: 'created_at',
                        name: 'contacts.created_at'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false
                    },
                ]
            });
        });
    </script>
@endsection
