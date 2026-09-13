<?php
require_once '../config/db.php';
$hospitals = $pdo->query("SELECT id, hospital_name FROM hospitals")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $email = $_POST['email']; $password = $_POST['password'];
    
    if ($_POST['action'] === 'register') {
        $name = $_POST['name']; $hid = $_POST['hospital_id'];
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $pdo->prepare("INSERT INTO doctors (hospital_id, name, email, password) VALUES (?, ?, ?, ?)")->execute([$hid, $name, $email, $hash]);
        $msg = "Registered! Wait for hospital approval.";
    } elseif ($_POST['action'] === 'login') {
        $doc = $pdo->prepare("SELECT * FROM doctors WHERE email = ?");
        $doc->execute([$email]);
        $doc = $doc->fetch();
        if ($doc && password_verify($password, $doc['password'])) {
            if ($doc['status'] !== 'approved') { $error = "Account pending hospital approval!"; }
            else {
                $_SESSION['doctor_id'] = $doc['id'];
                $_SESSION['doc_hospital_id'] = $doc['hospital_id'];
                $_SESSION['doctor_name'] = $doc['name'];
                header("Location: ../doctor/dashboard.php"); exit;
            }
        } else { $error = "Invalid Credentials!"; }
    }
}
?>
<!DOCTYPE html><html><head><script src="https://cdn.tailwindcss.com"></script></head>
<body class="bg-slate-50 p-10"><div class="max-w-md mx-auto bg-white p-8 rounded shadow">
    <h2 class="text-2xl font-bold mb-4">Doctor Portal</h2>
    <?= isset($msg) ? "<p class='text-green-600 mb-2'>$msg</p>" : "" ?>
    <?= isset($error) ? "<p class='text-red-600 mb-2'>$error</p>" : "" ?>
    
    <form method="POST" class="mb-6 space-y-3">
        <input type="hidden" name="action" value="login">
        <input type="email" name="email" placeholder="Email" required class="w-full border p-2">
        <input type="password" name="password" placeholder="Password" required class="w-full border p-2">
        <button class="bg-blue-600 text-white w-full p-2">Login</button>
    </form>
    <hr class="my-4">
    <h3 class="font-bold mb-2">Register as Doctor</h3>
    <form method="POST" class="space-y-3">
        <input type="hidden" name="action" value="register">
        <select name="hospital_id" required class="w-full border p-2">
            <option value="">Select Hospital</option>
            <?php foreach($hospitals as $h) echo "<option value='{$h['id']}'>{$h['hospital_name']}</option>"; ?>
        </select>
        <input type="text" name="name" placeholder="Dr. Name" required class="w-full border p-2">
        <input type="email" name="email" placeholder="Email" required class="w-full border p-2">
        <input type="password" name="password" placeholder="Password" required class="w-full border p-2">
        <button class="bg-green-600 text-white w-full p-2">Register</button>
    </form>
</div></body></html>