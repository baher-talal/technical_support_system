@extends('layouts.app')
@section('content')
@php $rp = $roles->mapWithKeys(fn ($r) => [$r->id => $r->permissions->pluck('key')->values()])->all();
     $adminId = $roles->firstWhere('slug', 'admin')?->id;
     $now = old('permissions_present') ? (array) old('permissions', []) : $checked; @endphp
<h4 class="fw-bold mb-3">{{ $user->exists ? 'تعديل مستخدم' : 'مستخدم جديد' }}</h4>
<form method="post" action="{{ $user->exists ? route('admin.users.update',$user) : route('admin.users.store') }}">@csrf @if($user->exists) @method('PUT') @endif
<input type="hidden" name="permissions_present" value="1">
<div class="row g-3">
 <div class="col-lg-6"><div class="card h-100"><div class="card-header">بيانات المستخدم</div><div class="card-body"><div class="row g-3">
  <div class="col-md-6"><label class="form-label">الاسم</label><input name="name" class="form-control" value="{{ old('name',$user->name) }}" required></div>
  <div class="col-md-6"><label class="form-label">البريد</label><input type="email" name="email" class="form-control" value="{{ old('email',$user->email) }}" required></div>
  <div class="col-md-6"><label class="form-label">كلمة المرور {{ $user->exists ? '(اتركها فارغة بدون تغيير)' : '' }}</label><input type="password" name="password" class="form-control" {{ $user->exists ? '' : 'required' }}></div>
  <div class="col-md-6"><label class="form-label">الهاتف (واتساب)</label><input name="phone" class="form-control" value="{{ old('phone',$user->phone) }}"></div>
  <div class="col-md-6"><label class="form-label">الدور</label><select name="role_id" id="role" class="form-select" required><option value="">— اختر الدور —</option>@foreach($roles as $r)<option value="{{ $r->id }}" @selected(old('role_id',$user->role_id)==$r->id)>{{ $r->name }}</option>@endforeach</select></div>
  <div class="col-md-6"><label class="form-label">الإدارة</label><select name="department_id" class="form-select"><option value="">—</option>@foreach($departments as $d)<option value="{{ $d->id }}" @selected(old('department_id',$user->department_id)==$d->id)>{{ $d->name }}</option>@endforeach</select></div>
  <div class="col-12 form-check ms-3"><input type="checkbox" class="form-check-input" name="is_active" value="1" id="ia" @checked(old('is_active',$user->exists ? $user->is_active : true))><label class="form-check-label" for="ia">الحساب نشط</label></div>
 </div></div></div></div>
 <div class="col-lg-6"><div class="card h-100"><div class="card-header d-flex justify-content-between align-items-center"><span>صلاحيات هذا المستخدم</span><button type="button" class="btn btn-sm btn-outline-secondary" id="resetPerms">إعادة لصلاحيات الدور</button></div>
  <div class="card-body"><div class="small text-body-secondary mb-2">تتحدد تلقائيا من الدور، وعدّل ما تريد لهذا المستخدم فقط (إضافة أو سحب). يظهر عليه «صلاحيات مخصصة» في القائمة.</div>
  @foreach($permissions as $p)<div class="form-check"><input class="form-check-input perm" type="checkbox" name="permissions[]" value="{{ $p->key }}" id="pm{{ $p->id }}" @checked(in_array($p->key,$now))><label class="form-check-label" for="pm{{ $p->id }}">{{ $p->label }}</label></div>@endforeach
  </div></div></div>
</div>
<button class="btn btn-primary mt-3">حفظ</button> <a class="btn btn-outline-secondary mt-3" href="{{ route('admin.users') }}">إلغاء</a>
</form>
<script>
const RP={!! json_encode($rp) !!},ADMIN="{{ $adminId }}",role=document.getElementById('role'),boxes=[...document.querySelectorAll('.perm')];
function apply(){const all=role.value===ADMIN,set=RP[role.value]||[];boxes.forEach(b=>{b.checked=all||set.includes(b.value);b.disabled=all;});}
function lock(){boxes.forEach(b=>b.disabled=role.value===ADMIN);}
role.onchange=apply;document.getElementById('resetPerms').onclick=apply;lock();
</script>
@endsection
