<?php
namespace App\Models;
use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use Auditable;

    public const OPEN = ['pending_approval', 'assigned', 'in_progress'];

    protected $guarded = [];
    protected $casts = ['resolved_at' => 'datetime'];

    /** التيكيت له سجل متابعة خاص، فيُسجَّل في سجل النشاط حذفه فقط */
    public function auditEvents(): array { return ['deleted']; }

    public function user() { return $this->belongsTo(User::class); }
    public function department() { return $this->belongsTo(Department::class); }
    public function device() { return $this->belongsTo(Device::class); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
    public function approver() { return $this->belongsTo(User::class, 'approver_id'); }
    public function technician() { return $this->belongsTo(User::class, 'technician_id'); }
    public function logs() { return $this->hasMany(TicketLog::class)->latest(); }
    public function supplies() { return $this->hasMany(TicketSupply::class); }

    public function log(string $action, ?string $note = null, bool $comment = false): void
    {
        $this->logs()->create(['user_id' => auth()->id(), 'action' => $action, 'note' => $note, 'is_comment' => $comment]);
    }

    /** موعد الحل المستهدف حسب الأولوية */
    public function getDueAtAttribute()
    {
        return $this->created_at?->copy()->addHours(config('helpdesk.sla_hours')[$this->priority] ?? 24);
    }

    /** متأخر: مفتوح وتجاوز الموعد، أو أُغلق بعد الموعد */
    public function getIsOverdueAttribute(): bool
    {
        $end = $this->status === 'resolved' ? $this->resolved_at : now();
        if ($this->status === 'rejected' || !$end || !$this->due_at) return false;
        return $end->gt($this->due_at);
    }

    public function scopeOverdue($q)
    {
        return $q->whereIn('tickets.status', self::OPEN)->where(function ($w) {
            foreach (config('helpdesk.sla_hours') as $p => $h) {
                $w->orWhere(fn ($x) => $x->where('tickets.priority', $p)->where('tickets.created_at', '<', now()->subHours($h)));
            }
        });
    }

    /** تصفية قائمة التيكيتات من معاملات الطلب (status, category, priority, department_id, device_id, from, to, sector_id, mine, assigned, overdue, q) */
    public function scopeApplyFilters($q, array $f, User $u)
    {
        $has = fn ($k) => isset($f[$k]) && is_scalar($f[$k]) && trim((string) $f[$k]) !== '';
        $on = fn ($k) => filter_var($f[$k] ?? false, FILTER_VALIDATE_BOOLEAN);
        foreach (['status', 'category', 'priority', 'department_id', 'device_id'] as $k) {
            if ($has($k)) $q->where("tickets.$k", $f[$k]);
        }
        if ($has('from')) $q->where('tickets.created_at', '>=', $f['from'] . ' 00:00:00');
        if ($has('to')) $q->where('tickets.created_at', '<=', $f['to'] . ' 23:59:59');
        if ($has('sector_id')) $q->whereHas('department', fn ($d) => $d->where('sector_id', $f['sector_id']));
        if ($on('mine')) $q->where('tickets.user_id', $u->id);
        if ($on('assigned')) $q->where('tickets.technician_id', $u->id);
        if ($on('overdue')) $q->overdue();
        if ($has('q')) {
            $like = '%' . addcslashes(trim((string) $f['q']), '%_\\') . '%';
            $q->where(fn ($w) => $w->where('tickets.code', 'like', $like)->orWhere('tickets.title', 'like', $like));
        }
        return $q;
    }

    public function whatsappUrl(): ?string
    {
        if (!$this->technician_id) return null;
        $this->loadMissing(['user', 'department', 'device', 'technician']);
        $cfg = config('helpdesk');
        $lines = ["*تيكيت {$this->code}*", "العنوان: {$this->title}",
            'النوع: ' . $cfg['categories'][$this->category] . ($this->item ? ' (' . $cfg['items'][$this->item] . ')' : ''),
            "مقدم الطلب: {$this->user->name}" . ($this->user->phone ? " - {$this->user->phone}" : ''),
            "الإدارة: {$this->department?->name} ({$this->department?->location})"];
        if ($this->device) $lines[] = "الجهاز: {$this->device->name}" . ($this->device->ip_address ? " - IP: {$this->device->ip_address}" : '');
        $lines[] = 'الأولوية: ' . $cfg['priorities'][$this->priority];
        $lines[] = 'الرابط: ' . route('tickets.show', $this);
        return 'https://wa.me/' . ($this->technician->whatsapp_number ?? '') . '?text=' . rawurlencode(implode("\n", $lines));
    }

    public function scopeVisibleTo($q, User $u)
    {
        if ($u->hasPermission('tickets.view_all')) return $q;
        return $q->where(function ($w) use ($u) {
            $w->where('tickets.user_id', $u->id)->orWhere('tickets.created_by', $u->id)->orWhere('tickets.technician_id', $u->id);
            if ($u->department_id && $u->hasPermission('tickets.view_department')) $w->orWhere('tickets.department_id', $u->department_id);
            // رئيس القطاع يرى تيكيتات كل إدارات قطاعه
            $w->orWhereIn('tickets.department_id', fn ($s) => $s->select('id')->from('departments')
                ->whereIn('sector_id', fn ($x) => $x->select('id')->from('sectors')->where('head_id', $u->id)));
        });
    }
}
