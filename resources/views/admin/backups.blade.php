@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-2"><h5 class="fw-bold mb-0"><i class="bi bi-database-down text-primary"></i> النسخ الاحتياطي</h5>
<form method="post" action="{{ route('backups.store') }}" onsubmit="this.querySelector('button').disabled=true;this.querySelector('button').textContent='جاري الإنشاء...'">@csrf<button class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> إنشاء نسخة الآن</button></form></div>
<div class="alert alert-info small py-2">تُنشأ نسخة تلقائية يوميا الساعة 2:00 صباحا (تتطلب تشغيل المجدول على الجهاز، راجع README) ويُحتفظ بآخر {{ config('helpdesk.backup_keep') }} نسخة. النسخة تحتوي كل جداول النظام وبياناته. الاستعادة تتم يدويا (استيراد الملف في phpMyAdmin أو أمر mysql).</div>
<div class="card"><div class="table-responsive"><table class="table table-sm align-middle mb-0">
<thead><tr class="text-body-secondary"><th>الملف</th><th>التاريخ</th><th>الحجم</th><th></th></tr></thead><tbody>
@forelse($files as $f)
<tr><td dir="ltr" class="text-end">{{ $f['name'] }}</td><td>{{ $f['time']->format('Y-m-d H:i') }}</td><td>{{ number_format($f['size']/1024, 1) }} KB</td>
<td class="text-nowrap"><a class="btn btn-sm btn-outline-primary py-0" href="{{ route('backups.download',$f['name']) }}"><i class="bi bi-download"></i> تحميل</a>
<form class="d-inline" method="post" action="{{ route('backups.destroy',$f['name']) }}" onsubmit="return confirm('حذف النسخة؟')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger py-0"><i class="bi bi-trash"></i></button></form></td></tr>
@empty<tr><td colspan="4" class="text-center text-body-secondary py-4">لا توجد نسخ بعد</td></tr>@endforelse
</tbody></table></div></div>
@endsection
