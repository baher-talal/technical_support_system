<?php
namespace App\Models;
use App\Models\Concerns\Auditable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable, Auditable;
    protected $guarded = [];
    protected $hidden = ['password', 'remember_token'];
    protected $casts = ['password' => 'hashed', 'is_active' => 'boolean'];
    private ?array $permCache = null;

    public function role() { return $this->belongsTo(Role::class); }
    public function department() { return $this->belongsTo(Department::class); }

    public function getWhatsappNumberAttribute(): ?string
    {
        $n = preg_replace('/\D+/', '', (string) $this->phone);
        if ($n === '') return null;
        if (str_starts_with($n, '00')) return substr($n, 2);
        if (str_starts_with($n, '0')) return config('helpdesk.country_code', '20') . substr($n, 1);
        return strlen($n) <= 10 ? config('helpdesk.country_code', '20') . $n : $n;
    }

    public function permissionOverrides() { return $this->belongsToMany(Permission::class)->withPivot('granted'); }

    public function scopeWithPermission($q, string $key)
    {
        return $q->where('is_active', true)->where(function ($w) use ($key) {
            $w->whereHas('role', fn ($r) => $r->where('slug', 'admin'))
              ->orWhere(fn ($x) => $x->whereHas('role.permissions', fn ($p) => $p->where('key', $key))
                    ->whereDoesntHave('permissionOverrides', fn ($p) => $p->where('key', $key)->where('permission_user.granted', false)))
              ->orWhereHas('permissionOverrides', fn ($p) => $p->where('key', $key)->where('permission_user.granted', true));
        });
    }

    /** صلاحيات الدور مع استثناءات المستخدم (إضافة/سحب) */
    public function effectivePermissions(): array
    {
        $base = $this->role?->permissions()->pluck('key')->all() ?? [];
        foreach ($this->permissionOverrides as $p) {
            if ($p->pivot->granted) $base[] = $p->key;
            else $base = array_diff($base, [$p->key]);
        }
        return array_values(array_unique($base));
    }

    public function hasPermission(string $key): bool
    {
        if ($this->role?->slug === 'admin') return true;
        $this->permCache ??= $this->effectivePermissions();
        return in_array($key, $this->permCache, true);
    }
}
