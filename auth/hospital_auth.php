<?php
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    if ($_POST['action'] === 'register') {
        $name = $_POST['hospital_name'];
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO hospitals (hospital_name, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$name, $email, $hash]);
        $msg = "Hospital Registered! You can now login.";
    } elseif ($_POST['action'] === 'login') {
        $stmt = $pdo->prepare("SELECT * FROM hospitals WHERE email = ?");
        $stmt->execute([$email]);
        $hospital = $stmt->fetch();
        if ($hospital && password_verify($password, $hospital['password'])) {
            $_SESSION['hospital_id'] = $hospital['id'];
            $_SESSION['hospital_name'] = $hospital['hospital_name'];
            header("Location: ../hospital/dashboard.php"); exit;
        } else { $error = "Invalid Credentials!"; }
    }
}
?>
<!DOCTYPE html><html><head><script src="https://cdn.tailwindcss.com"></script></head>
<body class="bg-slate-50 p-10"><div class="max-w-md mx-auto bg-white p-8 rounded shadow">
    <h2 class="text-2xl font-bold mb-4">Hospital Portal</h2>
    <?= isset($msg) ? "<p class='text-green-600 mb-2'>$msg</p>" : "" ?>
    <?= isset($error) ? "<p class='text-red-600 mb-2'>$error</p>" : "" ?>
    
    <form method="POST" class="mb-6 space-y-3">
        <input type="hidden" name="action" value="login">
        <input type="email" name="email" placeholder="Hospital Email" required class="w-full border p-2">
        <input type="password" name="password" placeholder="Password" required class="w-full border p-2">
        <button class="bg-blue-600 text-white w-full p-2">Login as Hospital</button>
    </form>
    <hr class="my-4">
    <h3 class="font-bold mb-2">Register New Hospital</h3>
    <form method="POST" class="space-y-3">
        <input type="hidden" name="action" value="register">
        <input type="text" name="hospital_name" placeholder="Hospital Name" required class="w-full border p-2">
        <input type="email" name="email" placeholder="Email" required class="w-full border p-2">
        <input type="password" name="password" placeholder="Password" required class="w-full border p-2">
        <button class="bg-green-600 text-white w-full p-2">Register Hospital</button>
    </form>
</div></body></html>