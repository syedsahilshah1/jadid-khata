@extends('layouts.app')

@section('title', __('Backupadv::lang.backup_dashboard'))

@section('content')
@include('Backupadv::layouts.nav')

<div class="container mt-5">
    <!-- Quick Summary Section -->
    <div class="row g-4 mb-4">
        <!-- Total Backups Card -->
        <div class="col-md-3">
            <div class="card border-0 shadow-lg h-100 text-center" style="background: linear-gradient(135deg, #6366F1, #4F46E5); border-radius: 20px;">
                <div class="card-body d-flex flex-column justify-content-center align-items-center text-white">
                    <div class="icon mb-3">
                        <i class="fas fa-database fa-3x"></i>
                    </div>
                    <h6 class="card-title text-uppercase mt-3">{{ __('Backupadv::lang.total_backups') }}</h6>
                    <h3 class="display-6 mt-2">{{ $totalBackups }}</h3>
                </div>
            </div>
        </div>

        <!-- Successful Backups Card -->
        <div class="col-md-3">
            <div class="card border-0 shadow-lg h-100 text-center" style="background: linear-gradient(135deg, #10B981, #059669); border-radius: 20px;">
                <div class="card-body d-flex flex-column justify-content-center align-items-center text-white">
                    <div class="icon mb-3">
                        <i class="fas fa-check-circle fa-3x"></i>
                    </div>
                    <h6 class="card-title text-uppercase mt-3">{{ __('Backupadv::lang.successful_backups') }}</h6>
                    <h3 class="display-6 mt-2">{{ $successfulBackups }}</h3>
                </div>
            </div>
        </div>

        <!-- Failed Backups Card -->
        <div class="col-md-3">
            <div class="card border-0 shadow-lg h-100 text-center" style="background: linear-gradient(135deg, #EF4444, #B91C1C); border-radius: 20px;">
                <div class="card-body d-flex flex-column justify-content-center align-items-center text-white">
                    <div class="icon mb-3">
                        <i class="fas fa-times-circle fa-3x"></i>
                    </div>
                    <h6 class="card-title text-uppercase mt-3">{{ __('Backupadv::lang.failed_backups') }}</h6>
                    <h3 class="display-6 mt-2">{{ $failedBackups }}</h3>
                </div>
            </div>
        </div>

        <!-- Total Size Card -->
        <div class="col-md-3">
            <div class="card border-0 shadow-lg h-100 text-center" style="background: linear-gradient(135deg, #F59E0B, #D97706); border-radius: 20px;">
                <div class="card-body d-flex flex-column justify-content-center align-items-center text-white">
                    <div class="icon mb-3">
                        <i class="fas fa-hdd fa-3x"></i>
                    </div>
                    <h6 class="card-title text-uppercase mt-3">{{ __('Backupadv::lang.total_size') }}</h6>
                    <h3 class="display-6 mt-2">{{ $totalSize }} MB</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="row mt-5">
        <!-- Status Pie Chart -->
        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-lg rounded-lg">
                <div class="card-header text-white text-center" style="background: linear-gradient(135deg, #6366F1, #4F46E5); border-radius: 20px 20px 0 0;">
                    <h5 class="card-title">{{ __('Backupadv::lang.status_distribution') }}</h5>
                </div>
                <div class="card-body">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Backup Size Over Time Line Chart -->
        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-lg rounded-lg">
                <div class="card-header text-white text-center" style="background: linear-gradient(135deg, #10B981, #059669); border-radius: 20px 20px 0 0;">
                    <h5 class="card-title">{{ __('Backupadv::lang.backup_size_over_time') }}</h5>
                </div>
                <div class="card-body">
                    <canvas id="sizeChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include FontAwesome for Icons -->
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

<!-- Include Chart.js library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // بيانات مخطط الحالة (Pie Chart)
    var statusCtx = document.getElementById('statusChart').getContext('2d');
    var statusChart = new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['{{ __('Backupadv::lang.success') }}', '{{ __('Backupadv::lang.failed') }}'],
            datasets: [{
                label: '{{ __('Backupadv::lang.status') }}',
                data: [{{ $successfulBackups }}, {{ $failedBackups }}],
                backgroundColor: ['#10B981', '#EF4444'],
                hoverBackgroundColor: ['#059669', '#B91C1C'],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            },
            animation: {
                animateScale: true
            }
        }
    });

    // بيانات مخطط الحجم (Line Chart)
    var sizeCtx = document.getElementById('sizeChart').getContext('2d');
    var sizeChart = new Chart(sizeCtx, {
        type: 'line',
        data: {
            labels: @json($backupDates ?? []),
            datasets: [{
                label: '{{ __('Backupadv::lang.size_mb') }}',
                data: @json($backupSizes ?? []),
                backgroundColor: 'rgba(16, 185, 129, 0.2)',
                borderColor: '#10B981',
                pointBackgroundColor: '#10B981',
                pointBorderColor: '#fff',
                pointHoverBackgroundColor: '#fff',
                pointHoverBorderColor: '#10B981',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    }
                },
                y: {
                    grid: {
                        borderDash: [5, 5]
                    }
                }
            },
            animation: {
                duration: 2000,
                easing: 'easeInOutExpo'
            }
        }
    });
</script>
@endsection
