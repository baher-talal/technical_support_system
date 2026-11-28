@extends('layouts.app')
@section('content')
@php
  $loc = $device->department?->building ? $device->department->building.' / '.$device->department->floor.' / '.$device->department->room_number : '—';
  $maxM = max(1, max($stats['months']));
  $fmt = fn ($v) => $v ?: '—';
@endphp
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
  <h4 class="fw-bold mb-0"><i class="bi bi-pc-display text-primary"></i> سجل صيانة الجهاز: {{ $device->name }}
    @if($stats['frequent'])<span class="badge text-bg-danger fs-6"><i class="bi bi-exclamation-triangle"></i> كثير الأعطال</span>@endif</h4>
  <div class="d-flex gap-1 flex-wrap">
    @if($canCreate)<a class="btn btn-primary btn-sm" href="{{ route('tickets.create', ['device' => $device->id]) }}"><i class="bi bi-plus-lg"></i> تيكيت جديد لهذا الجهاز</a>@endif
    <a class="btn btn-outline-secondary btn-sm" target="_blank" href="{{ route('devices.labels', ['type' => 'company', 'id' => $device->id]) }}"><i class="bi bi-qr-code"></i> ملصق QR</a>
    @if($canManage)<a class="btn btn-outline-primary btn-sm" href="{{ route('devices.edit', [$type, $device]) }}"><i class="bi bi-pencil"></i> تعديل</a>@endif
    <a class="btn btn-outline-secondary btn-sm" href="{{ route('devices.index', $type) }}">رجوع للأجهزة</a>
  </div>
</div>

@if($stats['frequent'])
<div class="alert alert-danger py-2 small"><i class="bi bi-exclamation-triangle-fill"></i>
  سُجّلت على هذا الجهاز <b>{{ $stats['recent_repairs'] }}</b> تيكيتات عطل خلال آخر <b>{{ $stats['days'] }}</b> يوما (حد التنبيه {{ config('helpdesk.device_alert.repairs') }}). راجع الأعطال أدناه وقرّر الإصلاح الجذري أو الاستبدال.</div>
@endif

<div class="row g-2 mb-3">
  @foreach([
    ['إجمالي التيكيتات', $stats['total'], 'secondary'], ['تيكيتات عطل', $stats['repairs'], 'danger'],
    ['أعطال آخر '.$stats['days'].' يوم', $stats['recent_repairs'], $stats['frequent'] ? 'danger' : 'warning'], ['طلبات مستلزمات', $stats['supplies_requests'], 'info'],
    ['مفتوحة الآن', $stats['open'], 'primary'], ['متأخرة عن الـ SLA', $stats['overdue'], $stats['overdue'] ? 'danger' : 'secondary'],
    ['متوسط زمن الحل (ساعة)', $stats['avg_hours'] ?? '—', 'success'], ['آخر تيكيت', $stats['last_at']?->format('Y-m-d') ?? '—', 'secondary'],
  ] as [$l, $v, $c])
  <div class="col-6 col-md-3"><div class="card h-100 border-{{ $c }}-subtle"><div class="card-body py-2 text-center">
    <div class="small text-body-secondary">{{ $l }}</div><div class="fs-4 fw-bold text-{{ $c }}">{{ $v }}</div></div></div></div>
  @endforeach
</div>

<div class="row g-3 mb-3">
  <div class="col-lg-5"><div class="card h-100"><div class="card-header">بيانات الجهاز</div><div class="card-body small"><div class="row g-2">
    @foreach([
      'الموظف' => $device->employee_name, 'الإدارة' => $device->department?->name, 'مبنى / دور / غرفة' => $loc,
      'السيريال' => $device->serial, 'IP' => $device->ip_address, 'MAC' => $device->mac_address,
      'المعالج' => $device->cpu, 'الذاكرة' => $device->ram, 'التخزين' => $device->storage, 'نظام التشغيل' => $device->os,
    ] as $l => $v)
      <div class="col-6"><span class="text-body-secondary d-block">{{ $l }}</span>
        @if($l === 'الموظف' && $device->user_id && $v)<a href="{{ route('employees.show', $device->user_id) }}"><b>{{ $v }}</b></a>
        @else<b @if(in_array($l, ['IP','MAC','السيريال'])) dir="ltr" @endif>{{ $fmt($v) }}</b>@endif</div>
    @endforeach
    @if($device->notes)<div class="col-12"><span class="text-body-secondary d-block">ملاحظات</span>{{ $device->notes }}</div>@endif
  </div></div></div></div>

  <div class="col-lg-4"><div class="card h-100"><div class="card-header">الأعطال خلال آخر 12 شهرا</div><div class="card-body">
    <div class="d-flex align-items-end gap-1" style="height:110px">
      @foreach($stats['months'] as $m => $n)
        <div class="flex-fill text-center" title="{{ $m }}: {{ $n }}">
          <div class="small text-body-secondary" style="font-size:.65rem">{{ $n ?: '' }}</div>
          <div class="rounded-top {{ $n ? 'bg-danger' : 'bg-body-secondary' }}" style="height:{{ $n ? max(6, round($n / $maxM * 80)) : 3 }}px"></div>
        </div>
      @endforeach
    </div>
    <div class="d-flex gap-1 mt-1">@foreach($stats['months'] as $m => $n)<div class="flex-fill text-center text-body-secondary" style="font-size:.6rem">{{ substr($m, 5) }}</div>@endforeach</div>
  </div></div></div>

  <div class="col-lg-3"><div class="card h-100"><div class="card-header">المستلزمات المصروفة</div>
    <ul class="list-group list-group-flush small">
      @forelse($stats['supplies'] as $s)<li class="list-group-item d-flex justify-content-between"><span>{{ $s->item }}</span><b>× {{ $s->qty }}</b></li>
      @empty<li class="list-group-item text-body-secondary">لا يوجد صرف</li>@endforelse
    </ul></div></div>
</div>

<div class="card"><div class="card-header fw-bold">كل التيكيتات على هذا الجهاز ({{ $tickets->total() }})</div>
@include('tickets._table')<div class="card-footer">{{ $tickets->links() }}</div></div>
@endsection
