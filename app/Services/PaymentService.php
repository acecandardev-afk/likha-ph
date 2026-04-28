<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function __construct(
        protected ImageUploadService $imageUploadService,
        protected NotificationService $notificationService
    ) {}

    /**
     * Upload payment proof.
     */
    public function uploadPaymentProof(Payment $payment, UploadedFile $proofImage): Payment
    {
        if (!$payment->isAwaitingProof()) {
            throw new \Exception("Payment proof cannot be uploaded at this time.");
        }

        // Validate image
        $errors = $this->imageUploadService->validateImage($proofImage);
        if (!empty($errors)) {
            throw new \Exception(implode(', ', $errors));
        }

        DB::transaction(function () use ($payment, $proofImage) {
            // Delete old proof if exists
            if ($payment->proof_image) {
                $this->imageUploadService->deletePaymentProof($payment->proof_image);
            }

            // Upload new proof
            $filename = $this->imageUploadService->uploadPaymentProof(
                $proofImage,
                $payment->order_id
            );

            // Update payment
            $payment->update([
                'proof_image' => $filename,
                'verification_status' => 'pending',
            ]);
        });

        // Notify admin
        $this->notificationService->notifyPaymentProofUploaded($payment);

        return $payment->fresh();
    }

    /**
     * Verify payment.
     */
    public function verifyPayment(Payment $payment, User $admin, ?string $notes = null): Payment
    {
        if (!$payment->isPending()) {
            throw new \Exception("Payment is not pending verification.");
        }

        DB::transaction(function () use ($payment, $admin, $notes) {
            $payment->update([
                'verification_status' => 'verified',
                'verified_by' => $admin->id,
                'verification_notes' => $notes,
                'verified_at' => now(),
            ]);

            // Order stays pending until the seller approves; payment is only cleared here.
        });

        // Notify customer and artisan
        $this->notificationService->notifyPaymentVerified($payment);

        return $payment->fresh();
    }

    /**
     * Reject payment.
     */
    public function rejectPayment(Payment $payment, User $admin, string $reason): Payment
    {
        if (!$payment->isPending()) {
            throw new \Exception("Payment is not pending verification.");
        }

        DB::transaction(function () use ($payment, $admin, $reason) {
            $payment->update([
                'verification_status' => 'rejected',
                'verified_by' => $admin->id,
                'verification_notes' => $reason,
                'verified_at' => now(),
            ]);

            // Optionally restore stock
            foreach ($payment->order->items as $item) {
                $item->product->increment('stock', $item->quantity);
            }

            // Update order status
            $payment->order->update(['status' => 'cancelled']);
        });

        // Notify customer
        $this->notificationService->notifyPaymentRejected($payment);

        return $payment->fresh();
    }

    /**
     * Get pending payments for verification.
     */
    public function getPendingPayments(int $perPage = 20)
    {
        return Payment::pending()
            ->with(['order.customer', 'order.artisan', 'order.items.product'])
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get payment methods.
     */
    public function getPaymentMethods(): array
    {
        return [
            'cod' => [
                'name' => 'Cash on Delivery (COD)',
                'description' => 'Pay the rider when your package arrives.',
                'instructions' => 'Prepare exact change when possible.',
            ],
        ];
    }

    /**
     * Get payment method details.
     */
    public function getPaymentMethodDetails(string $method): ?array
    {
        $methods = $this->getPaymentMethods();
        return $methods[$method] ?? null;
    }

    /**
     * Generate payment reference number.
     */
    public function generatePaymentReference(Order $order): string
    {
        return 'PAY-' . $order->order_number . '-' . strtoupper(uniqid());
    }

    /**
     * Get payment statistics.
     */
    public function getPaymentStats(): array
    {
        return [
            'awaiting_proof' => Payment::awaitingProof()->count(),
            'pending_verification' => Payment::pending()->count(),
            'verified_today' => Payment::verified()
                ->whereDate('verified_at', today())
                ->count(),
            'verified_this_month' => Payment::verified()
                ->whereMonth('verified_at', now()->month)
                ->count(),
            'rejected_this_month' => Payment::where('verification_status', 'rejected')
                ->whereMonth('verified_at', now()->month)
                ->count(),
            'total_verified_amount' => Payment::verified()->sum('amount'),
        ];
    }

    /**
     * Check if payment method requires proof.
     */
    public function requiresProof(string $method): bool
    {
        return false;
    }

    /**
     * Validate payment amount.
     */
    public function validatePaymentAmount(Payment $payment): bool
    {
        return $payment->amount === $payment->order->total;
    }
}