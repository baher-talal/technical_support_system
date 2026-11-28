@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-2"><h5 class="fw-bold mb-0">القطاعات</h5>
<button class="btn btn-primary btn-sm" onclick="openSector()"><i class="bi bi-plus-lg"></i> إضافة قطاع</button></div>
<div class="small text-body-secondary mb-3">القطاع يضم عدة إدارات ويتولاه رئيس قطاع يرى تيكيتات كل إداراته ويصله إشعار بالتيكيتات الجديدة فيها.</div>
<div class="row g-2">
@forelse($sectors as $s)
<div class="col-md-6 col-xl-4"><div class="card h-100"><div class="card-body p-3">
  <div class="d-flex justify-content-between"><h6 class="fw-bold"><i class="bi bi-diagram-3 text-primary"></i> {{ $s->name }}</h6><span class="badge text-bg-secondary">{{ $s->departments->count() }} إدارة</span></div>
  <div class="small mb-2">رئيس القطاع: <b>{{ $s->head?->name ?: '— غير محدد —' }}</b></div>
  <div class="d-flex flex-wrap gap-1 mb-3">@forelse($s->departments as $d)<span class="badge text-bg-light border">{{ $d->name }}</span>@empty<span class="small text-body-secondary">لا توجد إدارات</span>@endforelse</div>
  @php $payload = ['id' => $s->id, 'name' => $s->name, 'head_id' => $s->head_id, 'dept_ids' => $s->departments->pluck('id')->all()]; @endphp
  <button class="btn btn-sm btn-outline-primary" data-sector="{{ json_encode($payload) }}" onclick="openSector(JSON.parse(this.dataset.sector))"><i class="bi bi-pencil"></i> تعديل</button>
  <form class="d-inline" method="post" action="{{ route('admin.sectors.destroy',$s) }}" onsubmit="return confirm('حذف القطاع؟ الإدارات تبقى بدون قطاع')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form>
</div></div></div>
@empty<div class="col-12 text-center text-body-secondary py-5">لا توجد قطاعات</div>@endforelse
</div>

<div class="modal fade" id="secModal" tabindex="-1"><div class="modal-dialog"><form class="modal-content" method="post" id="secForm">@csrf<span id="secMethod"></span>
  <div class="modal-header py-2"><h6 class="modal-title" id="secTitle">إضافة قطاع</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
  <div class="modal-body"><div class="row g-2">
    <div class="col-md-6"><label class="form-label small mb-1">اسم القطاع</label><input name="name" id="sName" class="form-control form-control-sm" required></div>
    <div class="col-md-6"><label class="form-label small mb-1">رئيس القطاع</label><select name="head_id" id="sHead" class="form-select form-select-sm"><option value="">— غير محدد —</option>@foreach($users as $u)<option value="{{ $u->id }}">{{ $u->name }} — {{ $u->role?->name }}</option>@endforeach</select></div>
    <div class="col-12"><label class="form-label small mb-1">الإدارات التابعة للقطاع</label>
      <div class="border rounded p-2" style="max-height:220px;overflow:auto">
      @foreach($departments as $d)<div class="form-check"><input class="form-check-input sdept" type="checkbox" name="department_ids[]" value="{{ $d->id }}" id="sd{{ $d->id }}"><label class="form-check-label small" for="sd{{ $d->id }}">{{ $d->name }} @if($d->sector)<span class="text-body-secondary">(حاليا: {{ $d->sector->name }})</span>@endif</label></div>@endforeach
      </div><div class="form-text">اختيار إدارة تتبع قطاعا آخر ينقلها إلى هذا القطاع.</div></div>
  </div></div>
  <div class="modal-footer py-2"><button class="btn btn-primary btn-sm">حفظ</button></div>
</form></div></div>
<script>
function openSector(s){const f=document.getElementById('secForm');
 f.action=s?`{{ url('admin/sectors') }}/${s.id}`:`{{ route('admin.sectors.store') }}`;
 secMethod.innerHTML=s?'<input type="hidden" name="_method" value="PUT">':'';
 secTitle.textContent=s?'تعديل قطاع':'إضافة قطاع';sName.value=s?.name||'';sHead.value=s?.head_id||'';
 document.querySelectorAll('.sdept').forEach(c=>c.checked=!!s&&s.dept_ids.includes(Number(c.value)));
 bootstrap.Modal.getOrCreateInstance(document.getElementById('secModal')).show();}
</script>
@endsection
