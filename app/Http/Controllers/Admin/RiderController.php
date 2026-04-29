<?php

namespace App\Http\Controllers\Admin;

use App\Models\OrderPackage;
use App\Models\Rider;
use App\Models\User;
use App\Services\DeliveryService;
use App\Services\RiderSettlementService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
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

    /**
     * Rider profile: delivered packages, COD attribution, seller vs marketplace splits, optional date range.
     */
    public function show(Request $request, Rider $rider, RiderSettlementService $settlementService)
    {
        $rider->load('user');

        $validated = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ]);

        $fromInput = $validated['date_from'] ?? null;
        $toInput = $validated['date_to'] ?? null;

        $from = null;
        $to = null;

        if ($fromInput !== null && $toInput !== null) {
            $from = Carbon::parse($fromInput)->startOfDay();
            $to = Carbon::parse($toInput)->endOfDay();
            if ($from->greaterThan($to)) {
                return back()->withErrors(['date_from' => 'Start date must be on or before end date.']);
            }
            if ($from->diffInDays($to) > 366) {
                return back()->withErrors(['date_to' => 'Choose a range of one year or less.']);
            }
        } elseif ($fromInput !== null || $toInput !== null) {
            return back()->withErrors(['date_from' => 'Provide both start and end dates, or leave both blank for all time.']);
        }

        $stats = $settlementService->totalsForRider((int) $rider->id, $from, $to);
        $stats['deliveries_count'] = $stats['packages_count'];

        $stats['total_rider_fees'] = (float) OrderPackage::query()
            ->where('rider_id', $rider->id)
            ->where('delivery_status', DeliveryService::STATUS_DELIVERED)
            ->when($from && $to, fn ($q) => $q->whereBetween('delivery_completed_at', [$from, $to]))
            ->sum('rider_fee_amount');

        $sellerBreakdown = $settlementService->sellerTotalsForRider((int) $rider->id, $from, $to);

        $packages = OrderPackage::query()
            ->where('rider_id', $rider->id)
            ->where('delivery_status', DeliveryService::STATUS_DELIVERED)
            ->when($from && $to, fn ($q) => $q->whereBetween('delivery_completed_at', [$from, $to]))
            ->with([
                'order.customer',
                'order.artisan.artisanProfile',
                'items.orderItem.product',
                'order.packages.items.orderItem',
            ])
            ->orderByDesc('delivery_completed_at')
            ->paginate(12)
            ->withQueryString();

        $allocationByPackageId = [];
        foreach ($packages as $pkg) {
            $allocationByPackageId[$pkg->id] = $settlementService->allocatePackage($pkg);
        }

        return view('admin.riders.show', compact(
            'rider',
            'stats',
            'packages',
            'sellerBreakdown',
            'allocationByPackageId',
            'from',
            'to'
        ));
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
            'birth_date' => ['nullable', 'date'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:50'],
            'license_number' => ['nullable', 'string', 'max:100'],
            'license_expiry' => ['nullable', 'string', 'max:50'],
            'vehicle_plate' => ['nullable', 'string', 'max:50'],
            'bio' => ['nullable', 'string', 'max:2000'],
            'license_image' => ['nullable', 'image', 'max:5120'],
            'id_document_image' => ['nullable', 'image', 'max:5120'],
            'clearance_document_image' => ['nullable', 'image', 'max:5120'],
        ]);

        DB::transaction(function () use ($validated, $request) {
            $user = User::create([
                'name' => $validated['full_name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'rider',
                'phone' => $validated['contact_number'],
                'address' => $validated['address'] ?? null,
                'status' => $validated['status'] === 'offline' ? 'suspended' : 'active',
            ]);

            $riderData = [
                'rider_id' => 'RDR-'.strtoupper(uniqid()),
                'user_id' => $user->id,
                'full_name' => $validated['full_name'],
                'contact_number' => $validated['contact_number'],
                'email' => $validated['email'],
                'address' => $validated['address'] ?? null,
                'vehicle_type' => $validated['vehicle_type'] ?? null,
                'status' => $validated['status'],
                'date_created' => now(),
                'birth_date' => $validated['birth_date'] ?? null,
                'emergency_contact_name' => $validated['emergency_contact_name'] ?? null,
                'emergency_contact_phone' => $validated['emergency_contact_phone'] ?? null,
                'license_number' => $validated['license_number'] ?? null,
                'license_expiry' => $validated['license_expiry'] ?? null,
                'vehicle_plate' => $validated['vehicle_plate'] ?? null,
                'bio' => $validated['bio'] ?? null,
            ];

            foreach (['license_image', 'id_document_image', 'clearance_document_image'] as $docField) {
                if ($request->hasFile($docField)) {
                    $riderData[$docField] = $request->file($docField)->store('rider-documents', 'public');
                }
            }

            Rider::create($riderData);
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
            'birth_date' => ['nullable', 'date'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:50'],
            'license_number' => ['nullable', 'string', 'max:100'],
            'license_expiry' => ['nullable', 'string', 'max:50'],
            'vehicle_plate' => ['nullable', 'string', 'max:50'],
            'bio' => ['nullable', 'string', 'max:2000'],
            'license_image' => ['nullable', 'image', 'max:5120'],
            'id_document_image' => ['nullable', 'image', 'max:5120'],
            'clearance_document_image' => ['nullable', 'image', 'max:5120'],
        ]);

        DB::transaction(function () use ($rider, $validated, $request) {
            foreach (['license_image', 'id_document_image', 'clearance_document_image'] as $docField) {
                if ($request->hasFile($docField)) {
                    if ($rider->{$docField}) {
                        Storage::disk('public')->delete($rider->{$docField});
                    }
                    $validated[$docField] = $request->file($docField)->store('rider-documents', 'public');
                }
            }

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
