<?php

use App\Http\Controllers\AppSettingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\TicketAttachmentController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TicketNoteController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserPermissionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Tickets
    Route::resource('tickets', TicketController::class);
    Route::post('/tickets/check-duplicates', [TicketController::class, 'checkDuplicates'])->name('tickets.check-duplicates');
    Route::post('/tickets/{ticket}/assign', [TicketController::class, 'assign'])->name('tickets.assign');
    Route::post('/tickets/{ticket}/close', [TicketController::class, 'close'])->name('tickets.close');
    Route::post('/tickets/{ticket}/resolve', [TicketController::class, 'resolve'])->name('tickets.resolve');
    Route::post('/tickets/{ticket}/extend-deadline', [TicketController::class, 'extendDeadline'])->name('tickets.extend-deadline');

    // Ticket Notes
    Route::post('/tickets/{ticket}/notes', [TicketNoteController::class, 'store'])->name('tickets.notes.store');
    Route::patch('/tickets/{ticket}/notes/{note}', [TicketNoteController::class, 'update'])->name('tickets.notes.update');
    Route::delete('/tickets/{ticket}/notes/{note}', [TicketNoteController::class, 'destroy'])->name('tickets.notes.destroy');

    // Ticket Attachments
    Route::post('/tickets/{ticket}/attachments', [TicketAttachmentController::class, 'store'])->name('tickets.attachments.store');
    Route::get('/tickets/{ticket}/attachments/{attachment}/download', [TicketAttachmentController::class, 'download'])->name('tickets.attachments.download');
    Route::delete('/tickets/{ticket}/attachments/{attachment}', [TicketAttachmentController::class, 'destroy'])->name('tickets.attachments.destroy');

    // Reports
    Route::get('/reports/agent-performance', [ReportController::class, 'agentPerformance'])->name('reports.agent-performance');
    Route::get('/reports/global-statistics', [ReportController::class, 'globalStatistics'])->name('reports.global-statistics');

    // User Management
    Route::resource('users', UserController::class)->except(['show']);

    // Roles & Permissions Management
    Route::resource('roles', RoleController::class);
    Route::post('roles/{role}/permissions', [RolePermissionController::class, 'syncPermissions'])->name('roles.sync-permissions');
    Route::get('permissions', [PermissionController::class, 'index'])->name('permissions.index');
    Route::post('users/{user}/permissions/{permission}', [UserPermissionController::class, 'assign'])->name('users.permissions.assign');
    Route::delete('users/{user}/permissions/{permission}', [UserPermissionController::class, 'revoke'])->name('users.permissions.revoke');
    
    // Services Management
    Route::resource('services', ServiceController::class);

    // Settings (Superviseur only)
    Route::get('/settings', [AppSettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [AppSettingController::class, 'update'])->name('settings.update');
    Route::post('/settings/logo', [AppSettingController::class, 'uploadLogo'])->name('settings.upload-logo');
});

require __DIR__.'/auth.php';
