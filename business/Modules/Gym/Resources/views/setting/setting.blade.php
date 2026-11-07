@extends('layouts.app')
@section('title', __('messages.settings'))

@section('content')
    @include('gym::layouts.nav')
    <!-- Main content -->
    <section class="content">
        <!-- Custom Tabs -->
        @component('components.widget', ['class' => 'box-primary', 'title' => __('messages.settings') . ':'])
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">
                    <li class="active">
                        <a href="#cn_1" data-toggle="tab" aria-expanded="true">
                            @lang('gym::lang.settings')
                        </a>
                    </li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="cn_1">
                        <div class="row">
                            <div class="box-body">
                                {!! Form::open([
                                    'url' => action([\Modules\Gym\Http\Controllers\SettingController::class, 'store']),
                                    'method' => 'post',
                                    'id' => 'setting',
                                    'files' => true,
                                ]) !!}
                                @php
                                    $settings = json_decode($busines->gym_settings);
                                @endphp
                            

                                <div class="col-md-4">
                                    <div class="form-group">
                                        {!! Form::label('phone', __('contact.contact')) !!}
                                        {!! Form::text('phone', $settings->gym->phone ?? null, [
                                            'class' => 'form-control',
                                            'id' => 'phone',
                                            'placeholder' => __('contact.contact'),
                                        ]) !!}
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        {!! Form::label('email', __('gym::lang.email')) !!}
                                        {!! Form::email('email', $settings->gym->email ?? null, [
                                            'class' => 'form-control',
                                            'id' => 'email',
                                            'placeholder' => __('gym::lang.email'),
                                        ]) !!}
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        {!! Form::label('website', __('gym::lang.website')) !!}
                                        {!! Form::text('website', $settings->gym->website ?? null, [
                                            'class' => 'form-control',
                                            'id' => 'website',
                                            'placeholder' => __('gym::lang.website'),
                                        ]) !!}
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {!! Form::label('address', __('gym::lang.address')) !!}
                                        {!! Form::textarea('address', $settings->gym->address ?? null, [
                                            'class' => 'form-control',
                                            'id' => 'address',
                                            'rows' => '4',
                                            'placeholder' => __('gym::lang.address'),
                                        ]) !!}
                                    </div>
                                </div>
                                <div class="col-md-12 text-center">
                                    {!! Form::submit(__('messages.submit'), ['class' => 'tw-dw-btn tw-dw-btn-success tw-text-white tw-dw-btn-lg']) !!}
                                </div>

                                {!! Form::close() !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xs-12">
                <p class="help-block"><i>{!! __('gym::lang.version_info', ['version' => $module_version]) !!}</i></p>
            </div>
        @endcomponent
        
    </section>
    <!-- /.content -->
@endsection

@section('javascript')
    <script type="text/javascript">
        tinymce.init({
            selector: 'textarea#email_body',
        });
        tinymce.init({
            selector: 'textarea#footer_text',
        });
    </script>
@endsection
