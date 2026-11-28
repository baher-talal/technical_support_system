<?php
namespace App\Http\Controllers;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $r)
    {
        $u = $r->user();
        $r->validate(['from' => 'nullable|date', 'to' => 'nullable|date']);
        $from = $r->input('from', now()->subDays(30)->toDateString());
        $to = $r->input('to', now()->toDateString());
        $base = fn () => Ticket::visibleTo($u)->whereBetween('tickets.created_at', [$from . ' 00:00:00', $to . ' 23:59:59']);

        // الموعد المستهدف بالساعات حسب الأولوية (من الإعدادات الثابتة)
        $slaCase = 'case tickets.priority ' . collect(config('helpdesk.sla_hours'))->map(fn ($h, $p) => "when '$p' then " . (int) $h)->implode(' ') . ' else 24 end';
        $mins = 'timestampdiff(minute, tickets.created_at, tickets.resolved_at)';
        $resolved = $base()->whereNotNull('tickets.resolved_at')->count();
        $onTime = $base()->whereNotNull('tickets.resolved_at')->whereRaw("$mins <= ($slaCase) * 60")->count();

        return view('admin.reports', [
            'from' => $from, 'to' => $to,
            'total' => $base()->count(),
            'sla' => [
                'resolved' => $resolved,
                'rate' => $resolved ? round($onTime * 100 / $resolved) : null,
                'overdue_open' => $base()->overdue()->count(),
                'avg_hours' => round(($base()->whereNotNull('tickets.resolved_at')->avg(DB::raw($mins)) ?? 0) / 60, 1),
                'rating' => round($base()->whereNotNull('tickets.rating')->avg('tickets.rating') ?? 0, 1),
            ],
            'byStatus' => $base()->selectRaw('tickets.status, count(*) c')->groupBy('tickets.status')->pluck('c', 'status'),
            'byCategory' => $base()->selectRaw('tickets.category, count(*) c')->groupBy('tickets.category')->pluck('c', 'category'),
            'bySector' => $base()->join('departments', 'departments.id', '=', 'tickets.department_id')->join('sectors', 'sectors.id', '=', 'departments.sector_id')
                ->selectRaw('sectors.name n, count(*) c')->groupBy('sectors.name')->orderByDesc('c')->get(),
            'byDept' => $base()->join('departments', 'departments.id', '=', 'tickets.department_id')
                ->selectRaw('departments.name n, count(*) c')->groupBy('departments.name')->orderByDesc('c')->get(),
            'byTech' => $base()->whereNotNull('tickets.technician_id')->join('users', 'users.id', '=', 'tickets.technician_id')
                ->selectRaw("users.name n, count(*) c, sum(tickets.status='resolved') done, avg(case when tickets.resolved_at is not null then $mins end) mins, avg(tickets.rating) rating")
                ->groupBy('users.name')->get(),
        ]);
    }
}
