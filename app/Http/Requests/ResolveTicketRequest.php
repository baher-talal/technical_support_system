<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResolveTicketRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()->can('work', $this->route('ticket')); }

    public function rules(): array { return ['note' => 'required|string|max:2000']; }
}
