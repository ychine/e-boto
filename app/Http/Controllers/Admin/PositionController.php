<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Election;
use App\Models\Position;
use App\Support\AuditLogger;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PositionController extends Controller
{
    public function __construct(public AuditLogger $auditLogger)
    {
    }

    public function index(): Response
    {
        $positions = Position::with(['election'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($position) {
                return [
                    'id' => $position->id,
                    'name' => $position->name,
                    'election' => $position->election->title ?? 'N/A',
                    'description' => $position->description,
                    'max_votes' => $position->max_votes,
                ];
            });

        $elections = Election::orderBy('title')->get(['id', 'title']);

        return Inertia::render('Admin/Positions/Index', [
            'positions' => $positions,
            'elections' => $elections,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'election_id' => ['required', 'exists:elections,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'max_votes' => ['required', 'integer', 'min:1'],
        ]);

        $position = Position::create($validated);

        $this->auditLogger->log(
            action: 'position.created',
            title: 'Created position',
            description: sprintf('Position "%s" added.', $position->name),
            model: $position,
            changes: [
                'attributes' => $position->only(['name', 'election_id', 'max_votes']),
            ],
        );

        return redirect()->route('admin.positions.index');
    }

    public function update(Request $request, Position $position)
    {
        $validated = $request->validate([
            'election_id' => ['required', 'exists:elections,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'max_votes' => ['required', 'integer', 'min:1'],
        ]);

        $original = $position->only(['election_id', 'name', 'description', 'max_votes']);

        $position->update($validated);

        $this->auditLogger->log(
            action: 'position.updated',
            title: 'Updated position',
            description: sprintf('Position "%s" updated.', $position->name),
            model: $position->refresh(),
            changes: [
                'before' => $original,
                'after' => $position->only(['election_id', 'name', 'description', 'max_votes']),
            ],
        );

        return redirect()->route('admin.positions.index');
    }

    public function destroy(Position $position)
    {
        $snapshot = $position->only(['name', 'election_id']);
        $position->delete();

        $this->auditLogger->log(
            action: 'position.deleted',
            title: 'Deleted position',
            description: sprintf('Position "%s" removed.', $snapshot['name']),
            model: $position,
            changes: [
                'before' => $snapshot,
            ],
        );

        return redirect()->route('admin.positions.index');
    }
}
