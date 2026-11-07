@push('whatsapp-resources')
<script src="{{ Module::asset('whatsapp:js/app.js') }}"></script>
@endpush
<section class="no-print">
    <nav class="navbar navbar-default bg-white m-4">
        <div class="container-fluid">
            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="{{action('\Modules\WhatsApp\Http\Controllers\WhatsAppController@index')}}"><i class="fal fa fa-whatsapp"></i> {{__('whatsapp::lang.app_name')}}</a>
            </div>

            <!-- Collect the nav links, forms, and other content for toggling -->
            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                <ul class="nav navbar-nav">
                    @if (auth()->user()->can('edit_whatsapp_settings'))
                        <li @if(request()->segment(1) == 'whatsapp' && request()->segment(2) == 'whatsapp-settings') class="active" @endif><a href="{{action('\Modules\WhatsApp\Http\Controllers\WhatsAppController@index')}}">Dashboard</a></li>
                    @endif
                </ul>

            </div><!-- /.navbar-collapse -->
        </div><!-- /.container-fluid -->
    </nav>
</section>