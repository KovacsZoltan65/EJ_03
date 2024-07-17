<?php

namespace App\Http\Requests;

use App\Models\Person;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePersonRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'string', 'lowercase', 'email', 'max:255',
                Rule::unique(Person::class)
            ],
            'password' => ['required',],
            'language' => ['required',],
            'birthdate' => ['required',],
            'note' => ['nullable'],
        ];
        /*
        return [
                 'name' => 'required|string',
                'email' => 'required',
             'password' => 'required',
             'language' => 'required',
            'birthdate' => 'required',
        ];
        */
    }
}
