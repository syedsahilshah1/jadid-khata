@extends('layouts.app')

@section('title', __('Backupadv::lang.backup_logs'))

@section('content')

@include('Backupadv::layouts.nav')

<style>
    /* تحسينات أنماط التصميم العصري */
    .glass-card {
        background: rgba(255, 255, 255, 0.8);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        border-radius: 20px;
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        transition: transform 0.3s ease-in-out;
    }
    .glass-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.2);
    }
    .glass-table {
        background: rgba(255, 255, 255, 0.5);
        border-collapse: separate;
        border-spacing: 0 12px;
    }
    .glass-table thead th {
        font-weight: bold;
        border-bottom: 2px solid rgba(0, 0, 0, 0.1);
        background: rgba(0, 123, 255, 0.2);
    }
    .glass-table tbody tr {
        border-radius: 15px;
        box-shadow: 0 5px 10px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease;
        overflow: hidden;
    }
    .glass-table tbody tr:hover {
        transform: translateY(-5px);
        background: rgba(0, 123, 255, 0.1);
    }
    .rounded-pill {
        border-radius: 50px !important;
    }
    .btn-modern {
        border: none;
        transition: background 0.3s ease;
        background: linear-gradient(135deg, #00c6ff, #0072ff);
        color: #fff;
    }
    .btn-modern:hover {
        background: linear-gradient(135deg, #0072ff, #00c6ff);
    }
    .btn-outline-modern {
        border: 2px solid #0072ff;
        color: #0072ff;
        transition: all 0.3s ease;
    }
    .btn-outline-modern:hover {
        background: #0072ff;
        color: #fff;
        border-color: #0072ff;
    }
    .search-bar {
        padding: 10px 20px;
        border-radius: 30px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        border: 2px solid rgba(0, 123, 255, 0.2);
    }
    .pagination-modern .page-link {
        border-radius: 50px;
        border: none;
        color: #0072ff;
        transition: background 0.3s ease;
    }
    .pagination-modern .page-link:hover {
        background: #0072ff;
        color: #fff;
    }
    .btn-danger-modern {
        background: linear-gradient(135deg, #ff416c, #ff4b2b);
        color: #fff;
        border: none;
        padding: 12px 30px;
        border-radius: 30px;
        font-size: 1.1rem;
        transition: all 0.3s ease;
    }
    .btn-danger-modern:hover {
        background: linear-gradient(135deg, #ff4b2b, #ff416c);
        transform: translateY(-3px);
        box-shadow: 0 4px 15px rgba(255, 75, 43, 0.4);
    }
</style>

<div class="container my-5">
    <!-- عنوان الصفحة مع تأثير حديث -->
    <div class="text-center mb-5">
        <h1 class="display-4 fw-bold text-gradient" style="background: -webkit-linear-gradient(45deg, #0072ff, #00c6ff); -webkit-background-clip: text; color: transparent;">
            {{ __('Backupadv::lang.backup_logs') }}
        </h1>
        <p class="text-muted">{{ __('Backupadv::lang.manage_your_backups') }}</p>
    </div>

    <!-- قسم البحث بتصميم حديث -->
    <div class="card glass-card mb-5 p-4 border-0">
        <form method="GET" action="{{ route('backup.logs') }}" class="d-flex align-items-center">
            <input type="text" name="search" class="form-control form-control-lg search-bar flex-grow-1 me-3" placeholder="{{ __('Backupadv::lang.search') }}" value="{{ request('search') }}">
            <button type="submit" class="btn btn-modern btn-lg rounded-pill">
                <i class="bi bi-search"></i> {{ __('Backupadv::lang.search_button') }}
            </button>
        </form>
    </div>

    <!-- عرض السجلات الاحتياطية -->
    <form method="POST" action="{{ route('backup.bulk_delete') }}" id="bulk_action_form">
        @csrf

        @if ($backupLogs->isEmpty())
            <!-- رسالة عندما لا توجد سجلات -->
            <div class="alert alert-warning text-center glass-card mt-4 p-4">
                <i class="bi bi-exclamation-circle-fill"></i> {{ __('Backupadv::lang.no_backup_logs') }}
            </div>
        @else
            <div class="card glass-card border-0 mb-4">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table glass-table align-middle">
                            <thead class="bg-transparent">
                                <tr>
                                    <th class="text-center p-4"><input type="checkbox" id="select_all" class="form-check-input"></th>
                                    <th class="p-4">{{ __('Backupadv::lang.date_time') }}</th>
                                    <th class="p-4">{{ __('Backupadv::lang.user') }}</th>
                                    <th class="p-4">{{ __('Backupadv::lang.filename') }}</th>
                                    <th class="p-4">{{ __('Backupadv::lang.status') }}</th>
                                    <th class="p-4">{{ __('Backupadv::lang.size_mb') }}</th>
                                    <th class="p-4">{{ __('Backupadv::lang.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($backupLogs as $log)
                                    <tr>
                                        <td class="text-center p-4"><input type="checkbox" name="filenames[]" value="{{ $log->filename }}" class="form-check-input"></td>
                                        <td class="p-4">{{ \Carbon\Carbon::parse($log->created_at)->format('Y-m-d H:i') }}</td>
                                        <td class="p-4">{{ $log->user_name }} (ID: {{ $log->user_id }})</td>
                                        <td class="p-4 text-truncate" style="max-width: 250px;">{{ $log->filename }}</td>
                                        <td class="p-4">
                                            @if ($log->status == 'success')
                                                <span class="badge bg-success rounded-pill">{{ __('Backupadv::lang.success') }}</span>
                                            @else
                                                <span class="badge bg-danger rounded-pill">{{ __('Backupadv::lang.failed') }}</span>
                                            @endif
                                        </td>
                                        <td class="p-4">{{ $log->size_in_mb }} MB</td>
                                        <td class="p-4">
                                            @if ($log->status == 'success')
                                                <a href="{{ route('backup.download', ['filename' => $log->filename]) }}" class="btn btn-outline-modern btn-sm me-2 rounded-pill">
                                                    <i class="bi bi-cloud-arrow-down"></i> {{ __('Backupadv::lang.download_backup') }}
                                                </a>
                                                <a href="{{ route('backup.delete', ['filename' => $log->filename]) }}" class="btn btn-outline-danger btn-sm rounded-pill">
                                                    <i class="bi bi-trash"></i> {{ __('Backupadv::lang.delete_backup') }}
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- روابط الصفحات -->
                <div class="card-footer text-center bg-transparent py-4">
                    {{ $backupLogs->links('pagination::bootstrap-4', ['class' => 'pagination-modern']) }}
                </div>
            </div>

            <!-- زر الحذف الجماعي -->
            <div class="d-flex justify-content-end my-4">
                <button type="button" class="btn btn-danger-modern btn-lg" onclick="bulkDelete()">
                    <i class="bi bi-trash"></i> {{ __('Backupadv::lang.bulk_delete') }}
                </button>
            </div>
        @endif
    </form>
</div>

<!-- JavaScript to handle select all checkboxes and bulk actions -->
<script>
    document.getElementById('select_all').addEventListener('change', function(e) {
        let checkboxes = document.querySelectorAll('input[name="filenames[]"]');
        checkboxes.forEach(cb => cb.checked = e.target.checked);
    });

    function bulkDelete() {
        if (confirm('{{ __('Backupadv::lang.delete_confirmation') }}')) {
            document.getElementById('bulk_action_form').submit();
        }
    }
</script>

@endsection
