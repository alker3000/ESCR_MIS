<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\SystemSetting;
use App\Models\TelaOrder;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TelaService
{
    /**
     * Student creates a Uniform Fabric (Tela) & Logo Patch order, which generates a GCash invoice.
     */
    public function createOrder(User $student, array $data): TelaOrder
    {
        return DB::transaction(function () use ($student, $data) {
            $blouseYards = (float) ($data['blouse_polo_yards'] ?? 0);
            $skirtYards = (float) ($data['skirt_yards'] ?? 0);
            $patchQty = (int) ($data['logo_patch_qty'] ?? 0);

            $blousePrice = (float) SystemSetting::getValue('tela_blouse_polo_price', 160.00);
            $skirtPrice = (float) SystemSetting::getValue('tela_skirt_price', 100.00);
            $patchPrice = (float) SystemSetting::getValue('logo_patch_price', 100.00);

            $total = round(($blouseYards * $blousePrice) + ($skirtYards * $skirtPrice) + ($patchQty * $patchPrice), 2);

            $orderNumber = 'TELA-2026-' . strtoupper(Str::random(3)) . rand(100, 999);
            $enrollment = $student->enrollment;

            $invoice = Invoice::create([
                'invoice_number' => 'FAB-2026-' . strtoupper(Str::random(3)) . rand(100, 999),
                'user_id' => $student->id,
                'enrollment_id' => $enrollment?->id,
                'invoice_type' => 'tela_order',
                'exam_period' => null,
                'title' => "Uniform Fabric (Tela) & Patch Order ({$orderNumber})",
                'description' => "School uniform fabric (tela) & logo patch order. Student will have fabric sewn by their chosen tailor after pickup.",
                'amount' => $total,
                'amount_paid' => 0,
                'due_date' => now()->addDays(5)->toDateString(),
                'status' => 'unpaid',
            ]);

            $order = TelaOrder::create([
                'order_number' => $orderNumber,
                'user_id' => $student->id,
                'enrollment_id' => $enrollment?->id,
                'invoice_id' => $invoice->id,
                'blouse_polo_yards' => $blouseYards,
                'blouse_polo_unit_price' => $blousePrice,
                'skirt_yards' => $skirtYards,
                'skirt_unit_price' => $skirtPrice,
                'logo_patch_qty' => $patchQty,
                'logo_patch_unit_price' => $patchPrice,
                'total_amount' => $total,
                'student_notes' => $data['student_notes'] ?? null,
                'status' => 'awaiting_payment',
            ]);

            AuditService::log(
                'TELA_ORDER_CREATED',
                "Student {$student->name} placed Uniform Tela order {$order->order_number} ({$order->itemsSummary()}) totaling PHP " . number_format($total, 2) . ".",
                'TelaOrder',
                $order->id,
                null,
                ['items' => $order->itemsSummary(), 'total' => $total],
                $student
            );

            return $order;
        });
    }

    /**
     * Cashier prepares the fabric and clicks "Ready for Pickup".
     * System automatically sends an Email & Portal notification to the student!
     */
    public function markReadyForPickup(TelaOrder $order, User $cashier, ?string $remarks = null): TelaOrder
    {
        $oldStatus = $order->status;

        $order->update([
            'status' => 'ready_for_pickup',
            'ready_at' => now(),
            'processed_by' => $cashier->id,
            'cashier_remarks' => $remarks ?? 'Your uniform tela and patches have been measured, packed, and are ready for pickup at the School Cashier Office.',
        ]);

        AuditService::log(
            'TELA_READY_FOR_PICKUP',
            "Cashier {$cashier->name} marked Tela Order {$order->order_number} as Ready for Pickup for {$order->user->name}.",
            'TelaOrder',
            $order->id,
            ['status' => $oldStatus],
            ['status' => 'ready_for_pickup'],
            $cashier
        );

        NotificationService::send(
            $order->user,
            'tela_ready_pickup',
            "Uniform Tela Ready for Pickup! (Order #{$order->order_number})",
            "Good news, {$order->user->name}!\n\nYour Uniform Fabric (Tela) & Logo Patch order ({$order->order_number}: {$order->itemsSummary()}) has been prepared by the Cashier and is now READY FOR PICKUP at the East Systems Colleges of Rizal Cashier's Office.\n\nPlease bring your Student ID or Digital Official Receipt ({$order->invoice?->latestPayment?->receipt_number}) to claim your fabric so you can bring it to your preferred tailor.\n\nCashier Remarks: {$order->cashier_remarks}\n\nEast Systems Colleges of Rizal"
        );

        return $order;
    }

    /**
     * Cashier marks the order as "Claimed" once the student physically picks up the fabric at the school.
     */
    public function markClaimed(TelaOrder $order, User $cashier, ?string $remarks = null): TelaOrder
    {
        $oldStatus = $order->status;

        $order->update([
            'status' => 'claimed',
            'claimed_at' => now(),
            'processed_by' => $cashier->id,
            'cashier_remarks' => $remarks ?? $order->cashier_remarks,
        ]);

        AuditService::log(
            'TELA_ORDER_CLAIMED',
            "Cashier {$cashier->name} marked Tela Order {$order->order_number} as Claimed by {$order->user->name}.",
            'TelaOrder',
            $order->id,
            ['status' => $oldStatus],
            ['status' => 'claimed'],
            $cashier
        );

        NotificationService::send(
            $order->user,
            'tela_ready_pickup',
            "Uniform Tela Order Claimed ({$order->order_number})",
            "Dear {$order->user->name},\n\nThis confirms that your Uniform Fabric (Tela) order ({$order->order_number}: {$order->itemsSummary()}) was claimed at the Cashier's Office on " . now()->format('F d, Y h:i A') . ".\n\nThank you,\nEast Systems Colleges of Rizal"
        );

        return $order;
    }
}
