<?php
require_once '../config/db.php';
// Remove session_start() if it exists in db.php

if($_SESSION['role'] !== 'pharmacy') die("Access Denied");
$hid = $_SESSION['hospital_id'];

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['dispense'])) {
    $pdo->prepare("UPDATE patients SET pharmacy_status='dispensed' WHERE id=?")->execute([$_POST['pid']]);
    header("Location: dashboard.php"); exit;
}

$medicines = $pdo->prepare("SELECT p.*, d.name as doc_name FROM patients p JOIN doctors d ON p.doctor_id = d.id WHERE p.hospital_id=? AND p.pharmacy_status='sent' ORDER BY p.id ASC");
$medicines->execute([$hid]);
?>
<!DOCTYPE html><html><head><script src="https://cdn.tailwindcss.com"></script></head>
<body class="bg-slate-50 p-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-emerald-700">Hospital Pharmacy (Medical Store)</h1>
        <a href="../auth/logout.php" class="bg-red-500 text-white px-4 py-2 rounded">Logout</a>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <?php foreach($medicines as $m): ?>
        <div class="bg-white p-6 rounded shadow border-t-4 border-emerald-500">
            <h3 class="font-bold text-lg mb-1"><?= $m['name'] ?> (Token: <?= $m['token_number'] ?>)</h3>
            <p class="text-sm text-slate-500 mb-4">Prescribed by: Dr. <?= $m['doc_name'] ?></p>
            
            <div class="bg-slate-100 p-4 rounded mb-4">
                <pre class="text-sm font-mono whitespace-pre-wrap"><?= htmlspecialchars($m['ai_prescription']) ?></pre>
            </div>
            
            <form method="POST">
                <input type="hidden" name="pid" value="<?= $m['id'] ?>">
                <button type="submit" name="dispense" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 rounded shadow">
                    Mark as Dispensed (Medicines Given)
                </button>
            </form>
        </div>
        <?php endforeach; ?>
    </div>
</body></html>