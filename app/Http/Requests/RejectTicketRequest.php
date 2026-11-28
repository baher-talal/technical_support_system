<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RejectTicketRequest extends FormRequest
{
    public function authorize(): bool { return true; } // الصلاحية على مستوى الـ route (tickets.approve)

    public function rules(): array { return ['note' => 'required|string|max:1000']; }
}
