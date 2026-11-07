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

@section('content')
<div class="container">
    <h1>Restore Backup</h1>
    @if(session('status'))
        <div class="alert alert-{{ session('status.success') ? 'success' : 'danger' }}">
            {{ session('status.msg') }}
        </div>
    @endif
    <div id="status-message"></div>
    <div id="error-message"></div>
    <form method="POST" action="{{ route('restore.run') }}" id="restore-form" enctype="multipart/form-data">
        @csrf
        <div class="form-group">
            <label for="backup_file">Select Backup File (from server)</label>
            <select name="backup_file" id="backup_file" class="form-control">
                <option value="">-- Select --</option>
                @foreach($backups as $file)
                    <option value="{{ $file }}">{{ $file }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group mt-3">
            <label for="upload_zip">Or Upload Backup Zip</label>
            <input type="file" name="upload_zip" id="upload_zip" class="form-control" accept=".zip">
            <small class="form-text text-muted">Upload a backup .zip file to restore from your computer.</small>
        </div>
        <div class="form-group mt-3">
            <label>Restore Options</label><br>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" name="restore_option[]" id="restore_sql" value="sql" required>
                <label class="form-check-label" for="restore_sql">Database</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" name="restore_option[]" id="restore_uploads" value="uploads">
                <label class="form-check-label" for="restore_uploads">Uploaded Files</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" name="restore_option[]" id="restore_env" value="env">
                <label class="form-check-label" for="restore_env">.env File</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" name="restore_option[]" id="restore_custom_views" value="custom_views">
                <label class="form-check-label" for="restore_custom_views">Custom Views</label>
            </div>
        </div>
        <button type="submit" class="btn btn-primary mt-3" id="restore-btn">Restore</button>
    </form>
    <div class="mt-4">
        <label>Restore Progress (CLI Output):</label>
        <pre id="cli-output" style="background:#222;color:#0f0;padding:1em;height:250px;overflow:auto;"></pre>
        <button class="btn btn-success mt-3" id="done-btn" style="display:none;">Done</button>
    </div>
</div>
<script>
    let polling = false;
    let restoreFinished = false;
    function pollProgress() {
        fetch("{{ route('restore.progress') }}")
            .then(r => r.json())
            .then(data => {
                let output = (data || []).join('\n');
                document.getElementById('cli-output').textContent = output;
                if (output.match(/Restore finished\./)) {
                    polling = false;
                    restoreFinished = true;
                    document.getElementById('done-btn').style.display = 'inline-block';
                    document.getElementById('status-message').innerHTML = '<div class="alert alert-success">Restore completed successfully!</div>';
                } else if (polling) {
                    setTimeout(pollProgress, 1000);
                }
            });
    }
    document.getElementById('restore-form').addEventListener('submit', function(e) {
        e.preventDefault();
        polling = true;
        restoreFinished = false;
        document.getElementById('cli-output').textContent = 'Starting restore...\n';
        document.getElementById('done-btn').style.display = 'none';
        document.getElementById('status-message').innerHTML = '';
        document.getElementById('error-message').innerHTML = '';
        const form = e.target;
        const formData = new FormData(form);
        // If upload_zip is selected, clear backup_file selection
        if (form.upload_zip && form.upload_zip.files.length > 0) {
            formData.set('backup_file', '');
        }
        fetch(form.action, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': form.querySelector('[name=_token]').value
            },
            body: formData
        })
        .then(async response => {
            if (!response.ok) {
                let data = await response.json().catch(() => null);
                let msg = data && data.errors ? Object.values(data.errors).flat().join('<br>') : 'Restore failed.';
                document.getElementById('error-message').innerHTML = '<div class="alert alert-danger">' + msg + '</div>';
                polling = false;
                return;
            }
            setTimeout(pollProgress, 1000);
        })
        .catch(() => {
            document.getElementById('error-message').innerHTML = '<div class="alert alert-danger">Restore failed. Please try again.</div>';
            polling = false;
        });
    });
    document.getElementById('done-btn').addEventListener('click', function() {
        window.location.href = '/'; // Change to your desired redirect URL
    });
</script>
@endsection
