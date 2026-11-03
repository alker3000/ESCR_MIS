<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AuditService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route(Auth::user()->dashboardRoute());
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            /** @var User $user */
            $user = Auth::user();

            if (!$user->is_active) {
                Auth::logout();
                return back()->withErrors(['email' => 'Your account has been deactivated.']);
            }

            AuditService::log(
                'USER_LOGIN',
                "{$user->roleLabel()} {$user->name} signed in to the portal.",
                'User',
                $user->id,
                null,
                null,
                $user
            );

            return redirect()->intended(route($user->dashboardRoute()))
                ->with('success', "Welcome back, {$user->name}!");
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * One-click Demo Login helper so students/instructors can test any role immediately.
     */
    public function quickDemoLogin(Request $request)
    {
        $email = $request->input('demo_email');
        $user = User::where('email', $email)->first();

        if (!$user) {
            return redirect()->route('login')->with('error', 'Demo user account not found. Please run database seeder.');
        }

        Auth::login($user);
        $request->session()->regenerate();

        AuditService::log(
            'DEMO_QUICK_LOGIN',
            "Switched to demo account: {$user->name} ({$user->roleLabel()}).",
            'User',
            $user->id,
            null,
            null,
            $user
        );

        return redirect()->route($user->dashboardRoute())
            ->with('success', "Logged in as {$user->name} ({$user->roleLabel()}).");
    }

    public function showRegister()
    {
        if (Auth::check()) {
            return redirect()->route(Auth::user()->dashboardRoute());
        }
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'contact_number' => ['required', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'contact_number' => $validated['contact_number'],
            'role' => 'student',
            'is_active' => true,
            'password' => Hash::make($validated['password']),
        ]);

        Auth::login($user);

        AuditService::log(
            'STUDENT_REGISTERED',
            "New student account registered: {$user->name} ({$user->email}).",
            'User',
            $user->id,
            null,
            ['email' => $user->email],
            $user
        );

        return redirect()->route('student.enrollment')
            ->with('success', 'Account created! Complete your online enrollment form and document uploads below.');
    }

    public function logout(Request $request)
    {
        /** @var User|null $user */
        $user = Auth::user();
        if ($user) {
            AuditService::log(
                'USER_LOGOUT',
                "{$user->roleLabel()} {$user->name} logged out.",
                'User',
                $user->id,
                null,
                null,
                $user
            );
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'You have been signed out.');
    }

    /**
     * Allows any authenticated user (Student, Registrar, Cashier, Super Admin) to change their current password.
     */
    public function changePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:6', 'confirmed', 'different:current_password'],
        ], [
            'current_password.current_password' => 'Your current password is incorrect.',
            'password.different' => 'Your new password must be different from your current password.',
        ]);

        /** @var User $user */
        $user = Auth::user();
        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        AuditService::log(
            'PASSWORD_CHANGED',
            "{$user->roleLabel()} {$user->name} changed their account password.",
            'User',
            $user->id,
            null,
            null,
            $user
        );

        return back()->with('success', 'Your account password has been updated successfully!');
    }

    public function showForgotPassword()
    {
        return view('auth.forgot_password');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ], [
            'email.exists' => 'We could not find an ESCR Portal account with that email address.',
        ]);

        /** @var User $user */
        $user = User::where('email', $validated['email'])->firstOrFail();
        $plainToken = Str::random(64);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => Hash::make($plainToken),
                'created_at' => now(),
            ]
        );

        $resetUrl = route('password.reset', [
            'token' => $plainToken,
            'email' => $user->email,
        ]);

        $emailSent = true;
        try {
            $htmlBody = "
                <div style='font-family: Arial, sans-serif; max-width: 560px; margin: 0 auto; padding: 24px; border: 1px solid #e2e8f0; border-radius: 10px;'>
                    <h2 style='color: #1e3a8a; margin-top: 0;'>East Systems Colleges of Rizal</h2>
                    <p style='color: #334155;'>Hello <strong>" . e($user->name) . "</strong>,</p>
                    <p style='color: #334155;'>We received a request to reset the password for your ESCR Online Enrollment &amp; Payment System account (<strong>" . e($user->email) . "</strong>).</p>
                    <p style='margin: 24px 0;'>
                        <a href='" . e($resetUrl) . "' style='background-color: #1d4ed8; color: #ffffff; text-decoration: none; padding: 12px 22px; border-radius: 6px; font-weight: bold; display: inline-block;'>
                            Reset My ESCR Portal Password
                        </a>
                    </p>
                    <p style='color: #475569; font-size: 13px;'>On the reset page, please enter your <strong>Current Password</strong>, your <strong>New Password</strong>, and <strong>Repeat New Password</strong> to complete the reset.</p>
                    <p style='color: #64748b; font-size: 12px; word-break: break-all;'>Direct Link: <a href='" . e($resetUrl) . "'>" . e($resetUrl) . "</a></p>
                </div>
            ";

            Mail::html($htmlBody, function ($mail) use ($user) {
                $mail->to($user->email, $user->name)
                    ->subject('[ESCR Portal] Password Reset Link');
            });
        } catch (\Throwable $e) {
            $emailSent = false;
            Log::warning('Password reset email fallback: ' . $e->getMessage());
        }

        AuditService::log(
            'PASSWORD_RESET_LINK_SENT',
            "Password reset link generated and sent to {$user->email}.",
            'User',
            $user->id,
            null,
            ['email' => $user->email],
            $user
        );

        return back()
            ->with('success', "We have sent a password reset link to {$user->email}! Please check your email inbox and click the link to reset your password.")
            ->with('reset_link_preview', $resetUrl)
            ->with('email_sent_live', $emailSent);
    }

    public function showResetPassword(Request $request, string $token)
    {
        $email = (string) $request->query('email', '');
        return view('auth.reset_password', compact('token', 'email'));
    }

    public function resetPassword(Request $request)
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email', 'exists:users,email'],
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'confirmed', 'different:current_password'],
        ], [
            'password.different' => 'Your new password must be different from your current password.',
            'password.confirmed' => 'The new password confirmation does not match.',
        ]);

        $record = DB::table('password_reset_tokens')
            ->where('email', $validated['email'])
            ->first();

        if (!$record || !Hash::check($validated['token'], $record->token)) {
            return back()
                ->withErrors(['email' => 'This password reset link is invalid or has already been used. Please request a new link.'])
                ->withInput($request->only('email'));
        }

        if ($record->created_at && Carbon::parse($record->created_at)->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $validated['email'])->delete();
            return back()
                ->withErrors(['email' => 'This password reset link has expired (valid for 60 minutes). Please request a new link.'])
                ->withInput($request->only('email'));
        }

        /** @var User $user */
        $user = User::where('email', $validated['email'])->firstOrFail();

        if (!Hash::check($validated['current_password'], $user->password)) {
            return back()
                ->withErrors(['current_password' => 'Your current password is incorrect.'])
                ->withInput($request->only('email'));
        }

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        DB::table('password_reset_tokens')->where('email', $user->email)->delete();

        AuditService::log(
            'PASSWORD_RESET_COMPLETED',
            "{$user->roleLabel()} {$user->name} reset their password via email reset link.",
            'User',
            $user->id,
            null,
            null,
            $user
        );

        return redirect()->route('login')
            ->with('success', 'Your password has been reset successfully! You may now sign in with your new password.');
    }
}
