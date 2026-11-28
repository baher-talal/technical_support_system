@extends('layouts.app')
@section('content')
@php $qs = request()->query(); $active = fn ($k) => request()->boolean($k); $keep = ['mine', 'assigned', 'overdue', 'device_id']; @endphp
<div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2"><h5 class="fw-bold mb-0">التيكيتات</h5>
 <div class="d-flex gap-2">
  @if($canExport)<a class="btn btn-outline-success btn-sm" href="{{ route('tickets.export', $qs) }}"><i class="bi bi-file-earmark-excel"></i> تصدير Excel</a>@endif
  @if(auth()->user()->hasPermission('tickets.create'))<a href="{{ route('tickets.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> تيكيت جديد</a>@endif
 </div></div>
<div class="mb-2 d-flex gap-1 flex-wrap">
 <a class="btn btn-sm {{ !$qs ? 'btn-secondary' : 'btn-outline-secondary' }}" href="{{ route('tickets.index') }}">الكل</a>
 <a class="btn btn-sm {{ $active('mine') ? 'btn-secondary' : 'btn-outline-secondary' }}" href="{{ route('tickets.index',['mine'=>1]) }}">تيكيتاتي</a>
 <a class="btn btn-sm {{ $active('assigned') ? 'btn-secondary' : 'btn-outline-secondary' }}" href="{{ route('tickets.index',['assigned'=>1]) }}">المسندة إليّ</a>
 <a class="btn btn-sm {{ $active('overdue') ? 'btn-danger' : 'btn-outline-danger' }}" href="{{ route('tickets.index',['overdue'=>1]) }}">المتأخرة</a>
</div>
<form class="row g-1 mb-2" method="get">
  <div class="col-md-3"><input class="form-control form-control-sm" name="q" value="{{ request('q') }}" placeholder="بحث بالرقم أو العنوان"></div>
  <div class="col-md-2"><select name="status" class="form-select form-select-sm"><option value="">كل الحالات</option>@foreach(config('helpdesk.statuses') as $k=>[$l])<option value="{{ $k }}" @selected(request('status')==$k)>{{ $l }}</option>@endforeach</select></div>
  <div class="col-md-2"><select name="department_id" class="form-select form-select-sm"><option value="">كل الإدارات</option>@foreach($departments as $d)<option value="{{ $d->id }}" @selected(request('department_id')==$d->id)>{{ $d->name }}</option>@endforeach</select></div>
  @if($sectors->count())<div class="col-md-2"><select name="sector_id" class="form-select form-select-sm"><option value="">كل القطاعات</option>@foreach($sectors as $s)<option value="{{ $s->id }}" @selected(request('sector_id')==$s->id)>{{ $s->name }}</option>@endforeach</select></div>@endif
  <div class="col-md-1"><select name="priority" class="form-select form-select-sm"><option value="">الأولوية</option>@foreach(config('helpdesk.priorities') as $k=>$l)<option value="{{ $k }}" @selected(request('priority')==$k)>{{ $l }}</option>@endforeach</select></div>
  <div class="col-md-1"><select name="category" class="form-select form-select-sm"><option value="">النوع</option>@foreach(config('helpdesk.categories') as $k=>$l)<option value="{{ $k }}" @selected(request('category')==$k)>{{ $l }}</option>@endforeach</select></div>
  <div class="col-md-1"><input type="date" name="from" value="{{ request('from') }}" class="form-control form-control-sm" title="من"></div>
  <div class="col-md-1"><input type="date" name="to" value="{{ request('to') }}" class="form-control form-control-sm" title="إلى"></div>
  <div class="col-md-1"><button class="btn btn-outline-primary btn-sm w-100">تصفية</button></div>
  @foreach($keep as $h)@if(request()->filled($h))<input type="hidden" name="{{ $h }}" value="{{ request($h) }}">@endif @endforeach
</form>
<div class="card">@include('tickets._table')<div class="card-footer py-2">{{ $tickets->links() }}</div></div>
@endsection
