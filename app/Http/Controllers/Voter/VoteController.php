<?php

namespace App\Http\Controllers\Voter;

use App\Http\Controllers\Controller;
use App\Http\Requests\Voter\BulkCastVoteRequest;
use App\Http\Requests\Voter\CastVoteRequest;
use App\Models\Attendance;
use App\Models\Candidate;
use App\Models\Election;
use App\Models\Position;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VoteController extends Controller
{
    /**
     * Store a newly created vote in storage.
     */
    public function store(CastVoteRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $user = $request->user();

        $voteContext = $this->prepareVoteContext(
            $user->id,
            $data['election_id'],
            $data['position_id'],
            $data['candidate_id'],
        );

        DB::transaction(function () use ($user, $voteContext) {
            $this->persistVotes($user->id, collect([$voteContext]));
            $this->incrementVoteCount($user, 1);
            $this->recordAttendance($user->fresh(), $voteContext['election']);
        });

        return redirect()
            ->route('dashboard')
            ->with('success', 'Your vote has been recorded.');
    }

    public function bulkStore(BulkCastVoteRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $user = $request->user();

        $voteContexts = collect($data['votes'])
            ->map(function (array $vote) use ($user, $data) {
                return $this->prepareVoteContext(
                    $user->id,
                    $data['election_id'],
                    $vote['position_id'],
                    $vote['candidate_id'],
                );
            });

        if ($voteContexts->isEmpty()) {
            throw ValidationException::withMessages([
                'votes' => 'Please select at least one candidate.',
            ]);
        }

        $this->ensureUniquePositions($voteContexts);

        $election = $voteContexts->first()['election'];
        DB::transaction(function () use ($user, $voteContexts, $election) {
            $this->persistVotes($user->id, $voteContexts);
            $this->incrementVoteCount($user, $voteContexts->count());
            $this->recordAttendance($user->fresh(), $election);
        });

        return redirect()
            ->route('dashboard')
            ->with('success', 'Your votes have been recorded.');
    }

    /**
     * @return array{candidate: Candidate, position: Position, election: Election}
     */
    private function prepareVoteContext(int $userId, int $electionId, int $positionId, int $candidateId): array
    {
        $candidate = Candidate::with(['position.election'])
            ->findOrFail($candidateId);

        $position = $candidate->position;
        $election = $position?->election;

        if (! $position || ! $election) {
            throw ValidationException::withMessages([
                'candidate_id' => 'The selected candidate is not available for voting.',
            ]);
        }

        if ((int) $position->id !== $positionId) {
            throw ValidationException::withMessages([
                'position_id' => 'The selected candidate does not belong to this position.',
            ]);
        }

        if ((int) $election->id !== $electionId) {
            throw ValidationException::withMessages([
                'election_id' => 'The selected candidate does not belong to this election.',
            ]);
        }

        if (! $election->isActive()) {
            throw ValidationException::withMessages([
                'election_id' => 'This election is not currently accepting votes.',
            ]);
        }

        $alreadyVoted = Vote::where('election_id', $election->id)
            ->where('position_id', $position->id)
            ->where('voter_id', $userId)
            ->exists();

        if ($alreadyVoted) {
            throw ValidationException::withMessages([
                'candidate_id' => 'You have already cast a vote for this position.',
            ]);
        }

        return compact('candidate', 'position', 'election');
    }

    private function persistVotes(int $userId, Collection $voteContexts): void
    {
        foreach ($voteContexts as $context) {
            /** @var Candidate $candidate */
            $candidate = $context['candidate'];
            /** @var Position $position */
            $position = $context['position'];
            /** @var Election $election */
            $election = $context['election'];

            Vote::create([
                'election_id' => $election->id,
                'position_id' => $position->id,
                'candidate_id' => $candidate->id,
                'voter_id' => $userId,
                'encrypted_vote' => null,
            ]);
        }
    }

    private function incrementVoteCount(User $user, int $count): void
    {
        $user->voter()->firstOrCreate([], [
            'is_allowed' => true,
            'times_voted' => 0,
        ])->increment('times_voted', $count);
    }

    private function recordAttendance(User $user, Election $election): void
    {
        Attendance::updateOrCreate(
            [
                'user_id' => $user->id,
                'election_id' => $election->id,
            ],
            [
                'voted_at' => now(),
                'course' => $user->course,
                'section' => $user->section,
            ],
        );
    }

    private function ensureUniquePositions(Collection $voteContexts): void
    {
        $positionIds = $voteContexts->map(fn ($context) => $context['position']->id);

        if ($positionIds->duplicates()->isNotEmpty()) {
            throw ValidationException::withMessages([
                'votes' => 'You can only vote once per position.',
            ]);
        }
    }
}
