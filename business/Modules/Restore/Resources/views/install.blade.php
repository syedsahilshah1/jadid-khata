<!--
 * Restore backups Module
 * * This module helps restore already created backups from ultimate pos.
 * * Module: Restore
 * Author: [Telegram @omgklrahul]
 * Version: 1.0.0
 * * This is a complete free module for non commercial use.
 * * @package Modules\Restore
 -->

@extends('layouts.app')

@section('title', 'Install ' . $module_display_name)

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">
                    <i class="fa fa-download"></i> Install {{ $module_display_name }} (v{{ $appVersion }}) Nulled By <a href="https://t.me/omgklrahul" target="_blank">Telegram @omgklrahul</a> Contact on <a href="https://t.me/omgklrahul" target="_blank">For more scripts in cost</a>
                </h3>
            </div>
            <div class="panel-body">
                @if(isset($intruction_type) && $intruction_type == 'uf')
                    <div class="alert alert-info">
                        <h4><i class="icon fa fa-info"></i> Important Information</h4>
                        <p>You are about to install the <strong>{{ $module_display_name }}</strong> module version <strong>{{ $appVersion }}</strong>.</p>
                        <p>Please ensure that:</p>
                        <ul>
                            <li>You have a complete backup of your database</li>
                            <li>You are installing during a maintenance window</li>
                            <li>No other users are currently using the system</li>
                            <li>You have sufficient permissions to run database migrations</li>
                        </ul>
                    </div>

                    <div class="text-center">
                        <button type="button" class="btn btn-primary btn-lg" id="install_btn">
                            <i class="fa fa-check"></i> Install Now
                        </button>
                        <a href="{{ action('\App\Http\Controllers\Install\ModulesController@index') }}" class="btn btn-default btn-lg">
                            <i class="fa fa-times"></i> Cancel
                        </a>
                    </div>

                    <div id="installation_progress" style="display: none; margin-top: 30px;">
                        <div class="progress" style="height: 30px;">
                            <div class="progress-bar progress-bar-striped active" role="progressbar" 
                                 aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%">
                                <span class="sr-only">0% Complete</span>
                            </div>
                        </div>
                        <div class="text-center" id="progress_text">
                            <i class="fa fa-spinner fa-spin"></i> Starting installation...
                        </div>
                        <div class="alert" id="result_message" style="display: none; margin-top: 20px;"></div>
                    </div>
                @endif

                <div class="text-center">
                    <button type="button" class="btn btn-primary btn-lg" id="install_btn">
                        <i class="fa fa-download"></i> Install Module
                    </button>
                </div>

                <div id="installation_progress" style="display: none; margin-top: 30px;">
                    <div class="progress" style="height: 30px;">
                        <div class="progress-bar progress-bar-striped active" role="progressbar" 
                             aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%">
                            <span class="sr-only">0% Complete</span>
                        </div>
                    </div>
                    <div class="text-center" id="progress_text">
                        <i class="fa fa-spinner fa-spin"></i> Starting installation...
                    </div>
                    <div class="alert" id="result_message" style="display: none; margin-top: 20px;"></div>
                </div>

                <script>
                $(document).ready(function() {
                    $('#install_btn').click(function() {
                        $(this).prop('disabled', true);
                        $('#installation_progress').fadeIn();

                        updateProgress(10, 'Starting installation process...');

                        // Make AJAX call to install endpoint
                        $.ajax({
                            url: "{{ $action_url }}",
                            type: 'POST',
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            dataType: 'json',
                            success: function(response) {
                                if (response.success) {
                                    updateProgress(100, 'Installation completed successfully!');
                                    $('#result_message')
                                        .removeClass('alert-danger')
                                        .addClass('alert-success')
                                        .html('<h4><i class="icon fa fa-check"></i> Success!</h4><p>The module has been installed successfully.</p><p>You will be redirected in 3 seconds...</p>')
                                        .fadeIn();

                                    // Redirect to modules list after success
                                    setTimeout(function() {
                                        window.location.href = "{{ action('\\App\\Http\\Controllers\\Install\\ModulesController@index') }}";
                                    }, 3000);
                                } else {
                                    showError(response.message || 'Installation failed');
                                    $('#install_btn').prop('disabled', false);
                                }
                            },
                            error: function(xhr) {
                                var errorMsg = 'An error occurred during installation.';
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    errorMsg = xhr.responseJSON.message;
                                } else if (xhr.statusText) {
                                    errorMsg = 'Error: ' + xhr.statusText;
                                }
                                showError(errorMsg);
                                $('#install_btn').prop('disabled', false);
                            }
                        });
                    });

                    function updateProgress(percent, message) {
                        $('.progress-bar')
                            .css('width', percent + '%')
                            .attr('aria-valuenow', percent)
                            .text(percent + '%');
                        $('#progress_text').html('<i class="fa fa-spinner fa-spin"></i> ' + message);
                    }

                    function showError(message) {
                        updateProgress(0, 'Installation failed');
                        $('#result_message')
                            .removeClass('alert-success')
                            .addClass('alert-danger')
                            .html('<h4><i class="icon fa fa-warning"></i> Installation Failed</h4><p>' + message + '</p>')
                            .fadeIn();
                    }
                });
                </script>
            </div>
        </div>
    </div>
</div>
@endsection

@section('javascript')
<script>
$(document).ready(function() {
    $('#install_btn, #fallback_install_btn').click(function() {
        console.log('Install button clicked');
        console.log('Action URL:', "{{ $action_url }}");

        $(this).prop('disabled', true);
        $('#installation_progress').fadeIn();

        updateProgress(10, 'Starting installation process...');

        // Make AJAX call to install endpoint
        $.ajax({
            url: "{{ $action_url }}",
            type: 'POST',
            data: {
                _token: "{{ csrf_token() }}"
            },
            dataType: 'json',
            success: function(response) {
                console.log('AJAX success:', response);
                if (response.success) {
                    updateProgress(100, 'Installation completed successfully!');
                    $('#result_message')
                        .removeClass('alert-danger')
                        .addClass('alert-success')
                        .html('<h4><i class="icon fa fa-check"></i> Success!</h4><p>The module has been installed successfully.</p><p>You will be redirected in 3 seconds...</p>')
                        .fadeIn();

                    // Redirect to modules list after success
                    setTimeout(function() {
                        window.location.href = "{{ action('\\App\\Http\\Controllers\\Install\\ModulesController@index') }}";
                    }, 3000);
                } else {
                    showError(response.message || 'Installation failed');
                    $('#install_btn').prop('disabled', false);
                }
            },
            error: function(xhr) {
                console.error('AJAX error:', xhr);
                var errorMsg = 'An error occurred during installation.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                } else if (xhr.statusText) {
                    errorMsg = 'Error: ' + xhr.statusText;
                }
                showError(errorMsg);
                $('#install_btn').prop('disabled', false);
            }
        });
    });
    
    function updateProgress(percent, message) {
        $('.progress-bar')
            .css('width', percent + '%')
            .attr('aria-valuenow', percent)
            .text(percent + '%');
        $('#progress_text').html('<i class="fa fa-spinner fa-spin"></i> ' + message);
    }
    
    function showError(message) {
        updateProgress(0, 'Installation failed');
        $('#result_message')
            .removeClass('alert-success')
            .addClass('alert-danger')
            .html('<h4><i class="icon fa fa-warning"></i> Installation Failed</h4><p>' + message + '</p>')
            .fadeIn();
    }
});
</script>
@endsection