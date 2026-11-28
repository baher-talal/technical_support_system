<?php
namespace App\Http\Controllers;
use App\Models\{Department, Permission, Role, Sector, User};
use App\Support\Audit;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function users(Request $r)
    {
        $q = User::with(['role', 'department'])->withCount('permissionOverrides')->orderBy('name');
        if ($s = $r->q) $q->where(fn ($w) => $w->where('name', 'like', "%$s%")->orWhere('email', 'like', "%$s%"));
        if ($r->filled('role_id')) $q->where('role_id', $r->role_id);
        return view('admin.users', ['users' => $q->paginate(25)->withQueryString(), 'roles' => Role::all()]);
    }

    public function userForm(?User $user = null)
    {
        $user ??= new User();
        return view('admin.user-form', [
            'user' => $user, 'roles' => Role::with('permissions')->get(), 'departments' => Department::orderBy('name')->get(),
            'permissions' => Permission::orderBy('id')->get(), 'checked' => $user->exists ? $user->effectivePermissions() : [],
        ]);
    }

    public function userSave(Request $r, ?User $user = null)
    {
        $d = $r->validate([
            'name' => 'required|string|max:100',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user?->id)],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:8'],
            'role_id' => 'required|exists:roles,id', 'department_id' => 'nullable|exists:departments,id',
            'phone' => 'nullable|string|max:30',
            'permissions' => 'nullable|array', 'permissions.*' => [Rule::in(array_keys(config('helpdesk.permissions')))],
        ]);
        // فقط مدير النظام يستطيع إنشاء/تعديل حسابات مدير النظام أو منح هذا الدور
        $adminRoleId = Role::where('slug', 'admin')->value('id');
        abort_if(auth()->user()->role?->slug !== 'admin' && ((int) $d['role_id'] === $adminRoleId || ($user && $user->role_id === $adminRoleId)), 403, 'هذا الإجراء لمدير النظام فقط');
        $checked = $d['permissions'] ?? [];
        unset($d['permissions']);
        $d['is_active'] = $r->boolean('is_active');
        if (blank($d['password'] ?? null)) unset($d['password']);
        $saved = $user ? tap($user)->update($d) : User::create($d);
        if ($r->boolean('permissions_present')) $this->syncOverrides($saved, $checked);
        return redirect()->route('admin.users')->with('ok', 'تم حفظ المستخدم');
    }

    /** يحفظ الفروق بين الصلاحيات المحددة وصلاحيات دور المستخدم كاستثناءات خاصة به */
    private function syncOverrides(User $u, array $checked): void
    {
        $u->load('role.permissions');
        if ($u->role?->slug === 'admin') { $u->permissionOverrides()->detach(); return; }
        $base = $u->role?->permissions->pluck('key')->all() ?? [];
        $sync = [];
        foreach (Permission::all() as $p) {
            $has = in_array($p->key, $base, true); $want = in_array($p->key, $checked, true);
            if ($has !== $want) $sync[$p->id] = ['granted' => $want];
        }
        $before = $u->permissionOverrides()->get()->mapWithKeys(fn ($p) => [$p->key => (bool) $p->pivot->granted])->all();
        $u->permissionOverrides()->sync($sync);
        $keys = Permission::pluck('key', 'id');
        $after = collect($sync)->mapWithKeys(fn ($v, $id) => [$keys[$id] => $v['granted']])->all();
        if ($before != $after) Audit::log('permissions.updated', $u, ['before' => $before, 'after' => $after]);
    }

    public function userDestroy(User $user)
    {
        abort_if($user->id === auth()->id(), 422, 'لا يمكنك حذف حسابك');
        abort_if($user->role?->slug === 'admin' && auth()->user()->role?->slug !== 'admin', 403, 'هذا الإجراء لمدير النظام فقط');
        $user->update(['is_active' => false]); // تعطيل بدلا من الحذف للحفاظ على سجل التيكيتات
        return back()->with('ok', 'تم تعطيل المستخدم');
    }

    public function departments()
    {
        $departments = Department::with('sector')->withCount(['users', 'devices as devices_count' => fn ($q) => $q->where('type', 'company')])
            ->orderBy('building')->orderBy('name')->get();
        return view('admin.departments', compact('departments'));
    }

    public function departmentSave(Request $r, ?Department $department = null)
    {
        $d = $r->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('departments')->ignore($department?->id)],
            'building' => 'required|in:A,B,C', 'floor' => 'required|string|max:30', 'room_number' => 'required|string|max:30',
        ]);
        $department ? $department->update($d) : Department::create($d);
        return back()->with('ok', 'تم حفظ الإدارة');
    }

    public function departmentDestroy(Department $department)
    {
        if ($department->users()->exists() || $department->devices()->exists() || $department->tickets()->exists()) {
            return back()->withErrors(['department' => 'لا يمكن حذف إدارة مرتبطة بمستخدمين أو أجهزة أو تيكيتات']);
        }
        $department->delete();
        return back()->with('ok', 'تم الحذف');
    }

    public function sectors()
    {
        return view('admin.sectors', [
            'sectors' => Sector::with(['head', 'departments'])->orderBy('name')->get(),
            'departments' => Department::with('sector')->orderBy('name')->get(),
            'users' => User::where('is_active', true)->with('role')->orderBy('name')->get(['id', 'name', 'role_id']),
        ]);
    }

    public function sectorSave(Request $r, ?Sector $sector = null)
    {
        $d = $r->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('sectors')->ignore($sector?->id)],
            'head_id' => ['nullable', Rule::exists('users', 'id')->where('is_active', true)],
            'department_ids' => 'nullable|array', 'department_ids.*' => 'exists:departments,id',
        ]);
        $ids = $d['department_ids'] ?? [];
        $data = ['name' => $d['name'], 'head_id' => $d['head_id'] ?? null];
        $sector = $sector ? tap($sector)->update($data) : Sector::create($data);
        Department::where('sector_id', $sector->id)->whereNotIn('id', $ids)->update(['sector_id' => null]);
        Department::whereIn('id', $ids)->update(['sector_id' => $sector->id]);
        Audit::log('sectors.departments', $sector, ['departments' => Department::whereIn('id', $ids)->pluck('name')->all()]);
        return back()->with('ok', 'تم حفظ القطاع');
    }

    public function sectorDestroy(Sector $sector)
    {
        $sector->delete(); // الإدارات تبقى وتصبح بلا قطاع
        return back()->with('ok', 'تم حذف القطاع');
    }

    public function roles() { return view('admin.roles', ['roles' => Role::with('permissions')->get(), 'permissions' => Permission::all()]); }

    public function roleUpdate(Request $r, Role $role)
    {
        abort_if($role->slug === 'admin', 422, 'لا يمكن تعديل صلاحيات مدير النظام');
        $before = $role->permissions()->pluck('key')->sort()->values()->all();
        $role->permissions()->sync(Permission::whereIn('key', $r->input('permissions', []))->pluck('id'));
        $after = $role->permissions()->pluck('key')->sort()->values()->all();
        if ($before !== $after) Audit::log('permissions.updated', $role, ['added' => array_values(array_diff($after, $before)), 'removed' => array_values(array_diff($before, $after))]);
        return back()->with('ok', 'تم تحديث صلاحيات ' . $role->name);
    }
}
