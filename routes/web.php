<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Voter\DashboardController as VoterDashboardController;
use App\Http\Controllers\Voter\VoteController;
use App\Models\Election;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    $currentElection = Election::where('is_active', true)
        ->where(function ($query) {
            $query->whereNull('starts_at')
                ->orWhere('starts_at', '<=', now());
        })
        ->where(function ($query) {
            $query->whereNull('ends_at')
                ->orWhere('ends_at', '>=', now());
        })
        ->first();

    return Inertia::render('Landing', [
        'currentElection' => $currentElection,
    ]);
})->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', function (Request $request) {
        return Inertia::render('auth/Login', [
            'canResetPassword' => Features::enabled(Features::resetPasswords()),
            'status' => $request->session()->get('status'),
        ]);
    })->name('login');

    Route::get('/forgot-password', function (Request $request) {
        return Inertia::render('auth/ForgotPassword', [
            'status' => $request->session()->get('status'),
        ]);
    })->name('password.request');

    Route::get('/reset-password/{token}', function (Request $request, string $token) {
        return Inertia::render('auth/ResetPassword', [
            'email' => $request->email,
            'token' => $token,
        ]);
    })->name('password.reset');

    Route::get('/two-factor-challenge', function (Request $request) {
        if (! $request->session()->has('login.id')) {
            return redirect()->route('login');
        }

        return Inertia::render('auth/TwoFactorChallenge');
    })->name('two-factor.login');
});

Route::middleware('auth')->group(function () {
    Route::get('/email/verify', function (Request $request) {
        if ($request->user()?->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard'));
        }

        return Inertia::render('auth/VerifyEmail', [
            'status' => $request->session()->get('status'),
        ]);
    })->name('verification.notice');

    Route::get('/user/confirm-password', fn () => Inertia::render('auth/ConfirmPassword'))
        ->name('password.confirm');
});

Route::middleware(['auth', 'verified', 'approved'])->group(function () {
    Route::get('dashboard', VoterDashboardController::class)->name('dashboard');

    Route::prefix('admin')->name('admin.')->middleware('can:access-admin')->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::resource('elections', \App\Http\Controllers\Admin\ElectionController::class);
        Route::resource('positions', \App\Http\Controllers\Admin\PositionController::class);
        Route::resource('candidates', \App\Http\Controllers\Admin\CandidateController::class);
        Route::get('voters', [\App\Http\Controllers\Admin\VoterController::class, 'index'])->name('voters.index');
        Route::post('voters', [\App\Http\Controllers\Admin\VoterController::class, 'store'])->name('voters.store');
        Route::put('voters/{user}', [\App\Http\Controllers\Admin\VoterController::class, 'update'])->name('voters.update');
        Route::patch('voters/{user}/status', [\App\Http\Controllers\Admin\VoterController::class, 'updateStatus'])->name('voters.update-status');
        Route::get('elections/{election}/attendance/export', [\App\Http\Controllers\Admin\ElectionController::class, 'exportAttendance'])->name('elections.attendance.export');
        Route::get('audit-logs', [\App\Http\Controllers\Admin\AuditLogController::class, 'index'])->name('audit-logs.index');
    });

    Route::post('votes/bulk', [VoteController::class, 'bulkStore'])->name('votes.bulk');
    Route::post('votes', [VoteController::class, 'store'])->name('votes.store');
});

require __DIR__.'/settings.php';
