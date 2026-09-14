<?php
require_once '../config/db.php';

$hid = $_GET['hid'] ?? null;
$did = $_GET['did'] ?? null;

if (!$hid || !$did) die("Invalid QR Code. Please scan a valid Doctor's QR.");

// Fetch Hospital and Doctor Details
$stmt = $pdo->prepare("SELECT h.hospital_name, d.name AS doctor_name, d.status 
                       FROM doctors d JOIN hospitals h ON d.hospital_id = h.id 
                       WHERE d.id = ? AND h.id = ?");
$stmt->execute([$did, $hid]);
$doc_info = $stmt->fetch();

if (!$doc_info) die("Doctor or Hospital not found.");

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $phone = $_POST['phone'];
    $password = $_POST['password'];
    $name = $_POST['name'] ?? '';

    // Check if patient user already exists
    $user_stmt = $pdo->prepare("SELECT * FROM patient_users WHERE phone = ?");
    $user_stmt->execute([$phone]);
    $user = $user_stmt->fetch();

    if ($user) {
        // Login flow
        if (password_verify($password, $user['password'])) {
            $patient_user_id = $user['id'];
            $patient_name = $user['name'];
        } else {
            $error = "Incorrect password for this phone number!";
        }
    } else {
        // Registration flow
        if (empty($name)) {
            $error = "New users must provide a name.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $insert_user = $pdo->prepare("INSERT INTO patient_users (phone, password, name) VALUES (?, ?, ?)");
            $insert_user->execute([$phone, $hash, $name]);
            $patient_user_id = $pdo->lastInsertId();
            $patient_name = $name;
        }
    }

    if (empty($error)) {
        // Set Patient Session
        $_SESSION['patient_user_id'] = $patient_user_id;
        $_SESSION['patient_name'] = $patient_name;

        // Create Triage Queue Entry (Appointment)
        $token = 'T-' . rand(1000, 9999);
        $insert_visit = $pdo->prepare("INSERT INTO patients (hospital_id, doctor_id, patient_user_id, token_number, name, contact) VALUES (?, ?, ?, ?, ?, ?)");
        $insert_visit->execute([$hid, $did, $patient_user_id, $token, $patient_name, $phone]);
        $visit_id = $pdo->lastInsertId();

        header("Location: ai_triage.php?pid=" . $visit_id); 
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head><script src="https://cdn.tailwindcss.com"></script></head>
<body class="bg-slate-50 min-h-screen flex flex-col items-center py-10 px-4">
    
    <!-- Doctor & Hospital Info Card -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 w-full max-w-md mb-6 text-center">
        <div class="w-16 h-16 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-3 text-2xl">🏥</div>
        <h2 class="text-xl font-black text-slate-800"><?= htmlspecialchars($doc_info['hospital_name']) ?></h2>
        <div class="mt-4 p-4 bg-slate-50 rounded-xl border border-slate-100">
            <p class="text-xs text-slate-400 uppercase font-bold tracking-wider mb-1">Consulting Physician</p>
            <p class="text-lg font-bold text-blue-600"><?= htmlspecialchars($doc_info['doctor_name']) ?></p>
        </div>
    </div>

    <!-- Smart Auth Form -->
    <div class="bg-white p-8 rounded-2xl shadow-xl w-full max-w-md border border-slate-100">
        <h2 class="text-xl font-bold mb-2 text-slate-800 text-center">Patient Check-in</h2>
        <p class="text-sm text-slate-500 text-center mb-6">Login or create an account to save your reports.</p>
        
        <?php if($error): ?>
            <div class="bg-red-50 text-red-600 p-3 rounded-lg text-sm mb-4"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <div>
                <label class="text-xs font-bold text-slate-600">Phone Number (Your ID)</label>
                <input type="text" name="phone" required placeholder="e.g. 9876543210" class="w-full border border-slate-300 p-3 rounded-xl focus:ring-2 focus:ring-blue-500 mt-1">
            </div>
            <div>
                <label class="text-xs font-bold text-slate-600">Full Name (Only for new users)</label>
                <input type="text" name="name" placeholder="Leave blank if already registered" class="w-full border border-slate-300 p-3 rounded-xl focus:ring-2 focus:ring-blue-500 mt-1">
            </div>
            <div>
                <label class="text-xs font-bold text-slate-600">Password</label>
                <input type="password" name="password" required placeholder="Enter or create a password" class="w-full border border-slate-300 p-3 rounded-xl focus:ring-2 focus:ring-blue-500 mt-1">
            </div>
            
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3.5 rounded-xl shadow-md transition mt-4">
                Continue to AI Triage 🚀
            </button>
        </form>
        <div class="mt-4 text-center">
            <a href="dashboard.php" class="text-sm text-blue-500 hover:underline">Already consulted? View past reports here.</a>
        </div>
    </div>
</body>
</html>