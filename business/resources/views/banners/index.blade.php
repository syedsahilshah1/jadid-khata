@extends('layouts.app')
@section('title', __('business.banners'))

@section('content')
{{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script> --}}
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">

<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">@lang('business.banners')
        <small class="tw-text-sm md:tw-text-base tw-text-gray-700 tw-font-semibold">@lang('business.manage_banners')</small>
    </h1>
    <!-- <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
        <li class="active">Here</li>
    </ol> -->
</section>

<style>
.ui-sortable-handle:hover{
    cursor: move;
}
</style>



@component('components.widget', ['class' => 'box-primary', 'title' => __('business.banners')])
@slot('tool')
    <div class="box-tools">
        <a class="tw-dw-btn tw-bg-gradient-to-r tw-from-indigo-600 tw-to-blue-500 tw-font-bold tw-text-white tw-border-none tw-rounded-full"
        href="{{action([\App\Http\Controllers\BannerController::class, 'create'])}}" >
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="icon icon-tabler icons-tabler-outline icon-tabler-plus">
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <path d="M12 5l0 14" />
                <path d="M5 12l14 0" />
            </svg> @lang('messages.add')
        </a>
    </div>
@endslot


<div class="table-responsive">
    <table class="table table-bordered table-striped" id="banner_table">
        <thead>
            <tr>
                <th> @lang('business.action')</th>
                <th> @lang('business.banner_image')</th>
                <th> @lang('business.status')</th>
                {{-- <th> @lang('business.author')</th> --}}
                <th> @lang('business.date')</th>
            </tr>
        </thead>
    </table>
</div>
@endcomponent

@endsection


@section('javascript')

<script src="https://code.jquery.com/ui/1.14.0/jquery-ui.js"></script>

<script type="text/javascript">
   $(document).ready(function () {
        window._datatable_ = $('#banner_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ action([\App\Http\Controllers\BannerController::class, 'index']) }}',
            data: function (d) {
                }
            },
            columns: [
                {data: 'action', name: 'action'},
                {data: 'image', name: 'image'},
                {data: 'is_active', name: 'is_active'},
                // {data: 'user_id', name: 'user_id'},
                {data: 'created_at', name: 'created_at'}
            ],
            drawCallback: function() {
                $('#sortable-rows').sortable({
                    placeholder: "ui-state-highlight",
                    update: function(event, ui) {
                        var post_order_ids = [];
                        $('#sortable-rows tr').each(function() {
                            post_order_ids.push($(this).data("id")); 
                        });

                        $.ajax({
                            type: "POST",
                            url: "{{ url('admin.order_change') }}",
                            dataType: "json",
                            data: {
                                order: post_order_ids,
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(response) {
                                toastr.success(response.message);
                            },
                            error: function(xhr, status, error) {
                                console.log(xhr.responseText);
                            }
                        });
                    }
                });
            },
            rowCallback: function(row, data) {
                $(row).attr('data-id', data.id);
            }
        });
    });
</script>
@endsection
