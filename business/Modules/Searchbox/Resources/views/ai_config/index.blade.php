@extends('layouts.app')

@section('title', __('searchbox::lang.menu_ai_config'))

@section('content')
<section class="content-header">
    <h1>@lang('searchbox::lang.menu_ai_config')</h1>
</section>

<style>
    .searchbox-ai-settings .sb-panel-body { padding-left: 20px; padding-right: 20px; }
    .searchbox-ai-settings .sb-check-row { margin: 0 0 14px 0; }
    .searchbox-ai-settings .sb-check-label {
        font-weight: 400;
        cursor: pointer;
        display: flex;
        align-items: flex-start;
        gap: 10px;
        line-height: 1.5;
        padding: 4px 0;
        margin: 0;
    }
    .searchbox-ai-settings .sb-check-label input[type="checkbox"] {
        margin-top: 3px;
        flex-shrink: 0;
    }
    .searchbox-ai-settings .sb-check-text {
        flex: 1;
        min-width: 0;
        overflow: visible;
    }
</style>

<section class="content searchbox-ai-settings">
    @if (is_array(session('status')) && ! empty(session('status')['success']))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('status')['msg'] ?? __('lang_v1.success') }}
        </div>
    @endif

    <form action="{{ route('searchbox.ai-config.update') }}" method="post">
        @csrf

        <div class="row">
            <div class="col-md-10 col-md-offset-1 col-lg-8 col-lg-offset-2">
                {{-- Header search (global) --}}
                <div class="box box-solid box-default">
                    <div class="box-header with-border">
                        <h3 class="box-title">@lang('searchbox::lang.ai_config_section_header')</h3>
                    </div>
                    <div class="box-body sb-panel-body">
                        <p class="text-muted">@lang('searchbox::lang.ai_config_header_intro')</p>

                        <div class="sb-check-row">
                            <label class="sb-check-label">
                                <input type="checkbox" name="header_search_enabled" value="1" {{ old('header_search_enabled', $header_search_enabled) ? 'checked' : '' }}>
                                <span class="sb-check-text">@lang('searchbox::lang.ai_config_header_enable_desktop')</span>
                            </label>
                        </div>
                        <div class="sb-check-row">
                            <label class="sb-check-label">
                                <input type="checkbox" name="mobile_floating_search_enabled" value="1" {{ old('mobile_floating_search_enabled', $mobile_floating_search_enabled) ? 'checked' : '' }}>
                                <span class="sb-check-text">@lang('searchbox::lang.ai_config_mobile_fab_enable')</span>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- AI --}}
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">@lang('searchbox::lang.ai_config_section_ai')</h3>
                    </div>
                    <div class="box-body sb-panel-body">
                        <p class="text-muted">@lang('searchbox::lang.ai_config_intro')</p>

                        <div class="sb-check-row">
                            <label class="sb-check-label">
                                <input type="checkbox" name="ai_enabled" value="1" {{ old('ai_enabled', $ai_enabled) ? 'checked' : '' }}>
                                <span class="sb-check-text">@lang('searchbox::lang.ai_config_enable_backend')</span>
                            </label>
                        </div>

                        <div class="sb-check-row">
                            <label class="sb-check-label">
                                <input type="checkbox" name="ai_ui_enabled" value="1" {{ old('ai_ui_enabled', $ai_ui_enabled) ? 'checked' : '' }}>
                                <span class="sb-check-text">@lang('searchbox::lang.ai_config_enable_ui')</span>
                            </label>
                        </div>

                        <div class="form-group">
                            <label>@lang('searchbox::lang.ai_config_api_key')</label>
                            <input type="password" name="ai_api_key" class="form-control" autocomplete="new-password"
                                placeholder="{{ $has_stored_api_key ? __('searchbox::lang.ai_config_api_key_placeholder') : '' }}">
                            @if ($has_stored_api_key)
                                <p class="help-block text-success">@lang('searchbox::lang.ai_config_key_stored')</p>
                            @endif
                        </div>

                        <div class="sb-check-row">
                            <label class="sb-check-label">
                                <input type="checkbox" name="clear_ai_api_key" value="1">
                                <span class="sb-check-text">@lang('searchbox::lang.ai_config_clear_key')</span>
                            </label>
                        </div>

                        <div class="form-group">
                            <label>@lang('searchbox::lang.ai_config_base_url')</label>
                            <input type="url" name="base_url" class="form-control" value="{{ old('base_url', $base_url) }}"
                                placeholder="https://api.openai.com/v1">
                        </div>

                        <div class="form-group">
                            <label>@lang('searchbox::lang.ai_config_model')</label>
                            <input type="text" name="model" class="form-control" value="{{ old('model', $model) }}"
                                placeholder="gpt-4o-mini">
                        </div>

                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>@lang('searchbox::lang.ai_config_timeout')</label>
                                    <input type="number" name="timeout" class="form-control" min="5" max="120"
                                        value="{{ old('timeout', $timeout) }}">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>@lang('searchbox::lang.ai_config_min_length')</label>
                                    <input type="number" name="ai_min_length" class="form-control" min="1" max="10"
                                        value="{{ old('ai_min_length', $ai_min_length) }}">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="box-footer sb-panel-body">
                        <button type="submit" class="btn btn-primary">{{ __('messages.save') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</section>
@endsection
