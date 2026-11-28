<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        abort_unless($this->forOthers() || $this->user()->department_id, 403, 'حسابك غير مرتبط بإدارة، تواصل مع مدير النظام');
        return true;
    }

    public function forOthers(): bool { return $this->user()->hasPermission('tickets.create_for_others'); }

    /** إدارة التيكيت: يحددها صاحب صلاحية «تيكيت لأي مستخدم»، وغيره إدارته هو */
    public function departmentId(): int
    {
        return $this->forOthers() ? (int) $this->input('department_id') : (int) $this->user()->department_id;
    }

    public function rules(): array
    {
        $deptId = $this->departmentId();
        $rules = [
            'category' => 'required|in:repair,supplies',
            'item' => 'nullable|required_if:category,supplies|in:' . implode(',', array_keys(config('helpdesk.items'))),
            'title' => 'required|string|max:150',
            'description' => 'nullable|string|max:3000',
            'priority' => 'required|in:' . implode(',', array_keys(config('helpdesk.priorities'))),
            'device_id' => ['nullable', Rule::exists('devices', 'id')->where('department_id', $deptId)->where('type', 'company')],
        ];
        if ($this->forOthers()) {
            $rules['department_id'] = 'required|exists:departments,id';
            // مقدم الطلب هو المستخدم نفسه دائما، إلا لصاحب صلاحية «تيكيت لأي مستخدم»
            $rules['requester_id'] = ['nullable', Rule::exists('users', 'id')->where('is_active', true)->where('department_id', $deptId)];
        }
        return $rules;
    }
}
