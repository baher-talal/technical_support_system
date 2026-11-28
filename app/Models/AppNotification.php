<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class AppNotification extends Model
{
    protected $guarded = [];

    public static function send($users, ?Ticket $ticket, string $message): void
    {
        $now = now();
        $rows = collect($users)->pluck('id')->filter()->unique()->reject(fn ($id) => $id === auth()->id())
            ->map(fn ($id) => ['user_id' => $id, 'ticket_id' => $ticket?->id, 'message' => $message, 'created_at' => $now, 'updated_at' => $now])->values()->all();
        if ($rows) static::insert($rows);
    }
}
