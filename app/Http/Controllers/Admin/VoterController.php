<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Voter;
use App\Support\AuditLogger;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class VoterController extends Controller
{
    public function __construct(public AuditLogger $auditLogger)
    {
    }

    public function index(): Response
    {
        $voters = User::where('role', 'student')
            ->with('voter')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'email' => $user->email,
                    'age_group' => $user->age_group,
                    'gender' => $user->gender,
                    'location' => $user->location,
                    'status' => $user->status,
                    'times_voted' => $user->voter?->times_voted ?? 0,
                    'last_login' => $user->last_login?->format('Y-m-d H:i:s'),
                    'registered_date' => $user->created_at->format('Y-m-d H:i:s'),
                ];
            });

        $totalVoters = User::where('role', 'student')->count();
        $pendingApprovals = User::where('role', 'student')
            ->where('status', 'pending')
            ->count();

        return Inertia::render('Admin/Voters/Index', [
            'voters' => $voters,
            'totalVoters' => $totalVoters,
            'pendingApprovals' => $pendingApprovals,
        ]);
    }

    public function updateStatus(Request $request, User $user)
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:pending,approved,rejected'],
        ]);

        $previousStatus = $user->status;

        $user->update(['status' => $validated['status']]);

        if ($validated['status'] === 'approved' && $user->voter) {
            $user->voter->update(['is_allowed' => true]);
        }

        if ($previousStatus !== $validated['status']) {
            $this->auditLogger->log(
                action: 'voter.status.updated',
                title: 'Updated voter approval status',
                description: sprintf(
                    'Status changed from %s to %s',
                    $previousStatus,
                    $validated['status'],
                ),
                model: $user,
                changes: [
                    'before' => ['status' => $previousStatus],
                    'after' => ['status' => $validated['status']],
                ],
            );
        }

        return redirect()->route('admin.voters.index');
    }
}
