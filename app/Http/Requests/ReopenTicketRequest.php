<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReopenTicketRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()->can('reopen', $this->route('ticket')); }

    public function rules(): array { return ['note' => 'required|string|max:1000']; }
}
