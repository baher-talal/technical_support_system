<div class="table-responsive"><table class="table table-sm table-hover align-middle mb-0">
<thead><tr><th>الرقم</th><th>العنوان</th><th>النوع</th><th>مقدم الطلب</th><th>الإدارة</th><th>الأولوية</th><th>الحالة</th><th>التاريخ</th></tr></thead><tbody>
@forelse($tickets as $t) @php [$sl,$sc] = config('helpdesk.statuses')[$t->status]; @endphp
<tr><td><a href="{{ route('tickets.show',$t) }}">{{ $t->code }}</a></td>
<td>{{ $t->title }} @if(in_array($t->status, \App\Models\Ticket::OPEN) && $t->is_overdue)<span class="badge text-bg-danger">متأخر</span>@endif</td>
<td>{{ config('helpdesk.categories')[$t->category] }}</td><td>{{ $t->user->name }}</td><td>{{ $t->department?->name }}</td>
<td>{{ config('helpdesk.priorities')[$t->priority] }}</td><td><span class="badge text-bg-{{ $sc }}">{{ $sl }}</span></td>
<td class="small text-nowrap">{{ $t->created_at->format('Y-m-d H:i') }}</td></tr>
@empty <tr><td colspan="8" class="text-center text-body-secondary py-4">لا توجد تيكيتات</td></tr> @endforelse
</tbody></table></div>
