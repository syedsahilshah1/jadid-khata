@extends('layouts.app')
@section('title', __('partners::lang.partners'))

@section('content')
<style>
    .table-striped th {
        background-color: #626161;
        color: #ffffff;
    }
</style>

@include('partners::layouts.nav')

<section class="content-header">
    <h1>@lang('partners::lang.partners')</h1>
</section>

<section class="content">
    @component('components.widget', ['class' => 'box-primary', 'title' => ''])
        @can('partners.create')
            @slot('tool')
                <div class="box-tools">
                    <button type="button" class="btn btn-block btn-primary btn-modal"
                            data-href="{{ action('\Modules\Partners\Http\Controllers\PartnersController@create') }}"
                            data-container=".brands_modal">
                        <i class="fa fa-plus"></i> @lang('messages.add')
                    </button>
                </div>
            @endslot
        @endcan

        @can('partners.view')
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="partners_table">
                    <thead>
                        <tr>
                            <th>@lang('partners::lang.name')</th>
                            <th>@lang('partners::lang.address')</th>
                            <th>@lang('partners::lang.mobile')</th>
                            <th>@lang('partners::lang.share')</th>
                            <th>@lang('messages.action')</th>
                        </tr>
                    </thead>
                    <tbody id="datatable">
                        @foreach($partners as $partner)
                            <tr id="{{ $partner->id }}">
                                <td>{{ $partner->name }}</td>
                                <td>{{ $partner->address }}</td>
                                <td>{{ $partner->mobile }}</td>
                                <td>{{ $partner->share }}</td>
                                <td>
                                    <button onclick="assetedit({{ $partner->id }})"
                                            class="btn btn-xs btn-primary btn-modal">
                                        <i class="glyphicon glyphicon-edit"></i> @lang('messages.edit')
                                    </button>
                                    <button onclick="deleteasset({{ $partner->id }})"
                                            class="btn btn-xs btn-danger delete_partner_button">
                                        <i class="glyphicon glyphicon-trash"></i> @lang('messages.delete')
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endcan
    @endcomponent
</section>

<div class="modal fade brands_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
</div>

@endsection

@push('scripts')
<script>
    function assetedit(id) {
        $.ajax({
            url: '/partners/partners/' + id + '/edit',
            dataType: 'html',
            success: function(result) {
                $(".brands_modal").html(result).modal('show');
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText);
                toastr.error('@lang("messages.error_loading_edit_form")');
            }
        });
    }

    function deleteasset(id) {
        swal({
            title: '@lang("messages.are_you_sure")',
            text: '@lang("partners::lang.confirm_delete_partner")',
            icon: 'warning',
            buttons: true,
            dangerMode: true,
        }).then(willDelete => {
            if (willDelete) {
                var href = '/partners/partners/' + id;
                $.ajax({
                    method: 'DELETE',
                    url: href,
                    dataType: 'json',
                    success: function(result) {
                        if (result.success == true) {
                            toastr.success(result.msg);
                            $('#' + id).remove(); // Remove the row from the table
                        } else {
                            toastr.error(result.msg);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error(xhr.responseText);
                        toastr.error('@lang("messages.error_deleting_partner")');
                    }
                });
            }
        });
    }
</script>
@endpush
