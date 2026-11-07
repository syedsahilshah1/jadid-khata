@extends('layouts.app')

@section('title', __('Backupadv::lang.Backupadv_module'))

@section('content')
    @include('Backupadv::layouts.nav')

    <style>
        /* تصميم عصري للبطاقات الزجاجية */
        .glass-card {
            background: rgba(255, 255, 255, 0.8);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
            border-radius: 20px;
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 20px;
            transition: transform 0.3s ease-in-out;
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
            padding: 10px 30px;
            border-radius: 30px;
            font-size: 1rem;
        }
        .btn-modern:hover {
            background: linear-gradient(135deg, #0072ff, #00c6ff);
            transform: translateY(-3px);
            box-shadow: 0 4px 15px rgba(0, 114, 255, 0.4);
        }
        .table-glass {
            background: rgba(255, 255, 255, 0.5);
            border-collapse: separate;
            border-spacing: 0 12px;
            width: 100%;
        }
        .table-glass th, .table-glass td {
            padding: 15px;
        }
        .table-glass thead th {
            font-weight: bold;
            border-bottom: 2px solid rgba(0, 0, 0, 0.1);
            background: rgba(0, 123, 255, 0.2);
        }
        .table-glass tbody tr {
            border-radius: 15px;
            box-shadow: 0 5px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            overflow: hidden;
        }
        .table-glass tbody tr:hover {
            transform: translateY(-5px);
            background: rgba(0, 123, 255, 0.1);
        }
        .text-gradient {
            background: -webkit-linear-gradient(45deg, #0072ff, #00c6ff);
            -webkit-background-clip: text;
            color: transparent;
        }
    </style>

    <div class="container my-5">
        <!-- عنوان الصفحة بتصميم حديث -->
        <div class="text-center mb-5">
            <h1 class="display-4 fw-bold text-gradient">
                {{ __('Backupadv::lang.backup') }}
            </h1>
        </div>

        <!-- عرض خيارات النسخ الاحتياطي للشركة الحالية -->
        @if($business)
            <div class="glass-card mb-4">
                <table class="table table-glass align-middle">
                    <thead>
                        <tr>
                            <th>{{ __('Backupadv::lang.business_name') }}</th>
                            <th>{{ __('Backupadv::lang.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="fw-bold text-primary">{{ $business->name }}</td>
                            <td>
                                <!-- زر تنزيل النسخة الاحتياطية -->
                                <a href="{{ route('backup.download', ['business_id' => $business->id]) }}" 
                                   class="btn btn-modern">
                                    <i class="bi bi-cloud-arrow-down"></i> {{ __('Backupadv::lang.download_backup') }}
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @else
            <!-- رسالة عند عدم توفر بيانات الشركة -->
            <div class="alert alert-warning text-center glass-card mt-4 p-4">
                <i class="bi bi-exclamation-circle-fill"></i> {{ __('Backupadv::lang.no_business_data_available') }}
            </div>
        @endif
    </div>
@endsection
