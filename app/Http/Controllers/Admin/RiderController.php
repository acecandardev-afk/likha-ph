<?php

namespace App\Http\Controllers\Admin;

use App\Models\Rider;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class RiderController extends AdminController
{
    public function index(Request $request)
    {
        $query = Rider::query()->with('user')->latest();

        if ($request->filled('status') && in_array($request->status, ['available', 'busy', 'offline'], true)) {
            $query->where('status', $request->status);
        }

        $riders = $query->paginate(20)->withQueryString();

        return view('admin.riders.index', compact('riders'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'contact_number' => 'required|string|max:50',
            'email' => 'required|email:rfc,dns|unique:users,email|unique:riders,email',
            'address' => 'nullable|string|max:500',
            'vehicle_type' => 'nullable|string|max:100',
            'status' => ['required', Rule::in(['available', 'busy', 'offline'])],
            'password' => 'required|string|min:8|max:64',
        ]);

        DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['full_name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'rider',
                'phone' => $validated['contact_number'],
                'address' => $validated['address'] ?? null,
                'status' => $validated['status'] === 'offline' ? 'suspended' : 'active',
            ]);

            Rider::create([
                'rider_id' => 'RDR-'.strtoupper(uniqid()),
                'user_id' => $user->id,
                'full_name' => $validated['full_name'],
                'contact_number' => $validated['contact_number'],
                'email' => $validated['email'],
                'address' => $validated['address'] ?? null,
                'vehicle_type' => $validated['vehicle_type'] ?? null,
                'status' => $validated['status'],
                'date_created' => now(),
            ]);
        });

        return back()->with('success', 'Rider account created successfully.');
    }

    public function update(Request $request, Rider $rider)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'contact_number' => 'required|string|max:50',
            'email' => ['required', 'email:rfc,dns', Rule::unique('riders', 'email')->ignore($rider->id), Rule::unique('users', 'email')->ignore($rider->user_id)],
            'address' => 'nullable|string|max:500',
            'vehicle_type' => 'nullable|string|max:100',
            'status' => ['required', Rule::in(['available', 'busy', 'offline'])],
        ]);

        DB::transaction(function () use ($rider, $validated) {
            $rider->update($validated);
            $rider->user?->update([
                'name' => $validated['full_name'],
                'email' => $validated['email'],
                'phone' => $validated['contact_number'],
                'address' => $validated['address'] ?? null,
                'status' => $validated['status'] === 'offline' ? 'suspended' : 'active',
            ]);
        });

        return back()->with('success', 'Rider profile updated.');
    }

    public function activate(Rider $rider)
    {
        $rider->update(['status' => Rider::STATUS_AVAILABLE]);
        $rider->user?->update(['status' => 'active']);

        return back()->with('success', 'Rider is now active and available.');
    }

    public function deactivate(Rider $rider)
    {
        $rider->update(['status' => Rider::STATUS_OFFLINE]);
        $rider->user?->update(['status' => 'suspended']);

        return back()->with('success', 'Rider has been deactivated.');
    }
}
