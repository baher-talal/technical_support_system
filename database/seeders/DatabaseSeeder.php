<?php
namespace Database\Seeders;
use App\Models\{Department, Permission, Role, User};
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $new = [];
        foreach (config('helpdesk.permissions') as $k => $l) {
            $p = Permission::firstOrCreate(['key' => $k], ['label' => $l]);
            if ($p->wasRecentlyCreated) $new[] = $k;
            elseif ($p->label !== $l) $p->update(['label' => $l]);
        }
        Permission::whereNotIn('key', array_keys(config('helpdesk.permissions')))->delete(); // حذف الصلاحيات القديمة غير المستخدمة

        foreach (config('helpdesk.roles') as [$slug, $name, $perms]) {
            $role = Role::firstOrCreate(['slug' => $slug], ['name' => $name]);
            if ($perms === ['*']) { $role->permissions()->syncWithoutDetaching(Permission::pluck('id')); continue; }
            if ($role->wasRecentlyCreated) $role->permissions()->sync(Permission::whereIn('key', $perms)->pluck('id'));
            elseif ($new) $role->permissions()->syncWithoutDetaching(Permission::whereIn('key', array_intersect($perms, $new))->pluck('id')); // الصلاحيات الجديدة فقط، دون مسح تخصيصاتك
        }

        if (Department::count() === 0) {
            foreach ([['النظم والمعلومات','A','الأول','101'], ['الموارد البشرية','B','الثاني','204'], ['المالية','C','الثالث','305'], ['الشئون الإدارية','A','الثاني','210']] as [$n,$b,$f,$r])
                Department::create(['name'=>$n,'building'=>$b,'floor'=>$f,'room_number'=>$r]);
        }
        User::firstOrCreate(['email' => 'admin@admin.com'], [
            'name' => 'مدير النظام', 'password' => Hash::make('admin'),
            'role_id' => Role::where('slug', 'admin')->value('id'), 'department_id' => null, 'is_active' => true,
        ]);
        User::whereHas('role', fn ($q) => $q->where('slug', 'admin'))->update(['department_id' => null]); // مدير النظام غير مرتبط بإدارة
    }
}
