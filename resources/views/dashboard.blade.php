@extends('layouts.app')
@section('content')
<h5 class="fw-bold mb-2">لوحة التحكم</h5>
@foreach($todo as $label => $n) @if($n)<div class="alert alert-warning py-2 d-flex justify-content-between mb-2"><span><i class="bi bi-bell"></i> {{ $label }}</span><strong>{{ $n }}</strong></div>@endif @endforeach
<div class="row g-2 mb-3">
  <div class="col-6 col-md"><a href="{{ route('tickets.index') }}" class="text-decoration-none"><div class="card h-100"><div class="card-body p-2 text-center"><div class="fs-3 fw-bold text-primary">{{ $kpi['open'] }}</div><div class="small text-body-secondary">تيكيتات مفتوحة</div></div></div></a></div>
  <div class="col-6 col-md"><a href="{{ route('tickets.index',['overdue'=>1]) }}" class="text-decoration-none"><div class="card h-100 border-danger"><div class="card-body p-2 text-center"><div class="fs-3 fw-bold text-danger">{{ $kpi['overdue'] }}</div><div class="small text-body-secondary">متأخرة عن الموعد</div></div></div></a></div>
  <div class="col-6 col-md"><div class="card h-100"><div class="card-body p-2 text-center"><div class="fs-3 fw-bold text-success">{{ $kpi['today'] }}</div><div class="small text-body-secondary">تم حلها اليوم</div></div></div></div>
  <div class="col-6 col-md"><div class="card h-100"><div class="card-body p-2 text-center"><div class="fs-3 fw-bold">{{ $kpi['avg_hours'] }}</div><div class="small text-body-secondary">متوسط زمن الحل (ساعة، 30 يوم)</div></div></div></div>
  <div class="col-12 col-md"><div class="card h-100"><div class="card-body p-2 text-center"><div class="fs-3 fw-bold text-warning">{{ $kpi['rating'] ?: '—' }}</div><div class="small text-body-secondary">متوسط تقييم الحل من 5</div></div></div></div>
</div>
<div class="row g-2 mb-3">
  @foreach(config('helpdesk.statuses') as $k => [$label,$color])
  <div class="col-6 col-md-4 col-xl"><a href="{{ route('tickets.index',['status'=>$k]) }}" class="text-decoration-none">
    <div class="card border-{{ $color }}"><div class="card-body p-2 text-center"><div class="fs-4 fw-bold text-{{ $color }}">{{ $counts[$k] ?? 0 }}</div><div class="small text-body-secondary">{{ $label }}</div></div></div></a></div>
  @endforeach
</div>
<div class="row g-3 mb-3">
  <div class="col-lg-8"><div class="card h-100"><div class="card-header py-2 small fw-bold">آخر 14 يوم: الجديدة مقابل المحلولة</div><div class="card-body p-2"><div style="height:240px"><canvas id="chDays"></canvas></div></div></div></div>
  <div class="col-lg-4"><div class="card h-100"><div class="card-header py-2 small fw-bold">التيكيتات حسب الحالة</div><div class="card-body p-2"><div style="height:240px"><canvas id="chStatus"></canvas></div></div></div></div>
</div>
<div class="card"><div class="card-header py-2 small fw-bold">آخر التيكيتات</div>
@include('tickets._table',['tickets'=>$latest])</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
const D={!! json_encode($chart, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!};
if(window.Chart){
 Chart.defaults.color=getComputedStyle(document.body).color;Chart.defaults.borderColor='rgba(128,128,128,.2)';Chart.defaults.font.family='Cairo, sans-serif';
 new Chart(document.getElementById('chDays'),{type:'bar',data:{labels:D.labels,datasets:[
  {label:'جديدة',data:D.created,backgroundColor:'#0d6efd'},{label:'محلولة',data:D.resolved,backgroundColor:'#198754'}]},
  options:{responsive:true,maintainAspectRatio:false,scales:{y:{beginAtZero:true,ticks:{precision:0}}}}});
 new Chart(document.getElementById('chStatus'),{type:'doughnut',data:{labels:D.status.map(s=>s.label),datasets:[{data:D.status.map(s=>s.value),
  backgroundColor:['#ffc107','#dc3545','#0dcaf0','#0d6efd','#198754']}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{position:'bottom'}}}});
}
</script>
@endsection
