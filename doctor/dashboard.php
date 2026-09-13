<?php
require_once '../config/db.php';
if (!isset($_SESSION['doctor_id'])) { header("Location: ../auth/doctor_auth.php"); exit; }

$hid = $_SESSION['doc_hospital_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['print'])) {
    $pdo->prepare("UPDATE patients SET ai_prescription=?, status='completed' WHERE id=?")->execute([$_POST['rx'], $_POST['pid']]);
    // Optionally redirect to a pure print page, or just reload
    header("Location: dashboard.php"); exit;
}

$patients = $pdo->prepare("SELECT * FROM patients WHERE hospital_id=? AND status='waiting' ORDER BY urgency_level DESC");
$patients->execute([$hid]);
?>
<!DOCTYPE html><html><head><script src="https://cdn.tailwindcss.com"></script></head>
<body class="bg-slate-100 p-8">
    <div class="flex justify-between mb-8">
        <h1 class="text-2xl font-bold">Dr. <?= $_SESSION['doctor_name'] ?>'s Triage Queue</h1>
        <a href="../auth/logout.php" class="bg-red-500 text-white px-4 py-2 rounded">Logout</a>
    </div>
    
    <div class="grid gap-6">
        <?php foreach($patients as $p): ?>
        <div class="bg-white p-6 rounded shadow border-l-4 <?= $p['urgency_level']>3?'border-red-500':'border-green-500' ?>">
            <div class="flex justify-between font-bold mb-2">
                <span><?= $p['name'] ?> (Token: <?= $p['token_number'] ?>)</span>
                <span>Urgency: <?= $p['urgency_level'] ?>/5</span>
            </div>
            <div class="bg-slate-50 p-3 rounded mb-4 text-sm"><span class="font-bold text-gray-500">AI Summary:</span> <?= $p['ai_summary'] ?></div>
            <form method="POST">
                <input type="hidden" name="pid" value="<?= $p['id'] ?>">
                <textarea name="rx" class="w-full border p-2 text-sm font-mono mb-2" rows="4"><?= $p['ai_prescription'] ?></textarea>
                <button name="print" class="bg-emerald-600 text-white px-4 py-2 rounded text-sm font-bold">Approve & Send to Pharmacy</button>
            </form>
        </div>
        <?php endforeach; ?>
    </div>
</body></html>