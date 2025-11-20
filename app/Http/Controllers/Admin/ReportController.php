<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Election;
use App\Models\Vote;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    public function index(Request $request): Response
    {
        $electionId = $request->get('election_id');

        $elections = Election::orderBy('title')->get(['id', 'title']);

        $stats = [
            'total_voters' => User::where('role', 'student')->count(),
            'total_votes' => Vote::when($electionId, fn($q) => $q->where('election_id', $electionId))->count(),
            'voter_turnout' => 0,
        ];

        if ($electionId) {
            $election = Election::find($electionId);
            $totalVoters = User::where('role', 'student')->where('status', 'approved')->count();
            $votedCount = Vote::where('election_id', $electionId)
                ->distinct('voter_id')
                ->count('voter_id');
            $stats['voter_turnout'] = $totalVoters > 0 ? round(($votedCount / $totalVoters) * 100, 2) : 0;
        }

        return Inertia::render('Admin/Reports/Index', [
            'elections' => $elections,
            'stats' => $stats,
            'selectedElectionId' => $electionId,
        ]);
    }
}
