<?php

use App\Models\Candidate;
use App\Models\Election;
use App\Models\Position;
use App\Models\User;
use App\Models\Vote;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected to the login page', function () {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
});

test('admins are redirected to the admin dashboard', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'status' => 'approved',
    ]);

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertRedirect(route('admin.dashboard'));
});

test('voters see active elections and history data on dashboard', function () {
    $student = User::factory()->create([
        'role' => 'student',
        'status' => 'approved',
    ]);

    $election = Election::create([
        'title' => 'General Election',
        'description' => 'Test election',
        'is_active' => true,
        'starts_at' => now()->subDay(),
        'ends_at' => now()->addDay(),
        'created_by' => $student->id,
    ]);

    $position = Position::create([
        'name' => 'President',
        'description' => 'Lead officer',
        'max_votes' => 1,
        'election_id' => $election->id,
    ]);

    $candidate = Candidate::create([
        'position_id' => $position->id,
        'name' => 'Alex Candidate',
        'status' => 'active',
    ]);

    Vote::create([
        'election_id' => $election->id,
        'position_id' => $position->id,
        'candidate_id' => $candidate->id,
        'voter_id' => $student->id,
        'encrypted_vote' => null,
    ]);

    $this->actingAs($student)
        ->get(route('dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->has('activeElections', 1)
            ->has('historyStats', fn (Assert $stats) => $stats
                ->where('totalVotes', 1)
                ->where('electionsParticipated', 1)
            )
            ->where('fullHistory.0.candidate', 'Alex Candidate')
        );
});