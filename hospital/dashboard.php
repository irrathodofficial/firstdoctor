<?php
require_once '../config/db.php';
if (!isset($_SESSION['hospital_id'])) { header("Location: ../auth/hospital_auth.php"); exit; }
$hid = $_SESSION['hospital_id'];

if (isset($_GET['action']) && isset($_GET['did'])) {
    $status = $_GET['action'] == 'approve' ? 'approved' : 'rejected';
    $pdo->prepare("UPDATE doctors SET status=? WHERE id=? AND hospital_id=?")->execute([$status, $_GET['did'], $hid]);
    header("Location: dashboard.php"); exit;
}
$doctors = $pdo->prepare("SELECT * FROM doctors WHERE hospital_id=?"); $doctors->execute([$hid]);

// Generate QR Code Link for specific Doctor
$hid = $_SESSION['doc_hospital_id'];
$did = $_SESSION['doctor_id'];
$qr_link = "http://" . $_SERVER['HTTP_HOST'] . "/firstdoctor/patient/check_in.php?hid=" . $hid . "&did=" . $did;
$qr_img = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($qr_link);
?>
<!DOCTYPE html><html><head><script src="https://cdn.tailwindcss.com"></script></head>
<body class="bg-slate-50 p-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-2xl font-bold">Hospital Admin: <?= $_SESSION['hospital_name'] ?></h1>
        <a href="../auth/logout.php" class="bg-red-500 text-white px-4 py-2 rounded">Logout</a>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <div class="bg-white p-6 rounded shadow text-center">
            <h3 class="font-bold mb-2">Hospital Waiting Room QR</h3>
            <img src="<?= $qr_img ?>" class="mx-auto mb-4 border p-2">
            <a href="<?= $qr_link ?>" target="_blank" class="text-blue-500 text-sm break-all underline">Direct Link</a>
        </div>
        <div class="col-span-2 bg-white p-6 rounded shadow">
            <h3 class="font-bold mb-4">Manage Doctors</h3>
            <table class="w-full text-left">
                <tr class="bg-slate-100"><th class="p-2">Name</th><th class="p-2">Status</th><th class="p-2">Action</th></tr>
                <?php foreach($doctors as $d): ?>
                <tr class="border-b">
                    <td class="p-2"><?= $d['name'] ?></td>
                    <td class="p-2 font-bold <?= $d['status']=='approved'?'text-green-600':'' ?>"><?= ucfirst($d['status']) ?></td>
                    <td class="p-2">
                        <?php if($d['status'] == 'pending'): ?>
                        <a href="?action=approve&did=<?= $d['id'] ?>" class="text-green-600 mr-2">Approve</a>
                        <a href="?action=reject&did=<?= $d['id'] ?>" class="text-red-600">Reject</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
</body></html>