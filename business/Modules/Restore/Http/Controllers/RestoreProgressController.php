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
use Illuminate\Support\Facades\Cache;

class RestoreProgressController extends Controller
{
    public function get()
    {
        $progress = Cache::get('restore_progress', []);
        return response()->json($progress);
    }
}
