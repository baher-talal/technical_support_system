@extends('layouts.app')
@section('content')
<h4 class="fw-bold mb-3"><i class="bi bi-person-circle"></i> حسابي</h4>
<div class="row g-3">
 <div class="col-lg-4"><div class="card h-100"><div class="card-header py-2">بياناتي</div><div class="card-body small">
  <div class="mb-1">الاسم: <b>{{ $user->name }}</b></div><div class="mb-1">البريد: <b>{{ $user->email }}</b></div>
  <div class="mb-1">الدور: <b>{{ $user->role?->name }}</b></div><div class="mb-1">الإدارة: <b>{{ $user->department?->name ?: '—' }}</b></div>
  <div class="text-body-secondary mt-3">لتعديل الاسم أو الدور أو الإدارة تواصل مع مدير النظام.</div></div></div></div>
 <div class="col-lg-8"><div class="card"><div class="card-header py-2">تعديل بياناتي</div><div class="card-body">
  <form method="post" action="{{ route('profile.update') }}">@csrf @method('PUT')
   <div class="row g-2">
    <div class="col-md-6"><label class="form-label small mb-1">رقم الهاتف (واتساب)</label><input name="phone" class="form-control form-control-sm" value="{{ old('phone',$user->phone) }}"></div>
    <div class="col-12"><hr class="my-2"><div class="small fw-bold">تغيير كلمة المرور (اختياري)</div></div>
    <div class="col-md-4"><label class="form-label small mb-1">كلمة المرور الحالية</label><input type="password" name="current_password" class="form-control form-control-sm" autocomplete="current-password"></div>
    <div class="col-md-4"><label class="form-label small mb-1">الجديدة (8 أحرف على الأقل)</label><input type="password" name="password" class="form-control form-control-sm" autocomplete="new-password"></div>
    <div class="col-md-4"><label class="form-label small mb-1">تأكيد الجديدة</label><input type="password" name="password_confirmation" class="form-control form-control-sm" autocomplete="new-password"></div>
   </div>
   <button class="btn btn-primary btn-sm mt-3">حفظ</button>
  </form></div></div></div>
</div>
@endsection
