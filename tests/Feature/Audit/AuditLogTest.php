<?php

use App\Models\AuditLog;
use App\Models\Candidate;
use App\Models\Election;
use App\Models\Position;
use App\Models\User;
use Illuminate\Auth\Events\Login;

test('login events are captured in audit logs', function () {
    $user = User::factory()->create();

    event(new Login('web', $user, false));

    expect(
        AuditLog::where('action', 'auth.login')
            ->where('user_id', $user->id)
            ->exists(),
    )->toBeTrue();
});

test('approving a voter records an audit log entry', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'status' => 'approved',
    ]);

    $student = User::factory()->create([
        'role' => 'student',
        'status' => 'pending',
    ]);

    $this->actingAs($admin)
        ->patch(route('admin.voters.update-status', $student), [
            'status' => 'approved',
        ])
        ->assertRedirect(route('admin.voters.index'));

    expect(
        AuditLog::where('action', 'voter.status.updated')
            ->where('model_id', $student->id)
            ->exists(),
    )->toBeTrue();
});

test('creating an election records an audit log entry', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'status' => 'approved',
    ]);

    $this->actingAs($admin)
        ->post(route('admin.elections.store'), [
            'title' => 'General Election',
            'description' => 'Test election',
            'start_date' => now()->addDay()->toDateString(),
            'end_date' => now()->addDays(2)->toDateString(),
        ])
        ->assertRedirect(route('admin.elections.index'));

    expect(
        AuditLog::where('action', 'election.created')
            ->where('user_id', $admin->id)
            ->exists(),
    )->toBeTrue();
});

test('updating a position records an audit log entry', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'status' => 'approved',
    ]);

    $election = Election::create([
        'title' => 'Board Election',
        'description' => 'Board',
        'starts_at' => now(),
        'ends_at' => now()->addDays(5),
        'is_active' => true,
        'created_by' => $admin->id,
    ]);

    $position = Position::create([
        'name' => 'President',
        'election_id' => $election->id,
        'description' => 'Lead role',
        'max_votes' => 1,
    ]);

    $this->actingAs($admin)
        ->patch(route('admin.positions.update', $position), [
            'name' => 'Student President',
            'election_id' => $election->id,
            'description' => 'Lead student role',
            'max_votes' => 1,
        ])
        ->assertRedirect(route('admin.positions.index'));

    expect(
        AuditLog::where('action', 'position.updated')
            ->where('model_id', $position->id)
            ->exists(),
    )->toBeTrue();
});

test('creating a candidate records an audit log entry', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'status' => 'approved',
    ]);

    $election = Election::create([
        'title' => 'Council Election',
        'description' => 'Council',
        'starts_at' => now(),
        'ends_at' => now()->addDays(3),
        'is_active' => true,
        'created_by' => $admin->id,
    ]);

    $position = Position::create([
        'name' => 'Secretary',
        'election_id' => $election->id,
        'description' => 'Keeps notes',
        'max_votes' => 1,
    ]);

    $this->actingAs($admin)
        ->post(route('admin.candidates.store'), [
            'position_id' => $position->id,
            'name' => 'Jane Doe',
            'status' => 'active',
            'biography' => 'Student leader',
        ])
        ->assertRedirect(route('admin.candidates.index'));

    $candidate = Candidate::where('name', 'Jane Doe')->first();

    expect(
        AuditLog::where('action', 'candidate.created')
            ->where('model_id', $candidate?->id)
            ->exists(),
    )->toBeTrue();
});

