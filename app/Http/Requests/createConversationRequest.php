<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class createConversationRequest extends FormRequest
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
            //
            'unique_id' => 'required|numeric',
            'message' => 'required|string'

        ];
    }

    public function failedValidation(Validator $validator)
    {
        $data = ['message' => 'Validation Error',
            'errors' => $validator->errors()];
        throw new HttpResponseException(response()->json($data, 400));
    }
}
