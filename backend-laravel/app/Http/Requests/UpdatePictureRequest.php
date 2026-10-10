<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePictureRequest extends FormRequest
{
    /**
     * Only the owner may edit a picture.
     */
    public function authorize(): bool
    {
        return $this->route('picture')->isOwnedBy($this->user());
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|max:255',
            'price' => 'required|integer|min:1|max:10000',
            'category_id' => 'required|integer|exists:categories,id',
        ];
    }
}
