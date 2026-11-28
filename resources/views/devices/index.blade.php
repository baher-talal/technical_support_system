@extends('layouts.app')
@section('content')
@php $title = $type==='system' ? 'Computer ERP System' : 'أجهزة الشركة'; @endphp
<style>.dev-item{cursor:pointer}.dev-table{font-size:.82rem}.dev-table th{white-space:nowrap;font-weight:600}.dev-card{font-size:.8rem}.dev-card .card-body{padding:.6rem}</style>
<div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
  <h5 class="fw-bold mb-0"><i class="bi bi-{{ $type==='system' ? 'hdd-network' : 'pc-display' }} text-primary"></i> {{ $title }} <span class="badge text-bg-secondary fs-6">{{ $devices->total() }}</span></h5>
  <div class="d-flex gap-1 flex-wrap">
    <div class="btn-group btn-group-sm" role="group"><button type="button" class="btn btn-outline-secondary" id="btnT" title="جدول"><i class="bi bi-list-ul"></i></button><button type="button" class="btn btn-outline-secondary" id="btnC" title="بطاقات"><i class="bi bi-grid-3x3-gap"></i></button></div>
    @if($type==='company')<a class="btn btn-outline-secondary btn-sm" target="_blank" href="{{ route('devices.labels', ['type'=>'company'] + request()->query()) }}"><i class="bi bi-qr-code"></i> ملصقات QR</a>@endif
    @if($canExport)<a class="btn btn-outline-success btn-sm" href="{{ route('devices.export', ['type'=>$type] + request()->query()) }}"><i class="bi bi-file-earmark-excel"></i> تصدير</a>@endif
    @if($canImport)<button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#impModal"><i class="bi bi-upload"></i> استيراد</button>@endif
    @if($canManage)<a href="{{ route('devices.create',$type) }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> إضافة</a>@endif
  </div>
</div>
<form class="row g-1 mb-2" method="get">
  <div class="col-md-4"><input class="form-control form-control-sm" name="q" value="{{ request('q') }}" placeholder="بحث: جهاز / موظف / IP / MAC / سيريال"></div>
  <div class="col-md-3"><select name="department_id" class="form-select form-select-sm"><option value="">كل الإدارات</option>@foreach($departments as $d)<option value="{{ $d->id }}" @selected(request('department_id')==$d->id)>{{ $d->name }}</option>@endforeach</select></div>
  <div class="col-md-2"><select name="building" class="form-select form-select-sm"><option value="">كل المباني</option>@foreach(config('helpdesk.buildings') as $k=>$l)<option value="{{ $k }}" @selected(request('building')==$k)>{{ $l }}</option>@endforeach</select></div>
  <div class="col-md-1"><button class="btn btn-outline-primary btn-sm w-100">بحث</button></div>
  @if($type==='company')<div class="col-md-6 d-flex gap-2 align-items-center"><select name="sort" class="form-select form-select-sm w-auto"><option value="">الترتيب: بالاسم</option><option value="repairs" @selected(request('sort')==='repairs')>الترتيب: الأكثر أعطالا</option></select>
    <div class="form-check mb-0"><input class="form-check-input" type="checkbox" name="frequent" value="1" id="freq" @checked(request()->boolean('frequent'))><label class="form-check-label small" for="freq">الأجهزة كثيرة الأعطال فقط (≥ {{ config('helpdesk.device_alert.repairs') }} خلال {{ config('helpdesk.device_alert.days') }} يوم)</label></div></div>@endif
  @if(request()->hasAny(['q','department_id','building','sort','frequent']))<div class="col-md-1"><a href="{{ route('devices.index',$type) }}" class="btn btn-outline-secondary btn-sm w-100">مسح</a></div>@endif
</form>

@php
$items = $devices->map(function ($d) use ($type) {
    return ['m' => $d, 'p' => ['id'=>$d->id,'name'=>$d->name,'serial'=>$d->serial,'employee'=>$d->employee_name,'employee_url'=>$d->user_id ? route('employees.show',$d->user_id) : null,
     'department'=>$d->department?->name,'building'=>$d->department?->building,'floor'=>$d->department?->floor,'room'=>$d->department?->room_number,
     'ip'=>$d->ip_address,'mac'=>$d->mac_address,'cpu'=>$d->cpu,'ram'=>$d->ram,'storage'=>$d->storage,'os'=>$d->os,'notes'=>$d->notes,
     'repairs'=>(int) ($d->repairs_count ?? 0),'show_url'=>$type==='company' ? route('devices.show',['type'=>'company','device'=>$d->id]) : null,'created'=>$d->created_at?->format('Y-m-d'),'updated'=>$d->updated_at?->format('Y-m-d H:i')]];
});
$alertMin = (int) config('helpdesk.device_alert.repairs');
$loc = fn ($d) => $d->department?->building ? $d->department->building.' / '.$d->department->floor.' / '.$d->department->room_number : '—';
@endphp

<div id="vTable" class="card"><div class="table-responsive"><table class="table table-sm table-hover align-middle mb-0 dev-table">
<thead><tr class="text-body-secondary"><th>الجهاز</th><th>الموظف</th><th>الإدارة</th><th>مبنى / دور / غرفة</th><th>IP</th><th>MAC</th>@if($type==='company')<th class="text-center" title="تيكيتات عطل خلال {{ config('helpdesk.device_alert.days') }} يوم">الأعطال</th>@endif @if($canManage)<th></th>@endif</tr></thead><tbody>
@forelse($items as $it) @php $d = $it['m']; @endphp
<tr class="dev-item" data-d="{{ json_encode($it['p'], JSON_UNESCAPED_UNICODE) }}">
  <td><b>{{ $d->name }}</b>@if($d->serial)<div class="text-body-secondary" style="font-size:.72rem">{{ $d->serial }}</div>@endif</td>
  <td>@if($d->user_id)<a href="{{ route('employees.show',$d->user_id) }}" onclick="event.stopPropagation()">{{ $d->employee_name }}</a>@else{{ $d->employee_name ?: '—' }}@endif</td>
  <td>{{ $d->department?->name ?: '—' }}</td><td class="text-nowrap">{{ $loc($d) }}</td>
  <td dir="ltr" class="text-end">{{ $d->ip_address ?: '—' }}</td><td dir="ltr" class="text-end text-nowrap">{{ $d->mac_address ?: '—' }}</td>
  @if($type==='company')@php $rc = (int) $d->repairs_count; @endphp<td class="text-center">@if($rc)<span class="badge text-bg-{{ $rc >= $alertMin ? 'danger' : 'secondary' }}">{{ $rc }}</span>@else<span class="text-body-secondary">0</span>@endif</td>@endif
  @if($canManage)<td class="text-nowrap" onclick="event.stopPropagation()"><a class="btn btn-sm btn-outline-primary py-0 px-1" href="{{ route('devices.edit',[$type,$d]) }}"><i class="bi bi-pencil"></i></a>
   <form class="d-inline" method="post" action="{{ route('devices.destroy',[$type,$d]) }}" onsubmit="return confirm('حذف الجهاز؟')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger py-0 px-1"><i class="bi bi-trash"></i></button></form></td>@endif
</tr>
@empty<tr><td colspan="8" class="text-center text-body-secondary py-4">لا توجد أجهزة</td></tr>@endforelse
</tbody></table></div></div>

<div id="vCards" class="row g-2 d-none">
@foreach($items as $it) @php $d = $it['m']; @endphp
<div class="col-6 col-md-4 col-xl-3"><div class="card h-100 dev-item dev-card shadow-sm" data-d="{{ json_encode($it['p'], JSON_UNESCAPED_UNICODE) }}"><div class="card-body">
  <div class="d-flex justify-content-between align-items-start"><b class="text-truncate"><i class="bi bi-{{ $type==='system' ? 'hdd-network' : 'pc-display' }} text-primary"></i> {{ $d->name }}</b>
   @if($canManage)<span class="text-nowrap" onclick="event.stopPropagation()"><a class="btn btn-sm btn-outline-primary py-0 px-1" href="{{ route('devices.edit',[$type,$d]) }}"><i class="bi bi-pencil"></i></a>
   <form class="d-inline" method="post" action="{{ route('devices.destroy',[$type,$d]) }}" onsubmit="return confirm('حذف الجهاز؟')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger py-0 px-1"><i class="bi bi-trash"></i></button></form></span>@endif</div>
  <div class="text-truncate">@if($d->user_id)<a href="{{ route('employees.show',$d->user_id) }}" onclick="event.stopPropagation()">{{ $d->employee_name }}</a>@else{{ $d->employee_name ?: '—' }}@endif</div>
  <div class="text-body-secondary text-truncate">{{ $d->department?->name ?: '—' }} • {{ $loc($d) }}</div>
  <div dir="ltr" class="text-start">{{ $d->ip_address ?: '—' }}</div><div dir="ltr" class="text-start text-body-secondary">{{ $d->mac_address ?: '—' }}</div>
  @if($type==='company' && (int) $d->repairs_count)<div class="mt-1"><span class="badge text-bg-{{ (int) $d->repairs_count >= $alertMin ? 'danger' : 'secondary' }}"><i class="bi bi-wrench"></i> {{ $d->repairs_count }} أعطال</span></div>@endif
</div></div></div>
@endforeach
</div>
<div class="mt-2">{{ $devices->links() }}</div>

@if($canImport)
<div class="modal fade" id="impModal" tabindex="-1"><div class="modal-dialog"><form class="modal-content" method="post" action="{{ route('devices.import',$type) }}" enctype="multipart/form-data">@csrf
 <div class="modal-header py-2"><h6 class="modal-title">استيراد أجهزة من Excel</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
 <div class="modal-body">
  <input type="file" name="file" class="form-control form-control-sm mb-2" accept=".xlsx,.xls,.csv" required>
  <div class="small text-body-secondary">
   <a href="{{ route('devices.template',$type) }}">تحميل القالب</a> — الأعمدة المطلوبة: «الإدارة» (اسم إدارة موجودة) و«اسم الجهاز».<br>
   إن وُجد السيريال يُحدَّث الجهاز صاحب نفس السيريال، وإلا يُطابَق بالاسم + الإدارة، وإلا يُنشأ جهاز جديد.<br>
   «بريد الموظف» اختياري ويجب أن يكون لمستخدم من نفس الإدارة. الصفوف الخاطئة تُتخطى مع عرض أسبابها.
  </div></div>
 <div class="modal-footer py-2"><button class="btn btn-primary btn-sm">استيراد</button></div>
</form></div></div>
@endif
<div class="modal fade" id="devModal" tabindex="-1"><div class="modal-dialog modal-lg modal-dialog-scrollable"><div class="modal-content">
 <div class="modal-header py-2"><h6 class="modal-title" id="mTitle"></h6><button class="btn-close" data-bs-dismiss="modal"></button></div>
 <div class="modal-body"><div class="row g-2" id="mBody"></div>@if($type==='company')<a id="mTickets" class="small d-inline-block mt-2" href="#"><i class="bi bi-clock-history"></i> سجل صيانة الجهاز</a> <a id="mLabel" class="small d-inline-block mt-2 ms-3" target="_blank" href="#"><i class="bi bi-qr-code"></i> طباعة ملصق QR</a>@endif</div></div></div></div>
<script>
const vt=document.getElementById('vTable'),vc=document.getElementById('vCards'),bT=document.getElementById('btnT'),bC=document.getElementById('btnC');
function setView(v){try{localStorage.setItem('devView',v)}catch(e){}vt.classList.toggle('d-none',v!=='table');vc.classList.toggle('d-none',v!=='cards');bT.classList.toggle('active',v==='table');bC.classList.toggle('active',v==='cards');}
bT.onclick=()=>setView('table');bC.onclick=()=>setView('cards');
let sv='table';try{sv=localStorage.getItem('devView')||'table'}catch(e){}setView(sv);
const rows=[['employee','الموظف'],['department','الإدارة'],['building','المبنى'],['floor','الدور'],['room','رقم الغرفة'],['ip','IP Address'],['mac','MAC Address'],['serial','السيريال'],['cpu','المعالج'],['ram','الذاكرة'],['storage','التخزين'],['os','نظام التشغيل'],['created','تاريخ الإضافة'],['updated','آخر تحديث'],['notes','ملاحظات']];
document.querySelectorAll('.dev-item').forEach(c=>c.addEventListener('click',()=>{
 const d=JSON.parse(c.dataset.d);mTitle.textContent=d.name;mBody.innerHTML='';const mt=document.getElementById('mTickets');if(mt)mt.href=d.show_url||'#';const ml=document.getElementById('mLabel');if(ml)ml.href="{{ route('devices.labels',['type'=>'company']) }}?id="+d.id;
 rows.forEach(([k,l])=>{const col=document.createElement('div');col.className=k==='notes'?'col-12':'col-md-4 col-6';
  const b=document.createElement('div');b.className='border rounded p-2';const s=document.createElement('small');s.className='text-body-secondary d-block';s.textContent=l;b.appendChild(s);
  let v;if(k==='employee'&&d.employee_url&&d.employee){v=document.createElement('a');v.href=d.employee_url;v.textContent=d.employee;}else{v=document.createElement('b');v.textContent=d[k]||'—';if(k==='ip'||k==='mac')v.dir='ltr';}
  b.appendChild(v);col.appendChild(b);mBody.appendChild(col);});
 bootstrap.Modal.getOrCreateInstance(devModal).show();}));
</script>
@endsection
