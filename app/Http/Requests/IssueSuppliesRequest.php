<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IssueSuppliesRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()->can('work', $this->route('ticket')); }

    public function rules(): array
    {
        return ['item' => 'required|string|max:100', 'qty' => 'required|integer|min:1|max:100'];
    }
}
