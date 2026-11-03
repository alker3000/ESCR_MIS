<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>EAST SYSTEMS COLLEGES OF RIZAL - PROMISSORY NOTE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            background: #e2e8f0;
            font-family: Arial, Helvetica, sans-serif;
            color: #000;
            font-size: 14px;
            line-height: 1.55;
        }
        .paper-sheet {
            max-width: 800px;
            margin: 25px auto;
            background: #ffffff;
            padding: 55px 65px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
        }
        .underline-field {
            display: inline-block;
            border-bottom: 1px solid #000;
            padding: 0 8px;
            font-weight: bold;
            min-width: 180px;
        }
        .sig-line {
            display: inline-block;
            border-bottom: 1px solid #000;
            width: 310px;
            padding: 0 6px;
            font-weight: bold;
        }
        .checkbox-box {
            display: inline-block;
            width: 15px;
            height: 15px;
            border: 1.5px solid #333;
            text-align: center;
            line-height: 13px;
            font-size: 12px;
            font-weight: bold;
            margin-right: 8px;
        }
        @media print {
            body { background: #fff; }
            .paper-sheet { margin: 0; padding: 20px 30px; box-shadow: none; max-width: 100%; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="container no-print pt-3" style="max-width: 800px;">
        <div class="d-flex justify-content-between align-items-center bg-dark text-white p-3 rounded shadow-sm">
            <div>
                <strong>Pre-Filled ESCR Promissory Note Template</strong>
                <div class="small text-white-50">Print or Save as PDF, ask your Parent/Guardian to sign, then upload back to the portal.</div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('student.promissory') }}" class="btn btn-outline-light btn-sm">Back to Portal</a>
                <button onclick="window.print()" class="btn btn-warning btn-sm fw-bold">
                    <i class="bi bi-printer-fill me-1"></i>Print / Save as PDF
                </button>
            </div>
        </div>
    </div>

    @php
        $courseName = $enrollment?->course ?? 'BS in Information Technology';
        $amountBalance = $selectedInvoice ? number_format((float)$selectedInvoice->balance(), 2) : '5,000.00';
        $invType = $selectedInvoice?->invoice_type ?? 'prelim_installment';
    @endphp

    <div class="paper-sheet">
        <div class="text-center mb-4">
            <img src="{{ asset('images/escr-logo.png') }}" alt="ESCR Official Seal" style="width: 64px; height: 64px; object-fit: contain;" class="mb-1">
            <div class="fw-bold" style="font-size: 16px;">EAST SYSTEMS COLLEGES OF RIZAL</div>
            <div style="font-size: 13px;">Morong, Rizal</div>
            <div class="fw-bold mt-1" style="font-size: 20px; letter-spacing: 0.5px;">PROMISSORY NOTE</div>
        </div>

        <div class="mb-4">
            Date: <span class="underline-field" style="min-width: 160px;">{{ now()->format('F d, Y') }}</span>
        </div>

        <p class="mb-2">
            I, <span class="underline-field" style="min-width: 310px;">{{ $enrollment?->fullName() ?? $student->name }}</span>, a student of <strong>East Systems Colleges of Rizal</strong>,
            enrolled in <span class="underline-field" style="min-width: 240px;">{{ $courseName }}</span> for <strong>{{ $settings['academic_year'] ?? 'SY2026-2027' }}</strong> hereby promise to pay my
            outstanding tuition fee balance in the amount of: <strong>&#8369;</strong><span class="underline-field" style="min-width: 150px;">{{ $amountBalance }}</span>.
            <br>
            The payment pertains to my (please check):
        </p>

        <ul class="list-unstyled ms-4 mb-3">
            <li class="mb-1">
                &bull; <span class="checkbox-box">{!! $invType === 'prelim_installment' ? '&#10003;' : '&nbsp;' !!}</span>
                <strong>Prelim Installment</strong>
            </li>
            <li class="mb-1">
                &bull; <span class="checkbox-box">{!! $invType === 'midterm_installment' ? '&#10003;' : '&nbsp;' !!}</span>
                <strong>Midterm Installment</strong>
            </li>
            <li class="mb-1">
                &bull; <span class="checkbox-box">{!! $invType === 'semifinal_installment' ? '&#10003;' : '&nbsp;' !!}</span>
                <strong>Semi-Finals Installment</strong>
            </li>
            <li class="mb-1">
                &bull; <span class="checkbox-box">{!! $invType === 'finals_installment' ? '&#10003;' : '&nbsp;' !!}</span>
                <strong>Finals Installment</strong>
            </li>
        </ul>

        <p class="mb-3">
            I respectfully notify the Office of the Registrar that I am unable to settle the above installment on its scheduled due date, and I hereby request and undertake to have this tuition balance <strong>moved to my Final Term Installment</strong> to be paid in full on or before <span class="underline-field" style="min-width: 230px;">{{ $promisedDate }}</span>, in accordance with the payment terms and policies of the school.
        </p>

        <p class="mb-4">
            I understand that failure to comply with this commitment may result in the withholding of applicable school
            services, records, examination privileges, or other actions in accordance with existing school policies.
            I am executing this Promissory Note voluntarily and acknowledge my responsibility to settle the stated
            obligation within the agreed period.
        </p>

        <div class="mb-4">
            <div><strong>Student's Name:</strong> <span class="sig-line">{{ $enrollment?->fullName() ?? $student->name }}</span></div>
            <div><strong>Signature:</strong> <span class="sig-line">&nbsp;</span></div>
            <div><strong>Date:</strong> <span class="sig-line">{{ now()->format('F d, Y') }}</span></div>
            <div><strong>Contact No.:</strong> <span class="sig-line">{{ $student->contact_number ?: $enrollment?->contact_number }}</span></div>
        </div>

        <div class="mb-4">
            <div class="fw-bold text-uppercase mb-1" style="font-size: 15px;">PARENT/GUARDIAN'S CONFORME</div>
            <p class="mb-3">
                I, <span class="underline-field" style="min-width: 310px;">{{ $enrollment?->guardian_name ?? '' }}</span>, parent/guardian of the above-named student,
                acknowledge and agree to the terms of this Promissory Note and undertake to assist in ensuring that the
                stated tuition fee obligation is paid within the agreed period.
            </p>
            <div><strong>Parent/Guardian's Name:</strong> <span class="sig-line">{{ $enrollment?->guardian_name ?? '' }}</span></div>
            <div><strong>Signature:</strong> <span class="sig-line">&nbsp;</span></div>
            <div><strong>Contact No.:</strong> <span class="sig-line">{{ $enrollment?->guardian_contact ?? '' }}</span></div>
            <div><strong>Date:</strong> <span class="sig-line">{{ now()->format('F d, Y') }}</span></div>
        </div>

        <div class="mt-4 pt-2">
            <div><strong>Received &amp; Approved by:</strong> <span class="sig-line">&nbsp;</span></div>
            <div><strong>Position:</strong> <span class="sig-line">Office of the Registrar / Cashier</span></div>
            <div><strong>Signature:</strong> <span class="sig-line">&nbsp;</span></div>
            <div><strong>Date:</strong> <span class="sig-line">&nbsp;</span></div>
        </div>
    </div>
</body>
</html>
