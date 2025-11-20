<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Election;
use App\Support\AuditLogger;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ElectionController extends Controller
{
    public function __construct(public AuditLogger $auditLogger)
    {
    }

    public function index(): Response
    {
        $elections = Election::with('creator')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($election) {
                $now = now();
                $status = 'inactive';
                
                // Check if election hasn't started yet
                if ($election->starts_at && $election->starts_at > $now) {
                    $status = 'upcoming';
                }
                // Check if election has ended
                elseif ($election->ends_at && $election->ends_at < $now) {
                    $status = 'ended';
                }
                // Check if election is within active date range
                elseif ($election->starts_at && $election->ends_at && $election->starts_at <= $now && $election->ends_at >= $now) {
                    // If is_active is true, show as active, otherwise inactive
                    $status = $election->is_active ? 'active' : 'inactive';
                }
                // Check if election is currently active (using isActive method)
                elseif ($election->isActive()) {
                    $status = 'active';
                }
                // Default to ended if we can't determine
                else {
                    $status = 'ended';
                }
                
                return [
                    'id' => $election->id,
                    'title' => $election->title,
                    'description' => $election->description,
                    'start_date' => $election->starts_at?->format('Y-m-d'),
                    'end_date' => $election->ends_at?->format('Y-m-d'),
                    'status' => $status,
                    'is_active' => $election->is_active,
                    'created_by' => $election->creator?->name ?? 'System',
                ];
            });

        return Inertia::render('Admin/Elections/Index', [
            'elections' => $elections,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
        ]);

        $election = Election::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'starts_at' => $validated['start_date'],
            'ends_at' => $validated['end_date'],
            'is_active' => true,
            'created_by' => auth()->id(),
        ]);

        $this->auditLogger->log(
            action: 'election.created',
            title: 'Created election',
            description: sprintf('Election "%s" scheduled.', $election->title),
            model: $election,
            changes: [
                'attributes' => $election->only(['title', 'starts_at', 'ends_at', 'is_active']),
            ],
        );

        return redirect()->route('admin.elections.index');
    }

    public function update(Request $request, Election $election)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'is_active' => ['required'],
        ]);

        $original = $election->only(['title', 'description', 'starts_at', 'ends_at', 'is_active']);

        $election->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'starts_at' => $validated['start_date'],
            'ends_at' => $validated['end_date'],
            'is_active' => filter_var($validated['is_active'], FILTER_VALIDATE_BOOLEAN),
        ]);

        $election->refresh();

        $this->auditLogger->log(
            action: 'election.updated',
            title: 'Updated election details',
            description: sprintf('Election "%s" was updated.', $election->title),
            model: $election,
            changes: [
                'before' => $original,
                'after' => $election->only(['title', 'description', 'starts_at', 'ends_at', 'is_active']),
            ],
        );

        return redirect()->route('admin.elections.index');
    }

    public function destroy(Election $election)
    {
        $snapshot = $election->only(['title', 'description', 'starts_at', 'ends_at', 'is_active']);
        $election->delete();

        $this->auditLogger->log(
            action: 'election.deleted',
            title: 'Deleted election',
            description: sprintf('Election "%s" was removed.', $snapshot['title']),
            model: $election,
            changes: [
                'before' => $snapshot,
            ],
        );

        return redirect()->route('admin.elections.index');
    }
}
