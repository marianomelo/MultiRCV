<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RcvController;
use App\Http\Controllers\UserController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::post('/ticket/submit', [\App\Http\Controllers\TicketController::class, 'submit'])->name('ticket.submit');

    Route::resource('companies', CompanyController::class);

    Route::get('/rcv', [RcvController::class, 'index'])->name('rcv.index');
    Route::post('/rcv', [RcvController::class, 'store'])->name('rcv.store');
    Route::get('/rcv/{rcvRequest}', [RcvController::class, 'show'])->name('rcv.show');
    Route::get('/rcv/{rcvRequest}/export/{companyId}', [RcvController::class, 'export'])->name('rcv.export');
    Route::delete('/rcv/{rcvRequest}', [RcvController::class, 'destroy'])->name('rcv.destroy');

    // Super Admin only routes
    Route::middleware('super_admin')->group(function () {
        Route::resource('users', UserController::class);
        Route::post('/users/{user}/approve', [UserController::class, 'approve'])->name('users.approve');
        Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
    });
});

require __DIR__.'/auth.php';
