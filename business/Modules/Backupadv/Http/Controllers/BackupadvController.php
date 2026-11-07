<?php

namespace Modules\Backupadv\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Business;
use DB;
use ZipArchive;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\File;

class BackupadvController extends Controller
{
    /**
     * Display dashboard for Backupadv module with stats.
     *
     * @return Response
     */
    public function dashboard()
    {
        // Get the business ID associated with the current user
        $business_id = auth()->user()->business_id;

        // Retrieve statistics
        $totalBackups = DB::table('backupadv_logs')->where('business_id', $business_id)->count();
        $successfulBackups = DB::table('backupadv_logs')->where('business_id', $business_id)->where('status', 'success')->count();
        $failedBackups = DB::table('backupadv_logs')->where('business_id', $business_id)->where('status', 'failed')->count();
        $totalSize = DB::table('backupadv_logs')->where('business_id', $business_id)->sum('size_in_mb');
        $latestBackup = DB::table('backupadv_logs')->where('business_id', $business_id)->orderBy('created_at', 'desc')->first();

        return view('Backupadv::dashboard', compact('totalBackups', 'successfulBackups', 'failedBackups', 'totalSize', 'latestBackup'));
    }

    /**
     * Display the backup logs with pagination and search.
     *
     * @param Request $request
     * @return Renderable
     */
    public function backupLogs(Request $request)
    {
        // Get the business ID
        $business_id = auth()->user()->business_id;

        // Search functionality
        $query = DB::table('backupadv_logs')
            ->where('backupadv_logs.business_id', $business_id)
            ->join('users', 'backupadv_logs.user_id', '=', 'users.id')
            ->select('backupadv_logs.*', DB::raw("CONCAT(COALESCE(users.surname, ''),' ',COALESCE(users.first_name, ''),' ',COALESCE(users.last_name, '')) as user_name"));

        // Filter by search term
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('backupadv_logs.filename', 'LIKE', "%{$search}%")
                  ->orWhere('users.first_name', 'LIKE', "%{$search}%")
                  ->orWhere('users.last_name', 'LIKE', "%{$search}%");
            });
        }

        // Paginate results
        $backupLogs = $query->orderBy('backupadv_logs.created_at', 'desc')->paginate(10);

        return view('Backupadv::backup_logs', compact('backupLogs'));
    }

    /**
     * Delete a specific backup log and its associated file.
     *
     * @param string $filename
     * @return Response
     */
    public function deleteBackup($filename)
    {
        $backupLog = DB::table('backupadv_logs')->where('filename', $filename)->first();
        
        if ($backupLog) {
            // Delete file if exists
            $filePath = storage_path("app/backups/$filename");
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            // Delete log entry
            DB::table('backupadv_logs')->where('filename', $filename)->delete();

            return redirect()->back()->with('status', 'Backup deleted successfully.');
        }

        return redirect()->back()->with('error', 'Backup not found.');
    }

    /**
     * Bulk delete backups based on filenames.
     *
     * @param Request $request
     * @return Response
     */
    public function bulkDelete(Request $request)
    {
        $filenames = $request->input('filenames');

        if (empty($filenames)) {
            return redirect()->back()->with('error', 'No backups selected for deletion.');
        }

        foreach ($filenames as $filename) {
            $backupLog = DB::table('backupadv_logs')->where('filename', $filename)->first();
            
            if ($backupLog) {
                // Delete file if exists
                $filePath = storage_path("app/backups/$filename");
                if (file_exists($filePath)) {
                    unlink($filePath);
                }

                // Delete log entry
                DB::table('backupadv_logs')->where('filename', $filename)->delete();
            }
        }

        return redirect()->back()->with('status', 'Selected backups deleted successfully.');
    }

    /**
     * Create a new backup (dummy function for button).
     *
     * @return Response
     */
    public function createNewBackup()
    {
        // Logic for creating a new backup
        // (This should call the existing backup creation logic)

        return redirect()->back()->with('status', 'New backup created successfully.');
    }
    
    /**
     * Bulk download backups based on filenames.
     *
     * @param Request $request
     * @return Response
     */
    public function bulkDownload(Request $request)
    {
        $filenames = $request->input('filenames');

        if (empty($filenames)) {
            return redirect()->back()->with('error', 'No backups selected for download.');
        }

        $zip = new ZipArchive();
        $zipFileName = "bulk_backups.zip";
        $zipFilePath = storage_path("app/backups/$zipFileName");

        // Ensure backups directory exists
        File::ensureDirectoryExists(storage_path('app/backups'));

        if ($zip->open($zipFilePath, ZipArchive::CREATE) !== TRUE) {
            return redirect()->back()->with('error', 'Could not create zip file.');
        }

        foreach ($filenames as $filename) {
            $filePath = storage_path("app/backups/$filename");
            if (file_exists($filePath)) {
                $zip->addFile($filePath, $filename);
            }
        }

        $zip->close();

        return response()->download($zipFilePath)->deleteFileAfterSend(true);
    }

    /**
     * Display the upload backup page.
     * @return Renderable
     */
    public function uploadBackup()
    {
        return view('Backupadv::upload_backup');
    }

    /**
     * Display the backup page for the current user's business.
     * @return Renderable
     */
    public function backup()
    {
        // Get the business ID associated with the current user
        $business_id = auth()->user()->business_id;

        // Retrieve the business details
        $business = Business::where('id', $business_id)->first();

        return view('Backupadv::backup')->with(compact('business'));
    }

    /**
     * Download a backup for the current user's business.
     * @return Response
     */
    public function downloadBackup()
    {
        // Get the current user's business ID
        $business_id = auth()->user()->business_id;

        // Retrieve all location IDs related to this business ID
        $location_ids = DB::table('business_locations')
            ->where('business_id', $business_id)
            ->pluck('id')
            ->toArray();

        // List all tables to be included in the backup
        $tablesToBackup = [
            'accounting_accounts', 'accounting_accounts_transactions', 'accounting_account_types', 'accounting_acc_trans_mappings',
            'accounting_budgets', 'accounts', 'account_transactions', 'account_types', 'activity_log', 'aiassistance_history',
            'assets', 'asset_maintenances', 'asset_transactions', 'asset_warranties', 'barcodes', 'bookings', 'brands', 'business',
            'business_locations', 'cash_denominations', 'cash_registers', 'cash_register_transactions', 'categories', 'categorizables',
            'cms_pages', 'cms_page_metas', 'cms_site_details', 'contacts', 'crm_call_logs', 'crm_campaigns', 'crm_contact_person_commissions',
            'crm_followup_invoices', 'crm_lead_users', 'crm_marketplaces', 'crm_proposals', 'crm_proposal_templates', 'crm_schedules',
            'crm_schedule_logs', 'crm_schedule_users', 'currencies', 'customer_groups', 'dashboard_configurations', 'discounts',
            'discount_variations', 'document_and_notes', 'essentials_allowances_and_deductions', 'essentials_attendances',
            'essentials_documents', 'essentials_document_shares', 'essentials_holidays', 'essentials_kb', 'essentials_kb_users',
            'essentials_leaves', 'essentials_leave_types', 'essentials_messages', 'essentials_payroll_groups',
            'essentials_payroll_group_transactions', 'essentials_reminders', 'essentials_shifts', 'essentials_todos_users',
            'essentials_todo_comments', 'essentials_to_dos', 'essentials_user_allowance_and_deductions', 'essentials_user_sales_targets',
            'essentials_user_shifts', 'expense_categories', 'group_sub_taxes', 'hms_booking_extras', 'hms_booking_lines',
            'hms_coupons', 'hms_extras', 'hms_rooms', 'hms_room_types', 'hms_room_type_pricings', 'hms_room_unavailables',
            'installments', 'installment_db', 'installment_systems', 'inventory', 'inventory_products', 'invoice_layouts',
            'invoice_schemes', 'media', 'mfg_ingredient_groups', 'mfg_recipes', 'mfg_recipe_ingredients', 'migrations',
            'model_has_permissions', 'model_has_roles', 'notifications', 'notification_templates', 'oauth_access_tokens',
            'oauth_auth_codes', 'oauth_clients', 'oauth_personal_access_clients', 'oauth_refresh_tokens', 'packages', 'password_resets',
            'permissions', 'pjt_invoice_lines', 'pjt_projects', 'pjt_project_members', 'pjt_project_tasks',
            'pjt_project_task_comments', 'pjt_project_task_members', 'pjt_project_time_logs', 'printers', 'products',
            'product_locations', 'product_racks', 'product_variations', 'purchase_lines', 'reference_counts',
            'repair_device_models', 'repair_job_sheets', 'repair_statuses', 'res_product_modifier_sets', 'res_tables',
            'roles', 'role_has_permissions', 'selling_price_groups', 'sell_line_warranties', 'sessions', 'sheet_spreadsheets',
            'sheet_spreadsheet_shares', 'stock_adjustments_temp', 'stock_adjustment_lines', 'subscriptions', 'superadmin_communicator_logs',
            'superadmin_coupons', 'superadmin_frontend_pages', 'system', 'tax_rates', 'transactions', 'transaction_payments',
            'transaction_sell_lines', 'transaction_sell_lines_purchase_lines', 'types_of_services', 'units', 'users',
            'user_contact_access', 'variations', 'variation_group_prices', 'variation_location_details', 'variation_templates',
            'variation_value_templates', 'warranties', 'woocommerce_sync_logs'
        ];

        // Ensure backups directory exists
        $backupDir = storage_path('app/backups');
        if (!File::exists($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        // Create a temporary file for the SQL content
        $sqlFilePath = storage_path("app/backups/full_backup.sql");
        $sqlContent = "";

        // Export data to a single SQL file
        foreach ($tablesToBackup as $table) {
            if (Schema::hasTable($table)) {
                $columns = Schema::getColumnListing($table);
                $columnList = implode('`, `', $columns);

                $tableData = collect();
                if (Schema::hasColumn($table, 'business_id') && Schema::hasColumn($table, 'location_id')) {
                    $tableData = DB::table($table)
                        ->where('business_id', $business_id)
                        ->orWhereIn('location_id', $location_ids)
                        ->get();
                } elseif (Schema::hasColumn($table, 'business_id')) {
                    $tableData = DB::table($table)->where('business_id', $business_id)->get();
                } elseif (Schema::hasColumn($table, 'location_id')) {
                    $tableData = DB::table($table)->whereIn('location_id', $location_ids)->get();
                } else {
                    $tableData = DB::table($table)->get();
                }

                if ($tableData->isNotEmpty()) {
                    $sqlContent .= "INSERT INTO `$table` (`$columnList`) VALUES\n";
                    $rows = [];
                    foreach ($tableData as $row) {
                        $values = [];
                        foreach ($row as $value) {
                            $values[] = is_null($value) ? "NULL" : "'" . addslashes($value) . "'";
                        }
                        $rows[] = "(" . implode(", ", $values) . ")";
                    }
                    $sqlContent .= implode(",\n", $rows) . ";\n\n";
                }
            } else {
                \Log::warning("Table $table does not exist in the database.");
            }
        }

        if (!empty($sqlContent)) {
            try {
                file_put_contents($sqlFilePath, $sqlContent);

                $zip = new ZipArchive();
                $zipFileName = "full_backup.zip";
                $zipFilePath = storage_path("app/backups/$zipFileName");

                if ($zip->open($zipFilePath, ZipArchive::CREATE) !== TRUE) {
                    // Log failed backup
                    DB::table('backupadv_logs')->insert([
                        'business_id' => $business_id,
                        'user_id' => auth()->id(),
                        'filename' => $zipFileName,
                        'status' => 'failed',
                        'size_in_mb' => 0,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                    return redirect()->back()->with('error', 'Could not create zip file.');
                }

                $zip->addFile($sqlFilePath, basename($sqlFilePath));
                $zip->close();

                // Log successful backup
                DB::table('backupadv_logs')->insert([
                    'business_id' => $business_id,
                    'user_id' => auth()->id(),
                    'filename' => $zipFileName,
                    'status' => 'success',
                    'size_in_mb' => round(filesize($zipFilePath) / (1024 * 1024), 2),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                if (file_exists($sqlFilePath)) {
                    unlink($sqlFilePath);
                }

                return response()->download($zipFilePath)->deleteFileAfterSend(true);
            } catch (\Exception $e) {
                // Log error
                \Log::error('Backup creation failed: ' . $e->getMessage());
                DB::table('backupadv_logs')->insert([
                    'business_id' => $business_id,
                    'user_id' => auth()->id(),
                    'filename' => $zipFileName,
                    'status' => 'failed',
                    'size_in_mb' => 0,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                return redirect()->back()->with('error', 'Backup creation failed: ' . $e->getMessage());
            }
        } else {
            // Log failed backup
            DB::table('backupadv_logs')->insert([
                'business_id' => $business_id,
                'user_id' => auth()->id(),
                'filename' => null,
                'status' => 'failed',
                'size_in_mb' => 0,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            return redirect()->back()->with('error', 'No data found to backup.');
        }
    }

    /**
     * Store the uploaded backup file and restore data.
     * @param Request $request
     * @return Response
     */
    public function storeBackup(Request $request)
    {
        // Validate the uploaded file
        $request->validate([
            'backup_file' => 'required|file|mimes:zip|max:10240',
        ]);

        $file = $request->file('backup_file');

        $zip = new ZipArchive();
        $zipFilePath = $file->getRealPath();
        $filename = $file->getClientOriginalName();
        $sizeInMb = round($file->getSize() / (1024 * 1024), 2);

        if ($zip->open($zipFilePath) === TRUE) {
            $extractPath = storage_path('app/backups/extracted');
            File::ensureDirectoryExists($extractPath);
            $zip->extractTo($extractPath);
            $zip->close();

            $sqlFiles = glob("$extractPath/*.sql");

            DB::beginTransaction();
            try {
                DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                DB::statement('SET UNIQUE_CHECKS=0;');
                DB::statement('SET SQL_MODE=""');

                foreach ($sqlFiles as $sqlFile) {
                    $sql_content = file_get_contents($sqlFile);
                    $statements = array_filter(array_map('trim', explode(';', $sql_content)));

                    foreach ($statements as $statement) {
                        if (!empty($statement)) {
                            if (stripos($statement, 'INSERT INTO') === 0) {
                                $statement = str_replace('INSERT INTO', 'REPLACE INTO', $statement);
                            }
                            try {
                                DB::statement($statement);
                            } catch (\Exception $e) {
                                \Log::warning('Error executing statement: ' . $e->getMessage());
                            }
                        }
                    }
                }

                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
                DB::statement('SET UNIQUE_CHECKS=1;');

                DB::commit();

                // Log successful restore
                DB::table('backupadv_logs')->insert([
                    'business_id' => auth()->user()->business_id,
                    'user_id' => auth()->id(),
                    'filename' => $filename,
                    'status' => 'success',
                    'size_in_mb' => $sizeInMb,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

            } catch (\Exception $e) {
                DB::rollback();
                // Log failed restore
                DB::table('backupadv_logs')->insert([
                    'business_id' => auth()->user()->business_id,
                    'user_id' => auth()->id(),
                    'filename' => $filename,
                    'status' => 'failed',
                    'size_in_mb' => $sizeInMb,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                return redirect()->back()->with('error', 'Error restoring backup: ' . $e->getMessage());
            }

            File::deleteDirectory($extractPath);

            return redirect()->back()->with('status', 'Backup restored successfully.');
        } else {
            // Log failed restore
            DB::table('backupadv_logs')->insert([
                'business_id' => auth()->user()->business_id,
                'user_id' => auth()->id(),
                'filename' => $filename,
                'status' => 'failed',
                'size_in_mb' => $sizeInMb,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            return redirect()->back()->with('error', 'Could not open the zip file.');
        }
    }

    /**
     * Restore a backup by filename.
     *
     * @param string $filename
     * @return Response
     */
    public function restoreBackup($filename)
    {
        // تحقق من وجود النسخة الاحتياطية
        $filePath = storage_path("app/backups/$filename");

        if (!file_exists($filePath)) {
            return redirect()->back()->with('error', 'Backup file not found.');
        }

        $zip = new ZipArchive();
        $extractPath = storage_path('app/backups/extracted');

        if ($zip->open($filePath) === TRUE) {
            File::ensureDirectoryExists($extractPath);
            $zip->extractTo($extractPath);
            $zip->close();

            // استعادة البيانات من ملفات SQL
            $sqlFiles = glob("$extractPath/*.sql");

            DB::beginTransaction();
            try {
                DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                DB::statement('SET UNIQUE_CHECKS=0;');
                DB::statement('SET SQL_MODE=""');

                foreach ($sqlFiles as $sqlFile) {
                    $sqlContent = file_get_contents($sqlFile);
                    $statements = array_filter(array_map('trim', explode(';', $sqlContent)));

                    foreach ($statements as $statement) {
                        if (!empty($statement)) {
                            if (stripos($statement, 'INSERT INTO') === 0) {
                                $statement = str_replace('INSERT INTO', 'REPLACE INTO', $statement);
                            }
                            try {
                                DB::statement($statement);
                            } catch (\Exception $e) {
                                \Log::warning('Error executing statement: ' . $e->getMessage());
                            }
                        }
                    }
                }

                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
                DB::statement('SET UNIQUE_CHECKS=1;');

                DB::commit();

                // حذف الملفات المستخرجة بعد الاستعادة
                File::deleteDirectory($extractPath);

                return redirect()->back()->with('status', 'Backup restored successfully.');
            } catch (\Exception $e) {
                DB::rollback();

                // تسجيل فشل الاستعادة
                \Log::error('Error restoring backup: ' . $e->getMessage());

                return redirect()->back()->with('error', 'Error restoring backup.');
            }
        } else {
            return redirect()->back()->with('error', 'Could not open the zip file.');
        }
    }
}