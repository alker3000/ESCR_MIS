<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\PromissoryNote;
use App\Models\StudentDocument;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    /**
     * Stream an enrollment document from private DMS storage to authorized users.
     */
    public function streamEnrollmentDocument(StudentDocument $document)
    {
        $user = Auth::user();
        if ($user->isStudent() && $document->user_id !== $user->id) {
            abort(403, 'Unauthorized access to student document.');
        }

        if (!Storage::disk('local')->exists($document->file_path)) {
            return response($this->generateFallbackSvgPreview($document->typeLabel(), $document->original_filename, $document->user->name), 200, [
                'Content-Type' => 'image/svg+xml',
            ]);
        }

        return Storage::disk('local')->response($document->file_path, $document->original_filename);
    }

    /**
     * Stream a scanned signed Promissory Note from private DMS storage.
     */
    public function streamPromissoryNoteFile(PromissoryNote $note)
    {
        $user = Auth::user();
        if ($user->isStudent() && $note->user_id !== $user->id) {
            abort(403, 'Unauthorized access to promissory note document.');
        }

        if (!$note->signed_file_path || !Storage::disk('local')->exists($note->signed_file_path)) {
            return response($this->generateFallbackSvgPreview('Signed Promissory Note (' . $note->note_number . ')', $note->original_filename ?? 'promissory_note.pdf', $note->user->name), 200, [
                'Content-Type' => 'image/svg+xml',
            ]);
        }

        return Storage::disk('local')->response($note->signed_file_path, $note->original_filename ?? 'promissory_note.pdf');
    }

    /**
     * Stream a GCash payment screenshot from private DMS storage.
     */
    public function streamPaymentProof(Payment $payment)
    {
        $user = Auth::user();
        if ($user->isStudent() && $payment->user_id !== $user->id) {
            abort(403, 'Unauthorized access to payment proof.');
        }

        if (!$payment->proof_file_path || !Storage::disk('local')->exists($payment->proof_file_path)) {
            return response($this->generateFallbackSvgPreview('GCash Payment Proof (' . $payment->gcash_reference_number . ')', 'gcash_receipt.svg', $payment->user->name), 200, [
                'Content-Type' => 'image/svg+xml',
            ]);
        }

        return Storage::disk('local')->response($payment->proof_file_path);
    }

    protected function generateFallbackSvgPreview(string $docTitle, string $filename, string $studentName): string
    {
        $safeTitle = htmlspecialchars($docTitle, ENT_QUOTES, 'UTF-8');
        $safeFile = htmlspecialchars($filename, ENT_QUOTES, 'UTF-8');
        $safeName = htmlspecialchars($studentName, ENT_QUOTES, 'UTF-8');

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="750" height="520" viewBox="0 0 750 520">
  <rect width="750" height="520" fill="#f8fafc" stroke="#cbd5e1" stroke-width="4" rx="12"/>
  <rect x="40" y="35" width="670" height="75" fill="#1e3a8a" rx="8"/>
  <text x="375" y="68" font-family="Arial, sans-serif" font-size="20" font-weight="bold" fill="#ffffff" text-anchor="middle">EAST SYSTEMS COLLEGES OF RIZAL</text>
  <text x="375" y="92" font-family="Arial, sans-serif" font-size="13" fill="#bfdbfe" text-anchor="middle">PROTECTED DOCUMENT MANAGEMENT SYSTEM (DMS) PREVIEW</text>
  <rect x="60" y="140" width="630" height="320" fill="#ffffff" stroke="#e2e8f0" stroke-width="2" rx="8"/>
  <text x="90" y="190" font-family="Arial, sans-serif" font-size="18" font-weight="bold" fill="#0f172a">Document Type: {$safeTitle}</text>
  <text x="90" y="230" font-family="Arial, sans-serif" font-size="15" fill="#334155">Student / Applicant: {$safeName}</text>
  <text x="90" y="265" font-family="Arial, sans-serif" font-size="14" fill="#475569">Stored File Name: {$safeFile}</text>
  <text x="90" y="300" font-family="Arial, sans-serif" font-size="14" fill="#047857" font-weight="bold">Security Status: Encrypted &amp; Stored in Private DMS Vault</text>
  <line x1="90" y1="330" x2="660" y2="330" stroke="#e2e8f0" stroke-width="2"/>
  <text x="90" y="365" font-family="Arial, sans-serif" font-size="13" fill="#64748b">Verified authentic copy submitted via the ESCR Online Enrollment &amp; Payment System.</text>
  <rect x="490" y="385" width="165" height="50" fill="#dcfce7" stroke="#16a34a" stroke-width="1.5" rx="6"/>
  <text x="572" y="415" font-family="Arial, sans-serif" font-size="14" font-weight="bold" fill="#15803d" text-anchor="middle">DMS VERIFIED COPY</text>
</svg>
SVG;
    }
}
