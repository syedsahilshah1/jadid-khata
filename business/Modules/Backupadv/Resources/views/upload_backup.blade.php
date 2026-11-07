@extends('layouts.app')

@section('title', __('Backupadv::lang.upload_backup'))

@section('content')
    @include('Backupadv::layouts.nav')

    <style>
        /* تصميم عصري لتحسين شكل النموذج */
        .glass-card {
            background: rgba(255, 255, 255, 0.8);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
            border-radius: 20px;
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: transform 0.3s ease-in-out;
            padding: 20px;
        }
        .glass-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.2);
        }
        .btn-modern {
            border: none;
            transition: background 0.3s ease;
            background: linear-gradient(135deg, #00c6ff, #0072ff);
            color: #fff;
            padding: 12px 30px;
            border-radius: 30px;
            font-size: 1.1rem;
        }
        .btn-modern:hover {
            background: linear-gradient(135deg, #0072ff, #00c6ff);
            transform: translateY(-3px);
            box-shadow: 0 4px 15px rgba(0, 114, 255, 0.4);
        }
        .form-control-file {
            border-radius: 10px;
            border: 2px solid rgba(0, 123, 255, 0.2);
            padding: 10px;
        }
        .form-text {
            font-size: 0.9rem;
            color: #6c757d;
        }
        .text-gradient {
            background: -webkit-linear-gradient(45deg, #0072ff, #00c6ff);
            -webkit-background-clip: text;
            color: transparent;
        }
    </style>

    <div class="container my-5">
        <!-- عنوان الصفحة بتأثير حديث -->
        <div class="text-center mb-5">
            <h1 class="display-4 fw-bold text-gradient">
                {{ __('Backupadv::lang.upload_backup') }}
            </h1>
        </div>

        <!-- عرض رسائل النجاح أو الأخطاء -->
        @if (session('status'))
            <div class="alert alert-success glass-card">
                <i class="bi bi-check-circle-fill"></i> {{ session('status') }}
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger glass-card">
                <i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}
            </div>
        @endif

        <!-- عرض الأخطاء في التحقق -->
        @if ($errors->any())
            <div class="alert alert-danger glass-card">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li><i class="bi bi-exclamation-circle-fill"></i> {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- نموذج رفع النسخة الاحتياطية -->
        <div class="glass-card">
            <form action="{{ action([\Modules\Backupadv\Http\Controllers\BackupadvController::class, 'storeBackup']) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-group mb-4">
                    <label for="backup_file" class="form-label fw-bold">{{ __('Backupadv::lang.choose_backup_file') }}</label>
                    <input type="file" name="backup_file" id="backup_file" class="form-control form-control-file" required>
                    <small class="form-text">{{ __('Backupadv::lang.accepted_file_type') }}: .zip</small>
                </div>
                <button type="submit" class="btn btn-modern w-100">
                    <i class="bi bi-upload"></i> {{ __('Backupadv::lang.upload_backup') }}
                </button>
            </form>
        </div>
    </div>
@endsection
