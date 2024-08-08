<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateProjetRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "user_id" => ["sometimes", "exists:users,id"],
            "nom" => ["sometimes", "string", "max:255"],
            "description" => ["sometimes", "string"],
            "statut" => ["sometimes", "in:en-attente,en cours,terminé"],
            "date_debut" => ["sometimes", "date", "after_or_equal:today", "before_or_equal:date_fin"],
            "date_fin" => ["sometimes", "date", "after_or_equal:date_debut"],
            "budget" => ["sometimes", "numeric", "min:0"],
            "etat" => ["sometimes", "in:approuvé,rejeté"],
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json(
            ['success' => false, 'errors' => $validator->errors()],
            422
        ));
    }
}

