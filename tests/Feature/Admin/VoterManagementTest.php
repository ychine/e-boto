<?php

use App\Models\User;

test('admin can register a voter via the modal endpoint', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'status' => 'approved',
    ]);

    $payload = [
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'email' => 'jane@example.com',
        'student_id' => 'SID001',
        'lrn' => '123456789012',
        'phone' => '09171234567',
        'course' => 'BSIT',
        'section' => 'A',
        'year_level' => 2,
        'age_group' => '18-20',
        'gender' => 'female',
        'location' => 'Campus',
        'password' => 'password',
        'password_confirmation' => 'password',
    ];

    $response = $this->actingAs($admin)->post(route('admin.voters.store'), $payload);

    $response->assertRedirect(route('admin.voters.index'));

    $createdUser = User::where('email', 'jane@example.com')->first();

    $this->assertNotNull($createdUser);

    $this->assertDatabaseHas('users', [
        'email' => 'jane@example.com',
        'course' => 'BSIT',
        'status' => 'approved',
    ]);

    $this->assertDatabaseHas('voters', [
        'user_id' => $createdUser->id,
        'is_allowed' => true,
    ]);
});

test('non admins cannot access voter registration endpoint', function () {
    $student = User::factory()->create([
        'role' => 'student',
        'status' => 'approved',
    ]);

    $this->actingAs($student)
        ->post(route('admin.voters.store'), [
            'first_name' => 'Sam',
            'last_name' => 'Lee',
            'email' => 'sam@example.com',
            'student_id' => 'SID999',
            'course' => 'BSIT',
            'year_level' => '1',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])
        ->assertForbidden();
});

test('admin can update a voter profile', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'status' => 'approved',
    ]);

    $voter = User::factory()->create([
        'role' => 'student',
        'status' => 'pending',
        'course' => 'BSIT',
        'year_level' => '1',
    ]);

    $payload = [
        'first_name' => 'Updated',
        'last_name' => 'Student',
        'email' => 'updated@example.com',
        'student_id' => 'SID777',
        'lrn' => '987654321000',
        'phone' => '09181234567',
        'course' => 'BSIT',
        'section' => 'B',
        'year_level' => 3,
        'age_group' => '21-23',
        'gender' => 'male',
        'location' => 'North Campus',
        'status' => 'approved',
    ];

    $this->actingAs($admin)
        ->put(route('admin.voters.update', $voter), $payload)
        ->assertRedirect(route('admin.voters.index'));

    $this->assertDatabaseHas('users', [
        'id' => $voter->id,
        'email' => 'updated@example.com',
        'student_id' => 'SID777',
        'course' => 'BSIT',
        'status' => 'approved',
    ]);
});

test('non admins cannot update a voter profile', function () {
    $student = User::factory()->create([
        'role' => 'student',
        'status' => 'approved',
    ]);

    $voter = User::factory()->create([
        'role' => 'student',
        'status' => 'pending',
    ]);

    $this->actingAs($student)
        ->put(route('admin.voters.update', $voter), [
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => $voter->email,
            'student_id' => $voter->student_id,
            'course' => 'BSIT',
            'year_level' => 1,
            'status' => 'approved',
        ])
        ->assertForbidden();
});
