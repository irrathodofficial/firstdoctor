<?php
require_once '../config/db.php';

if (!isset($_SESSION['patient_user_id'])) {
    // If not logged in, show a simple login form
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['phone'])) {
        $stmt = $pdo->prepare("SELECT * FROM patient_users WHERE phone = ?");
        $stmt->execute([$_POST['phone']]);
        $user = $stmt->fetch();
        if ($user && password_verify($_POST['password'], $user['password'])) {
            $_SESSION['patient_user_id'] = $user['id'];
            $_SESSION['patient_name'] = $user['name'];
            header("Location: dashboard.php"); exit;
        } else {
            $login_error = "Invalid phone or password.";
        }
    }
?>
    <!-- Standalone Patient Login -->
    <!DOCTYPE html><html><head><script src="https://cdn.tailwindcss.com"></script></head>
    <body class="bg-slate-50 flex items-center justify-center min-h-screen p-4">
        <div class="bg-white p-8 rounded-2xl shadow-xl w-full max-w-sm">
            <h2 class="text-2xl font-bold mb-6 text-center text-slate-800">Patient Portal</h2>
            <?= isset($login_error) ? "<p class='text-red-500 text-sm mb-4'>$login_error</p>" : "" ?>
            <form method="POST" class="space-y-4">
                <input type="text" name="phone" placeholder="Phone Number" required class="w-full border p-3 rounded-xl">
                <input type="password" name="password" placeholder="Password" required class="w-full border p-3 rounded-xl">
                <button class="w-full bg-blue-600 text-white font-bold py-3 rounded-xl">View My Reports</button>
            </form>
        </div>
    </body></html>
<?php
    exit;
}

// Fetch all past consultations for this patient
$uid = $_SESSION['patient_user_id'];
$stmt = $pdo->prepare("SELECT p.*, h.hospital_name, d.name AS doctor_name 
                       FROM patients p 
                       LEFT JOIN hospitals h ON p.hospital_id = h.id 
                       LEFT JOIN doctors d ON p.doctor_id = d.id 
                       WHERE p.patient_user_id = ? ORDER BY p.created_at DESC");
$stmt->execute([$uid]);
$history = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head><script src="https://cdn.tailwindcss.com"></script></head>
<body class="bg-slate-100 p-4 md:p-10">
    <div class="max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-2xl font-bold text-slate-800">Hi, <?= htmlspecialchars($_SESSION['patient_name']) ?> 👋</h1>
            <a href="../auth/logout.php" class="bg-red-50 text-red-600 px-4 py-2 rounded-lg font-bold text-sm">Logout</a>
        </div>

        <h2 class="text-lg font-bold mb-4 text-slate-600">Your Medical History & Prescriptions</h2>
        
        <div class="grid gap-6">
            <?php foreach($history as $record): ?>
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
                <div class="flex justify-between items-start mb-4 border-b pb-4">
                    <div>
                        <h3 class="font-bold text-lg text-slate-800"><?= htmlspecialchars($record['hospital_name'] ?? 'Unknown Hospital') ?></h3>
                        <p class="text-sm text-slate-500">Dr. <?= htmlspecialchars($record['doctor_name'] ?? 'Consulting Physician') ?></p>
                    </div>
                    <span class="text-xs bg-slate-100 text-slate-600 px-3 py-1 rounded-full font-bold">
                        <?= date('d M Y, h:i A', strtotime($record['created_at'])) ?>
                    </span>
                </div>
                
                <div class="mb-4">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Reported Symptoms</span>
                    <p class="text-sm text-slate-700 mt-1"><?= htmlspecialchars($record['symptoms_raw'] ?? 'N/A') ?></p>
                </div>

                <div class="bg-blue-50 p-4 rounded-xl border border-blue-100">
                    <span class="text-xs font-bold text-blue-600 uppercase tracking-wider">Final Prescription (Rx)</span>
                    <pre class="text-sm text-slate-800 mt-2 font-mono whitespace-pre-wrap"><?= htmlspecialchars($record['ai_prescription'] ?? 'Awaiting doctor approval...') ?></pre>
                </div>
            </div>
            <?php endforeach; ?>
            
            <?php if(empty($history)): ?>
                <div class="text-center p-10 bg-white rounded-2xl text-slate-400">No medical records found.</div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>