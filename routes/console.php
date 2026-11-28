<?php
use App\Models\AuditLog;
use Illuminate\Support\Facades\Schedule;

// تحتاج مهمة مجدولة على الجهاز تشغّل: php artisan schedule:run كل دقيقة (راجع README)
Schedule::command('backup:db')->dailyAt('02:00');
Schedule::call(fn () => AuditLog::where('created_at', '<', now()->subYear())->delete())->weekly()->name('audit-prune');
