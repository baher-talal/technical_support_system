<?php
namespace App\Http\Controllers;
use App\Models\AppNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function poll(Request $r)
    {
        $base = fn () => AppNotification::where('user_id', $r->user()->id)->whereNull('read_at');
        $items = $base()->latest()->limit(15)->get();
        $payload = $items->map(fn ($n) => [
            'id' => $n->id, 'message' => $n->message, 'new' => is_null($n->notified_at),
            'url' => $n->ticket_id ? route('tickets.show', $n->ticket_id) : null, 'at' => $n->created_at->diffForHumans(),
        ])->values();
        $newIds = $items->filter(fn ($n) => is_null($n->notified_at))->pluck('id');
        if ($newIds->isNotEmpty()) AppNotification::whereIn('id', $newIds)->update(['notified_at' => now()]);
        return response()->json(['count' => $base()->count(), 'items' => $payload]);
    }

    public function read(Request $r, AppNotification $notification)
    {
        abort_unless($notification->user_id === $r->user()->id, 403);
        $notification->update(['read_at' => now()]);
        return response()->json(['ok' => true]);
    }

    public function readAll(Request $r)
    {
        AppNotification::where('user_id', $r->user()->id)->whereNull('read_at')->update(['read_at' => now()]);
        return response()->json(['ok' => true]);
    }
}
