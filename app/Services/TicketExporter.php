<?php
namespace App\Services;

use App\Models\Ticket;
use App\Support\XlsxExport;
use Illuminate\Database\Eloquent\Builder;

class TicketExporter
{
    public function download(Builder $query)
    {
        $cfg = config('helpdesk');
        $rows = $query->limit(5000)->get()->map(fn (Ticket $t) => [
            $t->code, $t->title, $cfg['categories'][$t->category] ?? '', $t->item ? ($cfg['items'][$t->item] ?? '') : '',
            $cfg['priorities'][$t->priority] ?? '', $cfg['statuses'][$t->status][0] ?? '', $t->user->name, $t->department?->name,
            $t->department?->building, $t->technician?->name, $t->created_at->format('Y-m-d H:i'),
            $t->resolved_at?->format('Y-m-d H:i'), $t->due_at?->format('Y-m-d H:i'), $t->is_overdue ? 'نعم' : 'لا', $t->rating,
        ]);
        return XlsxExport::download('tickets-' . date('Ymd-His') . '.xlsx',
            ['الرقم', 'العنوان', 'النوع', 'المستلزم', 'الأولوية', 'الحالة', 'مقدم الطلب', 'الإدارة', 'المبنى', 'التكنيكال', 'تاريخ الإنشاء', 'تاريخ الحل', 'الموعد المستهدف', 'متأخر', 'التقييم'],
            $rows, 'Tickets');
    }
}
