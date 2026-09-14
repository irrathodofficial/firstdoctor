<?php
require_once '../config/db.php';
// session_start() hata diya gaya hai kyunki db.php mein already hai

// Check if user is logged in and is a receptionist
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'reception') {
    // Agar login nahi hai, toh staff login page par bhej do
    header("Location: ../auth/staff_auth.php");
    exit;
}

$hid = $_SESSION['hospital_id'];

// Jab receptionist BP/Sugar dal kar submit karegi
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['send_to_doctor'])) {
    $pdo->prepare("UPDATE patients SET vitals_bp=?, vitals_sugar=?, status='vitals_done' WHERE id=?")
        ->execute([$_POST['bp'], $_POST['sugar'], $_POST['pid']]);
    header("Location: dashboard.php"); 
    exit;
}

// Sirf 'waiting' wale patients receptionist ko dikhenge
$patients = $pdo->prepare("SELECT * FROM patients WHERE hospital_id=? AND status='waiting' ORDER BY urgency_level DESC");
$patients->execute([$hid]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reception Desk - Vitals</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 p-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-blue-800">Receptionist Desk (Vitals Check)</h1>
        <a href="../auth/logout.php" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded shadow font-bold">Logout</a>
    </div>
    
    <div class="grid grid-cols-1 gap-6">
        <?php foreach($patients as $p): ?>
        <div class="bg-white p-6 rounded-xl shadow border-l-4 border-blue-500 flex flex-col md:flex-row justify-between md:items-center gap-4">
            <div>
                <h3 class="font-bold text-lg text-slate-800"><?= htmlspecialchars($p['name']) ?> 
                    <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded ml-2">Token: <?= $p['token_number'] ?></span>
                </h3>
                <p class="text-sm text-slate-500 mt-1">Contact: <?= htmlspecialchars($p['contact']) ?> | Urgency: <span class="font-bold text-rose-500"><?= $p['urgency_level'] ?>/5</span></p>
            </div>
            
            <form method="POST" class="flex flex-wrap gap-4 items-end bg-slate-50 p-4 rounded-lg border border-slate-200">
                <input type="hidden" name="pid" value="<?= $p['id'] ?>">
                <div>
                    <label class="text-xs font-bold text-slate-500 uppercase">Blood Pressure</label>
                    <input type="text" name="bp" placeholder="e.g. 120/80" required class="border border-slate-300 p-2 rounded-lg w-28 block mt-1 focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="text-xs font-bold text-slate-500 uppercase">Sugar Level</label>
                    <input type="text" name="sugar" placeholder="e.g. 90" required class="border border-slate-300 p-2 rounded-lg w-28 block mt-1 focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <button type="submit" name="send_to_doctor" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-5 rounded-lg shadow-md transition">
                    Send to Doctor ➔
                </button>
            </form>
        </div>
        <?php endforeach; ?>

        <?php if(empty($patients)): ?>
            <div class="text-center p-12 bg-white rounded-xl shadow-sm text-slate-400 font-medium border border-slate-200">No patients waiting at reception.</div>
        <?php endif; ?>
    </div>
</body>
</html>