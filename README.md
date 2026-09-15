<div align="center">

# FirstDoctor AI
**The Next-Generation Multilingual AI Clinical Decision Support System**

[![Live Demo](https://img.shields.io/badge/Live_Demo-firstdoctor.ishwarrathod.com-06b6d4?style=for-the-badge&logo=google-chrome)](http://firstdoctor.ishwarrathod.com)
[![Tech Stack](https://img.shields.io/badge/Tech-PHP%20|%20TailwindCSS%20|%20MySQL-3b82f6?style=for-the-badge)](#)
[![AI Engine](https://img.shields.io/badge/AI_Engine-Gemini_3.6_Flash-emerald?style=for-the-badge&logo=googlebard)](#)

*FirstDoctor AI eliminates language barriers, reduces doctor burnout by 70%, and provides a 100% digitized, zero-friction workflow from Reception to Pharmacy.*

</div>

<br>

---

## The Problem We Are Solving

In crowded clinics and hospitals, patients often struggle to articulate their symptoms accurately due to language barriers, medical illiteracy, or clinical anxiety. This leads to misdiagnosis, wasted consultation time, and massive doctor burnout. Traditional Hospital ERP systems function merely as data-entry software; they do not understand the patient.

## Our Solution

FirstDoctor AI is an advanced B2B SaaS platform that acts as an intelligent clinical bridge. Patients speak to our Native Voice AI in their local language. By the time they enter the consultation cabin, the doctor already has a 100-year expert-level clinical summary and a pre-generated smart prescription ready on their screen.

<br>

---

## The 5-Step Automated Workflow

<br>

### Step 1: Smart QR Check-in & Native Voice AI

Patients scan a dynamically generated QR Standee outside the doctor's cabin using their smartphone. They check in without downloading any application and simply speak their symptoms in their native language (Hindi, Marathi, Gujarati, English).

<p align="center">
  <img src="http://firstdoctor.ishwarrathod.com/assets/images/step1_qr.jpg" alt="Smart QR Check-in" width="80%">
</p>

<br>

### Step 2: Instant Vitals Sync (Reception Desk)

Before the patient enters the cabin, the receptionist checks essential vitals like Blood Pressure and Blood Sugar. This data is instantly synced to the patient's digital AI file in real-time, preparing the baseline for the doctor.

<p align="center">
  <img src="http://firstdoctor.ishwarrathod.com/assets/images/step2_vitals.jpg" alt="Vitals Sync" width="80%">
</p>

<br>

### Step 3: Expert Clinical Summary (Doctor Dashboard)

As the patient walks in, the doctor is already reviewing an ultra-precise, medically translated clinical summary (History of Present Illness). The Gemini 3.6 Flash AI engine extracts raw regional symptoms and identifies top differential diagnoses. No time is wasted on repetitive questioning.

<p align="center">
  <img src="http://firstdoctor.ishwarrathod.com/assets/images/step3_doctor.jpg" alt="Clinical Summary" width="80%">
</p>

<br>

### Step 4: One-Click Smart Prescriptions

Alongside the summary, the AI pre-generates a master-level treatment plan. This includes targeted Blood Tests (Investigations), precise Medications with exact dosages, and lifestyle precautions. The doctor simply reviews, edits if required, and clicks "Approve".

<p align="center">
  <img src="http://firstdoctor.ishwarrathod.com/assets/images/step4_rx.jpg" alt="Smart Prescription" width="80%">
</p>

<br>

### Step 5: Seamless Pharmacy Handoff

The moment the doctor clicks approve, the digital prescription is instantly flashed on the Pharmacy and Medical Store's terminal. Medicines are prepared immediately, completely eliminating patient wait time and minimizing errors caused by illegible handwriting.

<p align="center">
  <img src="http://firstdoctor.ishwarrathod.com/assets/images/step5_pharmacy.jpg" alt="Pharmacy Handoff" width="80%">
</p>

<br>

---

## Live Demo & Test Credentials

We have deployed a live staging environment. Evaluators and judges can use the following test credentials to experience the complete end-to-end workflow of FirstDoctor AI. *(To test the patient flow, simply scan the live demo QR code on the homepage or click the Demo link.)*

| Module / Role | Direct Access Link | Test Email | Password |
| :--- | :--- | :--- | :--- |
| **Hospital Admin** | [Access Admin Panel](http://firstdoctor.ishwarrathod.com/auth/hospital_auth) | `admin@cityhospital.com` | `123456` |
| **Doctor Dashboard** | [Access Doctor Portal](http://firstdoctor.ishwarrathod.com/auth/doctor_auth) | `dr.ramesh@cityhospital.com` | `123456` |
| **Reception Desk** | [Access Staff Portal](http://firstdoctor.ishwarrathod.com/auth/staff_auth) | `reception@cityhospital.com` | `123456` |
| **Pharmacy Store** | [Access Staff Portal](http://firstdoctor.ishwarrathod.com/auth/staff_auth) | `medical@cityhospital.com` | `123456` |

<br>

---

## Core System Architecture

*   **Hospital Admin Dashboard:** Complete doctor management, generating high-definition printable QR standees via Base64 and HTML2Canvas rendering.
*   **Native Voice AI Module:** Web Speech API integration supporting multiple regional languages for frictionless patient input.
*   **Gemini 3.6 Flash Engine:** Custom expert medical prompt engineering outputting strictly structured JSON data for safe backend parsing.
*   **Secure Infrastructure:** Clean URL routing via `.htaccess`, hidden environment variables, and robust PDO database security protocols.

<br>

## Technical Stack

*   **Frontend Interface:** HTML5, Tailwind CSS, Vanilla JavaScript (ES6+), Web Speech API
*   **Backend Logic:** PHP 8.x
*   **Database Management:** MySQL (PDO)
*   **Generative AI Integration:** Google Gemini API (Gemini 3.6 Flash Model) via cURL

<br>

## Local Installation Setup

**1. Clone the repository:**
    
    git clone https://github.com/irrathodofficial/firstdoctor.git
    cd firstdoctor

**2. Setup Database:**
* Create a MySQL database named `firstdoctor_db`.
* Import the provided SQL schema or simply run `update_db.php` in your browser to execute auto-migrations.

**3. Configure Environment Variables:**
* Create a `.env` file in the root directory.
* Add your Gemini API Key securely:
    
    GEMINI_API_KEY="your_api_key_here"

**4. Run the Server:**
* Host the directory on a local server environment (XAMPP, WAMP, or Laravel Valet).
* Ensure Apache `mod_rewrite` is enabled for clean URL functionality.
* Access `http://localhost/firstdoctor` in your web browser.

<br>

---

<div align="center">
  <b>Built for the Future of Healthcare</b><br>
  Developed by <a href="https://ishwarrathod.com">Ishwar Rathod</a>
</div>
