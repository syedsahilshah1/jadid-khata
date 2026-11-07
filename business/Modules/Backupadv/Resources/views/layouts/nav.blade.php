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
                <!-- Navbar Brand -->
                <a class="navbar-brand" href="{{action([\Modules\Backupadv\Http\Controllers\BackupadvController::class, 'dashboard'])}}">
                    📦 {{__('Backupadv::lang.Backupadv_module')}}
                </a>
            </div>
            <!-- Collect the nav links, forms, and other content for toggling -->
            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                <ul class="nav navbar-nav">
                    <!-- النسخ الاحتياطي - Backup -->
                    <li @if(request()->segment(2) == 'backup') class="active" @endif>
                        <a href="{{action([\Modules\Backupadv\Http\Controllers\BackupadvController::class, 'backup'])}}">
                            💾 {{__('Backupadv::lang.backup')}}
                        </a>
                    </li>
                    <!-- رفع نسخة احتياطية - Upload Backup -->
                    <li @if(request()->segment(2) == 'upload-backup') class="active" @endif>
                        <a href="{{action([\Modules\Backupadv\Http\Controllers\BackupadvController::class, 'uploadBackup'])}}">
                            ⬆️ {{__('Backupadv::lang.upload_backup')}}
                        </a>
                    </li>
                    <!-- سجل النسخ الاحتياطي - Backup Logs -->
                    <li @if(request()->segment(2) == 'backup-logs') class="active" @endif>
                        <a href="{{action([\Modules\Backupadv\Http\Controllers\BackupadvController::class, 'backupLogs'])}}">
                            📜 {{__('Backupadv::lang.backup_logs')}}
                        </a>
                    </li>
                </ul>
            </div><!-- /.navbar-collapse -->
        </div><!-- /.container-fluid -->
    </nav>
</section>
