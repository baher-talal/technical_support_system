@extends('layouts.app')
@section('content')
@php $sla = config('helpdesk.sla_hours'); @endphp
<div class="d-flex justify-content-between align-items-center mb-2">
  <h5 class="fw-bold mb-0"><i class="bi bi-plus-circle text-primary"></i> تيكيت / طلب جديد</h5>
  <a href="{{ route('tickets.index') }}" class="btn btn-outline-secondary btn-sm">رجوع</a>
</div>
@if($warn)<div class="alert alert-warning py-2 small">{{ $warn }}</div>@endif
@if($preDevice)<div class="alert alert-success py-2 small"><i class="bi bi-qr-code"></i> تم اختيار الجهاز <b>{{ $preDevice->name }}</b> من الملصق.</div>@endif
<form method="post" action="{{ route('tickets.store') }}">@csrf
<div class="row g-3">
 <div class="col-xl-8">
  <div class="card"><div class="card-body p-3"><div class="row g-2">
    <div class="col-md-6"><label class="form-label small mb-1">نوع الطلب</label>
      <div class="btn-group w-100" role="group">
      @foreach(config('helpdesk.categories') as $k=>$l)
        <input type="radio" class="btn-check" name="category" id="c_{{ $k }}" value="{{ $k }}" @checked(old('category','repair')==$k)>
        <label class="btn btn-outline-primary btn-sm" for="c_{{ $k }}">{{ $l }}</label>
      @endforeach</div></div>
    <div class="col-md-6" id="itemBox" style="display:none"><label class="form-label small mb-1">المستلزم المطلوب</label>
      <select name="item" class="form-select form-select-sm"><option value="">— اختر —</option>@foreach(config('helpdesk.items') as $k=>$l)<option value="{{ $k }}" @selected(old('item')==$k)>{{ $l }}</option>@endforeach</select></div>

    <div class="col-md-6"><label class="form-label small mb-1">الإدارة</label>
      @if($forOthers)<select name="department_id" id="dept" class="form-select form-select-sm" required><option value="">— اختر الإدارة —</option>@foreach($departments as $d)<option value="{{ $d->id }}" @selected(old('department_id',$preDevice?->department_id ?? $department?->id)==$d->id)>{{ $d->name }}</option>@endforeach</select>
      @else<input class="form-control form-control-sm" value="{{ $department->name }}" readonly>@endif</div>
    <div class="col-md-6"><label class="form-label small mb-1">مقدم الطلب</label>
      @if($forOthers)<select name="requester_id" id="req" class="form-select form-select-sm"><option value="">أنا ({{ auth()->user()->name }})</option></select>
      <div class="form-text">يمكنك الإنشاء لأي مستخدم في الإدارة المختارة.</div>
      @else<input class="form-control form-control-sm" value="{{ auth()->user()->name }}" readonly>
      <div class="form-text">الطلب باسمك، ويمكنك اختيار أي جهاز داخل إدارتك.</div>@endif</div>

    <div class="col-md-8"><label class="form-label small mb-1">عنوان المشكلة / الطلب</label><input name="title" value="{{ old('title') }}" class="form-control form-control-sm" required maxlength="150" placeholder="مثال: الطابعة لا تعمل"></div>
    <div class="col-md-4"><label class="form-label small mb-1">الأولوية</label>
      <div class="btn-group w-100" role="group">
      @foreach(config('helpdesk.priorities') as $k=>$l)
        <input type="radio" class="btn-check prio" name="priority" id="p_{{ $k }}" value="{{ $k }}" data-h="{{ $sla[$k] }}" @checked(old('priority','normal')==$k)>
        <label class="btn btn-outline-secondary btn-sm px-1" for="p_{{ $k }}">{{ $l }}</label>
      @endforeach</div></div>

    <div class="col-12"><label class="form-label small mb-1 d-flex justify-content-between">التفاصيل <span class="text-body-secondary" id="cnt">0 / 3000</span></label>
      <textarea name="description" id="desc" rows="3" maxlength="3000" class="form-control form-control-sm" placeholder="اشرح المشكلة باختصار (متى بدأت؟ رسالة الخطأ؟)">{{ old('description') }}</textarea></div>
  </div>
  <div class="d-flex align-items-center gap-3 mt-3">
    <button class="btn btn-primary btn-sm px-4">إرسال لمدير عام النظم والمعلومات</button>
    <span class="small text-body-secondary"><i class="bi bi-clock"></i> المدة المستهدفة للحل: <b id="slaTxt">24</b> ساعة</span>
  </div></div></div>
 </div>

 <div class="col-xl-4">
  <div class="card mb-3"><div class="card-header py-2 small fw-bold">الجهاز والموقع</div><div class="card-body p-3"><div class="row g-2">
    <div class="col-12"><label class="form-label small mb-1">الجهاز (أي جهاز داخل الإدارة)</label>
      <select name="device_id" id="dev" class="form-select form-select-sm" disabled><option value="">جاري التحميل...</option></select></div>
    <div class="col-4"><label class="form-label small mb-1">المبنى</label><input id="lBuilding" class="form-control form-control-sm" readonly></div>
    <div class="col-4"><label class="form-label small mb-1">الدور</label><input id="lFloor" class="form-control form-control-sm" readonly></div>
    <div class="col-4"><label class="form-label small mb-1">الغرفة</label><input id="lRoom" class="form-control form-control-sm" readonly></div>
  </div></div></div>
  <div class="card" id="devInfo" style="display:none"><div class="card-header py-2 small fw-bold">بيانات الجهاز المختار</div>
    <div class="card-body p-3 small"><div class="row g-1" id="devInfoBody"></div></div></div>
 </div>
</div>
</form>
<script>
const cats=[...document.querySelectorAll('input[name=category]')],fi=()=>itemBox.style.display=cats.find(c=>c.checked)?.value==='supplies'?'':'none';
cats.forEach(c=>c.onchange=fi);fi();
const prios=[...document.querySelectorAll('.prio')],fs=()=>slaTxt.textContent=(prios.find(p=>p.checked)||{dataset:{h:24}}).dataset.h;
prios.forEach(p=>p.onchange=fs);fs();
const desc=document.getElementById('desc'),cnt=document.getElementById('cnt'),fc=()=>cnt.textContent=desc.value.length+' / 3000';desc.oninput=fc;fc();

const oldDev="{{ old('device_id', $preDevice?->id) }}",oldReq="{{ old('requester_id') }}",base="{{ url('tickets/department') }}",fixed="{{ $forOthers ? '' : $department?->id }}",dept=document.getElementById('dept'),req=document.getElementById('req');
let DEVS=[];
function showDev(){const d=DEVS.find(x=>String(x.id)===dev.value);devInfo.style.display=d?'':'none';devInfoBody.innerHTML='';if(!d)return;
 [['الموظف',d.employee_name],['IP',d.ip_address],['MAC',d.mac_address],['المعالج',d.cpu],['الذاكرة',d.ram],['النظام',d.os]].forEach(([l,v])=>{
  const c=document.createElement('div');c.className='col-12';c.append(l+': ');const b=document.createElement('b');b.textContent=v||'—';if(l==='IP'||l==='MAC')b.dir='ltr';c.appendChild(b);devInfoBody.appendChild(c);});}
dev.onchange=showDev;
async function load(id){
 lBuilding.value=lFloor.value=lRoom.value='';dev.innerHTML='';DEVS=[];showDev();
 if(req&&dept){req.innerHTML='';req.add(new Option('أنا ({{ auth()->user()->name }})',''));}
 if(!id){dev.disabled=true;dev.add(new Option('اختر الإدارة أولا',''));return;}
 const r=await fetch(`${base}/${id}/devices`,{headers:{Accept:'application/json'}});if(!r.ok)return;const d=await r.json();DEVS=d.devices;
 lBuilding.value=d.location.building?'مبنى '+d.location.building:'—';lFloor.value=d.location.floor||'—';lRoom.value=d.location.room||'—';
 dev.disabled=false;dev.add(new Option('— غير محدد —',''));
 d.devices.forEach(x=>dev.add(new Option(x.name+(x.employee_name?' — '+x.employee_name:''),x.id,false,String(x.id)===oldDev)));
 if(req&&dept)d.users.forEach(x=>req.add(new Option(x.name,x.id,false,String(x.id)===oldReq)));showDev();
}
if(fixed)load(fixed);else{dept.onchange=()=>load(dept.value);load(dept.value);}
</script>
@endsection
