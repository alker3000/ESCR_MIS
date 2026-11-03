@extends('layouts.app')

@section('title', 'Academic Deadlines, Fees & Tela Pricing | ESCR Super Admin')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-sliders text-primary me-2"></i>Academic Term Deadlines, Fees &amp; Uniform Tela Configuration</h4>
        <p class="text-muted small mb-0">Configure active semester/school year, exam payment deadlines, Promissory Note submission dates, and Uniform Fabric pricing.</p>
    </div>
    <div class="d-flex flex-wrap gap-1">
        <button type="button" class="btn btn-sm btn-outline-primary fw-semibold" onclick="applyTermPreset('1st Semester')">
            <i class="bi bi-calendar-event me-1"></i>1st Sem Preset (Aug&ndash;Nov)
        </button>
        <button type="button" class="btn btn-sm btn-outline-primary fw-semibold" onclick="applyTermPreset('2nd Semester')">
            <i class="bi bi-calendar2-check me-1"></i>2nd Sem Preset (Jan&ndash;Apr)
        </button>
        <button type="button" class="btn btn-sm btn-outline-primary fw-semibold" onclick="applyTermPreset('Summer / Midyear')">
            <i class="bi bi-sun me-1"></i>Summer Preset (May&ndash;Jun)
        </button>
    </div>
</div>

<form action="{{ route('admin.settings.fees.update') }}" method="POST">
    @csrf
    <div class="row g-4">
        <!-- Tuition, Special Exam & Completion Fees -->
        <div class="col-lg-4">
            <div class="card p-4 h-100">
                <h6 class="fw-bold text-primary mb-3"><i class="bi bi-cash-coin me-2"></i>Active Academic Term &amp; Fees</h6>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Academic Year</label>
                    <input type="text" name="academic_year" id="settingAcademicYear" class="form-control" value="{{ $settings['academic_year'] ?? 'SY 2026-2027' }}" placeholder="e.g. SY 2026-2027">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Active Semester / Term</label>
                    @php
                        $currentSem = $settings['semester'] ?? '1st Semester';
                    @endphp
                    <select name="semester" id="settingSemester" class="form-select fw-semibold text-primary">
                        @foreach(['1st Semester', '2nd Semester', 'Summer / Midyear'] as $semOpt)
                            <option value="{{ $semOpt }}" {{ $currentSem === $semOpt ? 'selected' : '' }}>{{ $semOpt }}</option>
                        @endforeach
                    </select>
                    <div class="form-text small">Switching to a new semester opens re-enrollment for continuing students.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Full Semester Tuition &amp; Misc Fee (PHP)</label>
                    <input type="number" step="0.01" name="base_tuition_fee" class="form-control" value="{{ $settings['base_tuition_fee'] ?? '25000.00' }}">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Required Upon-Enrollment Downpayment (PHP)</label>
                    <input type="number" step="0.01" name="downpayment_amount" class="form-control" value="{{ $settings['downpayment_amount'] ?? '5000.00' }}">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold text-danger">Special Examination Fee (PHP)</label>
                    <input type="number" step="0.01" name="special_exam_fee" class="form-control" value="{{ $settings['special_exam_fee'] ?? '350.00' }}">
                </div>
                <div class="mb-0">
                    <label class="form-label small fw-semibold">Incomplete Completion / Remedial Fee (PHP)</label>
                    <input type="number" step="0.01" name="completion_fee" class="form-control" value="{{ $settings['completion_fee'] ?? '500.00' }}">
                </div>
            </div>
        </div>

        <!-- Active Term Payment & Promissory Note Deadlines -->
        <div class="col-lg-4">
            <div class="card p-4 h-100">
                <h6 class="fw-bold text-danger mb-3"><i class="bi bi-calendar-range me-2"></i>{{ $settings['semester'] ?? '1st Semester' }} Payment &amp; Promissory Deadlines</h6>

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Before Prelim Payment</label>
                        <input type="date" id="prelim_deadline" name="prelim_deadline" class="form-control form-control-sm" value="{{ $settings['prelim_deadline'] ?? '2027-08-17' }}">
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Prelim PN Deadline</label>
                        <input type="date" id="prelim_pn_deadline" name="prelim_pn_deadline" class="form-control form-control-sm" value="{{ $settings['prelim_pn_deadline'] ?? '2027-08-10' }}">
                    </div>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Before Midterm Payment</label>
                        <input type="date" id="midterm_deadline" name="midterm_deadline" class="form-control form-control-sm" value="{{ $settings['midterm_deadline'] ?? '2027-09-14' }}">
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Midterm PN Deadline</label>
                        <input type="date" id="midterm_pn_deadline" name="midterm_pn_deadline" class="form-control form-control-sm" value="{{ $settings['midterm_pn_deadline'] ?? '2027-09-07' }}">
                    </div>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Before Semi-Finals</label>
                        <input type="date" id="semifinal_deadline" name="semifinal_deadline" class="form-control form-control-sm" value="{{ $settings['semifinal_deadline'] ?? '2027-10-12' }}">
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Semi-Finals PN Deadline</label>
                        <input type="date" id="semifinal_pn_deadline" name="semifinal_pn_deadline" class="form-control form-control-sm" value="{{ $settings['semifinal_pn_deadline'] ?? '2027-10-05' }}">
                    </div>
                </div>

                <div class="row g-2">
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Before Finals Payment</label>
                        <input type="date" id="finals_deadline" name="finals_deadline" class="form-control form-control-sm" value="{{ $settings['finals_deadline'] ?? '2027-11-09' }}">
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Finals PN Deadline</label>
                        <input type="date" id="finals_pn_deadline" name="finals_pn_deadline" class="form-control form-control-sm" value="{{ $settings['finals_pn_deadline'] ?? '2027-11-02' }}">
                    </div>
                </div>
            </div>
        </div>

        <!-- Uniform Tela Pricing & GCash Merchant Info -->
        <div class="col-lg-4">
            <div class="card p-4 h-100">
                <h6 class="fw-bold text-success mb-3"><i class="bi bi-scissors me-2"></i>Uniform Tela Pricing &amp; GCash</h6>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Blouse / Polo Fabric Price per Yard (PHP)</label>
                    <input type="number" step="0.01" name="tela_blouse_polo_price" class="form-control" value="{{ $settings['tela_blouse_polo_price'] ?? '160.00' }}">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Skirt Fabric Price per Yard (PHP)</label>
                    <input type="number" step="0.01" name="tela_skirt_price" class="form-control" value="{{ $settings['tela_skirt_price'] ?? '100.00' }}">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Logo Patch Price per Piece (PHP)</label>
                    <input type="number" step="0.01" name="logo_patch_price" class="form-control" value="{{ $settings['logo_patch_price'] ?? '100.00' }}">
                </div>
                <hr>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">GCash Merchant Account Name</label>
                    <input type="text" name="gcash_merchant_name" class="form-control form-control-sm" value="{{ $settings['gcash_merchant_name'] ?? 'EAST SYSTEMS COLLEGES OF RIZAL' }}">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">GCash Merchant Number</label>
                    <input type="text" name="gcash_merchant_number" class="form-control form-control-sm" value="{{ $settings['gcash_merchant_number'] ?? '0917-888-3727' }}">
                </div>
                <button type="submit" class="btn btn-primary w-100 fw-bold py-2 mt-auto">
                    <i class="bi bi-save2-fill me-1"></i>Save All System Settings
                </button>
            </div>
        </div>
    </div>
</form>

<script>
function applyTermPreset(semester) {
    const semSelect = document.getElementById('settingSemester');
    if (semSelect) {
        semSelect.value = semester;
    }

    const presets = {
        '1st Semester': {
            prelim_deadline: '2027-08-17',
            prelim_pn_deadline: '2027-08-10',
            midterm_deadline: '2027-09-14',
            midterm_pn_deadline: '2027-09-07',
            semifinal_deadline: '2027-10-12',
            semifinal_pn_deadline: '2027-10-05',
            finals_deadline: '2027-11-09',
            finals_pn_deadline: '2027-11-02'
        },
        '2nd Semester': {
            prelim_deadline: '2028-01-25',
            prelim_pn_deadline: '2028-01-18',
            midterm_deadline: '2028-02-22',
            midterm_pn_deadline: '2028-02-15',
            semifinal_deadline: '2028-03-21',
            semifinal_pn_deadline: '2028-03-14',
            finals_deadline: '2028-04-18',
            finals_pn_deadline: '2028-04-11'
        },
        'Summer / Midyear': {
            prelim_deadline: '2028-05-15',
            prelim_pn_deadline: '2028-05-10',
            midterm_deadline: '2028-05-29',
            midterm_pn_deadline: '2028-05-24',
            semifinal_deadline: '2028-06-12',
            semifinal_pn_deadline: '2028-06-07',
            finals_deadline: '2028-06-26',
            finals_pn_deadline: '2028-06-21'
        }
    };

    const selected = presets[semester];
    if (!selected) return;

    Object.entries(selected).forEach(([id, val]) => {
        const el = document.getElementById(id);
        if (el) el.value = val;
    });
}
</script>
@endsection
