# East Systems Colleges of Rizal (ESCR) — Online Enrollment & Payment System (`ESCR_MIS`)

Official **Online Enrollment, Document Management System (DMS), Multi-Semester Installment Billing, Digital Promissory Note Rollover, GCash + Walk-In Cash Verification, Examination Clearance, and Uniform Fabric (*Tela*) Ordering System** for **East Systems Colleges of Rizal** (Morong, Rizal).

---

## Key Features
1. **Multi-Semester Online Enrollment & Private DMS (`1st Semester`, `2nd Semester`, `Summer / Midyear`)**:
   - New student registration, academic program & year-level selection, and private Document Management System (`PSA Birth Certificate`, `Form 138`, `Good Moral`, `2x2 ID Picture`, `Transfer Credentials`).
   - Continuing student re-enrollment with **automatic DMS credential carry-over** across semesters.
2. **Tuition & 4-Part Examination Installment Billing**:
   - Supports **Full Payment** (`₱25,000.00`) or **4-Part Installment Plan** (`₱5,000.00` Downpayment upon enrollment + `Prelim`, `Midterm`, `Semi-Finals`, and `Finals` installments).
3. **ESCR Promissory Note Rollover to Finals & Immediate Exam Clearance**:
   - Students can download a pre-filled ESCR Promissory Note and upload the parent-signed copy.
   - Upon Registrar approval, the unpaid installment balance is **automatically rolled into the 4th (Finals) Installment**, the current term is marked `Promised (Moved to Finals)` without double-counting total assessed tuition, and the student is immediately **Cleared for Examination**.
4. **3 Payment Methods (`GCash Instant`, `Manual GCash QR Code`, & `Walk-In Cash`)**:
   - **GCash Express Checkout** (instant OR issuance)
   - **Manual GCash QR Code & Merchant Number (`0917-888-3727`)** with proof upload for Cashier verification
   - **Walk-In Cash (`CSH-2026-*`)** over-the-counter payment confirmed by the School Cashier (`Confirm Cash Received & Issue OR`)
5. **Uniform Fabric (*Tela*) & Logo Patch Store**:
   - Online ordering for Blouse/Polo fabric (`₱160/yd`), Skirt fabric (`₱100/yd`), and Logo Patches (`₱100/pc`) with Cashier **"Ready for Pickup"** notifications and claim tracking.
6. **4 Role-Based Portals + Security & Account Recovery**:
   - **Student**, **Registrar**, **Cashier**, and **Super Admin** portals with Audit Trail logging, **Change Password** modal, and **Forgot Password / Email Reset Link** workflow.

---

## Quick Setup on XAMPP (Windows)

1. **Clone into `C:\xampp\htdocs\MIS`** (or `C:\xampp\htdocs\ESCR_MIS`):
   ```powershell
   cd C:\xampp\htdocs
   git clone https://github.com/alker3000/ESCR_MIS.git MIS
   cd MIS
   ```
2. **Install Composer Dependencies & Environment File**:
   ```powershell
   composer install
   Copy-Item .env.example .env
   ```
3. **Create the `escr_mis` Database in MySQL / phpMyAdmin & Seed Data**:
   - Start **Apache** and **MySQL** in XAMPP Control Panel.
   - Create a database named `escr_mis` in `http://localhost/phpmyadmin`, then run:
     ```powershell
     C:\xampp\php\php.exe artisan migrate:fresh --seed
     ```
   - *(Optional)* To also populate **500 realistic ESCR students** for load/pagination testing:
     ```powershell
     C:\xampp\php\php.exe artisan escr:seed-bulk 500
     ```
   - *(Alternative)* You can also import `database/escr_mis.sql` directly inside phpMyAdmin.
4. **Open in Browser**:
   - Visit: `http://localhost/MIS/public`

---

## Default Demo Accounts (Password: `password123`)
| Role | Email | Description |
| :--- | :--- | :--- |
| **Student (Enrolled)** | `student@escr.edu.ph` | Juan Dela Cruz — Officially Enrolled, Cleared for Exam |
| **Student (Promissory Note)** | `promissory@escr.edu.ph` | Angela Mendoza — Prelim Balance Moved to Finals (`₱10,000`) |
| **Student (Applicant)** | `applicant@escr.edu.ph` | Mark Villanueva — Pending Registrar Document Review |
| **Registrar** | `registrar@escr.edu.ph` | Elena Reyes — Document & Promissory Note Verification |
| **Cashier** | `cashier@escr.edu.ph` | Roberto Cruz — GCash/Cash Verification, ORs, & Tela Pickup |
| **Super Admin** | `admin@escr.edu.ph` | Dr. Antonio Santos — Analytics, Fee/Semester Settings, & Audit Trail |
