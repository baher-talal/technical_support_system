<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Services\TicketWorkflow;

class ApproveTicketRequest extends FormRequest
{
    public function authorize(): bool { return true; } // الصلاحية على مستوى الـ route (tickets.approve)

    public function rules(): array
    {
        return [
            'technician_id' => ['required', 'integer', function ($attr, $value, $fail) {
                if (!app(TicketWorkflow::class)->assignees($this->user())->contains('id', (int) $value)) $fail('المسؤول المختار غير مسموح به');
            }],
            'note' => 'nullable|string|max:1000',
        ];
    }
}
