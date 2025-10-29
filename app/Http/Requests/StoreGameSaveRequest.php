<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGameSaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'slot' => 'nullable|integer|min:1',
            'content' => 'required|array',
        ];
    }

    public function messages(): array
    {
        return [
            'content.required' => 'Le contenu de la sauvegarde est requis',
            'content.array' => 'Le contenu doit être un objet JSON valide',
            'slot.integer' => 'Le slot doit être un nombre entier',
            'slot.min' => 'Le slot doit être supérieur ou égal à 1',
        ];
    }
}
