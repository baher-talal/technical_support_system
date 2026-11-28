@extends('layouts.app')
@section('content')
@php $acts = config('helpdesk.audit_actions'); $subs = config('helpdesk.audit_subjects'); @endphp
<h5 class="fw-bold mb-2"><i class="bi bi-clock-history text-primary"></i> سجل النشاط</h5>
<form class="row g-1 mb-2" method="get">
  <div class="col-md-2"><select name="user_id" class="form-select form-select-sm"><option value="">كل المستخدمين</option>@foreach($users as $u)<option value="{{ $u->id }}" @selected(request('user_id')==$u->id)>{{ $u->name }}</option>@endforeach</select></div>
  <div class="col-md-2"><select name="action" class="form-select form-select-sm"><option value="">كل العمليات</option>@foreach($acts as $k=>$l)<option value="{{ $k }}" @selected(request('action')==$k)>{{ $l }}</option>@endforeach</select></div>
  <div class="col-md-2"><select name="type" class="form-select form-select-sm"><option value="">كل الأنواع</option>@foreach($subs as $k=>$l)<option value="{{ $k }}" @selected(request('type')==$k)>{{ $l }}</option>@endforeach</select></div>
  <div class="col-md-2"><input name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="اسم العنصر"></div>
  <div class="col-md-1"><input type="date" name="from" value="{{ request('from') }}" class="form-control form-control-sm"></div>
  <div class="col-md-1"><input type="date" name="to" value="{{ request('to') }}" class="form-control form-control-sm"></div>
  <div class="col-md-1"><button class="btn btn-outline-primary btn-sm w-100">تصفية</button></div>
</form>
<div class="card"><div class="table-responsive"><table class="table table-sm table-hover align-top mb-0" style="font-size:.82rem">
<thead><tr class="text-body-secondary"><th>الوقت</th><th>المستخدم</th><th>العملية</th><th>العنصر</th><th>التفاصيل</th><th>IP</th></tr></thead><tbody>
@forelse($logs as $l)
<tr><td class="text-nowrap">{{ $l->created_at->format('Y-m-d H:i') }}</td><td>{{ $l->user?->name ?? '—' }}</td>
<td><span class="badge text-bg-{{ in_array($l->action,['deleted','login_failed','backup.deleted']) ? 'danger' : (in_array($l->action,['created','backup.created']) ? 'success' : 'secondary') }}">{{ $acts[$l->action] ?? $l->action }}</span></td>
<td>@if($l->subject_type){{ $subs[$l->subject_type] ?? $l->subject_type }}: <b>{{ $l->subject_label }}</b>@else—@endif</td>
<td style="max-width:420px;word-break:break-word">{{ $l->summary }}</td><td dir="ltr" class="text-end">{{ $l->ip }}</td></tr>
@empty<tr><td colspan="6" class="text-center text-body-secondary py-4">لا توجد سجلات</td></tr>@endforelse
</tbody></table></div><div class="card-footer py-2">{{ $logs->links() }}</div></div>
@endsection
