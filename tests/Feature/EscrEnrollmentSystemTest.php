<?php

namespace Tests\Feature;

use App\Models\Enrollment;
use App\Models\Invoice;
use App\Models\PromissoryNote;
use App\Models\TelaOrder;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EscrEnrollmentSystemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->seed(DatabaseSeeder::class);
    }

    public function test_public_home_and_application_tracker_render_correctly(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('East Systems Colleges of Rizal');
        $response->assertSee('Before AUG 17');

        $trackResponse = $this->get('/track-application?ref=APP-2026-JUAN01');
        $trackResponse->assertStatus(200);
        $trackResponse->assertSee('Juan Reyes Dela Cruz');
    }

    public function test_complete_enrollment_and_downpayment_flow(): void
    {
        $applicant = User::where('email', 'applicant@escr.edu.ph')->firstOrFail();
        $registrar = User::where('email', 'registrar@escr.edu.ph')->firstOrFail();
        $enrollment = Enrollment::where('user_id', $applicant->id)->firstOrFail();

        // 1. Registrar approves documents -> generates Downpayment + 4 Installments
        $this->actingAs($registrar)
            ->post(route('registrar.applications.approve', $enrollment), [
                'registrar_remarks' => 'Verified all documents.',
            ])
            ->assertRedirect(route('registrar.applications'));

        $enrollment->refresh();
        $this->assertEquals('approved_awaiting_payment', $enrollment->status);
        $this->assertEquals(5, $enrollment->invoices()->count()); // 1 Downpayment + 4 Installments

        // 2. Student pays Downpayment via GCash Sandbox -> Officially Enrolled!
        $dpInvoice = $enrollment->invoices()->where('invoice_type', 'downpayment')->firstOrFail();

        $this->actingAs($applicant)
            ->post(route('student.billing.pay.submit', $dpInvoice), [
                'payment_mode' => 'sandbox_instant',
                'gcash_sender_name' => $applicant->name,
                'gcash_sender_number' => '09175550303',
                'gcash_reference_number' => '9018999888777',
            ])
            ->assertRedirect();

        $enrollment->refresh();
        $applicant->refresh();
        $this->assertEquals('enrolled', $enrollment->status);
        $this->assertNotNull($applicant->student_number);
    }

    public function test_promissory_note_moves_term_tuition_to_finals_and_clears_exam(): void
    {
        $student = User::where('email', 'student@escr.edu.ph')->firstOrFail();
        $registrar = User::where('email', 'registrar@escr.edu.ph')->firstOrFail();
        $cashier = User::where('email', 'cashier@escr.edu.ph')->firstOrFail();
        $enrollment = $student->enrollment;

        $midtermInvoice = Invoice::where('user_id', $student->id)
            ->where('invoice_type', 'midterm_installment')
            ->firstOrFail();

        $semifinalInvoice = Invoice::where('user_id', $student->id)
            ->where('invoice_type', 'semifinal_installment')
            ->firstOrFail();

        $finalsInvoice = Invoice::where('user_id', $student->id)
            ->where('invoice_type', 'finals_installment')
            ->firstOrFail();

        $this->assertEquals(5000.00, (float) $finalsInvoice->amount);

        // 1. Before deadline (while Midterm is still 'unpaid'): Student uploads signed Promissory Note to warn Registrar
        $fakePdf = UploadedFile::fake()->create('signed_promissory_note.pdf', 120, 'application/pdf');
        $this->actingAs($student)
            ->post(route('student.promissory.upload'), [
                'invoice_id' => $midtermInvoice->id,
                'promised_settlement_date' => '2027-11-09',
                'student_contact' => '09175550101',
                'guardian_name' => 'Ricardo Dela Cruz',
                'guardian_contact' => '09175550199',
                'signed_note_file' => $fakePdf,
            ])
            ->assertRedirect(route('student.promissory'));

        $note = PromissoryNote::where('invoice_id', $midtermInvoice->id)->firstOrFail();
        $this->assertEquals('pending_review', $note->status);

        // 2. Registrar approves Promissory Note -> Moves ₱5,000 balance to Finals Installment (₱10,000) and keeps student cleared!
        $this->actingAs($registrar)
            ->post(route('registrar.promissory.approve', $note), [
                'registrar_remarks' => 'Parent signature verified. Moved Midterm tuition to Finals.',
            ])
            ->assertRedirect();

        $note->refresh();
        $midtermInvoice->refresh();
        $finalsInvoice->refresh();
        $enrollment->refresh();

        $this->assertEquals('approved', $note->status);
        $this->assertEquals('promised', $midtermInvoice->status);
        $this->assertEquals(0.00, $midtermInvoice->balance());
        $this->assertEquals(10000.00, (float) $finalsInvoice->amount);
        $this->assertFalse($enrollment->exam_restricted);

        // 3. Simulate missing the Semi-Finals payment deadline (Late) -> Student is Exam Restricted & CANNOT pass a Promissory Note!
        $this->actingAs($student)
            ->post(route('student.billing.simulate_overdue', $semifinalInvoice))
            ->assertRedirect(route('student.billing'));

        $enrollment->refresh();
        $semifinalInvoice->refresh();
        $this->assertEquals('overdue', $semifinalInvoice->status);
        $this->assertTrue($enrollment->exam_restricted);

        // Attempting to upload a Promissory Note for the late/overdue installment is blocked
        $latePdf = UploadedFile::fake()->create('late_note.pdf', 100, 'application/pdf');
        $this->actingAs($student)
            ->post(route('student.promissory.upload'), [
                'invoice_id' => $semifinalInvoice->id,
                'promised_settlement_date' => '2027-11-09',
                'student_contact' => '09175550101',
                'guardian_name' => 'Ricardo Dela Cruz',
                'guardian_contact' => '09175550199',
                'signed_note_file' => $latePdf,
            ])
            ->assertRedirect(route('student.promissory'))
            ->assertSessionHas('error');

        $this->assertNull(PromissoryNote::where('invoice_id', $semifinalInvoice->id)->first());

        // Paying the late/overdue Semi-Finals installment via GCash immediately lifts the exam restriction!
        $this->actingAs($student)
            ->post(route('student.billing.pay.submit', $semifinalInvoice), [
                'payment_mode' => 'sandbox_instant',
                'gcash_sender_name' => $student->name,
                'gcash_sender_number' => '09175550101',
                'gcash_reference_number' => '9018777666555',
            ])
            ->assertRedirect();

        $enrollment->refresh();
        $semifinalInvoice->refresh();
        $this->assertEquals('paid', $semifinalInvoice->status);
        $this->assertFalse($enrollment->exam_restricted);

        // 4. Student pays the Finals Installment (₱10,000) via Walk-In Cash -> Cashier confirms cash -> Issued Official Receipt!
        $this->actingAs($student)
            ->post(route('student.billing.pay.submit', $finalsInvoice), [
                'payment_mode' => 'walk_in_cash',
                'gcash_sender_name' => $student->name,
                'gcash_sender_number' => '09175550101',
                'cash_reference_number' => 'CSH-2026-TEST01',
            ])
            ->assertRedirect(route('student.billing'));

        $finalsInvoice->refresh();
        $this->assertEquals('pending_verification', $finalsInvoice->status);

        $cashPayment = $finalsInvoice->latestPayment;
        $this->assertNotNull($cashPayment);
        $this->assertEquals('cash', $cashPayment->payment_method);
        $this->assertEquals('pending', $cashPayment->status);

        // Cashier confirms walk-in cash received
        $this->actingAs($cashier)
            ->post(route('cashier.payments.verify', $cashPayment))
            ->assertRedirect();

        $finalsInvoice->refresh();
        $cashPayment->refresh();
        $this->assertEquals('paid', $finalsInvoice->status);
        $this->assertEquals('verified', $cashPayment->status);
    }

    public function test_uniform_tela_ordering_gcash_payment_and_cashier_pickup_workflow(): void
    {
        $student = User::where('email', 'student@escr.edu.ph')->firstOrFail();
        $cashier = User::where('email', 'cashier@escr.edu.ph')->firstOrFail();

        // 1. Student orders 2 yards Blouse/Polo (160/yd = 320), 1.5 yards Skirt (100/yd = 150), 2 Patches (100/pc = 200) => Total = 670
        $this->actingAs($student)
            ->post(route('student.tela.store'), [
                'blouse_polo_yards' => 2.0,
                'skirt_yards' => 1.5,
                'logo_patch_qty' => 2,
                'student_notes' => 'Standard BSIT uniform tela',
            ])
            ->assertRedirect();

        $order = TelaOrder::where('user_id', $student->id)->latest('id')->firstOrFail();
        $this->assertEquals(670.00, (float) $order->total_amount);
        $this->assertEquals('awaiting_payment', $order->status);

        // 2. Pay Tela Invoice via GCash -> Routes to Cashier Dashboard (paid_processing)
        $this->actingAs($student)
            ->post(route('student.billing.pay.submit', $order->invoice_id), [
                'payment_mode' => 'sandbox_instant',
                'gcash_sender_name' => $student->name,
                'gcash_sender_number' => '09175550101',
                'gcash_reference_number' => '9018333222111',
            ])
            ->assertRedirect();

        $order->refresh();
        $this->assertEquals('paid_processing', $order->status);

        // 3. Cashier clicks "Ready for Pickup" -> Notifies student
        $this->actingAs($cashier)
            ->post(route('cashier.tela.ready', $order))
            ->assertRedirect();

        $order->refresh();
        $this->assertEquals('ready_for_pickup', $order->status);

        // 4. Cashier marks order as "Claimed"
        $this->actingAs($cashier)
            ->post(route('cashier.tela.claimed', $order))
            ->assertRedirect();

        $order->refresh();
        $this->assertEquals('claimed', $order->status);
    }

    public function test_rbac_prevents_student_from_accessing_staff_portals(): void
    {
        $student = User::where('email', 'student@escr.edu.ph')->firstOrFail();

        $this->actingAs($student)
            ->get(route('cashier.dashboard'))
            ->assertRedirect(route('student.dashboard'));

        $this->actingAs($student)
            ->get(route('registrar.dashboard'))
            ->assertRedirect(route('student.dashboard'));

        $this->actingAs($student)
            ->get(route('admin.dashboard'))
            ->assertRedirect(route('student.dashboard'));
    }

    public function test_new_student_registration_does_not_auto_populate_demo_fields(): void
    {
        $this->post('/register', [
            'name' => 'Alker Eumague',
            'email' => 'alker@escr.edu.ph',
            'contact_number' => '09511710598',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('student.enrollment'));

        $newStudent = User::where('email', 'alker@escr.edu.ph')->firstOrFail();

        $response = $this->actingAs($newStudent)->get(route('student.enrollment'));
        $response->assertStatus(200);
        $response->assertSee('Alker');
        $response->assertSee('Eumague');
        $response->assertSee('09511710598');
        $response->assertDontSee('2006-05-15');
        $response->assertDontSee('value="Morong, Rizal"', false);
        $response->assertDontSee('value="2025-2026"', false);
    }

    public function test_user_can_change_password_and_view_manual_gcash_qr_and_cash_checkout(): void
    {
        $student = User::where('email', 'student@escr.edu.ph')->firstOrFail();

        $midtermInvoice = Invoice::where('user_id', $student->id)
            ->where('invoice_type', 'midterm_installment')
            ->firstOrFail();

        // 1. Checkout page renders Manual GCash QR Code, Merchant Number, and Cash payment option
        $checkoutResponse = $this->actingAs($student)->get(route('student.billing.pay', $midtermInvoice));
        $checkoutResponse->assertStatus(200);
        $checkoutResponse->assertSee('0917-888-3727');
        $checkoutResponse->assertSee('EAST SYSTEMS COLLEGES OF RIZAL');
        $checkoutResponse->assertSee('Cash (School Cashier)');

        // 2. Wrong current password fails validation
        $this->actingAs($student)
            ->from(route('student.dashboard'))
            ->post(route('account.password.update'), [
                'current_password' => 'wrongpassword',
                'password' => 'newsecret123',
                'password_confirmation' => 'newsecret123',
            ])
            ->assertRedirect(route('student.dashboard'))
            ->assertSessionHasErrors('current_password');

        // 3. Valid current password updates user password hash
        $this->actingAs($student)
            ->from(route('student.dashboard'))
            ->post(route('account.password.update'), [
                'current_password' => 'password123',
                'password' => 'newsecret123',
                'password_confirmation' => 'newsecret123',
            ])
            ->assertRedirect(route('student.dashboard'))
            ->assertSessionHas('success');

        $student->refresh();
        $this->assertTrue(Hash::check('newsecret123', $student->password));
    }

    public function test_multi_semester_continuing_student_reenrollment_and_document_carryover(): void
    {
        $admin = User::where('email', 'admin@escr.edu.ph')->firstOrFail();
        $registrar = User::where('email', 'registrar@escr.edu.ph')->firstOrFail();
        $student = User::where('email', 'student@escr.edu.ph')->firstOrFail();

        $firstSemEnrollment = $student->enrollments()->where('semester', '1st Semester')->firstOrFail();
        $firstSemDocCount = $firstSemEnrollment->documents()->count();
        $this->assertGreaterThan(0, $firstSemDocCount);

        // 1. Super Admin switches active semester to 2nd Semester (SY 2026-2027)
        $this->actingAs($admin)
            ->post(route('admin.settings.fees.update'), [
                'academic_year' => 'SY 2026-2027',
                'semester' => '2nd Semester',
                'prelim_deadline' => '2028-01-25',
                'prelim_pn_deadline' => '2028-01-18',
                'midterm_deadline' => '2028-02-22',
                'midterm_pn_deadline' => '2028-02-15',
                'semifinal_deadline' => '2028-03-21',
                'semifinal_pn_deadline' => '2028-03-14',
                'finals_deadline' => '2028-04-18',
                'finals_pn_deadline' => '2028-04-11',
            ])
            ->assertRedirect();

        // 2. Student Dashboard shows "2nd Semester (SY 2026-2027) Enrollment is Now Open!"
        $dashResponse = $this->actingAs($student)->get(route('student.dashboard'));
        $dashResponse->assertStatus(200);
        $dashResponse->assertSee('2nd Semester (SY 2026-2027) Enrollment is Now Open!');

        // 3. Student visits Enrollment page -> Form is unlocked for 2nd Semester and pre-filled with 1st Sem profile
        $enrPage = $this->actingAs($student)->get(route('student.enrollment'));
        $enrPage->assertStatus(200);
        $enrPage->assertSee('Continuing Student Re-Enrollment: 2nd Semester (SY 2026-2027)');
        $enrPage->assertSee($firstSemEnrollment->address);

        // 4. Student submits 2nd Semester re-enrollment without re-uploading DMS files
        $this->actingAs($student)
            ->post(route('student.enrollment.submit'), [
                'course' => $firstSemEnrollment->course,
                'year_level' => '2nd Year',
                'student_type' => 'Continuing / Old',
                'first_name' => $firstSemEnrollment->first_name,
                'middle_name' => $firstSemEnrollment->middle_name,
                'last_name' => $firstSemEnrollment->last_name,
                'gender' => $firstSemEnrollment->gender,
                'birth_date' => $firstSemEnrollment->birth_date->format('Y-m-d'),
                'address' => $firstSemEnrollment->address,
                'contact_number' => $firstSemEnrollment->contact_number,
                'guardian_name' => $firstSemEnrollment->guardian_name,
                'guardian_relationship' => $firstSemEnrollment->guardian_relationship,
                'guardian_contact' => $firstSemEnrollment->guardian_contact,
                'previous_school' => $firstSemEnrollment->previous_school,
                'previous_school_year' => $firstSemEnrollment->previous_school_year,
                'payment_scheme' => 'installment',
            ])
            ->assertRedirect(route('student.enrollment'));

        // Verify student now has TWO separate Enrollment records (1st Semester + 2nd Semester)
        $this->assertEquals(2, $student->enrollments()->count());

        $secondSemEnrollment = $student->enrollments()->where('semester', '2nd Semester')->firstOrFail();
        $this->assertEquals('Continuing / Old', $secondSemEnrollment->student_type);
        // Verify DMS documents were automatically carried over from 1st Semester!
        $this->assertEquals($firstSemDocCount, $secondSemEnrollment->documents()->count());

        // 5. Registrar approves 2nd Semester application -> generates 5 new 2nd Semester invoices
        $this->actingAs($registrar)
            ->post(route('registrar.applications.approve', $secondSemEnrollment), [
                'registrar_remarks' => '2nd Semester continuing enrollment approved.',
            ])
            ->assertRedirect(route('registrar.applications'));

        $this->assertEquals(5, $secondSemEnrollment->invoices()->count());
        $this->assertEquals(6, $firstSemEnrollment->invoices()->count()); // 5 tuition/installments + 1 uniform tela order
    }

    public function test_all_student_registrar_cashier_and_admin_pages_render_without_error(): void
    {
        $student = User::where('email', 'student@escr.edu.ph')->firstOrFail();
        $promissoryStudent = User::where('email', 'promissory@escr.edu.ph')->firstOrFail();
        $registrar = User::where('email', 'registrar@escr.edu.ph')->firstOrFail();
        $cashier = User::where('email', 'cashier@escr.edu.ph')->firstOrFail();
        $admin = User::where('email', 'admin@escr.edu.ph')->firstOrFail();

        $enrollment = $student->enrollment;
        $unpaidInvoice = $student->invoices()->where('status', 'unpaid')->firstOrFail();
        $paidPayment = $student->payments()->where('status', 'verified')->firstOrFail();

        // 1. Public & Auth Pages
        $this->get(route('home'))->assertStatus(200);
        $this->get(route('public.track'))->assertStatus(200);
        $this->get(route('login'))->assertStatus(200);
        $this->get(route('register'))->assertStatus(200);

        // 2. All Student Portal Pages (Enrolled Student & Promissory Student)
        $this->actingAs($student)->get(route('student.dashboard'))->assertStatus(200);
        $this->actingAs($student)->get(route('student.enrollment'))->assertStatus(200);
        $this->actingAs($student)->get(route('student.billing'))->assertStatus(200);
        $this->actingAs($student)->get(route('student.billing.pay', $unpaidInvoice))->assertStatus(200);
        $this->actingAs($student)->get(route('student.promissory'))->assertStatus(200);
        $this->actingAs($student)->get(route('student.promissory.template'))->assertStatus(200);
        $this->actingAs($student)->get(route('student.clearance'))->assertStatus(200);
        $this->actingAs($student)->get(route('student.tela'))->assertStatus(200);
        $this->actingAs($student)->get(route('student.receipt', $paidPayment))->assertStatus(200);

        $this->actingAs($promissoryStudent)->get(route('student.dashboard'))->assertStatus(200);
        $this->actingAs($promissoryStudent)->get(route('student.billing'))->assertStatus(200);
        $this->actingAs($promissoryStudent)->get(route('student.promissory'))->assertStatus(200);
        $this->actingAs($promissoryStudent)->get(route('student.clearance'))->assertStatus(200);

        // 3. All Registrar Portal Pages
        $this->actingAs($registrar)->get(route('registrar.dashboard'))->assertStatus(200);
        $this->actingAs($registrar)->get(route('registrar.applications'))->assertStatus(200);
        $this->actingAs($registrar)->get(route('registrar.applications.review', $enrollment))->assertStatus(200);
        $this->actingAs($registrar)->get(route('registrar.promissory'))->assertStatus(200);
        $this->actingAs($registrar)->get(route('registrar.students'))->assertStatus(200);

        // 4. All Cashier Portal Pages
        $this->actingAs($cashier)->get(route('cashier.dashboard'))->assertStatus(200);
        $this->actingAs($cashier)->get(route('cashier.payments'))->assertStatus(200);
        $this->actingAs($cashier)->get(route('cashier.tela'))->assertStatus(200);
        $this->actingAs($cashier)->get(route('cashier.receipts'))->assertStatus(200);

        // 5. All Super Admin Portal Pages
        $this->actingAs($admin)->get(route('admin.dashboard'))->assertStatus(200);
        $this->actingAs($admin)->get(route('admin.users'))->assertStatus(200);
        $this->actingAs($admin)->get(route('admin.settings.fees'))->assertStatus(200);
        $this->actingAs($admin)->get(route('admin.audit'))->assertStatus(200);
    }

    public function test_forgot_password_sends_email_link_and_resets_password_with_current_password(): void
    {
        \Illuminate\Support\Facades\Mail::fake();
        $student = User::where('email', 'student@escr.edu.ph')->firstOrFail();

        // 1. Forgot Password page renders
        $this->get(route('password.request'))
            ->assertStatus(200)
            ->assertSee('Forgot Your Password?');

        // 2. Submit registered email -> generates reset link in session & stores token
        $response = $this->from(route('password.request'))
            ->post(route('password.email'), [
                'email' => $student->email,
            ]);

        $response->assertRedirect(route('password.request'));
        $response->assertSessionHas('reset_link_preview');

        $resetUrl = session('reset_link_preview');
        $this->assertNotEmpty($resetUrl);

        // Extract token from the reset URL path (/reset-password/{token}?email=...)
        $path = parse_url($resetUrl, PHP_URL_PATH);
        $token = basename($path);

        // 3. Visit Reset Password page from the email link
        $this->get(route('password.reset', ['token' => $token, 'email' => $student->email]))
            ->assertStatus(200)
            ->assertSee('Current Password')
            ->assertSee('New Password')
            ->assertSee('Repeat New Password');

        // 4. Submitting wrong current password fails validation
        $this->from(route('password.reset', ['token' => $token, 'email' => $student->email]))
            ->post(route('password.update'), [
                'token' => $token,
                'email' => $student->email,
                'current_password' => 'wrongcurrent123',
                'password' => 'mynewpass789',
                'password_confirmation' => 'mynewpass789',
            ])
            ->assertSessionHasErrors('current_password');

        // 5. Submitting valid current password + new password + repeat new password resets password and deletes token
        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $student->email,
            'current_password' => 'password123',
            'password' => 'mynewpass789',
            'password_confirmation' => 'mynewpass789',
        ])
            ->assertRedirect(route('login'))
            ->assertSessionHas('success');

        $student->refresh();
        $this->assertTrue(Hash::check('mynewpass789', $student->password));
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => $student->email]);
    }
}
