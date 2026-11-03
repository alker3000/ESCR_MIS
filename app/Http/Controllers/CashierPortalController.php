<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\TelaOrder;
use App\Services\PaymentService;
use App\Services\TelaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CashierPortalController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService,
        protected TelaService $telaService
    ) {}

    public function dashboard()
    {
        $totalRevenue = (float) Payment::where('status', 'verified')->sum('amount');
        $todayRevenue = (float) Payment::where('status', 'verified')
            ->whereDate('created_at', today())
            ->sum('amount');
        $pendingVerificationsCount = Payment::where('status', 'pending')->count();
        $paidProcessingTelaCount = TelaOrder::where('status', 'paid_processing')->count();
        $readyForPickupTelaCount = TelaOrder::where('status', 'ready_for_pickup')->count();

        $recentPayments = Payment::with(['user', 'invoice'])
            ->latest()
            ->take(6)
            ->get();

        $pendingTelaOrders = TelaOrder::with(['user', 'invoice'])
            ->whereIn('status', ['paid_processing', 'ready_for_pickup'])
            ->latest()
            ->take(6)
            ->get();

        // Revenue by fee category
        $tuitionRevenue = (float) Invoice::whereIn('invoice_type', [
            'full_tuition',
            'downpayment',
            'prelim_installment',
            'midterm_installment',
            'semifinal_installment',
            'finals_installment',
        ])->where('status', 'paid')->sum('amount_paid');

        $specialExamRevenue = (float) Invoice::where('invoice_type', 'special_exam_fee')
            ->where('status', 'paid')
            ->sum('amount_paid');

        $completionRevenue = (float) Invoice::where('invoice_type', 'completion_fee')
            ->where('status', 'paid')
            ->sum('amount_paid');

        $telaRevenue = (float) Invoice::where('invoice_type', 'tela_order')
            ->where('status', 'paid')
            ->sum('amount_paid');

        return view('cashier.dashboard', compact(
            'totalRevenue',
            'todayRevenue',
            'pendingVerificationsCount',
            'paidProcessingTelaCount',
            'readyForPickupTelaCount',
            'recentPayments',
            'pendingTelaOrders',
            'tuitionRevenue',
            'specialExamRevenue',
            'completionRevenue',
            'telaRevenue'
        ));
    }

    public function payments(Request $request)
    {
        $statusFilter = $request->query('status', 'all');
        $query = Payment::with(['user', 'invoice', 'verifier'])->latest('id');

        if ($statusFilter !== 'all') {
            $query->where('status', $statusFilter);
        }

        $payments = $query->paginate(20)->withQueryString();

        return view('cashier.payments', compact('payments', 'statusFilter'));
    }

    public function verifyPayment(Request $request, Payment $payment)
    {
        $this->paymentService->verifyPayment(
            $payment,
            Auth::user(),
            $request->input('cashier_remarks')
        );

        return back()->with('success', "{$payment->methodLabel()} #{$payment->gcash_reference_number} verified! Official Receipt {$payment->receipt_number} issued to {$payment->user->name}.");
    }

    public function rejectPayment(Request $request, Payment $payment)
    {
        $validated = $request->validate([
            'cashier_remarks' => ['required', 'string', 'max:500'],
        ]);

        $this->paymentService->rejectPayment(
            $payment,
            Auth::user(),
            $validated['cashier_remarks']
        );

        return back()->with('warning', "Payment #{$payment->gcash_reference_number} marked as rejected.");
    }

    public function telaOrders(Request $request)
    {
        $statusFilter = $request->query('status', 'all');
        $query = TelaOrder::with(['user', 'invoice.latestPayment', 'processor'])->latest('id');

        if ($statusFilter !== 'all') {
            $query->where('status', $statusFilter);
        }

        $orders = $query->paginate(20)->withQueryString();

        return view('cashier.tela_orders', compact('orders', 'statusFilter'));
    }

    public function markTelaReady(Request $request, TelaOrder $order)
    {
        $this->telaService->markReadyForPickup(
            $order,
            Auth::user(),
            $request->input('cashier_remarks')
        );

        return back()->with(
            'success',
            "Tela Order {$order->order_number} marked READY FOR PICKUP! Automated Email & Portal Notification sent to {$order->user->name}."
        );
    }

    public function markTelaClaimed(Request $request, TelaOrder $order)
    {
        $this->telaService->markClaimed(
            $order,
            Auth::user(),
            $request->input('cashier_remarks')
        );

        return back()->with(
            'success',
            "Tela Order {$order->order_number} marked as CLAIMED by {$order->user->name}."
        );
    }

    public function receipts(Request $request)
    {
        $search = trim((string) $request->query('q', ''));
        $query = Payment::with(['user', 'invoice', 'verifier'])
            ->where('status', 'verified')
            ->latest('id');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('receipt_number', 'like', "%{$search}%")
                    ->orWhere('gcash_reference_number', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('student_number', 'like', "%{$search}%");
                    });
            });
        }

        $receipts = $query->paginate(20)->withQueryString();

        return view('cashier.receipts', compact('receipts', 'search'));
    }
}
