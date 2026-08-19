<?php

namespace App\Http\Controllers;

use App\Models\SystemNotification;
use App\Services\SystemAlertService;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    public function index(SystemAlertService $alerts): JsonResponse
    {
        $user = auth()->user();
        $alerts->ensureFor($user);

        $notifications = SystemNotification::where('user_id', $user->id)
            ->latest()->limit(30)->get()
            ->map(fn ($notification) => [
                'id' => $notification->id,
                'kind' => $notification->kind,
                'title' => $notification->title,
                'message' => $notification->message,
                'link' => $notification->link,
                'read' => $notification->read_at !== null,
                'time' => $notification->created_at->diffForHumans(),
                'created_at' => $notification->created_at->toIso8601String(),
            ]);

        return response()->json([
            'unread' => SystemNotification::where('user_id', $user->id)->whereNull('read_at')->count(),
            'notifications' => $notifications,
        ]);
    }

    public function read(SystemNotification $notification): JsonResponse
    {
        abort_unless($notification->user_id === auth()->id(), 403);
        $notification->update(['read_at' => now()]);

        return response()->json(['ok' => true]);
    }

    public function readAll(): JsonResponse
    {
        SystemNotification::where('user_id', auth()->id())
            ->whereNull('read_at')->update(['read_at' => now()]);

        return response()->json(['ok' => true]);
    }
}
