<?php
namespace App\Services;

use App\Models\{Device, Ticket, TicketSupply, User};

/** ملخص سجل صيانة جهاز: عدد الأعطال والمستلزمات ومتوسط زمن الحل، ومؤشر «كثير الأعطال» */
class DeviceMaintenance
{
    public static function isFrequent(int $repairsInWindow): bool
    {
        return $repairsInWindow >= (int) config('helpdesk.device_alert.repairs');
    }

    /** الأرقام محسوبة على التيكيتات التي يحق لـ $viewer رؤيتها فقط */
    public function summary(Device $device, User $viewer): array
    {
        $days = (int) config('helpdesk.device_alert.days');
        $all = Ticket::visibleTo($viewer)->where('tickets.device_id', $device->id)
            ->get(['tickets.id', 'tickets.category', 'tickets.status', 'tickets.priority', 'tickets.created_at', 'tickets.resolved_at']);

        $repairs = $all->where('category', 'repair');
        $recent = $repairs->filter(fn ($t) => $t->created_at->gte(now()->subDays($days)))->count();
        $resolved = $all->filter(fn ($t) => $t->status === 'resolved' && $t->resolved_at);
        $avgMin = $resolved->avg(fn ($t) => abs($t->created_at->diffInMinutes($t->resolved_at)));

        // آخر 12 شهرا (أعطال فقط)
        $months = [];
        for ($i = 11; $i >= 0; $i--) $months[now()->subMonths($i)->format('Y-m')] = 0;
        foreach ($repairs as $t) { $k = $t->created_at->format('Y-m'); if (isset($months[$k])) $months[$k]++; }

        return [
            'total' => $all->count(),
            'repairs' => $repairs->count(),
            'supplies_requests' => $all->where('category', 'supplies')->count(),
            'open' => $all->whereIn('status', Ticket::OPEN)->count(),
            'overdue' => $all->filter(fn ($t) => $t->is_overdue)->count(),
            'recent_repairs' => $recent,
            'days' => $days,
            'frequent' => self::isFrequent($recent),
            'last_at' => $all->max('created_at'),
            'avg_hours' => $avgMin === null ? null : round($avgMin / 60, 1),
            'months' => $months,
            'supplies' => $all->isEmpty() ? collect() : TicketSupply::whereIn('ticket_id', $all->pluck('id'))
                ->selectRaw('item, SUM(qty) as qty')->groupBy('item')->orderByDesc('qty')->limit(15)->get(),
        ];
    }
}
