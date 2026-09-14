<?php
require_once 'config/db.php';

try {
    // Drop existing to recreate clean schema with new features
    $pdo->exec("DROP TABLE IF EXISTS patients");
    $pdo->exec("DROP TABLE IF EXISTS doctors");
    $pdo->exec("DROP TABLE IF EXISTS hospital_staff");
    $pdo->exec("DROP TABLE IF EXISTS patient_users");
    $pdo->exec("DROP TABLE IF EXISTS hospitals");

    // 1. Hospitals
    $pdo->exec("CREATE TABLE hospitals (
        id INT AUTO_INCREMENT PRIMARY KEY,
        hospital_name VARCHAR(255) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL
    )");

    // 2. Patient Users (Login)
    $pdo->exec("CREATE TABLE patient_users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        phone VARCHAR(20) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        name VARCHAR(100) NOT NULL
    )");

    // 3. Doctors (Added preferred_lang)
    $pdo->exec("CREATE TABLE doctors (
        id INT AUTO_INCREMENT PRIMARY KEY,
        hospital_id INT NOT NULL,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        preferred_lang VARCHAR(20) DEFAULT 'en',
        status ENUM('pending', 'approved', 'rejected') DEFAULT 'approved',
        FOREIGN KEY (hospital_id) REFERENCES hospitals(id) ON DELETE CASCADE
    )");

    // 4. Receptionist & Pharmacy Staff
    $pdo->exec("CREATE TABLE hospital_staff (
        id INT AUTO_INCREMENT PRIMARY KEY,
        hospital_id INT NOT NULL,
        role ENUM('reception', 'pharmacy') NOT NULL,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        FOREIGN KEY (hospital_id) REFERENCES hospitals(id) ON DELETE CASCADE
    )");

    // 5. Appointments / Triage Queue (Added Vitals & Pharmacy Status)
    $pdo->exec("CREATE TABLE patients (
        id INT AUTO_INCREMENT PRIMARY KEY,
        hospital_id INT NOT NULL,
        doctor_id INT NULL,
        patient_user_id INT NULL,
        token_number VARCHAR(20) NOT NULL,
        name VARCHAR(100) NOT NULL,
        contact VARCHAR(20) NOT NULL,
        symptoms_raw TEXT,
        ai_summary TEXT,
        ai_prescription TEXT,
        urgency_level INT DEFAULT 1,
        vitals_bp VARCHAR(20) NULL,
        vitals_sugar VARCHAR(20) NULL,
        status ENUM('waiting', 'vitals_done', 'completed') DEFAULT 'waiting',
        pharmacy_status ENUM('pending', 'sent', 'dispensed') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (hospital_id) REFERENCES hospitals(id) ON DELETE CASCADE
    )");

    // --- INSERT TEST DATA ---
    $hash = password_hash('123456', PASSWORD_DEFAULT);
    
    // Hospital
    $pdo->exec("INSERT INTO hospitals (id, hospital_name, email, password) VALUES (1, 'City Hospital', 'admin@cityhospital.com', '$hash')");
    // Doctor
    $pdo->exec("INSERT INTO doctors (id, hospital_id, name, email, password) VALUES (1, 1, 'Dr. Ramesh', 'dr.ramesh@cityhospital.com', '$hash')");
    // Receptionist
    $pdo->exec("INSERT INTO hospital_staff (hospital_id, role, name, email, password) VALUES (1, 'reception', 'Priya (Reception)', 'reception@cityhospital.com', '$hash')");
    // Pharmacy
    $pdo->exec("INSERT INTO hospital_staff (hospital_id, role, name, email, password) VALUES (1, 'pharmacy', 'Rahul (Medical)', 'medical@cityhospital.com', '$hash')");
    // Dummy Patient Data (Check-in complete, waiting at reception)
    $pdo->exec("INSERT INTO patients (hospital_id, doctor_id, token_number, name, contact, symptoms_raw, ai_summary, ai_prescription, urgency_level, status) 
                VALUES (1, 1, 'T-1001', 'Rahul Kumar', '9876543210', 'Mujhe 2 din se bukhar aur sardi hai.', 'Patient has mild fever and cold for 2 days. Needs evaluation.', 'Rx:\n1. Paracetamol 650mg - 1 TDS\n2. Cetirizine 10mg - 1 at night', 2, 'waiting')");

    echo "<h2 style='color:green;'>✅ Mega Hospital ERP Schema & Test Data Installed!</h2>";
} catch (Exception $e) { echo $e->getMessage(); }
?>