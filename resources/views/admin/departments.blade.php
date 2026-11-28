@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between mb-3"><h4 class="fw-bold">الإدارات</h4>
<button class="btn btn-primary" onclick="openDept()"><i class="bi bi-plus-lg"></i> إضافة إدارة</button></div>
<div class="row g-3">
@forelse($departments as $d)
<div class="col-md-6 col-xl-4"><div class="card h-100 shadow-sm"><div class="card-body">
  <div class="d-flex justify-content-between align-items-start">
    <h5 class="fw-bold mb-2"><i class="bi bi-building text-primary"></i> {{ $d->name }}</h5>
    <span class="badge text-bg-primary fs-6">{{ $d->building ? 'مبنى '.$d->building : '—' }}</span>
  </div>
  <div class="row small g-1 mb-3">
    <div class="col-6">الدور: <b>{{ $d->floor ?: '—' }}</b></div>
    <div class="col-6">رقم الغرفة: <b>{{ $d->room_number ?: '—' }}</b></div>
    <div class="col-6">عدد الأجهزة: <b class="text-success">{{ $d->devices_count }}</b></div>
    <div class="col-6">عدد الموظفين: <b>{{ $d->users_count }}</b></div>
    <div class="col-12">القطاع: <b>{{ $d->sector?->name ?: '—' }}</b></div>
  </div>
  @php $payload = ['id' => $d->id, 'name' => $d->name, 'building' => $d->building, 'floor' => $d->floor, 'room_number' => $d->room_number]; @endphp
  <button class="btn btn-sm btn-outline-primary" data-dept="{{ json_encode($payload, JSON_UNESCAPED_UNICODE) }}" onclick="openDept(JSON.parse(this.dataset.dept))"><i class="bi bi-pencil"></i> تعديل</button>
  <form class="d-inline" method="post" action="{{ route('admin.departments.destroy',$d) }}" onsubmit="return confirm('حذف الإدارة؟')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form>
</div></div></div>
@empty<div class="col-12 text-center text-body-secondary py-5">لا توجد إدارات</div>@endforelse
</div>

<div class="modal fade" id="deptModal" tabindex="-1"><div class="modal-dialog"><form class="modal-content" method="post" id="deptForm">@csrf<span id="deptMethod"></span>
  <div class="modal-header"><h5 class="modal-title" id="deptTitle">إضافة إدارة</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
  <div class="modal-body row g-3">
    <div class="col-12"><label class="form-label">اسم الإدارة</label><input name="name" id="dName" class="form-control" required></div>
    <div class="col-md-4"><label class="form-label">المبنى</label><select name="building" id="dBuilding" class="form-select" required>@foreach(config('helpdesk.buildings') as $k=>$l)<option value="{{ $k }}">{{ $l }}</option>@endforeach</select></div>
    <div class="col-md-4"><label class="form-label">الدور</label><input name="floor" id="dFloor" class="form-control" required></div>
    <div class="col-md-4"><label class="form-label">رقم الغرفة</label><input name="room_number" id="dRoom" class="form-control" required></div>
    <div class="col-12 small text-body-secondary">عدد الأجهزة يُحسب تلقائيا من أجهزة الشركة المسجلة على الإدارة.</div>
  </div>
  <div class="modal-footer"><button class="btn btn-primary">حفظ</button></div>
</form></div></div>
<script>
const dm=()=>bootstrap.Modal.getOrCreateInstance(document.getElementById('deptModal'));
function openDept(d){const f=document.getElementById('deptForm');
 f.action=d?`{{ url('admin/departments') }}/${d.id}`:`{{ route('admin.departments.store') }}`;
 deptMethod.innerHTML=d?'<input type="hidden" name="_method" value="PUT">':'';
 deptTitle.textContent=d?'تعديل إدارة':'إضافة إدارة';
 dName.value=d?.name||'';dBuilding.value=d?.building||'A';dFloor.value=d?.floor||'';dRoom.value=d?.room_number||'';dm().show();}
</script>
@endsection
