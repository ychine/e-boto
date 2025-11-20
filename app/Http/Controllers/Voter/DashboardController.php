<?php

namespace App\Http\Controllers\Voter;

use App\Http\Controllers\Controller;
use App\Models\Election;
use App\Models\Vote;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): Response|RedirectResponse
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        $userVotes = Vote::with(['election', 'position', 'candidate'])
            ->where('voter_id', $user->id)
            ->orderByDesc('created_at')
            ->get();

        $activeElections = Election::with([
            'positions' => fn ($query) => $query->orderBy('name'),
            'positions.candidates' => fn ($query) => $query->where('status', 'active')->orderBy('name'),
        ])
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            })
            ->orderBy('starts_at')
            ->get()
            ->map(function (Election $election) use ($userVotes) {
                return [
                    'id' => $election->id,
                    'title' => $election->title,
                    'description' => $election->description,
                    'starts_at' => $election->starts_at?->toDateTimeString(),
                    'ends_at' => $election->ends_at?->toDateTimeString(),
                    'positions' => $election->positions
                        ->filter(fn ($position) => $position->candidates->isNotEmpty())
                        ->map(function ($position) use ($userVotes) {
                            $vote = $userVotes->firstWhere('position_id', $position->id);

                            return [
                                'id' => $position->id,
                                'name' => $position->name,
                                'max_votes' => $position->max_votes,
                                'has_voted' => (bool) $vote,
                                'current_vote' => $vote ? [
                                    'candidate_name' => $vote->candidate?->name,
                                    'cast_at' => $vote->created_at->toDateTimeString(),
                                ] : null,
                                'candidates' => $position->candidates->map(fn ($candidate) => [
                                    'id' => $candidate->id,
                                    'name' => $candidate->name,
                                    'photo' => $candidate->photo
                                        ? Storage::url($candidate->photo)
                                        : null,
                                ])->values(),
                            ];
                        })
                        ->values(),
                ];
            })
            ->values();

        $formatVote = static fn (Vote $vote) => [
            'id' => $vote->id,
            'election' => $vote->election?->title ?? '—',
            'position' => $vote->position?->name ?? '—',
            'candidate' => $vote->candidate?->name ?? '—',
            'cast_at' => $vote->created_at->toDateTimeString(),
        ];

        $votingHistoryPreview = $userVotes->take(5)->map($formatVote)->values();
        $fullHistory = $userVotes->map($formatVote)->values();

        $historyStats = [
            'totalVotes' => $userVotes->count(),
            'electionsParticipated' => $userVotes->groupBy('election_id')->count(),
        ];

        return Inertia::render('Dashboard', [
            'activeElections' => $activeElections,
            'votingHistoryPreview' => $votingHistoryPreview,
            'historyStats' => $historyStats,
            'fullHistory' => $fullHistory,
        ]);
    }
}
