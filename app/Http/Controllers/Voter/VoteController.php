<?php

namespace App\Http\Controllers\Voter;

use App\Http\Controllers\Controller;
use App\Http\Requests\Voter\CastVoteRequest;
use App\Models\Candidate;
use App\Models\Election;
use App\Models\Position;
use App\Models\Vote;
use Illuminate\Http\RedirectResponse;
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

        $candidate = Candidate::with(['position.election'])
            ->whereKey($data['candidate_id'])
            ->firstOrFail();

        $position = $candidate->position;
        $election = $position?->election;

        if (! $position || ! $election) {
            throw ValidationException::withMessages([
                'candidate_id' => 'The selected candidate is not available for voting.',
            ]);
        }

        if ((int) $position->id !== (int) $data['position_id']) {
            throw ValidationException::withMessages([
                'position_id' => 'The selected candidate does not belong to this position.',
            ]);
        }

        if ((int) $election->id !== (int) $data['election_id']) {
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
            ->where('voter_id', $user->id)
            ->exists();

        if ($alreadyVoted) {
            throw ValidationException::withMessages([
                'candidate_id' => 'You have already cast a vote for this position.',
            ]);
        }

        DB::transaction(function () use ($user, $candidate, $position, $election) {
            Vote::create([
                'election_id' => $election->id,
                'position_id' => $position->id,
                'candidate_id' => $candidate->id,
                'voter_id' => $user->id,
                'encrypted_vote' => null,
            ]);

            $user->voter()->firstOrCreate([], [
                'is_allowed' => true,
                'times_voted' => 0,
            ])->increment('times_voted');
        });

        return redirect()
            ->route('dashboard')
            ->with('success', 'Your vote has been recorded.');
    }
}
