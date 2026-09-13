<?php
require_once '../config/db.php';
$hid = $_GET['hid'] ?? null;
if (!$hid) die("Invalid Hospital QR");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $token = 'T-' . rand(1000, 9999);
    $stmt = $pdo->prepare("INSERT INTO patients (hospital_id, token_number, name, contact) VALUES (?, ?, ?, ?)");
    $stmt->execute([$hid, $token, $_POST['name'], $_POST['contact']]);
    header("Location: ai_triage.php?pid=" . $pdo->lastInsertId()); exit;
}
?>
<!DOCTYPE html><html><head><script src="https://cdn.tailwindcss.com"></script></head>
<body class="bg-slate-100 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white p-8 rounded shadow w-full max-w-md text-center">
        <h2 class="text-2xl font-bold mb-6 text-blue-600">Patient Registration</h2>
        <form method="POST" class="space-y-4">
            <input type="text" name="name" required placeholder="Patient Name" class="w-full border p-3 rounded">
            <input type="text" name="contact" required placeholder="Phone Number" class="w-full border p-3 rounded">
            <button class="w-full bg-blue-600 text-white font-bold py-3 rounded">Start AI Consultation</button>
        </form>
    </div>
</body></html>