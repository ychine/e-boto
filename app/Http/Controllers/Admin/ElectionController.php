<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Election;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ElectionController extends Controller
{
    public function __construct(public AuditLogger $auditLogger) {}

    public function index(Request $request): Response
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

        $attendanceOptions = Election::orderBy('title')
            ->get(['id', 'title']);

        $selectedElectionId = $request->integer('attendance_election_id') ?: $attendanceOptions->first()?->id;

        return Inertia::render('Admin/Elections/Index', [
            'elections' => $elections,
            'attendanceReport' => [
                'selectedElectionId' => $selectedElectionId,
                'options' => $attendanceOptions,
                'summary' => $this->buildAttendanceSummary($selectedElectionId),
            ],
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

    public function exportAttendance(Election $election): StreamedResponse
    {
        $filename = sprintf(
            'attendance-%s-%s.csv',
            Str::slug($election->title ?: 'election'),
            now()->format('Ymd-His'),
        );

        return response()->streamDownload(function () use ($election): void {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Election', $election->title]);
            fputcsv($handle, []);

            fputcsv($handle, [
                'Student ID',
                'LRN',
                'First Name',
                'Last Name',
                'Email',
                'Course',
                'Section',
                'Year Level',
                'Gender',
                'Location',
                'Voted At',
            ]);

            Attendance::with('user')
                ->where('election_id', $election->id)
                ->orderBy('voted_at')
                ->chunk(200, function ($rows) use ($handle): void {
                    foreach ($rows as $attendance) {
                        $user = $attendance->user;

                        fputcsv($handle, [
                            $user?->student_id,
                            $user?->lrn,
                            $user?->first_name,
                            $user?->last_name,
                            $user?->email,
                            $attendance->course ?? $user?->course,
                            $user?->section,
                            $user?->year_level,
                            $user?->gender,
                            $user?->location,
                            optional($attendance->voted_at)->toDateTimeString(),
                        ]);
                    }
                });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function buildAttendanceSummary(?int $electionId): ?array
    {
        if (! $electionId) {
            return null;
        }

        $totalRegistered = User::where('role', 'student')->count();

        $attendanceCount = Attendance::where('election_id', $electionId)->count();

        $courseBreakdown = Attendance::query()
            ->leftJoin('users', 'users.id', '=', 'attendances.user_id')
            ->select([
                DB::raw('COALESCE(attendances.course, users.course, "Unspecified") as course_name'),
                DB::raw('COUNT(*) as count'),
            ])
            ->where('attendances.election_id', $electionId)
            ->groupBy('course_name')
            ->orderBy('course_name')
            ->get()
            ->map(fn ($row) => [
                'course' => $row->course_name,
                'count' => (int) $row->count,
            ])
            ->values();

        $absent = max(0, $totalRegistered - $attendanceCount);

        return [
            'electionId' => $electionId,
            'totalRegistered' => $totalRegistered,
            'attended' => $attendanceCount,
            'absent' => $absent,
            'attendanceRate' => $totalRegistered > 0
                ? round(($attendanceCount / $totalRegistered) * 100, 1)
                : 0,
            'courseBreakdown' => $courseBreakdown,
            'generatedAt' => now()->toDateTimeString(),
        ];
    }
}
