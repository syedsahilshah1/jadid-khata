@extends('layouts.app')
@section('title', __('gym::lang.attendance'))
@section('content')
    @include('gym::layouts.nav')
    <section class="content-header">
        <h3 class="text-muted"> @lang('gym::lang.attendance_for_today')  {{ @format_date(now())}}
        </h3>
    </section>

    <!-- Main content -->
    <section class="content">

        @component('components.widget')
            <table class="table table-bordered table-striped" id="member_table">
                <thead>
                    <tr>
                        <th>@lang('contact.name')</th>
                        <th>@lang('business.email')</th>
                        <th>@lang('contact.mobile')</th>
                        <th>
                            @lang('lang_v1.created_at')
                        </th>
                        <th>@lang('gym::lang.in_time')</th>
                        <th>@lang('gym::lang.out_time')</th>
                    </tr>
                </thead>
            </table>
        @endcomponent

    </section>
    <div class="modal fade view_modal_in_out" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel"></div>
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
                    url: "{{ action([\Modules\Gym\Http\Controllers\AttendanceController::class, 'index']) }}",
                },
                aaSorting: [
                    [3, 'desc']
                ],
                columns: [{
                        data: 'name',
                        name: 'name'
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
                        data: 'created_at',
                        name: 'contacts.created_at'
                    },
                    {
                        data: 'in',
                        name: 'in',
                        sorting: false,
                    },
                    {
                        data: 'out',
                        name: 'out',
                        sorting: false,
                    },
                ]
            });

            $(document).on('click', '.btn-modal-in', function(e) {
                e.preventDefault();
                $.ajax({
                    url: $(this).attr('href'),
                    dataType: 'html',
                    success: function(result) {
                        $('.view_modal_in_out')
                            .html(result)
                            .modal('show');
                    },
                });
            });

            $('.view_modal_in_out').on('shown.bs.modal', function() {

                $('#add_edit_in_time').on('submit', function(event) {
                    event.preventDefault(); // Prevent the default form submission

                    // Get the form data
                    var formData = $(this).serialize();

                    // Send the data via AJAX
                    $.ajax({
                        url: $(this).attr('action'), // URL to send the request to
                        method: $(this).attr('method'), // HTTP method (POST)
                        data: formData, // The data to send
                        dataType: 'json', // Expecting JSON response
                        success: function(response) {
                            if (response
                                .success) { // Assuming the response has a message field
                                $('.view_modal_in_out').modal(
                                'hide');
                                member_table.ajax.reload();
                            } else {
                                // Handle errors (e.g., display an error message)
                                alert(response.message || 'An error occurred.');
                            }
                        },
                        error: function(xhr, status, error) {
                            // Handle AJAX errors
                            console.log(xhr
                            .responseText); // Log error response for debugging
                            alert('Something went wrong. Please try again.');
                        }
                    });
                });

                $('#add_edit_out_time').on('submit', function(event) {
                    event.preventDefault(); // Prevent the default form submission

                    // Get the form data
                    var formData = $(this).serialize();

                    // Send the data via AJAX
                    $.ajax({
                        url: $(this).attr('action'), // URL to send the request to
                        method: $(this).attr('method'), // HTTP method (POST)
                        data: formData, // The data to send
                        dataType: 'json', // Expecting JSON response
                        success: function(response) {
                            if (response
                                .success) { // Assuming the response has a message field
                                $('.view_modal_in_out').modal(
                                'hide');
                                member_table.ajax.reload();
                            } else {
                                // Handle errors (e.g., display an error message)
                                alert(response.message || 'An error occurred.');
                            }
                        },
                        error: function(xhr, status, error) {
                            // Handle AJAX errors
                            console.log(xhr
                            .responseText); // Log error response for debugging
                            alert('Something went wrong. Please try again.');
                        }
                    });
                });

                $('.time_picker').datetimepicker({
                    format: moment_time_format,
                    ignoreReadonly: true,
                    defaultDate: moment(),
                });
            });

            $(document).on('click', '.btn-modal-out', function(e) {
                e.preventDefault();
                $.ajax({
                    url: $(this).attr('href'),
                    dataType: 'html',
                    success: function(result) {
                        $('.view_modal_in_out')
                            .html(result)
                            .modal('show');
                    },
                });
            });
        });
    </script>
@endsection
