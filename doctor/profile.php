<?php
require_once '../config/db.php';
// Remove session_start() if it exists in db.php

$did = $_SESSION['doctor_id'];
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pdo->prepare("UPDATE doctors SET name=?, email=? WHERE id=?")->execute([$_POST['name'], $_POST['email'], $did]);
    $_SESSION['doctor_name'] = $_POST['name'];
    $msg = "Profile Updated Successfully!";
}
$doc = $pdo->prepare("SELECT name, email FROM doctors WHERE id=?"); $doc->execute([$did]);
$doctor = $doc->fetch();
?>
<!DOCTYPE html><html><head><script src="https://cdn.tailwindcss.com"></script></head>
<body class="bg-slate-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded shadow w-full max-w-md">
        <h2 class="text-2xl font-bold mb-4">Edit Doctor Profile</h2>
        <?= isset($msg) ? "<p class='text-green-600 mb-4 font-bold'>$msg</p>" : "" ?>
        <form method="POST" class="space-y-4">
            <input type="text" name="name" value="<?= $doctor['name'] ?>" required class="w-full border p-2 rounded">
            <input type="email" name="email" value="<?= $doctor['email'] ?>" required class="w-full border p-2 rounded">
            <button class="bg-blue-600 text-white w-full py-2 rounded">Save Changes</button>
            <a href="dashboard.php" class="block text-center text-slate-500 text-sm mt-2">Back to Dashboard</a>
        </form>
    </div>
</body></html>