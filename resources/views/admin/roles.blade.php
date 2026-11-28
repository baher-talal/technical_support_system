@extends('layouts.app')
@section('content')
<h4 class="fw-bold mb-3">الأدوار والصلاحيات</h4>
<div class="row g-3">
@foreach($roles as $role) @php $has = $role->permissions->pluck('key')->all(); @endphp
<div class="col-lg-6"><div class="card h-100"><div class="card-header fw-bold">{{ $role->name }}</div><div class="card-body">
<form method="post" action="{{ route('admin.roles.update',$role) }}">@csrf @method('PUT')
@foreach($permissions as $p)<div class="form-check"><input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $p->key }}" id="p{{ $role->id }}{{ $p->id }}" @checked($role->slug==='admin' || in_array($p->key,$has)) @disabled($role->slug==='admin')><label class="form-check-label" for="p{{ $role->id }}{{ $p->id }}">{{ $p->label }}</label></div>@endforeach
@if($role->slug!=='admin')<button class="btn btn-primary btn-sm mt-3">حفظ</button>@else<div class="small text-body-secondary mt-2">مدير النظام يملك كل الصلاحيات</div>@endif
</form></div></div></div>
@endforeach
</div>
@endsection
