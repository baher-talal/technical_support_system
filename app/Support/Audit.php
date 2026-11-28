<?php
namespace App\Support;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

class Audit
{
    /** عند true لا تُسجَّل أحداث الموديلات (للعمليات الجماعية كالاستيراد، ويُسجَّل ملخص بدلا منها) */
    public static bool $muted = false;

    private const IGNORE = ['updated_at', 'created_at', 'remember_token'];
    private const MASK = ['password'];

    public static function log(string $action, ?Model $subject = null, array $data = []): void
    {
        try {
            AuditLog::create([
                'user_id' => auth()->id(), 'action' => $action,
                'subject_type' => $subject ? class_basename($subject) : null,
                'subject_id' => $subject?->getKey(),
                'subject_label' => $subject ? mb_substr(static::label($subject), 0, 250) : null,
                'changes' => $data ?: null,
                'ip' => request()->ip(),
            ]);
        } catch (\Throwable $e) {
            report($e); // السجل لا يعطّل العملية الأصلية
        }
    }

    public static function fromModel(string $event, Model $m): void
    {
        if (static::$muted) return;
        $cut = fn ($v) => is_string($v) ? mb_substr($v, 0, 200) : $v;
        $data = [];
        if ($event === 'updated') {
            foreach ($m->getChanges() as $k => $new) {
                if (in_array($k, self::IGNORE, true)) continue;
                $data[$k] = in_array($k, self::MASK, true) ? ['***', '***'] : [$cut($m->getOriginal($k)), $cut($new)];
            }
            if (!$data) return;
        } elseif ($event === 'created') {
            foreach ($m->getAttributes() as $k => $v) {
                if ($k === $m->getKeyName() || in_array($k, array_merge(self::IGNORE, self::MASK), true) || $v === null || $v === '') continue;
                $data[$k] = $cut($v);
            }
        }
        static::log($event, $m, $data);
    }

    private static function label(Model $m): string
    {
        return (string) ($m->name ?? $m->code ?? ('#' . $m->getKey()));
    }
}
