<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Response;

class BackupController extends Controller
{
    public function download()
    {
        $dbPath = database_path('database.sqlite');

        if (!file_exists($dbPath)) {
            return back()->with('error', 'ملف قاعدة البيانات غير موجود');
        }

        return Response::download(
            $dbPath,
            'alwaleed-backup-' . date('Y-m-d-H-i') . '.sqlite'
        );
    }
}