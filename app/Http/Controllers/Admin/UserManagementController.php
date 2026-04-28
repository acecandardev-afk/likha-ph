<?php

namespace App\Http\Controllers\Admin;

use App\Models\AuditLog;
use App\Models\User;
use App\Models\UserNotification;

class UserManagementController extends AdminController
{
    /**
     * Display all artisans.
     */
    public function artisans()
    {
        $artisans = User::artisans()
            ->with('artisanProfile')
            ->withCount('products')
            ->latest()
            ->paginate(20);

        return view('admin.users.artisans', compact('artisans'));
    }

    /**
     * Display all customers.
     */
    public function customers()
    {
        $customers = User::customers()
            ->withCount('orders')
            ->latest()
            ->paginate(20);

        return view('admin.users.customers', compact('customers'));
    }

    /**
     * Suspend a user account.
     */
    public function suspend(User $user)
    {
        if ($user->isAdmin()) {
            return back()->withErrors(['error' => 'Cannot suspend admin accounts.']);
        }

        $previousStatus = $user->status;
        $user->update(['status' => 'suspended']);

        AuditLog::record('account.suspended', 'Paused access for '.$user->name.'.', $user);
        if ($previousStatus === 'pending' && $user->role === 'artisan') {
            UserNotification::create([
                'user_id' => $user->id,
                'type' => 'artisan_application_rejected',
                'title' => 'Artisan application rejected',
                'body' => 'Your request to sell as an artisan was not approved. You can update your details and apply again when available.',
                'action_url' => route('register.artisan'),
                'is_read' => false,
            ]);
        }

        return back()->with('success', "User {$user->name} has been suspended.");
    }

    /**
     * Activate a user account.
     */
    public function activate(User $user)
    {
        $previousStatus = $user->status;
        $user->update(['status' => 'active']);

        AuditLog::record('account.activated', 'Restored access for '.$user->name.'.', $user);
        if ($previousStatus === 'pending' && $user->role === 'artisan') {
            UserNotification::create([
                'user_id' => $user->id,
                'type' => 'artisan_application_approved',
                'title' => 'Artisan application approved',
                'body' => 'Your artisan account is now active. You can start listing your products for review.',
                'action_url' => route('artisan.dashboard'),
                'is_read' => false,
            ]);
        }

        return back()->with('success', "User {$user->name} has been activated.");
    }
}
