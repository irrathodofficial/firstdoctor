<?php
require_once '../config/db.php';
// session_start(); yahan se hata diya gaya hai

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email']; $password = $_POST['password'];
    $stmt = $pdo->prepare("SELECT * FROM hospital_staff WHERE email = ?");
    $stmt->execute([$email]);
    $staff = $stmt->fetch();
    
    if ($staff && password_verify($password, $staff['password'])) {
        $_SESSION['staff_id'] = $staff['id'];
        $_SESSION['staff_name'] = $staff['name'];
        $_SESSION['hospital_id'] = $staff['hospital_id'];
        $_SESSION['role'] = $staff['role'];
        
        if($staff['role'] == 'reception') header("Location: ../reception/dashboard.php");
        else header("Location: ../pharmacy/dashboard.php");
        exit;
    } else { $error = "Invalid Login!"; }
}
?>
<!DOCTYPE html><html><head><script src="https://cdn.tailwindcss.com"></script></head>
<body class="bg-slate-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded shadow w-full max-w-md">
        <h2 class="text-2xl font-bold mb-4">Staff Portal</h2>
        <?= isset($error) ? "<p class='text-red-500 mb-2'>$error</p>" : "" ?>
        <form method="POST" class="space-y-4">
            <input type="email" name="email" value="reception@cityhospital.com" required class="w-full border p-2 rounded">
            <input type="password" name="password" value="123456" required class="w-full border p-2 rounded">
            <button class="bg-blue-600 text-white w-full py-2 rounded font-bold">Login</button>
        </form>
    </div>
</body></html>