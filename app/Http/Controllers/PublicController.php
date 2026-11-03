<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\SystemSetting;
use Illuminate\Http\Request;

class PublicController extends Controller
{
    public function home()
    {
        $settings = SystemSetting::getAllKeyed();
        return view('public.home', compact('settings'));
    }

    public function trackApplication(Request $request)
    {
        $reference = trim((string) $request->query('ref', ''));
        $enrollment = null;

        if ($reference !== '') {
            $enrollment = Enrollment::with(['documents', 'invoices', 'user'])
                ->where('application_reference', $reference)
                ->orWhereHas('user', function ($q) use ($reference) {
                    $q->where('student_number', $reference);
                })
                ->first();
        }

        return view('public.track', compact('reference', 'enrollment'));
    }
}
