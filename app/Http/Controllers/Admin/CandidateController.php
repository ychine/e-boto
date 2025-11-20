<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Models\Position;
use App\Support\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class CandidateController extends Controller
{
    public function __construct(public AuditLogger $auditLogger)
    {
    }

    public function index(): Response
    {
        $candidates = Candidate::with(['position.election', 'user'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($candidate) {
                return [
                    'id' => $candidate->id,
                    'name' => $candidate->name,
                    'photo' => $candidate->photo ? Storage::url($candidate->photo) : null,
                    'election' => $candidate->position?->election?->title ?? 'N/A',
                    'position' => $candidate->position?->name ?? 'N/A',
                    'biography' => $candidate->biography,
                    'status' => $candidate->status,
                ];
            });

        $positions = Position::with('election')
            ->orderBy('name')
            ->get()
            ->map(function ($position) {
                return [
                    'id' => $position->id,
                    'name' => $position->name,
                    'election' => $position->election->title ?? 'N/A',
                ];
            });

        $totalCandidates = Candidate::count();
        $totalElections = \App\Models\Election::count();

        return Inertia::render('Admin/Candidates/Index', [
            'candidates' => $candidates,
            'positions' => $positions,
            'totalCandidates' => $totalCandidates,
            'totalElections' => $totalElections,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'position_id' => ['required', 'exists:positions,id'],
            'name' => ['required', 'string', 'max:255'],
            'biography' => ['nullable', 'string'],
            'status' => ['required', 'string', 'in:active,inactive'],
            'photo' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('candidates', 'public');
        }

        $candidate = Candidate::create($validated);

        $this->auditLogger->log(
            action: 'candidate.created',
            title: 'Created candidate',
            description: sprintf('Candidate "%s" added.', $candidate->name),
            model: $candidate,
            changes: [
                'attributes' => $candidate->only(['name', 'position_id', 'status']),
            ],
        );

        return redirect()->route('admin.candidates.index');
    }

    public function update(Request $request, Candidate $candidate)
    {
        $validated = $request->validate([
            'position_id' => ['required', 'exists:positions,id'],
            'name' => ['required', 'string', 'max:255'],
            'biography' => ['nullable', 'string'],
            'status' => ['required', 'string', 'in:active,inactive'],
            'photo' => ['nullable', 'image', 'max:2048'],
        ]);

        $original = $candidate->only(['position_id', 'name', 'status', 'photo']);

        if ($request->hasFile('photo')) {
            if ($candidate->photo) {
                Storage::disk('public')->delete($candidate->photo);
            }
            $validated['photo'] = $request->file('photo')->store('candidates', 'public');
        }

        $candidate->update($validated);

        $this->auditLogger->log(
            action: 'candidate.updated',
            title: 'Updated candidate',
            description: sprintf('Candidate "%s" updated.', $candidate->name),
            model: $candidate->refresh(),
            changes: [
                'before' => $original,
                'after' => $candidate->only(['position_id', 'name', 'status', 'photo']),
            ],
        );

        return redirect()->route('admin.candidates.index');
    }

    public function destroy(Candidate $candidate)
    {
        $snapshot = $candidate->only(['name', 'position_id']);
        if ($candidate->photo) {
            Storage::disk('public')->delete($candidate->photo);
        }
        $candidate->delete();

        $this->auditLogger->log(
            action: 'candidate.deleted',
            title: 'Deleted candidate',
            description: sprintf('Candidate "%s" removed.', $snapshot['name']),
            model: $candidate,
            changes: [
                'before' => $snapshot,
            ],
        );

        return redirect()->route('admin.candidates.index');
    }
}
