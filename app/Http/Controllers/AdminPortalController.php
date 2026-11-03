<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Enrollment;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PromissoryNote;
use App\Models\SystemSetting;
use App\Models\TelaOrder;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminPortalController extends Controller
{
    public function dashboard()
    {
        $totalStudents = User::where('role', 'student')->count();
        $enrolledStudents = Enrollment::where('status', 'enrolled')->count();
        $pendingApplications = Enrollment::whereIn('status', ['pending', 'under_review', 'approved_awaiting_payment'])->count();

        $totalBilled = (float) Invoice::where('status', '!=', 'promised')->sum('amount');
        $totalCollected = (float) Payment::where('status', 'verified')->sum('amount');
        $outstandingBalance = max(0, $totalBilled - $totalCollected);

        $activePromissoryNotes = PromissoryNote::where('status', 'approved')->count();
        $pendingPromissoryNotes = PromissoryNote::where('status', 'pending_review')->count();

        $telaStats = [
            'awaiting_payment' => TelaOrder::where('status', 'awaiting_payment')->count(),
            'paid_processing' => TelaOrder::where('status', 'paid_processing')->count(),
            'ready_for_pickup' => TelaOrder::where('status', 'ready_for_pickup')->count(),
            'claimed' => TelaOrder::where('status', 'claimed')->count(),
        ];

        // Revenue breakdown by fee type for Analytics Chart
        $revenueByType = [
            'Downpayment & Tuition' => (float) Invoice::whereIn('invoice_type', ['full_tuition', 'downpayment'])->where('status', 'paid')->sum('amount_paid'),
            'Regular Installments' => (float) Invoice::whereIn('invoice_type', ['prelim_installment', 'midterm_installment', 'semifinal_installment', 'finals_installment'])->where('status', 'paid')->sum('amount_paid'),
            'Special Exam Fees' => (float) Invoice::where('invoice_type', 'special_exam_fee')->where('status', 'paid')->sum('amount_paid'),
            'Completion (Remedial) Fees' => (float) Invoice::where('invoice_type', 'completion_fee')->where('status', 'paid')->sum('amount_paid'),
            'Uniform Tela & Patches' => (float) Invoice::where('invoice_type', 'tela_order')->where('status', 'paid')->sum('amount_paid'),
        ];

        // Enrollment count by course
        $enrollmentsByCourse = Enrollment::selectRaw('course, count(*) as total')
            ->groupBy('course')
            ->pluck('total', 'course')
            ->toArray();

        $recentAuditLogs = AuditLog::latest()->take(8)->get();

        return view('admin.dashboard', compact(
            'totalStudents',
            'enrolledStudents',
            'pendingApplications',
            'totalBilled',
            'totalCollected',
            'outstandingBalance',
            'activePromissoryNotes',
            'pendingPromissoryNotes',
            'telaStats',
            'revenueByType',
            'enrollmentsByCourse',
            'recentAuditLogs'
        ));
    }

    public function users(Request $request)
    {
        $roleFilter = $request->query('role', 'all');
        $search = trim((string) $request->query('q', ''));
        $query = User::latest('id');

        if ($roleFilter !== 'all') {
            $query->where('role', $roleFilter);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('student_number', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(20)->withQueryString();

        return view('admin.users', compact('users', 'roleFilter', 'search'));
    }

    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', 'in:student,registrar,cashier,super_admin'],
            'contact_number' => ['nullable', 'string', 'max:40'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'contact_number' => $validated['contact_number'] ?? null,
            'is_active' => true,
            'password' => Hash::make($validated['password']),
        ]);

        AuditService::log(
            'USER_ACCOUNT_CREATED',
            "Super Admin created {$user->roleLabel()} account for {$user->name} ({$user->email}).",
            'User',
            $user->id,
            null,
            ['role' => $user->role, 'email' => $user->email]
        );

        return back()->with('success', "Created {$user->roleLabel()} account for {$user->name}.");
    }

    public function updateUserRole(Request $request, User $user)
    {
        $validated = $request->validate([
            'role' => ['required', 'in:student,registrar,cashier,super_admin'],
        ]);

        $oldRole = $user->role;
        $user->update(['role' => $validated['role']]);

        AuditService::log(
            'USER_ROLE_CHANGED',
            "Changed {$user->name}'s role from {$oldRole} to {$validated['role']}.",
            'User',
            $user->id,
            ['role' => $oldRole],
            ['role' => $validated['role']]
        );

        return back()->with('success', "Updated {$user->name}'s role to {$user->roleLabel()}.");
    }

    public function toggleUserStatus(User $user)
    {
        $old = $user->is_active;
        $user->update(['is_active' => !$old]);

        AuditService::log(
            'USER_STATUS_TOGGLED',
            ($user->is_active ? 'Activated' : 'Deactivated') . " account for {$user->name}.",
            'User',
            $user->id,
            ['is_active' => $old],
            ['is_active' => $user->is_active]
        );

        return back()->with('success', "Account status updated for {$user->name}.");
    }

    public function feeSettings()
    {
        $settings = SystemSetting::getAllKeyed();
        return view('admin.settings_fees', compact('settings'));
    }

    public function updateFeeSettings(Request $request)
    {
        $keys = [
            'academic_year',
            'semester',
            'base_tuition_fee',
            'downpayment_amount',
            'special_exam_fee',
            'completion_fee',
            'prelim_deadline',
            'prelim_pn_deadline',
            'midterm_deadline',
            'midterm_pn_deadline',
            'semifinal_deadline',
            'semifinal_pn_deadline',
            'finals_deadline',
            'finals_pn_deadline',
            'tela_blouse_polo_price',
            'tela_skirt_price',
            'logo_patch_price',
            'gcash_merchant_name',
            'gcash_merchant_number',
        ];

        $oldSettings = SystemSetting::getAllKeyed();
        $newValues = [];

        foreach ($keys as $key) {
            if ($request->has($key)) {
                $val = $request->input($key);
                SystemSetting::setValue($key, $val);
                $newValues[$key] = $val;
            }
        }

        AuditService::log(
            'FEE_AND_TERM_SETTINGS_UPDATED',
            'Super Admin updated academic term deadlines, fee schedules, and uniform tela pricing.',
            'SystemSetting',
            null,
            $oldSettings,
            $newValues
        );

        return back()->with('success', 'Academic deadlines, fee schedules, and Uniform Tela prices updated!');
    }

    public function auditLogs(Request $request)
    {
        $actionFilter = $request->query('action', 'all');
        $query = AuditLog::latest();

        if ($actionFilter !== 'all') {
            $query->where('action', $actionFilter);
        }

        $logs = $query->take(150)->get();
        $distinctActions = AuditLog::select('action')->distinct()->pluck('action');

        return view('admin.audit_logs', compact('logs', 'actionFilter', 'distinctActions'));
    }
}
