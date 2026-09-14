<?php
require_once 'config/db.php';

try {
    // Patient ke account/login credentials ke liye nayi table
    $pdo->exec("CREATE TABLE IF NOT EXISTS patient_users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        phone VARCHAR(20) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        name VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Existing appointments (patients) table mein doctor_id aur patient_user_id add karna
    $pdo->exec("ALTER TABLE patients ADD COLUMN patient_user_id INT NULL AFTER hospital_id");
    $pdo->exec("ALTER TABLE patients ADD COLUMN doctor_id INT NULL AFTER patient_user_id");

    echo "<h3 style='color:green;'>✅ Database Updated for Patient Login!</h3>";
} catch (PDOException $e) { 
    echo "Error: " . $e->getMessage(); 
}
?>