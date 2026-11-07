@extends('layouts.app')
@section('title', __('fieldforce::lang.field_force'))

@section('content')

@include('fieldforce::layouts.nav')


<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>@lang('fieldforce::lang.field_force')
    </h1>
</section>

<!-- Main content -->
<section class="content">
    @component('components.filters', ['title' => __('report.filters')])
    <div class="col-md-3">
        <div class="form-group">
            {!! Form::label('contact_id_filter', __('report.contact') . ':') !!}
            {!! Form::select('contact_id_filter', [], null, ['class' => 'form-control', 'placeholder' => __('lang_v1.all'), 'id' => 'contact_id_filter']); !!}
        </div>
    </div>
    @can('visit.view_all')
    <div class="col-md-3">
        <div class="form-group">
            {!! Form::label('assigned_to_filter', __('lang_v1.assigned_to') .':*') !!}
            {!! Form::select('assigned_to_filter', $users, null, ['class' => 'form-control select2', 'style' => 'width: 100%;', 'placeholder' => __('lang_v1.all')]); !!}
        </div>
    </div>
    @endcan
    <div class="col-md-3">
        <div class="form-group">
            {!! Form::label('status_filter', __('sale.status') . ':*' )!!}
            <select class="form-control" id="status_filter">
                <option value="">{{__('lang_v1.all')}}</option>
                @foreach($visit_statuses as $key => $value)
                    <option value="{{$key}}" @if(request()->get('status') == $key) {{'selected'}} @endif>{{$value['label']}}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            {!! Form::label('date_range_filter', __('report.date_range') . ':') !!}
            {!! Form::text('date_range_filter', null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'readonly']); !!}
        </div>
    </div>
    @endcomponent
    @component('components.widget', ['class' => 'box-primary', 'title' => __('fieldforce::lang.visits')])
    @can('visit.create')
    @slot('tool')
    <div class="box-tools">
        <button type="button" class="btn btn-block btn-primary btn-modal" data-href="{{action('\Modules\FieldForce\Http\Controllers\FieldForceController@create')}}" data-container="#visit_modal">
            <i class="fa fa-plus"></i> @lang( 'messages.add' )</button>
    </div>
    @endslot
    @endcan
    <div class="table-responsive">
        <table class="table table-bordered table-striped" id="field_force_visits_table">
            <thead>
                <tr>
                    <th>@lang( 'fieldforce::lang.visit_on' )</th>
                    <th>@lang( 'fieldforce::lang.visited_on' )</th>
                    <th>@lang( 'fieldforce::lang.visit_id' )</th>
                    <th>@lang( 'lang_v1.assigned_to' )</th>
                    <th>@lang( 'report.contact' )</th>
                    <th>@lang( 'sale.status' )</th>
                    <th>@lang( 'business.address' )</th>
                    <th>@lang( 'messages.action' )</th>
                </tr>
            </thead>
        </table>
    </div>
    @endcomponent

    <div class="modal fade" id="visit_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>
    <div class="modal fade" id="update_status_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>

</section>

@stop

@section('javascript')

<!-- Add the Awesomplete JavaScript file to the bottom of your HTML -->
<!-- Add the Awesomplete JavaScript file to the bottom of your HTML -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/awesomplete/1.1.2/awesomplete.min.js"></script>
  <!-- Add the Awesomplete CSS file to the head of your HTML -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/awesomplete/1.1.2/awesomplete.css" />



<script type="text/javascript">
    $(document).ready(function() {
        $('select#contact_id_filter').select2({
            ajax: {
                url: '/contacts/customers',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        q: params.term, // search term
                        page: params.page,
                        all_contact: true
                    };
                },
                processResults: function(data) {
                    return {
                        results: data,
                    };
                },
            },
            templateResult: function(data) {
                var template = '';
                if (data.supplier_business_name) {
                    template += data.supplier_business_name + "<br>";
                }
                template += data.text + "<br>" + LANG.mobile + ": " + data.mobile;

                return template;
            },
            minimumInputLength: 1,
            escapeMarkup: function(markup) {
                return markup;
            },
        });

        field_force_visits_table = $('#field_force_visits_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '/field-force/visits',
                data: function(d) {
                    d.contact_id = $('#contact_id_filter').val();
                    d.assigned_to = $('#assigned_to_filter').val();
                    d.status = $('#status_filter').val();

                    var start = '';
                    var end = '';
                    if ($('#date_range_filter').val()) {
                        start = $('input#date_range_filter')
                            .data('daterangepicker')
                            .startDate.format('YYYY-MM-DD');
                        end = $('input#date_range_filter')
                            .data('daterangepicker')
                            .endDate.format('YYYY-MM-DD');
                    }
                    d.start_date = start;
                    d.end_date = end;
                },
            },
            columns: [{
                    data: 'visit_on',
                    name: 'visit_on'
                },
                {
                    data: 'visited_on',
                    name: 'visited_on'
                },
                {
                    data: 'visit_id',
                    name: 'visit_id'
                },
                {
                    data: 'assigned_user',
                    name: 'assigned_user'
                },
                {
                    data: 'contact_name',
                    name: 'c.name'
                },
                {
                    data: 'status',
                    name: 'status'
                },
                {
                    data: 'visited_address',
                    name: 'visited_address',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                },
            ],
        });

        $(document).on('change', '#contact_id_filter, #assigned_to_filter, #status_filter', function() {
            field_force_visits_table.ajax.reload();
        });

        $('#date_range_filter').daterangepicker(
            dateRangeSettings,
            function(start, end) {
                $('#date_range_filter').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
                field_force_visits_table.ajax.reload();
            }
        );
        $('#date_range_filter').on('cancel.daterangepicker', function(ev, picker) {
            $('#date_range_filter').val('');
            field_force_visits_table.ajax.reload();
        });

        // Set default date from get parameter
        @if(!empty($default_start_date) && !empty($default_end_date))
            $('#date_range_filter').val({{$default_start_date . ' - ' . $default_end_date}});
            $('#date_range_filter').data('daterangepicker').setStartDate('{{$default_start_date}}');
            $('#date_range_filter').data('daterangepicker').setEndDate('{{$default_end_date}}');
            field_force_visits_table.ajax.reload();
        @endif

        $(document).on('click', 'a.delete_visit', function(e) {
            e.preventDefault();
            swal({
                title: LANG.sure,
                icon: 'warning',
                buttons: true,
                dangerMode: true,
            }).then(willDelete => {
                if (willDelete) {
                    var href = $(this).attr('href');
                    var data = $(this).serialize();

                    $.ajax({
                        method: 'DELETE',
                        url: href,
                        dataType: 'json',
                        data: data,
                        success: function(result) {
                            if (result.success == true) {
                                toastr.success(result.msg);
                                field_force_visits_table.ajax.reload();
                            } else {
                                toastr.error(result.msg);
                            }
                        },
                    });
                }
            });
        });
    });

    $(document).on('shown.bs.modal', '#visit_modal', function() {
        $('select#contact_id').select2({
            ajax: {
                url: '/contacts/customers',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        q: params.term, // search term
                        page: params.page,
                        all_contact: true
                    };
                },
                processResults: function(data) {
                    return {
                        results: data,
                    };
                },
            },
            templateResult: function(data) {
                var template = '';
                if (data.supplier_business_name) {
                    template += data.supplier_business_name + "<br>";
                }
                template += data.text + "<br>" + LANG.mobile + ": " + data.mobile;

                return template;
            },
            minimumInputLength: 1,
            escapeMarkup: function(markup) {
                return markup;
            },
        });

        $('#visit_on').datetimepicker({
            format: moment_date_format + ' ' + moment_time_format,
            ignoreReadonly: true,
        });

        $('#visit_modal form#add_visit_form').submit(function(e) {
            e.preventDefault();
        }).validate({
            submitHandler: function(form) {
                var data = $(form).serialize();
                $.ajax({
                    method: $('form#add_visit_form').attr('method'),
                    url: $('form#add_visit_form').attr('action'),
                    dataType: 'json',
                    data: data,
                    beforeSend: function(xhr) {
                        __disable_submit_button($(form).find('button[type="submit"]'));
                    },
                    success: function(result) {
                        if (result.success == true) {
                            $('#visit_modal').modal('hide');
                            toastr.success(result.msg);
                            field_force_visits_table.ajax.reload();
                        } else {
                            toastr.error(result.msg);
                        }
                    },
                });
            },
        });
    })

    $(document).on('shown.bs.modal', '#update_status_modal, #visit_modal', function() {
        $(this)
            .find('.input-icheck')
            .each(function() {
                $(this).iCheck({
                    checkboxClass: 'icheckbox_square-blue',
                    radioClass: 'iradio_square-blue',
                });
            });
    });
    $(document).on('click', '#get_current_location_ff', function() {
        getFieldForceFullAddress();
    });

    $(document).on('ifChanged', 'input[type=radio][name=status]', function() {
        var val = $(this).val();
        if (val == 'did_not_meet_contact') {
            $('#reason_to_not_meet_contact').removeClass('hide');
        } else {
            $('#reason_to_not_meet_contact').addClass('hide');
        }
    });

    $(document).on('ifChanged', 'input[type=radio][name=visit_to_type]', function() {
        var val = $(this).val();

        if (val == 'contact') {
            $('#contact_div').removeClass('hide');
            $('#others_div').addClass('hide');
        } else {
            $('#contact_id').val('');
            $('#contact_id').change();
            $('#contact_div').addClass('hide');
            $('#others_div').removeClass('hide');
        }
    });

    function getFieldForceFullAddress() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    var latitude = position.coords.latitude;
                    var longitude = position.coords.longitude;
                    $('#visited_address_latitude').val(latitude);
                    $('#visited_address_longitude').val(longitude);

                    $.ajax({
                        url: '/user-location/' + latitude + ',' + longitude,
                        dataType: 'json',
                        success: function(result) {
                            if (typeof result.address !== 'undefined') {
                                $('#visited_address_ff').val(result.address);
                                $('#visited_address_div_ff').html(result.address);
                            } else if (typeof result.error_message !== 'undefined') {
                                console.log(result.error_message);
                            }
                        }
                    });

                },
                () => {
                    console.log("Error: The Geolocation service failed.");
                }
            );
        } else {
            // Browser doesn't support Geolocation
            console.log("Browser doesn't support Geolocation");
        }
    }
</script>

@endsection