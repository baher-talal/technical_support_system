<?php
namespace App\Services;

use App\Models\{AppNotification, Department, Ticket, User};
use Illuminate\Support\Collection;

/**
 * دورة حياة التيكيت: تغيير الحالة + سجل المتابعة + الإشعارات.
 * الصلاحيات والتحقق من المدخلات في الـ Policy والـ Form Requests، وفحص الحالة المسموحة في الـ Controller.
 */
class TicketWorkflow
{
    /** المسؤولون المسموح إسناد التيكيت لهم: مستخدمو إدارة النظم والمعلومات عدا مدير النظام ومدير عام النظم والمعلومات */
    public function assignees(User $actor): Collection
    {
        $dept = $actor->department_id ?: User::where('is_active', true)->whereHas('role', fn ($q) => $q->where('slug', 'it_gm'))
            ->whereNotNull('department_id')->value('department_id');
        if (!$dept) return collect();
        return User::where('is_active', true)->where('department_id', $dept)
            ->whereHas('role', fn ($q) => $q->whereNotIn('slug', ['admin', 'it_gm']))->orderBy('name')->get();
    }

    public function create(User $actor, User $requester, int $deptId, array $data): Ticket
    {
        $onBehalf = $requester->id !== $actor->id;
        $t = Ticket::create($data + ['code' => 'TMP', 'user_id' => $requester->id, 'created_by' => $actor->id, 'department_id' => $deptId, 'status' => 'pending_approval']);
        $t->update(['code' => 'TKT-' . date('y') . '-' . str_pad($t->id, 5, '0', STR_PAD_LEFT)]);
        $t->log(($onBehalf ? "أنشأه {$actor->name} نيابة عن {$requester->name} وأُرسل" : 'تم إنشاء التيكيت وأُرسل') . ' لمدير عام النظم والمعلومات');

        $label = $t->category === 'supplies' ? 'طلب مستلزمات' : 'تيكيت عطل';
        AppNotification::send(User::withPermission('tickets.approve')->get(), $t, "$label جديد {$t->code} من {$requester->name}" . ($onBehalf ? " (أنشأه {$actor->name})" : '') . ' بانتظار قرارك');
        if ($onBehalf) AppNotification::send([$requester], $t, "تم إنشاء {$label} {$t->code} باسمك بواسطة {$actor->name}");
        $head = Department::with('sector.head')->find($deptId)?->sector?->head;
        if ($head) AppNotification::send([$head], $t, "{$label} جديد {$t->code} في قطاعك من {$requester->name}");
        return $t;
    }

    public function approve(Ticket $t, User $approver, int $technicianId, ?string $note): void
    {
        $tech = $this->assignees($approver)->firstWhere('id', $technicianId);
        abort_unless($tech, 422, 'المسؤول المختار غير مسموح به');
        $t->update(['status' => 'assigned', 'approver_id' => $approver->id, 'approval_note' => $note, 'technician_id' => $tech->id]);
        $t->log('تمت الموافقة وإسناد التيكيت للمسؤول: ' . $tech->name, $note);
        AppNotification::send([$tech], $t, "تم إسناد التيكيت {$t->code} إليك: {$t->title}");
        AppNotification::send([$t->user], $t, "تمت الموافقة على تيكيتك {$t->code} وإسناده إلى {$tech->name}");
    }

    public function reject(Ticket $t, User $approver, string $note): void
    {
        $t->update(['status' => 'rejected', 'approver_id' => $approver->id, 'approval_note' => $note]);
        $t->log('تم رفض التيكيت', $note);
        AppNotification::send([$t->user], $t, "تم رفض تيكيتك {$t->code}");
    }

    public function reassign(Ticket $t, User $actor, int $technicianId, ?string $note): void
    {
        $tech = $this->assignees($actor)->firstWhere('id', $technicianId);
        abort_unless($tech, 422, 'المسؤول المختار غير مسموح به');
        $old = $t->technician;
        $t->update(['technician_id' => $tech->id, 'status' => 'assigned']);
        $t->log('تمت إعادة الإسناد من ' . ($old?->name ?? '—') . ' إلى ' . $tech->name, $note);
        AppNotification::send([$tech], $t, "تم إسناد التيكيت {$t->code} إليك: {$t->title}");
        AppNotification::send([$t->user], $t, "تمت إعادة إسناد تيكيتك {$t->code} إلى {$tech->name}");
    }

    public function start(Ticket $t): void
    {
        $t->update(['status' => 'in_progress']);
        $t->log('بدأ التكنيكال العمل على التيكيت');
    }

    public function issue(Ticket $t, User $issuer, array $d): void
    {
        $t->supplies()->create($d + ['issued_by' => $issuer->id]);
        $t->log("تم صرف: {$d['item']} × {$d['qty']}");
    }

    public function resolve(Ticket $t, string $note): void
    {
        $t->update(['status' => 'resolved', 'resolution_note' => $note, 'resolved_at' => now()]);
        $t->log('تم حل المشكلة / إغلاق التيكيت', $note);
        AppNotification::send([$t->user], $t, "تم حل تيكيتك {$t->code}، يمكنك تقييم الحل");
    }

    public function reopen(Ticket $t, string $note): void
    {
        $t->update([
            'status' => $t->technician_id ? 'assigned' : 'pending_approval',
            'resolved_at' => null, 'resolution_note' => null, 'rating' => null, 'rating_note' => null,
        ]);
        $t->log('تمت إعادة فتح التيكيت', $note);
        $targets = collect([$t->technician])->merge($t->technician_id ? [] : User::withPermission('tickets.approve')->get());
        AppNotification::send($targets->filter(), $t, "أُعيد فتح التيكيت {$t->code}");
    }

    public function rate(Ticket $t, array $d): void
    {
        $t->update($d);
        $t->log("تم تقييم الحل: {$d['rating']}/5", $d['rating_note'] ?? null);
        AppNotification::send([$t->technician], $t, "تقييم جديد ({$d['rating']}/5) على التيكيت {$t->code}");
    }

    public function comment(Ticket $t, User $author, string $body): void
    {
        $t->log('تعليق', $body, true);
        $targets = collect([$t->user, $t->technician, $t->approver]);
        if ($t->status === 'pending_approval') $targets = $targets->merge(User::withPermission('tickets.approve')->get());
        AppNotification::send($targets->filter(), $t, "تعليق جديد على {$t->code} من {$author->name}");
    }
}
