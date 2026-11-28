<?php
namespace App\Http\Controllers;
use App\Models\Ticket;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $u = auth()->user();
        if (!$u->hasPermission('dashboard.view')) return redirect()->route('tickets.index');
        $base = fn () => Ticket::visibleTo($u);

        $counts = $base()->selectRaw('tickets.status, count(*) c')->groupBy('tickets.status')->pluck('c', 'status');
        $kpi = [
            'open' => $base()->whereIn('tickets.status', Ticket::OPEN)->count(),
            'overdue' => $base()->overdue()->count(),
            'today' => $base()->whereDate('tickets.resolved_at', today())->count(),
            'avg_hours' => round(($base()->whereNotNull('tickets.resolved_at')->where('tickets.resolved_at', '>=', now()->subDays(30))
                ->avg(DB::raw('timestampdiff(minute, tickets.created_at, tickets.resolved_at)')) ?? 0) / 60, 1),
            'rating' => round($base()->whereNotNull('tickets.rating')->avg('tickets.rating') ?? 0, 1),
        ];

        $days = collect(range(13, 0))->map(fn ($i) => now()->subDays($i)->toDateString());
        $since = now()->subDays(13)->startOfDay();
        $created = $base()->where('tickets.created_at', '>=', $since)->selectRaw('date(tickets.created_at) d, count(*) c')->groupBy('d')->pluck('c', 'd');
        $resolved = $base()->where('tickets.resolved_at', '>=', $since)->selectRaw('date(tickets.resolved_at) d, count(*) c')->groupBy('d')->pluck('c', 'd');
        $chart = [
            'labels' => $days->map(fn ($d) => substr($d, 5))->values(),
            'created' => $days->map(fn ($d) => (int) ($created[$d] ?? 0))->values(),
            'resolved' => $days->map(fn ($d) => (int) ($resolved[$d] ?? 0))->values(),
            'status' => collect(config('helpdesk.statuses'))->map(fn ($s, $k) => ['label' => $s[0], 'value' => (int) ($counts[$k] ?? 0)])->values(),
        ];

        $todo = collect();
        if ($u->hasPermission('tickets.approve')) {
            $todo->put('بانتظار موافقتك', $base()->where('tickets.status', 'pending_approval')->count());
        }
        $todo->put('مسندة إليك', Ticket::where('technician_id', $u->id)->whereIn('status', ['assigned', 'in_progress'])->count());

        $latest = $base()->with(['user', 'department'])->latest()->limit(8)->get();
        return view('dashboard', compact('counts', 'kpi', 'chart', 'todo', 'latest'));
    }
}
