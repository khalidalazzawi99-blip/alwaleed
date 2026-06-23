<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;

class ActivityLogController extends Controller
{
    public function index()
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403);
        }

        return view('activity_logs.index', [
            'logs' => ActivityLog::latest()->paginate(50),
        ]);
    }
}