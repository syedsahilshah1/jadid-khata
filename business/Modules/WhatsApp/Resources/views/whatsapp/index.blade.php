@extends('layouts.app')

@section('title', __('whatsapp::lang.app_name'))

@section('content')
@include('whatsapp::layouts.nav')
<!-- Content Header (Page header) -->
<section class="content-header no-print">
    <h1>
    	@lang('whatsapp::lang.app_name')
        <small>{{config('whatsapp.module_version')}}</small>
    </h1>
   
</section>
<!-- Main content -->
<section class="content no-print">
    @component('components.widget', ['class' => 'box-solid'])
	<div class="row">
        <div class="col-md-12">
            @slot('tool')
            <div class="box-tools">
                <button type="button" class="btn btn-block btn-dark btn-modal" 
                    data-toggle="modal" 
                    data-href="{{ action([\Modules\WhatsApp\Http\Controllers\WhatsAppController::class, 'create']) }}"
                    data-container=".whatsapp_add_modal">
                    <i class="fal fa fa-light fa-plus"></i> @lang( 'whatsapp::lang.connect_to_apps' )</button>
            </div>
        @endslot
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="accounts">
                <thead>
                    <tr>
                        <th style="width: 9%">{{__('Action')}}</th>
                        <th>@lang('whatsapp::lang.sources')</th>
                        <th>@lang('whatsapp::lang.wa_server')</th>
                        <th>@lang('whatsapp::lang.sender')</th>
                        <th>@lang('whatsapp::lang.default')</th></th>
                        <th>@lang('whatsapp::lang.app_key')</th>
                        <th>@lang('whatsapp::lang.auth_key')</th>
                    </tr>
                </thead>
            </table>
        </div>
        </div>
    </div>
    @endcomponent
</section>
<div class="modal fade whatsapp_add_modal" role="dialog" 
aria-labelledby="gridSystemModalLabel">
</div>
<div class="modal fade whatsapp_edit_modal" role="dialog" 
aria-labelledby="gridSystemModalLabel">
</div>
<!-- /.content -->
@stop
@section('css')
<link rel="stylesheet" href="{{  Module::asset('whatsapp:css/whatsapp.css').'?version='.config('whatsapp.module_version') }}">
@endsection
@section('javascript')
<script src="{{ Module::asset('whatsapp:js/app.js').'?version='.config('whatsapp.module_version') }}"></script>
@endsection