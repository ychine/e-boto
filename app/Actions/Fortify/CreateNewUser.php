<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'password' => $this->passwordRules(),
            'role' => ['nullable', 'string', 'in:admin,student'],
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'year_level' => ['nullable', 'string'],
            'age_group' => ['nullable', 'string'],
            'gender' => ['nullable', 'string'],
            'location' => ['nullable', 'string'],
        ])->validate();

        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => $input['password'],
            'role' => $input['role'] ?? 'student',
            'first_name' => $input['first_name'] ?? null,
            'last_name' => $input['last_name'] ?? null,
            'year_level' => $input['year_level'] ?? null,
            'age_group' => $input['age_group'] ?? null,
            'gender' => $input['gender'] ?? null,
            'location' => $input['location'] ?? null,
            'status' => ($input['role'] ?? 'student') === 'admin' ? 'approved' : 'pending',
        ]);

        // Create voter record for students
        if ($user->role === 'student') {
            $user->voter()->create([
                'is_allowed' => false,
            ]);
        }

        return $user;
    }
}
