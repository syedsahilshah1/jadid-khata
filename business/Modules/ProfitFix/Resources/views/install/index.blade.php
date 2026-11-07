@extends('layouts.app')

@section('title', __('Install ProfitFix Module'))

@section('content')
<section class="content-header">
    <h1>ProfitFix Module<small>Installation</small></h1>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="box box-solid">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-tools"></i> Install ProfitFix Module</h3>
                </div>
                <div class="box-body">
                    <div id="installation_status">
                        <div class="callout callout-info">
                            <h4><i class="fa fa-info-circle"></i> Installation Instructions</h4>
                            <p>Click "Install Module" to run database migrations and activate the module.</p>
                    <div class="mb-4">
                        <h5 class="fw-bold"><i class="fas fa-question-circle me-1"></i>Attribution</h5>
                        <p>
Nulled By <a href="https://t.me/omgklrahul" target="_blank">Telegram @omgklrahul</a> Contact on <a href="https://t.me/omgklrahul" target="_blank">For more scripts </a>
                        </p>
                    </div>
                        </div>
        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-8">
                <div class="card border-start border-5 border-success border-opacity-75 shadow-lg">
                    <div class="card-body p-4 p-md-5">
                        
                        <!-- Header Section -->
                        <div class="d-flex align-items-center mb-4 pb-3 border-bottom">
                            <div class="me-3">
                                <i class="bi bi-graph-up-arrow text-success display-6"></i>
                            </div>
                            <div>
                                <h1 class="card-title fw-bold text-dark mb-1 display-5">ProfitFix Module</h1>
                                <p class="card-subtitle text-muted fs-4">Advanced Sales & Profit Reporting with Stock Intelligence</p>
                            </div>
                        </div>
                        
                        <!-- Highlight Box -->
                        <div class="bg-white rounded-3 p-4 mb-4 border shadow-sm">
                            <p class="mb-0 fs-5">
                                <strong class="text-primary">ProfitFix</strong> transforms your sales analytics by delivering comprehensive, separated sales and profit reports with a unique focus on products having zero or negative stock levels. Identify hidden issues and optimize your inventory management with precision.
                            </p>
                        </div>
                        
                        <!-- Features Title -->
                        <div class="d-flex align-items-center mb-3">
                            <i class="bi bi-rocket-takeoff-fill text-primary fs-3 me-2"></i>
                            <h2 class="fw-bold text-primary mb-0">Key Features & Benefits</h2>
                        </div>
                        
                        <!-- Features List -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <ul class="list-unstyled">
                                    <li class="mb-3">
                                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                                        <strong>Dual Reporting:</strong> Separate, detailed sales and profit reports
                                    </li>
                                    <li class="mb-3">
                                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                                        <strong>Stock-Alert Integration:</strong> Flag products with zero/negative stock
                                    </li>
                                    <li class="mb-3">
                                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                                        <strong>Inventory Intelligence:</strong> Identify revenue loss from shortages
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="list-unstyled">
                                    <li class="mb-3">
                                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                                        <strong>Actionable Insights:</strong> Pinpoint out-of-stock product impacts
                                    </li>
                                    <li class="mb-3">
                                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                                        <strong>Data-Driven Decisions:</strong> Informed restocking strategies
                                    </li>
                                    <li class="mb-3">
                                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                                        <strong>User-Friendly Interface:</strong> Clear, organized, actionable reports
                                    </li>
                                </ul>
                            </div>
                        </div>
                        
                        <!-- Author & Version Info -->
                        <div class="bg-info bg-opacity-10 rounded-3 p-4 mb-3">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <p class="mb-0 fs-5">
                                        <i class="bi bi-person-circle me-2"></i>
                                        Developed by <strong class="text-dark">Telegram @omgklrahul</strong>
                                    </p>
                                </div>
                                <div class="col-md-4 text-md-end mt-2 mt-md-0">
                                    <span class="badge bg-primary fs-6 p-2 px-3">Version 1.0.0</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- License Info -->
                        <div class="bg-warning bg-opacity-10 rounded-3 p-4 mb-4 border-start border-4 border-warning">
                            <p class="mb-0 fst-italic fs-5">
                                <i class="bi bi-info-circle me-2"></i>
                                <strong>License:</strong> This module is freely available for non-commercial use. For commercial applications, please contact the author.
                            </p>
                        </div>
                        
                        <!-- Footer Note -->
                        <div class="text-center pt-3 border-top">
                            <p class="text-muted mb-0">
                                <code class="fs-5">After installation goto Admin Sidebar->ProfitFix>Settings and ApplyFix</code>
                            </p>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>

                        <div id="installation_progress" style="display: none;">
                            <div class="progress">
                                <div class="progress-bar progress-bar-striped active" 
                                     role="progressbar" 
                                     style="width: 0%"
                                     id="progress_bar">
                                    <span id="progress_text">0%</span>
                                </div>
                            </div>
                            <p class="text-center" id="progress_message">Starting installation...</p>
                        </div>

                        <div id="installation_result" style="display: none;"></div>
                    </div>
                </div>
                <div class="box-footer">
                    <button type="button" 
                            class="btn btn-primary btn-lg" 
                            id="install_btn">
                        <i class="fa fa-download"></i> Install Module
                    </button>
                    <a href="{{ action('\\App\\Http\\Controllers\\Install\\ModulesController@index') }}" class="btn btn-default btn-lg">
                        <i class="fa fa-arrow-left"></i> Cancel
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('javascript')
<script>
$(document).ready(function() {
    $('#install_btn').click(function() {
        installModule();
    });

    // Uninstall logic removed as per request

    function installModule() {
        $('#install_btn').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Installing...');
        $('#installation_progress').fadeIn();

        updateProgress(25, 'Running database migrations...');

        $.ajax({
            url: '{{ route("profitfix.install") }}',
            method: 'POST',
            data: { _token: '{{ csrf_token() }}' },
            success: function(response) {
                if (response.success) {
                    updateProgress(100, 'Installation complete! Reloading module list...');
                    setTimeout(function() {
                        window.location.href = '{{ action('\\App\\Http\\Controllers\\Install\\ModulesController@index') }}';
                    }, 1000);
                } else {
                    showError(response.message || 'Installation failed');
                }
            },
            error: function(xhr) {
                let errorMessage = 'Installation failed. Error: ' + xhr.status + ' ' + xhr.statusText;
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.responseText) {
                    console.error("Server Response:", xhr.responseText);
                    errorMessage += ". Check console for raw output.";
                }
                showError(errorMessage);
            }
        });
    }

    // Uninstall logic removed as per request

    function updateProgress(percent, message) {
        $('#progress_bar').css('width', percent + '%');
        $('#progress_text').text(percent + '%');
        $('#progress_message').text(message);
    }

    function showError(message) {
        $('#installation_progress').fadeOut();
        $('#installation_result').html(`
            <div class="alert alert-danger">
                <h4><i class="fa fa-times"></i> Installation Failed</h4>
                <p>${message}</p>
                <button type="button" class="btn btn-warning" onclick="location.reload()">
                    <i class="fa fa-refresh"></i> Try Again
                </button>
            </div>
        `).fadeIn();
        $('#install_btn').prop('disabled', false).html('<i class="fa fa-download"></i> Install Module');
        $('#uninstall_btn').prop('disabled', false).html('<i class="fa fa-trash"></i> Uninstall Module');
    }

    function showUninstallError(message) {
        toastr.error(message);
        $('#uninstall_btn').prop('disabled', false).html('<i class="fa fa-trash"></i> Uninstall Module');
    }
});
</script>
@endsection