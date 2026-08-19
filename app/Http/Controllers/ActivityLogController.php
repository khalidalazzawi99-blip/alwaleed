<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = ActivityLog::query()
            ->when($user->role !== 'super_admin', fn ($query) => $query->where('company_id', $user->company_id))
            ->when($request->filled('event'), fn ($query) => $query->where('event', $request->event))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = '%'.$request->search.'%';
                $query->where(fn ($query) => $query
                    ->where('user_name', 'like', $search)
                    ->orWhere('auditable_type', 'like', $search)
                    ->orWhere('details', 'like', $search));
            });

        $logs = $query->latest()->paginate(100)->withQueryString();

        return view('activity_logs.index', [
            'logs' => $logs,
        ]);
    }
}
