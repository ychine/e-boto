<?php

namespace App\Http\Requests\Admin;

use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVoterRequest extends FormRequest
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
        $user = $this->route('user');
        $userId = $user instanceof User ? $user->id : null;

        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique(User::class, 'email')->ignore($userId),
            ],
            'student_id' => [
                'required',
                'string',
                'max:255',
                Rule::unique(User::class, 'student_id')->ignore($userId),
            ],
            'lrn' => [
                'nullable',
                'digits:12',
                Rule::unique(User::class, 'lrn')->ignore($userId),
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
            'status' => ['required', 'string', 'in:pending,approved,rejected'],
        ];
    }
}
