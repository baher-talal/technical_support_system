<!doctype html>
<html lang="ar" dir="rtl">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>ملصقات QR للأجهزة</title>
@include('partials.css')
<style>
 body{font-family:'Cairo','Segoe UI',Tahoma,Arial,sans-serif;background:#fff;color:#000}
 .labels{display:grid;grid-template-columns:repeat(3,1fr);gap:6px;padding:8px}
 .label{border:1px dashed #888;padding:6px;display:flex;gap:8px;align-items:center;break-inside:avoid;font-size:11px;line-height:1.35}
 .label .qr{flex:0 0 auto}.label .qr img,.label .qr canvas{display:block}
 .label b{font-size:12px}.hint{font-size:9px;color:#555}
 @media print{.no-print{display:none!important}.labels{padding:0}@page{margin:8mm}}
</style></head>
<body>
<div class="no-print p-2 d-flex gap-2 align-items-center border-bottom bg-light">
  <button class="btn btn-primary btn-sm" onclick="window.print()">طباعة</button>
  <a class="btn btn-outline-secondary btn-sm" href="javascript:history.back()">رجوع</a>
  <span class="small">{{ $devices->count() }} ملصق@if($devices->count() >= 300) (الحد الأقصى 300، ضيّق التصفية لطباعة الباقي)@endif — يفتح مسح الملصق صفحة تيكيت جديد بالجهاز محددا.</span>
</div>
@php
  // عنوان النظام داخل الـ QR: HELPDESK_PUBLIC_URL إن وُجد، وإلا عنوان الصفحة الحالية
  $pub = rtrim((string) config('helpdesk.public_url'), '/');
  $qrUrl = fn ($d) => $pub ? $pub . route('tickets.create', ['device' => $d->id], false) : route('tickets.create', ['device' => $d->id]);
  $sample = $devices->isNotEmpty() ? $qrUrl($devices->first()) : '';
@endphp
@if(preg_match('~//(localhost|127\.0\.0\.1)~', $sample))
<div class="no-print alert alert-warning m-2 py-2 small">تنبيه: الرمز يشير إلى <b dir="ltr">{{ parse_url($sample, PHP_URL_HOST) }}</b> ولن يعمل من أجهزة الموظفين. اضبط <b dir="ltr">HELPDESK_PUBLIC_URL</b> في ملف .env (مثال: http://192.168.1.10) أو افتح هذه الصفحة من عنوان الخادم الحقيقي ثم اطبع.</div>
@endif
<div class="labels">
@foreach($devices as $d)
  <div class="label"><div class="qr" data-url="{{ $qrUrl($d) }}"></div>
    <div><b>{{ $d->name }}</b><br>{{ $d->department?->name }}<br>@if($d->department?->building){{ $d->department->building }} / {{ $d->department->floor }} / {{ $d->department->room_number }}<br>@endif @if($d->serial)<span dir="ltr">{{ $d->serial }}</span><br>@endif<span class="hint">امسح الكود لفتح تيكيت</span></div></div>
@endforeach
</div>
<script src="{{ asset('vendor/qr/qr.js') }}"></script>
<script>
document.querySelectorAll('.qr').forEach(function(e){LocalQR.render(e,e.dataset.url,{size:90,margin:1});});
</script>
</body></html>
