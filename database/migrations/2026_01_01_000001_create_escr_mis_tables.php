<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. System Settings (Fees, Deadlines, Tela Pricing)
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('group')->default('general');
            $table->string('label')->nullable();
            $table->timestamps();
        });

        // 2. Student Enrollments
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('application_reference')->unique();
            $table->string('academic_year')->default('SY 2026-2027');
            $table->string('semester')->default('1st Semester');
            $table->string('course');
            $table->string('year_level');
            $table->string('student_type')->default('New / Freshman');
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('gender')->nullable();
            $table->date('birth_date')->nullable();
            $table->text('address');
            $table->string('contact_number');
            $table->string('guardian_name');
            $table->string('guardian_relationship')->default('Parent/Guardian');
            $table->string('guardian_contact');
            $table->string('previous_school')->nullable();
            $table->string('previous_school_year')->nullable();
            $table->string('payment_scheme')->default('installment'); // full, installment
            $table->string('status')->default('pending')->index();
            // pending, under_review, returned, approved_awaiting_payment, enrolled
            $table->boolean('exam_restricted')->default(false);
            $table->string('exam_restriction_reason')->nullable();
            $table->text('registrar_remarks')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('enrolled_at')->nullable();
            $table->timestamps();
        });

        // 3. Document Management System (DMS)
        Schema::create('student_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained('enrollments')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('document_type');
            // psa_birth_certificate, form_138_report_card, good_moral_certificate, id_picture, transfer_credentials
            $table->string('original_filename');
            $table->string('file_path');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->default(0);
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->text('remarks')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });

        // 4. Invoices (Tuition, Downpayment, Installments, Special Exam, Completion, Tela)
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('enrollment_id')->nullable()->constrained('enrollments')->cascadeOnDelete();
            $table->string('invoice_type')->index();
            // full_tuition, downpayment, prelim_installment, midterm_installment,
            // semifinal_installment, finals_installment, special_exam_fee, completion_fee, tela_order
            $table->string('exam_period')->nullable(); // Prelim, Midterm, Semi-Finals, Finals
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('amount', 10, 2);
            $table->decimal('amount_paid', 10, 2)->default(0.00);
            $table->date('due_date')->nullable();
            $table->date('promised_date')->nullable();
            $table->string('status')->default('unpaid')->index();
            // unpaid, pending_verification, paid, overdue, promised
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        // 5. Payments (GCash Transactions & Official Receipts)
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_number')->unique();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('payment_method')->default('gcash');
            $table->string('payment_mode')->default('sandbox_instant'); // sandbox_instant, manual_reference
            $table->string('gcash_reference_number');
            $table->string('gcash_sender_name');
            $table->string('gcash_sender_number');
            $table->decimal('amount', 10, 2);
            $table->string('proof_file_path')->nullable();
            $table->string('status')->default('verified')->index(); // pending, verified, rejected
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->text('cashier_remarks')->nullable();
            $table->timestamps();
        });

        // 6. Promissory Notes
        Schema::create('promissory_notes', function (Blueprint $table) {
            $table->id();
            $table->string('note_number')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('enrollment_id')->constrained('enrollments')->cascadeOnDelete();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->foreignId('special_exam_invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            $table->string('installment_period');
            // Prelim Installment, Midterm Installment, Semi-Finals Installment, Finals Installment
            $table->decimal('outstanding_amount', 10, 2);
            $table->date('promised_settlement_date');
            $table->date('submission_deadline')->nullable();
            $table->string('student_contact');
            $table->string('guardian_name');
            $table->string('guardian_contact');
            $table->string('signed_file_path')->nullable();
            $table->string('original_filename')->nullable();
            $table->string('status')->default('pending_review')->index();
            // pending_review, approved, rejected
            $table->text('registrar_remarks')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });

        // 7. Uniform Fabric (Tela) & Logo Patch Orders
        Schema::create('tela_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('enrollment_id')->nullable()->constrained('enrollments')->nullOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            $table->decimal('blouse_polo_yards', 6, 2)->default(0);
            $table->decimal('blouse_polo_unit_price', 10, 2)->default(160.00);
            $table->decimal('skirt_yards', 6, 2)->default(0);
            $table->decimal('skirt_unit_price', 10, 2)->default(100.00);
            $table->integer('logo_patch_qty')->default(0);
            $table->decimal('logo_patch_unit_price', 10, 2)->default(100.00);
            $table->decimal('total_amount', 10, 2);
            $table->text('student_notes')->nullable();
            $table->string('status')->default('awaiting_payment')->index();
            // awaiting_payment, paid_processing, ready_for_pickup, claimed
            $table->timestamp('ready_at')->nullable();
            $table->timestamp('claimed_at')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('cashier_remarks')->nullable();
            $table->timestamps();
        });

        // 8. Automated Email & Portal Notifications
        Schema::create('system_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('type');
            $table->string('subject');
            $table->text('message');
            $table->string('channel')->default('email_and_portal');
            $table->boolean('is_read')->default(false);
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });

        // 9. Security Audit Trail / Activity Logs
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('actor_name');
            $table->string('actor_role');
            $table->string('action')->index();
            $table->string('target_type')->nullable();
            $table->unsignedBigInteger('target_id')->nullable();
            $table->text('description');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('system_notifications');
        Schema::dropIfExists('tela_orders');
        Schema::dropIfExists('promissory_notes');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('student_documents');
        Schema::dropIfExists('enrollments');
        Schema::dropIfExists('system_settings');
    }
};
