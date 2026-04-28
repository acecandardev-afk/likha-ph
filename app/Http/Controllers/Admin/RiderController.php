<?php

namespace App\Http\Controllers\Admin;

use App\Models\OrderPackage;
use App\Models\Rider;
use App\Models\User;
use App\Services\DeliveryService;
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
     * Rider profile: delivered packages, merchandise totals, rider fees, delivery timestamps.
     */
    public function show(Rider $rider)
    {
        $rider->load('user');

        $stats = [
            'deliveries_count' => OrderPackage::query()
                ->where('rider_id', $rider->id)
                ->where('delivery_status', DeliveryService::STATUS_DELIVERED)
                ->count(),
            'total_rider_fees' => (float) OrderPackage::query()
                ->where('rider_id', $rider->id)
                ->where('delivery_status', DeliveryService::STATUS_DELIVERED)
                ->sum('rider_fee_amount'),
            'total_merchandise' => (float) DB::table('order_packages')
                ->join('order_package_items', 'order_packages.id', '=', 'order_package_items.order_package_id')
                ->join('order_items', 'order_package_items.order_item_id', '=', 'order_items.id')
                ->where('order_packages.rider_id', $rider->id)
                ->where('order_packages.delivery_status', DeliveryService::STATUS_DELIVERED)
                ->sum(DB::raw('order_items.price * order_package_items.quantity')),
        ];

        $packages = OrderPackage::query()
            ->where('rider_id', $rider->id)
            ->where('delivery_status', DeliveryService::STATUS_DELIVERED)
            ->with([
                'order.customer',
                'items.orderItem.product',
            ])
            ->orderByDesc('delivery_completed_at')
            ->paginate(12)
            ->withQueryString();

        return view('admin.riders.show', compact('rider', 'stats', 'packages'));
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
