@extends('layouts.app')
@section('content')
@php
  $u = auth()->user(); [$sl,$sc] = config('helpdesk.statuses')[$ticket->status];
  $mine = $ticket->technician_id === $u->id || $u->role?->slug === 'admin';
  $isRequester = $ticket->user_id === $u->id;
  $canApprove = $u->hasPermission('tickets.approve');
  $open = in_array($ticket->status, \App\Models\Ticket::OPEN);
  $comments = $ticket->logs->where('is_comment', true)->reverse();
  $events = $ticket->logs->where('is_comment', false);
  $ratings = [5 => '5 - ممتاز', 4 => '4 - جيد جدا', 3 => '3 - جيد', 2 => '2 - مقبول', 1 => '1 - سيئ'];
@endphp
<div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
  <h5 class="fw-bold mb-0">{{ $ticket->code }} <span class="badge text-bg-{{ $sc }}">{{ $sl }}</span>
    @if($open && $ticket->is_overdue)<span class="badge text-bg-danger">متأخر</span>@endif</h5>
  <div class="d-flex gap-2">
    @if($ticket->technician_id)<a href="{{ $ticket->whatsappUrl() }}" target="_blank" rel="noopener" class="btn btn-success btn-sm"><i class="bi bi-whatsapp"></i> مشاركة للمسؤول</a>@endif
    @if($u->hasPermission('tickets.delete'))
    <form method="post" action="{{ route('tickets.destroy',$ticket) }}" onsubmit="return confirm('حذف التيكيت نهائيا مع سجله؟')">@csrf @method('DELETE')<button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i> حذف</button></form>
    @endif
    <a href="{{ route('tickets.index') }}" class="btn btn-outline-secondary btn-sm">رجوع</a>
  </div>
</div>
@if($ticket->technician_id && !$ticket->technician?->phone)<div class="small text-warning mb-2">لا يوجد رقم هاتف للمسؤول، سيُطلب منك اختيار جهة الاتصال في واتساب.</div>@endif

<div class="row g-3">
 <div class="col-lg-8">
  <div class="card mb-3"><div class="card-body p-3">
    <h6 class="fw-bold">{{ $ticket->title }}</h6>
    <p class="text-body-secondary small mb-2" style="white-space:pre-line">{{ $ticket->description ?: 'لا توجد تفاصيل' }}</p>
    <div class="row small g-1">
      <div class="col-sm-4">النوع: <b>{{ config('helpdesk.categories')[$ticket->category] }}</b></div>
      @if($ticket->item)<div class="col-sm-4">المستلزم: <b>{{ config('helpdesk.items')[$ticket->item] }}</b></div>@endif
      <div class="col-sm-4">الأولوية: <b>{{ config('helpdesk.priorities')[$ticket->priority] }}</b></div>
      <div class="col-sm-4">التكنيكال: <b>{{ $ticket->technician?->name ?? '—' }}</b></div>
      <div class="col-sm-4">المدير المعتمد: <b>{{ $ticket->approver?->name ?? '—' }}</b></div>
      <div class="col-sm-4">الموعد المستهدف: <b class="{{ $open && $ticket->is_overdue ? 'text-danger' : '' }}">{{ $ticket->due_at?->format('Y-m-d H:i') }}</b></div>
      <div class="col-sm-4">تاريخ الإنشاء: <b>{{ $ticket->created_at->format('Y-m-d H:i') }}</b></div>
      @if($ticket->resolved_at)<div class="col-sm-4">تاريخ الحل: <b>{{ $ticket->resolved_at->format('Y-m-d H:i') }}</b></div>@endif
    </div>
    @if($ticket->approval_note)<div class="alert alert-secondary py-2 small mt-2 mb-0">ملاحظة المدير: {{ $ticket->approval_note }}</div>@endif
    @if($ticket->resolution_note)<div class="alert alert-success py-2 small mt-2 mb-0">ملاحظة الحل: {{ $ticket->resolution_note }}</div>@endif
    @if($ticket->rating)<div class="small mt-2">تقييم الحل: <b class="text-warning">{{ str_repeat('★', $ticket->rating) }}{{ str_repeat('☆', 5 - $ticket->rating) }}</b> {{ $ticket->rating_note }}</div>@endif
  </div></div>


  @if($ticket->status==='pending_approval' && $canApprove)
  <div class="card mb-3 border-warning"><div class="card-header py-2 small fw-bold">قرار مدير عام النظم والمعلومات</div><div class="card-body p-3">
    @if($techs->isEmpty())<div class="text-danger small mb-2">لا يوجد التكنيكال/موظفون في إدارة النظم والمعلومات.</div>@endif
    <form method="post" action="{{ route('tickets.approve',$ticket) }}" class="row g-2 mb-2">@csrf
      <div class="col-md-5"><select name="technician_id" class="form-select form-select-sm" required><option value="">اختر المسؤول (من إدارة النظم والمعلومات)</option>@foreach($techs as $t)<option value="{{ $t->id }}">{{ $t->name }}</option>@endforeach</select></div>
      <div class="col-md-5"><input name="note" class="form-control form-control-sm" placeholder="ملاحظة (اختياري)"></div>
      <div class="col-md-2"><button class="btn btn-success btn-sm w-100">قبول</button></div></form>
    <form method="post" action="{{ route('tickets.reject',$ticket) }}" class="row g-2">@csrf
      <div class="col-md-10"><input name="note" class="form-control form-control-sm" placeholder="سبب الرفض" required></div>
      <div class="col-md-2"><button class="btn btn-outline-danger btn-sm w-100">رفض</button></div></form>
  </div></div>
  @endif

  @if(in_array($ticket->status,['assigned','in_progress']) && $canApprove)
  <div class="card mb-3"><div class="card-header py-2 small fw-bold">إعادة إسناد التيكيت</div><div class="card-body p-3">
    <form method="post" action="{{ route('tickets.reassign',$ticket) }}" class="row g-2">@csrf
      <div class="col-md-5"><select name="technician_id" class="form-select form-select-sm" required><option value="">اختر مسؤولا آخر</option>@foreach($techs->where('id','!=',$ticket->technician_id) as $t)<option value="{{ $t->id }}">{{ $t->name }}</option>@endforeach</select></div>
      <div class="col-md-5"><input name="note" class="form-control form-control-sm" placeholder="سبب إعادة الإسناد (اختياري)"></div>
      <div class="col-md-2"><button class="btn btn-outline-primary btn-sm w-100">إعادة إسناد</button></div></form>
  </div></div>
  @endif

  @if($mine && in_array($ticket->status,['assigned','in_progress']))
  <div class="card mb-3 border-primary"><div class="card-header py-2 small fw-bold">إجراءات التكنيكال</div><div class="card-body p-3">
    @if($ticket->status==='assigned')<form method="post" action="{{ route('tickets.start',$ticket) }}" class="mb-2">@csrf<button class="btn btn-primary btn-sm">بدء العمل</button></form>@endif
    <form method="post" action="{{ route('tickets.supplies',$ticket) }}" class="row g-2 mb-2">@csrf
      <div class="col-md-6"><input name="item" class="form-control form-control-sm" placeholder="المستلزم المصروف (كيبورد، ماوس، حبر...)" required></div>
      <div class="col-md-3"><input type="number" name="qty" value="1" min="1" class="form-control form-control-sm"></div>
      <div class="col-md-3"><button class="btn btn-outline-primary btn-sm w-100">صرف</button></div></form>
    <form method="post" action="{{ route('tickets.resolve',$ticket) }}" class="row g-2">@csrf
      <div class="col-md-9"><input name="note" class="form-control form-control-sm" placeholder="ما تم عمله لحل المشكلة" required></div>
      <div class="col-md-3"><button class="btn btn-success btn-sm w-100">تم الحل</button></div></form>
  </div></div>
  @endif

  @if($ticket->status==='resolved' && ($isRequester || $canApprove))
  <div class="card mb-3"><div class="card-header py-2 small fw-bold">بعد الحل</div><div class="card-body p-3">
    @if($isRequester)
    <form method="post" action="{{ route('tickets.rate',$ticket) }}" class="row g-2 mb-2">@csrf
      <div class="col-md-3"><select name="rating" class="form-select form-select-sm" required><option value="">قيّم الحل</option>@foreach($ratings as $v=>$l)<option value="{{ $v }}" @selected($ticket->rating==$v)>{{ $l }}</option>@endforeach</select></div>
      <div class="col-md-7"><input name="rating_note" class="form-control form-control-sm" placeholder="ملاحظتك (اختياري)" value="{{ $ticket->rating_note }}"></div>
      <div class="col-md-2"><button class="btn btn-outline-primary btn-sm w-100">إرسال</button></div></form>
    @endif
    <form method="post" action="{{ route('tickets.reopen',$ticket) }}" class="row g-2">@csrf
      <div class="col-md-10"><input name="note" class="form-control form-control-sm" placeholder="سبب إعادة الفتح (المشكلة ما زالت قائمة...)" required></div>
      <div class="col-md-2"><button class="btn btn-outline-warning btn-sm w-100">إعادة فتح</button></div></form>
  </div></div>
  @endif

  @if($ticket->supplies->count())
  <div class="card mb-3"><div class="card-header py-2 small fw-bold">المستلزمات المصروفة</div><ul class="list-group list-group-flush small">
    @foreach($ticket->supplies as $s)<li class="list-group-item d-flex justify-content-between"><span>{{ $s->item }} × {{ $s->qty }}</span><span class="text-body-secondary">{{ $s->issuer?->name }} — {{ $s->created_at->format('Y-m-d H:i') }}</span></li>@endforeach</ul></div>
  @endif

  <div class="card mb-3"><div class="card-header py-2 small fw-bold">المحادثة ({{ $comments->count() }})</div><div class="card-body p-3">
    @forelse($comments as $c)
      <div class="mb-2 p-2 rounded {{ $c->user_id === $u->id ? 'bg-primary-subtle' : 'bg-body-secondary' }}">
        <div class="small fw-bold">{{ $c->user?->name }} <span class="text-body-secondary fw-normal">— {{ $c->created_at->format('Y-m-d H:i') }}</span></div>
        <div class="small" style="white-space:pre-line">{{ $c->note }}</div></div>
    @empty<div class="small text-body-secondary mb-2">لا توجد تعليقات بعد.</div>@endforelse
    <form method="post" action="{{ route('tickets.comment',$ticket) }}" class="mt-2">@csrf
      <textarea name="body" rows="2" maxlength="2000" class="form-control form-control-sm mb-1" placeholder="اكتب تعليقا..." required></textarea>
      <button class="btn btn-primary btn-sm">إرسال</button>
    </form>
  </div></div>

  <div class="card"><div class="card-header py-2 small fw-bold">سجل المتابعة</div><ul class="list-group list-group-flush small">
    @foreach($events as $l)<li class="list-group-item py-1"><div>{{ $l->action }}</div>@if($l->note)<div class="text-body-secondary">{{ $l->note }}</div>@endif<span class="text-body-secondary">{{ $l->user?->name }} — {{ $l->created_at->format('Y-m-d H:i') }}</span></li>@endforeach</ul></div>
 </div>

 <div class="col-lg-4">
  <div class="card mb-3"><div class="card-header py-2 small fw-bold">صاحب الطلب</div><div class="card-body p-3 small">
    <div>الاسم: <b>{{ $ticket->user->name }}</b></div>
    @if($ticket->created_by && $ticket->created_by !== $ticket->user_id)<div>أنشأه: <b>{{ $ticket->creator?->name }}</b></div>@endif<div>الإدارة: <b>{{ $ticket->user->department?->name }}</b></div>
    <div>الهاتف: <b>{{ $ticket->user->phone ?: '—' }}</b></div><div>البريد: <b>{{ $ticket->user->email }}</b></div></div></div>
  <div class="card mb-3"><div class="card-header py-2 small fw-bold">موقع المشكلة</div><div class="card-body p-3 small">
    <div>الإدارة: <b>{{ $ticket->department?->name }}</b></div><div>المبنى: <b>{{ $ticket->department?->building ?: '—' }}</b></div>
    <div>الدور: <b>{{ $ticket->department?->floor ?: '—' }}</b></div><div>رقم الغرفة: <b>{{ $ticket->department?->room_number ?: '—' }}</b></div></div></div>
  @if($ticket->device)
  <div class="card"><div class="card-header py-2 small fw-bold">بيانات الجهاز</div><div class="card-body p-3 small">
    <div>الاسم: <b>{{ $ticket->device->name }}</b></div><div>IP: <b dir="ltr">{{ $ticket->device->ip_address ?: '—' }}</b></div>
    <div>MAC: <b dir="ltr">{{ $ticket->device->mac_address ?: '—' }}</b></div><div>المعالج: <b>{{ $ticket->device->cpu ?: '—' }}</b></div>
    <div>الذاكرة: <b>{{ $ticket->device->ram ?: '—' }}</b></div><div>النظام: <b>{{ $ticket->device->os ?: '—' }}</b></div>
    @if($ticket->device->type==='company' && auth()->user()->hasPermission('devices.view'))<a class="d-block mt-2" href="{{ route('devices.show',['type'=>'company','device'=>$ticket->device_id]) }}"><i class="bi bi-clock-history"></i> سجل صيانة هذا الجهاز</a>@endif
    <a class="d-block mt-1" href="{{ route('tickets.index',['device_id'=>$ticket->device_id]) }}">كل تيكيتات هذا الجهاز</a></div></div>
  @endif
 </div>
</div>
@endsection
