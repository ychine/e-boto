<?php

namespace App\Http\Requests\Admin;

use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreVoterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('access-admin') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique(User::class, 'email'),
            ],
            'student_id' => [
                'required',
                'string',
                'max:255',
                Rule::unique(User::class, 'student_id'),
            ],
            'lrn' => [
                'nullable',
                'digits:12',
                Rule::unique(User::class, 'lrn'),
            ],
            'phone' => ['nullable', 'string', 'max:255'],
            'course' => [
                'required',
                'string',
                Rule::exists(Course::class, 'name'),
            ],
            'section' => ['nullable', 'string', 'max:255'],
            'year_level' => ['required', 'integer', 'min:1', 'max:4'],
            'age_group' => ['nullable', 'string', 'max:255'],
            'gender' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ];
    }
}
