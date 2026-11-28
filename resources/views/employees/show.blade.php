@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between mb-3"><h4 class="fw-bold"><i class="bi bi-person-badge"></i> سجل الموظف: {{ $user->name }}</h4>
<a href="javascript:history.back()" class="btn btn-outline-secondary btn-sm">رجوع</a></div>
<div class="row g-3 mb-3">
 <div class="col-lg-4"><div class="card h-100"><div class="card-header">بيانات الموظف</div><div class="card-body small">
  <div>الاسم: <b>{{ $user->name }}</b></div><div>الدور: <b>{{ $user->role?->name }}</b></div>
  <div>الإدارة: <b>{{ $user->department?->name ?: '—' }}</b></div><div>الموقع: <b>{{ $user->department?->location ?? '—' }}</b></div>
  <div>الهاتف: <b>{{ $user->phone ?: '—' }}</b></div><div>البريد: <b>{{ $user->email }}</b></div></div></div></div>
 <div class="col-lg-8"><div class="card h-100"><div class="card-header">الأجهزة المسجلة عليه</div><ul class="list-group list-group-flush">
  @forelse($devices as $d)<li class="list-group-item small d-flex justify-content-between"><span>{{ $d->name }} <span class="text-body-secondary">({{ $d->type==='system' ? 'نظام الشركة' : 'أجهزة الشركة' }})</span></span><span dir="ltr">{{ $d->ip_address }} | {{ $d->mac_address }}</span></li>
  @empty<li class="list-group-item text-body-secondary">لا توجد أجهزة</li>@endforelse</ul></div></div>
</div>
<div class="card"><div class="card-header fw-bold">سجل التيكيتات السابقة ({{ $tickets->total() }})</div>
@include('tickets._table')<div class="card-footer">{{ $tickets->links() }}</div></div>
@endsection
