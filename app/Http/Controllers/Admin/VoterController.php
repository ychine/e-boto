<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreVoterRequest;
use App\Http\Requests\Admin\UpdateVoterRequest;
use App\Models\Course;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class VoterController extends Controller
{
    public function __construct(public AuditLogger $auditLogger) {}

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
                    'student_id' => $user->student_id,
                    'lrn' => $user->lrn,
                    'phone' => $user->phone,
                    'course' => $user->course,
                    'section' => $user->section,
                    'year_level' => $user->year_level,
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

        return Inertia::render('Admin/Voters/Index', [
            'voters' => $voters,
            'totalVoters' => $totalVoters,
            'courses' => Course::orderBy('name')
                ->get(['id', 'name']),
        ]);
    }

    public function store(StoreVoterRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => trim($validated['first_name'].' '.$validated['last_name']),
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'email_verified_at' => now(),
            'password' => $validated['password'],
            'role' => 'student',
            'status' => 'approved',
            'student_id' => $validated['student_id'],
            'lrn' => $validated['lrn'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'course' => strtoupper($validated['course']),
            'section' => $validated['section'] ?? null,
            'year_level' => (string) $validated['year_level'],
            'age_group' => $validated['age_group'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'location' => $validated['location'] ?? null,
        ]);

        $user->voter()->create([
            'is_allowed' => true,
        ]);

        $this->auditLogger->log(
            action: 'voter.created',
            title: 'Registered voter',
            description: sprintf('Registered %s as a voter', $user->name),
            model: $user,
            changes: [
                'attributes' => $user->only([
                    'email',
                    'student_id',
                    'course',
                    'year_level',
                    'section',
                    'age_group',
                ]),
            ],
        );

        return redirect()
            ->route('admin.voters.index')
            ->with('success', 'Voter registered successfully.');
    }

    public function update(UpdateVoterRequest $request, User $user): RedirectResponse
    {
        $validated = $request->validated();

        $original = $user->only([
            'first_name',
            'last_name',
            'email',
            'student_id',
            'lrn',
            'phone',
            'course',
            'section',
            'year_level',
            'age_group',
            'gender',
            'location',
            'status',
        ]);

        $user->update([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'name' => trim($validated['first_name'].' '.$validated['last_name']),
            'email' => $validated['email'],
            'student_id' => $validated['student_id'],
            'lrn' => $validated['lrn'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'course' => strtoupper($validated['course']),
            'section' => $validated['section'] ?? null,
            'year_level' => (string) $validated['year_level'],
            'age_group' => $validated['age_group'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'location' => $validated['location'] ?? null,
            'status' => $validated['status'],
        ]);

        if ($validated['status'] === 'approved' && $user->voter) {
            $user->voter->update(['is_allowed' => true]);
        }

        if ($user->wasChanged()) {
            $this->auditLogger->log(
                action: 'voter.updated',
                title: 'Updated voter details',
                description: sprintf('Updated %s', $user->name),
                model: $user,
                changes: [
                    'before' => $original,
                    'after' => $user->only(array_keys($original)),
                ],
            );
        }

        return redirect()
            ->route('admin.voters.index')
            ->with('success', 'Voter updated successfully.');
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
