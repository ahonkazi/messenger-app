<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UserSignupRequest extends FormRequest
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
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'required|string|max:255|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'otp_code' => 'required|numeric',
            'date_of_birth' => 'required|date',
            'gender' => 'required|string',

        ];
    }

    public function failedValidation(Validator $validator)
    {
        $data = ['message' => 'Validation Error',
            'errors' => $validator->errors()];
        throw new HttpResponseException(response()->json($data, 400));
    }
}
