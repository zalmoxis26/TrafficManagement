<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PedimentoRequest extends FormRequest
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
			'numPedimento' => 'required|string',
			'aduana' => 'string',
			'patente' => 'string',
			'clavePed' => 'string',
			'fechaPed' => 'required',
			'adjunto' =>  'nullable|file|mimes:pdf,jpg,jpeg,png|max:3000', 
        ];
    }
}


