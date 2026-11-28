@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between mb-3"><h4 class="fw-bold">المستخدمون</h4><a href="{{ route('admin.users.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> مستخدم جديد</a></div>
<form class="row g-2 mb-3" method="get">
  <div class="col-md-5"><input class="form-control" name="q" value="{{ request('q') }}" placeholder="بحث بالاسم أو البريد"></div>
  <div class="col-md-4"><select name="role_id" class="form-select"><option value="">كل الأدوار</option>@foreach($roles as $r)<option value="{{ $r->id }}" @selected(request('role_id')==$r->id)>{{ $r->name }}</option>@endforeach</select></div>
  <div class="col-md-3"><button class="btn btn-outline-primary w-100">بحث</button></div>
</form>
<div class="card"><div class="table-responsive"><table class="table table-hover align-middle mb-0">
<thead><tr><th>الاسم</th><th>البريد</th><th>الدور</th><th>الإدارة</th><th>الحالة</th><th></th></tr></thead><tbody>
@foreach($users as $x)<tr><td>{{ $x->name }}</td><td>{{ $x->email }}</td><td>{{ $x->role?->name }} @if($x->permission_overrides_count)<span class="badge text-bg-info">صلاحيات مخصصة</span>@endif</td><td>{{ $x->department?->name }}</td>
<td><span class="badge text-bg-{{ $x->is_active ? 'success':'secondary' }}">{{ $x->is_active ? 'نشط':'معطل' }}</span></td>
<td class="text-nowrap"><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.users.edit',$x) }}"><i class="bi bi-pencil"></i></a>
@if($x->is_active && $x->id!==auth()->id())<form class="d-inline" method="post" action="{{ route('admin.users.destroy',$x) }}" onsubmit="return confirm('تعطيل المستخدم؟')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="bi bi-person-slash"></i></button></form>@endif</td></tr>@endforeach
</tbody></table></div><div class="card-footer">{{ $users->links() }}</div></div>
@endsection
