<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RateTicketRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()->can('rate', $this->route('ticket')); }

    public function rules(): array
    {
        return ['rating' => 'required|integer|between:1,5', 'rating_note' => 'nullable|string|max:500'];
    }
}
