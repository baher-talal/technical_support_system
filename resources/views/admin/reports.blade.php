@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-2"><h5 class="fw-bold mb-0">التقارير</h5></div>
<form class="row g-1 mb-3" method="get">
  <div class="col-md-2"><input type="date" name="from" value="{{ $from }}" class="form-control form-control-sm"></div>
  <div class="col-md-2"><input type="date" name="to" value="{{ $to }}" class="form-control form-control-sm"></div>
  <div class="col-md-1"><button class="btn btn-outline-primary btn-sm w-100">عرض</button></div>
  <div class="col-md-2"><a class="btn btn-outline-success btn-sm w-100" href="{{ route('tickets.export',['from'=>$from,'to'=>$to]) }}"><i class="bi bi-file-earmark-excel"></i> تصدير Excel</a></div>
  <div class="col-md-1"><button type="button" class="btn btn-outline-secondary btn-sm w-100" onclick="window.print()"><i class="bi bi-printer"></i></button></div>
</form>
<div class="row g-2 mb-3">
  <div class="col-6 col-md"><div class="card text-center h-100"><div class="card-body p-2"><div class="fs-3 fw-bold">{{ $total }}</div><div class="small text-body-secondary">إجمالي التيكيتات</div></div></div></div>
  <div class="col-6 col-md"><div class="card text-center h-100"><div class="card-body p-2"><div class="fs-3 fw-bold text-success">{{ $sla['rate'] === null ? '—' : $sla['rate'].'%' }}</div><div class="small text-body-secondary">الالتزام بالموعد المستهدف ({{ $sla['resolved'] }} محلولة)</div></div></div></div>
  <div class="col-6 col-md"><div class="card text-center h-100"><div class="card-body p-2"><div class="fs-3 fw-bold text-danger">{{ $sla['overdue_open'] }}</div><div class="small text-body-secondary">مفتوحة ومتأخرة</div></div></div></div>
  <div class="col-6 col-md"><div class="card text-center h-100"><div class="card-body p-2"><div class="fs-3 fw-bold">{{ $sla['avg_hours'] }}</div><div class="small text-body-secondary">متوسط زمن الحل (ساعة)</div></div></div></div>
  <div class="col-12 col-md"><div class="card text-center h-100"><div class="card-body p-2"><div class="fs-3 fw-bold text-warning">{{ $sla['rating'] ?: '—' }}</div><div class="small text-body-secondary">متوسط التقييم من 5</div></div></div></div>
</div>
<div class="row g-2 mb-3">
  @foreach(config('helpdesk.statuses') as $k=>[$l,$c])<div class="col-6 col-md"><div class="card text-center"><div class="card-body p-2"><div class="fs-4 fw-bold text-{{ $c }}">{{ $byStatus[$k] ?? 0 }}</div><div class="small">{{ $l }}</div></div></div></div>@endforeach
</div>
<div class="row g-3">
 @if($bySector->count())<div class="col-lg-6"><div class="card"><div class="card-header py-2 small fw-bold">حسب القطاع</div><table class="table table-sm mb-0">@foreach($bySector as $r)<tr><td>{{ $r->n }}</td><td class="text-end">{{ $r->c }}</td></tr>@endforeach</table></div></div>@endif
 <div class="col-lg-6"><div class="card"><div class="card-header py-2 small fw-bold">حسب الإدارة</div><table class="table table-sm mb-0">@foreach($byDept as $r)<tr><td>{{ $r->n }}</td><td class="text-end">{{ $r->c }}</td></tr>@endforeach</table></div></div>
 <div class="col-lg-6"><div class="card"><div class="card-header py-2 small fw-bold">حسب النوع</div><table class="table table-sm mb-0">@foreach($byCategory as $k=>$c)<tr><td>{{ config('helpdesk.categories')[$k] ?? $k }}</td><td class="text-end">{{ $c }}</td></tr>@endforeach</table></div></div>
 <div class="col-12"><div class="card"><div class="card-header py-2 small fw-bold">أداء التكنيكال</div><div class="table-responsive"><table class="table table-sm mb-0"><thead><tr><th>التكنيكال</th><th>المسند</th><th>المحلول</th><th>متوسط زمن الحل (ساعة)</th><th>متوسط التقييم</th></tr></thead>
 @foreach($byTech as $r)<tr><td>{{ $r->n }}</td><td>{{ $r->c }}</td><td>{{ (int)$r->done }}</td><td>{{ $r->mins ? round($r->mins/60,1) : '—' }}</td><td>{{ $r->rating ? round($r->rating,1) : '—' }}</td></tr>@endforeach</table></div></div></div>
</div>
@endsection
