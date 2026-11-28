@extends('layouts.app')
@section('content')
@php
  $isSystem = $type === 'system';
  $title = ($device->exists ? 'تعديل ' : 'إضافة ') . ($isSystem ? 'جهاز نظام الشركة (ERP)' : 'جهاز للشركة');
  $usersJs = $users->map(fn ($x) => ['id' => $x->id, 'name' => $x->name, 'd' => $x->department_id])->values();
  $deptJs = $departments->mapWithKeys(fn ($d) => [$d->id => ['b' => $d->building, 'f' => $d->floor, 'r' => $d->room_number]]);
  $sel = old('user_id', $device->user_id);
@endphp
<div class="d-flex justify-content-between align-items-center mb-2">
  <h5 class="fw-bold mb-0"><i class="bi bi-{{ $isSystem ? 'hdd-network' : 'pc-display' }} text-primary"></i> {{ $title }}
    @if($isSystem)<span class="badge text-bg-info align-middle">ERP</span>@endif</h5>
  <a href="{{ route('devices.index',$type) }}" class="btn btn-outline-secondary btn-sm">رجوع</a>
</div>
<form method="post" action="{{ $device->exists ? route('devices.update',[$type,$device]) : route('devices.store',$type) }}">@csrf @if($device->exists) @method('PUT') @endif
<div class="row g-3">
 <div class="col-lg-8">
  <div class="card mb-3"><div class="card-header py-2 small"><i class="bi bi-info-circle"></i> البيانات الأساسية</div><div class="card-body p-3"><div class="row g-2">
    <div class="col-md-6"><label class="form-label small mb-1">{{ $isSystem ? 'اسم الجهاز / الخادم' : 'اسم الجهاز' }} <span class="text-danger">*</span></label>
      <input name="name" class="form-control form-control-sm @error('name') is-invalid @enderror" value="{{ old('name',$device->name) }}" required placeholder="{{ $isSystem ? 'مثال: ERP-FIN-01' : 'مثال: PC-HR-05' }}"></div>
    <div class="col-md-6"><label class="form-label small mb-1">السيريال</label>
      <input name="serial" class="form-control form-control-sm" value="{{ old('serial',$device->serial) }}" dir="ltr"></div>
    <div class="col-md-6"><label class="form-label small mb-1">الإدارة <span class="text-danger">*</span></label>
      <select name="department_id" id="dept" class="form-select form-select-sm @error('department_id') is-invalid @enderror" required><option value="">— اختر الإدارة —</option>@foreach($departments as $d)<option value="{{ $d->id }}" @selected(old('department_id',$device->department_id)==$d->id)>{{ $d->name }}</option>@endforeach</select></div>
    <div class="col-md-6"><label class="form-label small mb-1">{{ $isSystem ? 'المستخدم المسؤول عن الجهاز' : 'الموظف' }} (من مستخدمي الإدارة)</label>
      <select name="user_id" id="emp" class="form-select form-select-sm @error('user_id') is-invalid @enderror"></select>
      <input name="employee_name" class="form-control form-control-sm mt-1" placeholder="أو اكتب الاسم إن لم يكن مستخدما" value="{{ old('employee_name',$device->user_id ? '' : $device->employee_name) }}"></div>
  </div></div></div>

  <div class="card mb-3"><div class="card-header py-2 small"><i class="bi bi-ethernet"></i> الشبكة</div><div class="card-body p-3"><div class="row g-2">
    <div class="col-md-6"><label class="form-label small mb-1">IP Address</label>
      <input name="ip_address" dir="ltr" class="form-control form-control-sm @error('ip_address') is-invalid @enderror" value="{{ old('ip_address',$device->ip_address) }}" placeholder="192.168.1.10"></div>
    <div class="col-md-6"><label class="form-label small mb-1">MAC Address</label>
      <input name="mac_address" dir="ltr" maxlength="17" class="form-control form-control-sm @error('mac_address') is-invalid @enderror" value="{{ old('mac_address',$device->mac_address) }}" placeholder="AA:BB:CC:DD:EE:FF"></div>
  </div></div></div>

  <div class="card mb-3"><div class="card-header py-2 small"><i class="bi bi-cpu"></i> المواصفات</div><div class="card-body p-3"><div class="row g-2">
    <div class="col-md-6"><label class="form-label small mb-1">المعالج</label><input name="cpu" class="form-control form-control-sm" value="{{ old('cpu',$device->cpu) }}" placeholder="Core i5-10400"></div>
    <div class="col-md-2 col-6"><label class="form-label small mb-1">الذاكرة</label><input name="ram" class="form-control form-control-sm" value="{{ old('ram',$device->ram) }}" placeholder="8GB"></div>
    <div class="col-md-2 col-6"><label class="form-label small mb-1">التخزين</label><input name="storage" class="form-control form-control-sm" value="{{ old('storage',$device->storage) }}" placeholder="256GB SSD"></div>
    <div class="col-md-2"><label class="form-label small mb-1">النظام</label><input name="os" class="form-control form-control-sm" value="{{ old('os',$device->os) }}" placeholder="Windows 11"></div>
  </div></div></div>

  <div class="card"><div class="card-header py-2 small"><i class="bi bi-sticky"></i> ملاحظات</div><div class="card-body p-3">
    <textarea name="notes" rows="2" class="form-control form-control-sm" placeholder="أي ملاحظات إضافية">{{ old('notes',$device->notes) }}</textarea></div></div>
 </div>

 <div class="col-lg-4"><div class="position-sticky" style="top:1rem">
  <div class="card mb-3"><div class="card-header py-2 small"><i class="bi bi-geo-alt"></i> الموقع (يتحدد من الإدارة)</div><div class="card-body p-3"><div class="row g-2">
    <div class="col-4"><label class="form-label small mb-1">المبنى</label><input id="lB" class="form-control form-control-sm" readonly></div>
    <div class="col-4"><label class="form-label small mb-1">الدور</label><input id="lF" class="form-control form-control-sm" readonly></div>
    <div class="col-4"><label class="form-label small mb-1">الغرفة</label><input id="lR" class="form-control form-control-sm" readonly></div>
  </div></div></div>
  @if($isSystem)<div class="alert alert-info small py-2"><i class="bi bi-hdd-network"></i> هذا الجهاز يُستخدم مع نظام ERP الخاص بالشركة، ويظهر في بوابة «Computer ERP System» فقط.</div>@endif
  <div class="d-grid gap-2"><button class="btn btn-primary btn-sm"><i class="bi bi-check-lg"></i> حفظ</button><a class="btn btn-outline-secondary btn-sm" href="{{ route('devices.index',$type) }}">إلغاء</a></div>
 </div></div>
</div>
</form>
<script>
const USERS={!! json_encode($usersJs, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!},DEPTS={!! json_encode($deptJs, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!},SEL="{{ $sel }}";
const dept=document.getElementById('dept'),emp=document.getElementById('emp');
function fillEmp(keep){const d=dept.value,cur=keep?SEL:emp.value;emp.innerHTML='';
 emp.add(new Option(d?'— بدون —':'اختر الإدارة أولا',''));
 USERS.filter(u=>d&&String(u.d)===d).forEach(u=>emp.add(new Option(u.name,u.id,false,String(u.id)===String(cur))));}
function fillLoc(){const l=DEPTS[dept.value]||{};lB.value=l.b||'';lF.value=l.f||'';lR.value=l.r||'';}
dept.onchange=()=>{fillEmp(false);fillLoc();};fillEmp(true);fillLoc();
</script>
@endsection
