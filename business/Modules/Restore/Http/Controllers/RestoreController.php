<?php
/**
 * Restore backups Module
 * * This module helps restore already created backups from ultimate pos.
 * * Module: Restore
 * Author: [Telegram @omgklrahul]
 * Version: 1.0.0
 * * This is a complete free module for non commercial use.
 * * @package Modules\Restore
 */

namespace Modules\Restore\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class RestoreController extends Controller
{
    public function __construct()
    {
        // Manually start the session if not already started
        if (!session()->isStarted()) {
            session()->start();
        }
    }

    public function index()
    {
        // List available backup files from the backup disk
        $disk = config('backup.backup.destination.disks')[0] ?? 'local';
        $backupFolder = config('backup.backup.name', 'UltimatePOS');
        $files = Storage::disk($disk)->files($backupFolder);
        $backups = array_filter($files, function ($file) {
            return preg_match('/\.zip$|\.sql$/', $file);
        });
        // Add restore options for the view
        $restoreOptions = [
            'sql' => 'Database',
            'uploads' => 'Uploaded Files',
            'env' => '.env File',
            'custom_views' => 'Custom Views',
        ];
        return view('restore::index', compact('backups', 'restoreOptions'));
    }

    public function run(Request $request)
    {
        $restoreOptions = $request->input('restore_option');
        $progress = ['Starting restore...'];
        $backupFile = $request->input('backup_file');
        $uploadedZip = $request->file('upload_zip');
        $disk = config('backup.backup.destination.disks')[0] ?? 'local';
        $backupFolder = config('backup.backup.name', 'UltimatePOS');
        $path = null;

        // Validation
        if ($uploadedZip) {
            $validator = \Validator::make($request->all(), [
                'upload_zip' => 'required|file|mimes:zip',
                'restore_option' => 'required|array',
                'restore_option.*' => 'in:sql,uploads,env,custom_views',
            ]);
            if ($validator->fails()) {
                if ($request->ajax()) {
                    return response()->json(['errors' => $validator->errors()], 422);
                }
                return back()->withErrors($validator->errors());
            }
            // Save uploaded zip to temp location
            $path = storage_path('app/restore-upload.zip');
            $uploadedZip->move(storage_path('app'), 'restore-upload.zip');
            $backupFile = 'restore-upload.zip';
        } else {
            $validator = \Validator::make($request->all(), [
                'backup_file' => 'required|string',
                'restore_option' => 'required|array',
                'restore_option.*' => 'in:sql,uploads,env,custom_views',
            ]);
            if ($validator->fails()) {
                if ($request->ajax()) {
                    return response()->json(['errors' => $validator->errors()], 422);
                }
                return back()->withErrors($validator->errors());
            }
            $path = Storage::disk($disk)->path($backupFile);
        }

        // Only allow .sql or .zip files
        if (!preg_match('/\.sql$|\.zip$/', $backupFile)) {
            $progress[] = 'Invalid backup file type.';
            Cache::put('restore_progress', $progress, 600);
            return back()->withErrors(['Invalid backup file type.']);
        }

        $runSqlRestore = function($sqlFile) use (&$progress) {
            $progress[] = 'Restoring database from SQL file: ' . basename($sqlFile);
            $sql = file_get_contents($sqlFile);
            $statements = preg_split("/(;\s*\n)/", $sql);
            foreach ($statements as $statement) {
                $statement = trim($statement);
                if ($statement) {
                    try {
                        DB::unprepared($statement);
                    } catch (\Exception $e) {
                        $progress[] = 'SQL restore error: ' . $e->getMessage();
                        Cache::put('restore_progress', $progress, 600);
                        Log::error('SQL restore error', ['error' => $e->getMessage(), 'sql' => $statement]);
                        return false;
                    }
                }
            }
            $progress[] = 'Database restore complete.';
            Cache::put('restore_progress', $progress, 600);
            return true;
        };

        // Restore the database from .sql file
        if (preg_match('/\.sql$/', $backupFile)) {
            if (!in_array('sql', $restoreOptions)) {
                $progress[] = 'Selected backup is .sql, but database restore not selected.';
                Cache::put('restore_progress', $progress, 600);
                return back()->withErrors(['Selected backup is .sql, but database restore not selected.']);
            }
            $ok = $runSqlRestore($path);
            if (!$ok) {
                Cache::put('restore_progress', $progress, 600);
                return back()->withErrors(['Restore failed. Check logs for details.']);
            }
            $progress[] = 'Restore finished.';
            Cache::put('restore_progress', $progress, 600);
            return back()->with('status', 'Database restored successfully!');
        }

        // Restore from .zip file
        if (preg_match('/\.zip$/', $backupFile)) {
            $extractPath = storage_path('app/restore-temp');
            $progress[] = 'Extracting zip file...';
            Cache::put('restore_progress', $progress, 600);
            if (!is_dir($extractPath)) {
                mkdir($extractPath, 0777, true);
            }
            $zip = new \ZipArchive();
            if ($zip->open($path) === TRUE) {
                $zip->extractTo($extractPath);
                $zip->close();
                $progress[] = 'Zip extracted.';
            } else {
                $progress[] = 'Failed to extract zip file.';
                Cache::put('restore_progress', $progress, 600);
                return back()->withErrors(['Failed to extract zip file.']);
            }
            $sqlFile = null;
            $uploadsDir = null;
            $envFile = null;
            $customViewsDir = null;
            $rii = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($extractPath));
            foreach ($rii as $file) {
                if (!$file->isDir() && preg_match('/\.sql$/', $file->getFilename())) {
                    $sqlFile = $file->getPathname();
                }
                if ($file->isDir() && strtolower($file->getFilename()) === 'uploads') {
                    $uploadsDir = $file->getPathname();
                }
                if (!$file->isDir() && $file->getFilename() === '.env') {
                    $envFile = $file->getPathname();
                }
                if ($file->isDir() && strtolower($file->getFilename()) === 'custom_views') {
                    $customViewsDir = $file->getPathname();
                }
            }
            $messages = [];
            // Restore SQL if requested
            if (in_array('sql', $restoreOptions)) {
                if (!$sqlFile) {
                    $progress[] = 'No .sql file found in the backup archive.';
                    Cache::put('restore_progress', $progress, 600);
                    return back()->withErrors(['No .sql file found in the backup archive.']);
                }
                $ok = $runSqlRestore($sqlFile);
                if (!$ok) {
                    \File::deleteDirectory($extractPath);
                    $progress[] = 'Restore from zip failed.';
                    Cache::put('restore_progress', $progress, 600);
                    return back()->withErrors(['Restore from zip failed. Check logs for details.']);
                }
                $messages[] = 'Database restored successfully!';
            }
            // Restore uploads if requested
            if (in_array('uploads', $restoreOptions) && $uploadsDir && is_dir($uploadsDir)) {
                $progress[] = 'Restoring uploaded files...';
                Cache::put('restore_progress', $progress, 600);
                $publicUploads = public_path('uploads');
                if (!is_dir($publicUploads)) {
                    mkdir($publicUploads, 0777, true);
                }
                $riiUploads = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($uploadsDir, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST);
                foreach ($riiUploads as $item) {
                    $targetPath = $publicUploads . DIRECTORY_SEPARATOR . $riiUploads->getSubPathName();
                    if ($item->isDir()) {
                        if (!is_dir($targetPath)) {
                            mkdir($targetPath, 0777, true);
                        }
                    } else {
                        copy($item->getPathname(), $targetPath);
                    }
                }
                $progress[] = 'Uploaded files restored successfully!';
                Cache::put('restore_progress', $progress, 600);
                $messages[] = 'Uploaded files restored successfully!';
            } elseif (in_array('uploads', $restoreOptions) && !$uploadsDir) {
                $progress[] = 'No uploads directory found in the backup archive.';
                Cache::put('restore_progress', $progress, 600);
                $messages[] = 'No uploads directory found in the backup archive.';
            }
            // Restore .env if requested
            if (in_array('env', $restoreOptions) && $envFile) {
                $progress[] = 'Restoring .env file...';
                Cache::put('restore_progress', $progress, 600);
                copy($envFile, base_path('.env'));
                $progress[] = '.env file restored!';
                Cache::put('restore_progress', $progress, 600);
                $messages[] = '.env file restored!';
            } elseif (in_array('env', $restoreOptions) && !$envFile) {
                $progress[] = 'No .env file found in the backup archive.';
                Cache::put('restore_progress', $progress, 600);
                $messages[] = 'No .env file found in the backup archive.';
            }
            // Restore custom_views if requested
            if (in_array('custom_views', $restoreOptions) && $customViewsDir && is_dir($customViewsDir)) {
                $progress[] = 'Restoring custom_views directory...';
                Cache::put('restore_progress', $progress, 600);
                $targetCustomViews = base_path('custom_views');
                if (!is_dir($targetCustomViews)) {
                    mkdir($targetCustomViews, 0777, true);
                }
                $riiCustom = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($customViewsDir, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST);
                foreach ($riiCustom as $item) {
                    $targetPath = $targetCustomViews . DIRECTORY_SEPARATOR . $riiCustom->getSubPathName();
                    if ($item->isDir()) {
                        if (!is_dir($targetPath)) {
                            mkdir($targetPath, 0777, true);
                        }
                    } else {
                        copy($item->getPathname(), $targetPath);
                    }
                }
                $progress[] = 'custom_views directory restored!';
                Cache::put('restore_progress', $progress, 600);
                $messages[] = 'custom_views directory restored!';
            } elseif (in_array('custom_views', $restoreOptions) && !$customViewsDir) {
                $progress[] = 'No custom_views directory found in the backup archive.';
                Cache::put('restore_progress', $progress, 600);
                $messages[] = 'No custom_views directory found in the backup archive.';
            }
            // Clean up extracted files
            \File::deleteDirectory($extractPath);
            $progress[] = 'Restore finished.';
            Cache::put('restore_progress', $progress, 600);
            if ($request->ajax()) {
                return response()->json(['success' => true, 'messages' => $messages]);
            }
            return back()->with('status', implode(' ', $messages));
        }

        $progress[] = 'Unknown backup file type.';
        Cache::put('restore_progress', $progress, 600);
        return back()->withErrors(['Unknown backup file type.']);
    }
}
