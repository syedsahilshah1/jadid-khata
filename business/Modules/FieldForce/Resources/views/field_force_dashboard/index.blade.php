@extends('layouts.app')
@section('title', __('fieldforce::lang.field_force'))

@section('content')

@include('fieldforce::layouts.nav')


<section class="content no-print">
    <div class="row">
        <div class="col-md-4">
            <div class="col-md-12">
                <div class="info-box info-box-new-style">
                    <span class="info-box-icon bg-aqua"><i class="fas fa-calendar-check"></i></span>

                    <div class="info-box-content">
                        <span class="info-box-text">{{ __('fieldforce::lang.assigned') }}</span>
                        <span class="info-box-number">{{$my_visit_status->assigned ?? 0}}</span>

                    </div>
                    <!-- /.info-box-content -->
                </div>
            </div>
            <div class="col-md-12">
                <div class="info-box info-box-new-style">
                    <span class="info-box-icon bg-green"><i class="fas fa-user-check"></i></span>

                    <div class="info-box-content">
                        <span class="info-box-text">{{ __('fieldforce::lang.met_contact') }}</span>
                        <span class="info-box-number">{{$my_visit_status->met_contact ?? 0}}</span>

                    </div>
                    <!-- /.info-box-content -->
                </div>
            </div>
            <div class="col-md-12">
                <div class="info-box info-box-new-style">
                    <span class="info-box-icon bg-red"><i class="fas fa-user-times"></i></span>

                    <div class="info-box-content">
                        <span class="info-box-text">{{ __('fieldforce::lang.did_not_meet_contact') }}</span>
                        <span class="info-box-number">{{$my_visit_status->did_not_meet_contact ?? 0}}</span>

                    </div>
                    <!-- /.info-box-content -->
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="box box-solid">
                <div class="box-header with-border">
                    <h3 class="box-title">@lang('fieldforce::lang.my_visits')</h3>
                </div>
                <div class="box-body p-10">
                    <table class="table no-margin">
                        <tr>
                            <th></th>
                            <th>@lang('fieldforce::lang.contact')</th>
                            <th>@lang('fieldforce::lang.others')</th>
                        </tr>
                        <tr>
                            <th>@lang('fieldforce::lang.visits_today')</th>
                            <td>{{$my_visits->contact_visits_today ?? 0}}</td>
                            <td>{{$my_visits->others_visits_today ?? 0}}</td>
                        </tr>
                        <tr>
                            <th>@lang('fieldforce::lang.visits_yesterday')</th>
                            <td>{{$my_visits->contact_visits_yesterday ?? 0}}</td>
                            <td>{{$my_visits->others_visits_yesterday ?? 0}}</td>
                        </tr>
                        <tr>
                            <th>@lang('fieldforce::lang.visits_this_month')</th>
                            <td>{{$my_visits->contact_visits_this_month ?? 0}}</td>
                            <td>{{$my_visits->others_visits_this_month ?? 0}}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <hr>
    @if($is_admin)
    <div class="row row-custom">
        <div class="col-md-3 col-sm-6 col-xs-12 col-custom">
            <div class="info-box info-box-new-style">
                <span class="info-box-icon bg-orange">
                    <i class="fas fa-life-ring"></i>
                </span>

                <div class="info-box-content">
                    <span class="info-box-text">{{ __('fieldforce::lang.total_visits') }}</span>
                    <span class="info-box-number invoice_due">{{$all_visits->total_visits ?? 0}}</span>

                    <span class="info-box-text">
                        <a href="{{action('\Modules\FieldForce\Http\Controllers\FieldForceController@index')}}" target="_blank">
                            {{ __('fieldforce::lang.view') }} <i class="fa fa-arrow-circle-right"></i>
                        </a>
                    </span>
                </div>
                <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
        </div>
        <!-- /.col -->
        <div class="col-md-3 col-sm-6 col-xs-12 col-custom">
            <div class="info-box info-box-new-style">
                <span class="info-box-icon bg-aqua"><i class="fas fa-calendar-check"></i></span>

                <div class="info-box-content">
                    <span class="info-box-text">{{ __('fieldforce::lang.assigned') }}</span>
                    <span class="info-box-number">{{$all_visits->assigned ?? 0}}</span>

                    <span class="info-box-text">
                        <a href="{{action('\Modules\FieldForce\Http\Controllers\FieldForceController@index') . '?status=assigned'}}" target="_blank">
                            {{ __('fieldforce::lang.view') }} <i class="fa fa-arrow-circle-right"></i>
                        </a>
                    </span>
                </div>
                <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
        </div>
        <!-- /.col -->
        <div class="col-md-3 col-sm-6 col-xs-12 col-custom">
            <div class="info-box info-box-new-style">
                <span class="info-box-icon bg-green"><i class="fas fa-user-check"></i></span>

                <div class="info-box-content">
                    <span class="info-box-text">{{ __('fieldforce::lang.met_contact') }}</span>
                    <span class="info-box-number">{{$all_visits->met_contact ?? 0}}</span>

                    <span class="info-box-text">
                        <a href="{{action('\Modules\FieldForce\Http\Controllers\FieldForceController@index') . '?status=met_contact'}}" target="_blank">
                            {{ __('fieldforce::lang.view') }} <i class="fa fa-arrow-circle-right"></i>
                        </a>
                    </span>
                </div>
                <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
        </div>
        <!-- /.col -->
        <div class="col-md-3 col-sm-6 col-xs-12 col-custom">
            <div class="info-box info-box-new-style">
                <span class="info-box-icon bg-red">
                    <i class="fas fa-user-times"></i>
                </span>

                <div class="info-box-content">
                    <span class="info-box-text">{{ __('fieldforce::lang.did_not_meet_contact') }}</span>
                    <span class="info-box-number">{{$all_visits->did_not_meet_contact ?? 0}}</span>

                    <span class="info-box-text">
                        <a href="{{action('\Modules\FieldForce\Http\Controllers\FieldForceController@index') . '?status=did_not_meet_contact'}}" target="_blank">
                            {{ __('fieldforce::lang.view') }} <i class="fa fa-arrow-circle-right"></i>
                        </a>
                    </span>
                </div>
                <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
        </div>
        <!-- /.col -->
    </div>

    <div class="row row-custom">
        <div class="col-md-3 col-sm-6 col-xs-12 col-custom">
            <div class="info-box info-box-new-style">
                <span class="info-box-icon bg-orange">
                    <i class="fas fa-life-ring"></i>
                </span>

                <div class="info-box-content">
                    <span class="info-box-text">{{ __('fieldforce::lang.today_total_visits') }}</span>
                    <span class="info-box-number invoice_due">{{$today_visits->total_visits ?? 0}}</span>

                    <span class="info-box-text">
                        <a href="{{action('\Modules\FieldForce\Http\Controllers\FieldForceController@index') . '?start_date=' . @format_date(\Carbon::today()) . '&end_date=' . @format_date(\Carbon::today())}}" target="_blank">
                            {{ __('fieldforce::lang.view') }} <i class="fa fa-arrow-circle-right"></i>
                        </a>
                    </span>
                </div>
                <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
        </div>
        <!-- /.col -->
        <div class="col-md-3 col-sm-6 col-xs-12 col-custom">
            <div class="info-box info-box-new-style">
                <span class="info-box-icon bg-aqua"><i class="fas fa-calendar-check"></i></span>

                <div class="info-box-content">
                    <span class="info-box-text">{{ __('fieldforce::lang.today_assigned') }}</span>
                    <span class="info-box-number">{{$today_visits->assigned ?? 0}}</span>

                    <span class="info-box-text">
                        <a href="{{action('\Modules\FieldForce\Http\Controllers\FieldForceController@index') . '?status=assigned&start_date=' . @format_date(\Carbon::today()) . '&end_date=' . @format_date(\Carbon::today())}}" 
                        target="_blank">{{ __('fieldforce::lang.view') }} <i class="fa fa-arrow-circle-right"></i>
                        </a>
                    </span>
                </div>
                <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
        </div>
        <!-- /.col -->
        <div class="col-md-3 col-sm-6 col-xs-12 col-custom">
            <div class="info-box info-box-new-style">
                <span class="info-box-icon bg-green"><i class="fas fa-user-check"></i></span>

                <div class="info-box-content">
                    <span class="info-box-text">{{ __('fieldforce::lang.today_met_contact') }}</span>
                    <span class="info-box-number">{{$today_visits->met_contact ?? 0}}</span>

                    <span class="info-box-text">
                        <a href="{{action('\Modules\FieldForce\Http\Controllers\FieldForceController@index') . '?status=met_contact&start_date=' . @format_date(\Carbon::today()) . '&end_date=' . @format_date(\Carbon::today())}}" 
                        target="_blank">{{ __('fieldforce::lang.view') }} <i class="fa fa-arrow-circle-right"></i>
                        </a>
                    </span>
                </div>
                <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
        </div>
        <!-- /.col -->
        <div class="col-md-3 col-sm-6 col-xs-12 col-custom">
            <div class="info-box info-box-new-style">
                <span class="info-box-icon bg-red">
                    <i class="fas fa-user-times"></i>
                </span>

                <div class="info-box-content">
                    <span class="info-box-text">{{ __('fieldforce::lang.today_did_not_meet_contact') }}</span>
                    <span class="info-box-number">{{$today_visits->did_not_meet_contact ?? 0}}</span>

                    <span class="info-box-text">
                        <a href="{{action('\Modules\FieldForce\Http\Controllers\FieldForceController@index') . '?status=did_not_meet_contact&start_date=' . @format_date(\Carbon::today()) . '&end_date=' . @format_date(\Carbon::today())}}" 
                        target="_blank">{{ __('fieldforce::lang.view') }} <i class="fa fa-arrow-circle-right"></i>
                        </a>
                    </span>
                </div>
                <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
        </div>
        <!-- /.col -->
    </div>

    <div class="row">
        <div class="col-md-12">
            @component('components.widget', ['class' => 'box-solid', 'title' => __('fieldforce::lang.visits_by_user')])

            <table class="table table-bordered table-striped" id="visits_by_user" style="width: 100%;">
                <thead>
                    <tr>
                        <th>@lang('role.user')</th>
                        <th>@lang('fieldforce::lang.contact_visits_today')</th>
                        <th>@lang('fieldforce::lang.others_visits_today')</th>
                        <th>@lang('fieldforce::lang.contact_visits_yesterday')</th>
                        <th>@lang('fieldforce::lang.others_visits_yesterday')</th>
                        <th>@lang('fieldforce::lang.contact_visits_this_month')</th>
                        <th>@lang('fieldforce::lang.others_visits_this_month')</th>
                        <th>@lang('fieldforce::lang.total_visits')</th>
                    </tr>
                </thead>
            </table>

            @endcomponent
        </div>
    </div>

    @endif
</section>
@stop

@section('javascript')
<script type="text/javascript">
    $(document).ready(function() {

        @if($is_admin)
        $("#visits_by_user").DataTable({
            processing: true,
            serverSide: true,
            scrollY: "75vh",
            scrollX: true,
            scrollCollapse: true,
            fixedHeader: false,
            'ajax': {
                url: "{{action('\Modules\FieldForce\Http\Controllers\FieldForceDashboardController@visitByUsers')}}"
            },
            columns: [{
                    data: 'name',
                    name: 'u.first_name'
                },
                {
                    data: 'contact_visits_today',
                    searchable: false
                },
                {
                    data: 'others_visits_today',
                    searchable: false
                },
                {
                    data: 'contact_visits_yesterday',
                    searchable: false
                },
                {
                    data: 'others_visits_yesterday',
                    searchable: false
                },
                {
                    data: 'contact_visits_this_month',
                    searchable: false
                },
                {
                    data: 'others_visits_this_month',
                    searchable: false
                },
                {
                    data: 'all_visits',
                    searchable: false
                }
            ],
        });
        @endif
    });
</script>
@endsection