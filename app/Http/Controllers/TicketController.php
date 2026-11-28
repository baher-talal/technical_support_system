<?php
namespace App\Http\Controllers;

use App\Http\Requests\{ApproveTicketRequest, CommentTicketRequest, IssueSuppliesRequest, RateTicketRequest, ReassignTicketRequest, RejectTicketRequest, ReopenTicketRequest, ResolveTicketRequest, StoreTicketRequest};
use App\Models\{Department, Device, Sector, Ticket, User};
use App\Services\{TicketExporter, TicketWorkflow};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * طبقة HTTP فقط: الصلاحيات في TicketPolicy، التحقق في Form Requests، والمنطق في TicketWorkflow.
 */
class TicketController extends Controller
{
    public function __construct(private TicketWorkflow $flow) {}

    private function filtered(Request $r)
    {
        $u = $r->user();
        return Ticket::visibleTo($u)->with(['user', 'department', 'technician'])->latest()->applyFilters($r->all(), $u);
    }

    /** يمنع تغيير الحالة إلا من الحالات المسموحة */
    private function requireStatus(Ticket $t, string ...$allowed): void
    {
        abort_unless(in_array($t->status, $allowed, true), 422);
    }

    // ---------- القوائم ----------
    public function index(Request $r)
    {
        return view('tickets.index', [
            'tickets' => $this->filtered($r)->paginate(20)->withQueryString(),
            'departments' => Department::orderBy('name')->get(['id', 'name']),
            'sectors' => Sector::orderBy('name')->get(['id', 'name']),
            'canExport' => $r->user()->hasPermission('reports.view'),
        ]);
    }

    public function export(Request $r, TicketExporter $exporter)
    {
        return $exporter->download($this->filtered($r));
    }

    // ---------- الإنشاء ----------
    public function create(Request $r)
    {
        $u = $r->user();
        $forOthers = $u->hasPermission('tickets.create_for_others');
        abort_unless($u->department_id || $forOthers, 403, 'حسابك غير مرتبط بإدارة، تواصل مع مدير النظام');
        // اختيار مسبق للجهاز عند فتح الصفحة من ملصق QR (?device=ID)
        $pre = $r->filled('device') ? Device::where('type', 'company')->find($r->input('device')) : null;
        $warn = null;
        if ($pre && !$forOthers && $pre->department_id !== $u->department_id) { $pre = null; $warn = 'هذا الجهاز تابع لإدارة أخرى، اختر جهازا من إدارتك.'; }
        return view('tickets.create', [
            'preDevice' => $pre, 'warn' => $warn,
            'department' => $u->department, 'forOthers' => $forOthers,
            'departments' => $forOthers ? Department::orderBy('name')->get() : collect(),
        ]);
    }

    public function departmentDevices(Department $department)
    {
        $u = auth()->user();
        $forOthers = $u->hasPermission('tickets.create_for_others');
        abort_unless($forOthers || $department->id === $u->department_id, 403);
        return response()->json([
            'location' => ['building' => $department->building, 'floor' => $department->floor, 'room' => $department->room_number],
            'devices' => Device::where('type', 'company')->where('department_id', $department->id)->orderBy('name')
                ->get(['id', 'name', 'employee_name', 'ip_address', 'mac_address', 'cpu', 'ram', 'os']),
            'users' => $forOthers ? User::where('is_active', true)->where('department_id', $department->id)->orderBy('name')->get(['id', 'name']) : [],
        ]);
    }

    public function store(StoreTicketRequest $r)
    {
        $u = $r->user();
        $requesterId = $r->validated()['requester_id'] ?? null;
        $requester = $requesterId ? User::findOrFail($requesterId) : $u;
        $t = $this->flow->create($u, $requester, $r->departmentId(), $r->safe()->except(['department_id', 'requester_id']));
        return redirect()->route('tickets.show', $t)->with('ok', 'تم إرسال الطلب لمدير عام النظم والمعلومات');
    }

    // ---------- العرض والحذف ----------
    public function show(Ticket $ticket)
    {
        Gate::authorize('view', $ticket);
        $ticket->load(['user.department', 'department', 'device', 'approver', 'technician', 'logs.user', 'supplies.issuer', 'creator']);
        return view('tickets.show', ['ticket' => $ticket, 'techs' => $this->flow->assignees(auth()->user())]);
    }

    public function destroy(Ticket $ticket)
    {
        $code = $ticket->code;
        $ticket->delete();
        return redirect()->route('tickets.index')->with('ok', "تم حذف التيكيت $code");
    }

    public function comment(CommentTicketRequest $r, Ticket $ticket)
    {
        $this->flow->comment($ticket, $r->user(), $r->validated()['body']);
        return back()->with('ok', 'تمت إضافة التعليق');
    }

    // ---------- الموافقة والإسناد ----------
    public function approve(ApproveTicketRequest $r, Ticket $ticket)
    {
        $this->requireStatus($ticket, 'pending_approval');
        $d = $r->validated();
        $this->flow->approve($ticket, $r->user(), (int) $d['technician_id'], $d['note'] ?? null);
        return back()->with('ok', 'تمت الموافقة وإسناد التيكيت');
    }

    public function reject(RejectTicketRequest $r, Ticket $ticket)
    {
        $this->requireStatus($ticket, 'pending_approval');
        $this->flow->reject($ticket, $r->user(), $r->validated()['note']);
        return back()->with('ok', 'تم رفض التيكيت');
    }

    public function reassign(ReassignTicketRequest $r, Ticket $ticket)
    {
        $this->requireStatus($ticket, 'assigned', 'in_progress');
        $d = $r->validated();
        $this->flow->reassign($ticket, $r->user(), (int) $d['technician_id'], $d['note'] ?? null);
        return back()->with('ok', 'تمت إعادة الإسناد');
    }

    // ---------- عمل التكنيكال ----------
    public function start(Request $r, Ticket $ticket)
    {
        Gate::authorize('work', $ticket);
        $this->requireStatus($ticket, 'assigned');
        $this->flow->start($ticket);
        return back()->with('ok', 'تم بدء العمل');
    }

    public function issue(IssueSuppliesRequest $r, Ticket $ticket)
    {
        $this->requireStatus($ticket, 'assigned', 'in_progress');
        $this->flow->issue($ticket, $r->user(), $r->validated());
        return back()->with('ok', 'تم تسجيل الصرف');
    }

    public function resolve(ResolveTicketRequest $r, Ticket $ticket)
    {
        $this->requireStatus($ticket, 'assigned', 'in_progress');
        $this->flow->resolve($ticket, $r->validated()['note']);
        return back()->with('ok', 'تم إغلاق التيكيت');
    }

    // ---------- بعد الحل ----------
    public function reopen(ReopenTicketRequest $r, Ticket $ticket)
    {
        $this->flow->reopen($ticket, $r->validated()['note']);
        return back()->with('ok', 'تمت إعادة فتح التيكيت');
    }

    public function rate(RateTicketRequest $r, Ticket $ticket)
    {
        $this->flow->rate($ticket, $r->validated());
        return back()->with('ok', 'شكرا لتقييمك');
    }
}
