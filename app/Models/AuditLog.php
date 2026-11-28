<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    public const UPDATED_AT = null;
    protected $guarded = [];
    protected $casts = ['changes' => 'array'];

    public function user() { return $this->belongsTo(User::class); }

    /** ملخص نصي للتغييرات للعرض */
    public function getSummaryAttribute(): string
    {
        $parts = [];
        foreach ((array) $this->changes as $k => $v) {
            if (is_array($v) && count($v) === 2 && array_is_list($v) && !is_array($v[0]) && !is_array($v[1])) {
                $parts[] = "$k: " . ($v[0] ?? '—') . ' ← ' . ($v[1] ?? '—');
            } else {
                $parts[] = "$k: " . (is_array($v) ? json_encode($v, JSON_UNESCAPED_UNICODE) : (string) $v);
            }
        }
        return implode(' | ', $parts);
    }
}
