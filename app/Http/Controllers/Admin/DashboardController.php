<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Election;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $totalVoters = User::where('role', 'student')->count();
        $activeElections = Election::where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            })
            ->count();

        $upcomingElections = Election::where('starts_at', '>', now())
            ->orWhere(function ($query) {
                $query->where('is_active', false)
                    ->whereNull('starts_at');
            })
            ->orderBy('starts_at', 'asc')
            ->limit(10)
            ->get();

        $recentActivities = AuditLog::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'title' => $log->title,
                    'description' => $log->description,
                    'user_name' => $log->user_name ?? $log->user?->name ?? 'System',
                    'created_at' => $log->created_at->format('Y-m-d H:i:s'),
                ];
            });

        $yearLevelBreakdown = User::where('role', 'student')
            ->whereNotNull('year_level')
            ->selectRaw('year_level, COUNT(*) as count')
            ->groupBy('year_level')
            ->orderBy('year_level')
            ->get();

        $pendingApprovalsQuery = User::where('role', 'student')
            ->where('status', 'pending');

        $pendingApprovalsCount = (clone $pendingApprovalsQuery)->count();

        $pendingApprovals = (clone $pendingApprovalsQuery)
            ->orderBy('created_at')
            ->limit(5)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'email' => $user->email,
                    'course' => $user->course,
                    'year_level' => $user->year_level,
                    'registered_at' => $user->created_at->format('M d, Y'),
                ];
            });

        return Inertia::render('Admin/Dashboard', [
            'totalVoters' => $totalVoters,
            'activeElections' => $activeElections,
            'upcomingElections' => $upcomingElections,
            'recentActivities' => $recentActivities,
            'yearLevelBreakdown' => $yearLevelBreakdown,
            'pendingApprovals' => $pendingApprovals,
            'pendingApprovalsCount' => $pendingApprovalsCount,
        ]);
    }
}
