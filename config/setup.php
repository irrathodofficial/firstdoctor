<?php
require_once 'db.php';

try {
    // ❌ Yahan se CREATE DATABASE aur USE DATABASE wali lines hata di gayi hain
    // Taaki tables direct 'firstdoctor' DB mein hi ban jayein

    $pdo->exec("CREATE TABLE IF NOT EXISTS hospitals (
        id INT AUTO_INCREMENT PRIMARY KEY,
        hospital_name VARCHAR(255) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS doctors (
        id INT AUTO_INCREMENT PRIMARY KEY,
        hospital_id INT NOT NULL,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (hospital_id) REFERENCES hospitals(id) ON DELETE CASCADE
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS patients (
        id INT AUTO_INCREMENT PRIMARY KEY,
        hospital_id INT NOT NULL,
        token_number VARCHAR(20) NOT NULL,
        name VARCHAR(100) NOT NULL,
        contact VARCHAR(20) NOT NULL,
        symptoms_raw TEXT,
        ai_summary TEXT,
        ai_prescription TEXT,
        urgency_level INT DEFAULT 1,
        status ENUM('waiting', 'completed') DEFAULT 'waiting',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (hospital_id) REFERENCES hospitals(id) ON DELETE CASCADE
    )");

    echo "✅ SaaS Database Setup Complete in your current DB!";
} catch (PDOException $e) { 
    echo "Error: " . $e->getMessage(); 
}
?>