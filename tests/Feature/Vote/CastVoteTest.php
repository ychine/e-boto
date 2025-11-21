<?php

use App\Models\Attendance;
use App\Models\Candidate;
use App\Models\Election;
use App\Models\Position;
use App\Models\User;
use App\Models\Vote;

test('student can cast a vote for an active election', function () {
    $student = User::factory()->create([
        'role' => 'student',
        'status' => 'approved',
    ]);

    $election = Election::create([
        'title' => 'Campus Election',
        'is_active' => true,
        'starts_at' => now()->subDay(),
        'ends_at' => now()->addDay(),
        'created_by' => $student->id,
    ]);

    $position = Position::create([
        'name' => 'Secretary',
        'description' => 'Records minutes',
        'max_votes' => 1,
        'election_id' => $election->id,
    ]);

    $candidate = Candidate::create([
        'position_id' => $position->id,
        'name' => 'Jamie Rivers',
        'status' => 'active',
    ]);

    $this->actingAs($student)
        ->post(route('votes.store'), [
            'election_id' => $election->id,
            'position_id' => $position->id,
            'candidate_id' => $candidate->id,
        ])
        ->assertRedirect(route('dashboard'));

    expect(
        Vote::where('voter_id', $student->id)
            ->where('position_id', $position->id)
            ->exists(),
    )->toBeTrue();

    $student->refresh();

    expect($student->voter)->not->toBeNull()
        ->and($student->voter->times_voted)->toBe(1);

    expect(
        Attendance::where('user_id', $student->id)
            ->where('election_id', $election->id)
            ->exists(),
    )->toBeTrue();
});

test('student cannot vote twice for the same position', function () {
    $student = User::factory()->create([
        'role' => 'student',
        'status' => 'approved',
    ]);

    $election = Election::create([
        'title' => 'Midterm Election',
        'is_active' => true,
        'starts_at' => now()->subDay(),
        'ends_at' => now()->addDay(),
        'created_by' => $student->id,
    ]);

    $position = Position::create([
        'name' => 'Treasurer',
        'description' => 'Handles finances',
        'max_votes' => 1,
        'election_id' => $election->id,
    ]);

    $candidate = Candidate::create([
        'position_id' => $position->id,
        'name' => 'Jordan Lee',
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
        ->from(route('dashboard'))
        ->post(route('votes.store'), [
            'election_id' => $election->id,
            'position_id' => $position->id,
            'candidate_id' => $candidate->id,
        ])
        ->assertSessionHasErrors('candidate_id');
});

test('student can cast multiple votes in a single submission', function () {
    $student = User::factory()->create([
        'role' => 'student',
        'status' => 'approved',
    ]);

    $election = Election::create([
        'title' => 'General Election',
        'is_active' => true,
        'starts_at' => now()->subDay(),
        'ends_at' => now()->addDay(),
        'created_by' => $student->id,
    ]);

    $president = Position::create([
        'name' => 'President',
        'description' => 'Leads the council',
        'max_votes' => 1,
        'election_id' => $election->id,
    ]);

    $secretary = Position::create([
        'name' => 'Secretary',
        'description' => 'Records minutes',
        'max_votes' => 1,
        'election_id' => $election->id,
    ]);

    $presCandidate = Candidate::create([
        'position_id' => $president->id,
        'name' => 'Taylor Green',
        'status' => 'active',
    ]);

    $secCandidate = Candidate::create([
        'position_id' => $secretary->id,
        'name' => 'Morgan Blue',
        'status' => 'active',
    ]);

    $this->actingAs($student)
        ->post(route('votes.bulk'), [
            'election_id' => $election->id,
            'votes' => [
                [
                    'position_id' => $president->id,
                    'candidate_id' => $presCandidate->id,
                ],
                [
                    'position_id' => $secretary->id,
                    'candidate_id' => $secCandidate->id,
                ],
            ],
        ])
        ->assertRedirect(route('dashboard'));

    expect(
        Vote::where('voter_id', $student->id)
            ->where('election_id', $election->id)
            ->count(),
    )->toBe(2);

    $student->refresh();
    expect($student->voter?->times_voted)->toBe(2);

    expect(
        Attendance::where('user_id', $student->id)
            ->where('election_id', $election->id)
            ->exists(),
    )->toBeTrue();
});
