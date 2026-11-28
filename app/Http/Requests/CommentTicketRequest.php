<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CommentTicketRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()->can('view', $this->route('ticket')); }

    public function rules(): array { return ['body' => 'required|string|max:2000']; }
}
