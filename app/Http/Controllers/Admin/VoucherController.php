<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\StoreVoucherRequest;
use App\Http\Requests\Admin\UpdateVoucherRequest;
use App\Models\Voucher;
use App\Services\NotificationService;

class VoucherController extends AdminController
{
    public function __construct(
        protected NotificationService $notificationService
    ) {
        parent::__construct();
    }

    public function index()
    {
        $vouchers = Voucher::query()->orderByDesc('created_at')->paginate(25);

        return view('admin.vouchers.index', compact('vouchers'));
    }

    public function create()
    {
        return view('admin.vouchers.create');
    }

    public function store(StoreVoucherRequest $request)
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');
        $data['times_redeemed'] = 0;

        $voucher = Voucher::create($data);

        $admin = $request->user();
        $this->notificationService->notifyVoucherManagedByAdmin($admin, 'created', $voucher->code);

        return redirect()
            ->route('admin.vouchers.index')
            ->with('success', 'Promo voucher '.$voucher->code.' was created.');
    }

    public function edit(Voucher $voucher)
    {
        return view('admin.vouchers.edit', compact('voucher'));
    }

    public function update(UpdateVoucherRequest $request, Voucher $voucher)
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');

        $beforeCode = $voucher->code;
        $voucher->update($data);

        return redirect()
            ->route('admin.vouchers.index')
            ->with('success', 'Voucher '.($beforeCode !== $voucher->code ? $beforeCode.' → ' : '').$voucher->code.' saved.');
    }

    public function destroy(Voucher $voucher)
    {
        $code = $voucher->code;
        $admin = request()->user();
        $voucher->delete();

        if ($admin?->isAdmin()) {
            $this->notificationService->notifyVoucherManagedByAdmin($admin, 'deleted', $code);
        }

        return redirect()
            ->route('admin.vouchers.index')
            ->with('success', 'Voucher '.$code.' removed.');
    }
}
