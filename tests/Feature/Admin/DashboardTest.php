<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('admin dashboard includes pending approvals summary', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'status' => 'approved',
    ]);

    $pending = User::factory()->create([
        'role' => 'student',
        'status' => 'pending',
        'first_name' => 'Pending',
        'last_name' => 'Student',
        'course' => 'BSIT',
        'year_level' => '3rd Year',
    ]);

    User::factory()->create([
        'role' => 'student',
        'status' => 'approved',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Dashboard')
            ->where('pendingApprovalsCount', 1)
            ->has('pendingApprovals', 1, fn (Assert $approval) => $approval
                ->where('id', $pending->id)
                ->where('email', $pending->email)
                ->where('course', 'BSIT')
                ->etc()
            )
        );
});


