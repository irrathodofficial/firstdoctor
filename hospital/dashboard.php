<?php
error_reporting(0);
require_once '../config/db.php';

// Secure Hospital Session
if (!isset($_SESSION['hospital_id'])) { 
    header("Location: ../auth/hospital_auth.php"); 
    exit; 
}
$hid = $_SESSION['hospital_id'];
$hospital_name = $_SESSION['hospital_name'];

// --- AUTO DB MIGRATION: Add new columns if they don't exist ---
try { $pdo->exec("ALTER TABLE doctors ADD COLUMN specialization VARCHAR(255) DEFAULT 'MBBS, MD (General Medicine)'"); } catch(PDOException $e) {}
try { $pdo->exec("ALTER TABLE doctors ADD COLUMN working_days VARCHAR(255) DEFAULT 'Mon - Sat'"); } catch(PDOException $e) {}
try { $pdo->exec("ALTER TABLE doctors ADD COLUMN working_hours VARCHAR(255) DEFAULT '10:00 AM - 06:00 PM'"); } catch(PDOException $e) {}

// --- HANDLE ADD DOCTOR ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add_doctor') {
    $name = preg_replace('/^Dr\.\s*/i', '', trim($_POST['name'])); // Remove "Dr." if typed
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $spec = $_POST['specialization'];
    $days = $_POST['working_days'];
    $hours = $_POST['working_hours'];
    $status = 'approved'; // Added by admin, so auto-approve
    
    $pdo->prepare("INSERT INTO doctors (hospital_id, name, email, password, specialization, working_days, working_hours, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)")
        ->execute([$hid, $name, $email, $password, $spec, $days, $hours, $status]);
    
    header("Location: dashboard.php"); 
    exit;
}

// --- HANDLE EDIT DOCTOR ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'edit_doctor') {
    $did = $_POST['did'];
    $name = preg_replace('/^Dr\.\s*/i', '', trim($_POST['name']));
    $spec = $_POST['specialization'];
    $days = $_POST['working_days'];
    $hours = $_POST['working_hours'];
    $status = $_POST['status'];
    
    $pdo->prepare("UPDATE doctors SET name=?, specialization=?, working_days=?, working_hours=?, status=? WHERE id=? AND hospital_id=?")
        ->execute([$name, $spec, $days, $hours, $status, $did, $hid]);
    
    header("Location: dashboard.php"); 
    exit;
}

// --- HANDLE ACTIONS (APPROVE, REJECT, DELETE) ---
if (isset($_GET['action']) && isset($_GET['did'])) {
    if ($_GET['action'] == 'delete') {
        $pdo->prepare("DELETE FROM doctors WHERE id=? AND hospital_id=?")->execute([$_GET['did'], $hid]);
    } else {
        $status = $_GET['action'] == 'approve' ? 'approved' : 'rejected';
        $pdo->prepare("UPDATE doctors SET status=? WHERE id=? AND hospital_id=?")->execute([$status, $_GET['did'], $hid]);
    }
    header("Location: dashboard.php"); 
    exit;
}

// Fetch All Doctors for this Hospital
$doctors = $pdo->prepare("SELECT * FROM doctors WHERE hospital_id=? ORDER BY id DESC"); 
$doctors->execute([$hid]);
$doctorsList = $doctors->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hospital Admin - FirstDoctor</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- HTML2Canvas for downloading PNG -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .modal-active { display: flex !important; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen p-4 md:p-8">

    <!-- Header -->
    <div class="max-w-[1400px] mx-auto flex justify-between items-center mb-8 bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-xl bg-blue-600 text-white flex items-center justify-center font-bold text-3xl shadow-lg shadow-blue-200">🏥</div>
            <div>
                <h1 class="text-2xl md:text-3xl font-extrabold text-slate-800 tracking-tight"><?= htmlspecialchars($hospital_name) ?></h1>
                <p class="text-sm text-blue-600 font-bold uppercase tracking-wider">Hospital Administration Dashboard</p>
            </div>
        </div>
        <a href="../auth/logout.php" class="bg-red-50 hover:bg-red-100 text-red-600 px-6 py-2.5 rounded-xl font-bold transition shadow-sm border border-red-100">Logout</a>
    </div>
    
    <div class="max-w-[1400px] mx-auto grid grid-cols-1 xl:grid-cols-3 gap-8">
        
        <!-- LEFT SIDE: Manage Doctors Table -->
        <div class="xl:col-span-2 bg-white p-6 md:p-8 rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="flex justify-between items-center mb-6 border-b pb-4">
                <div class="flex items-center gap-4">
                    <h3 class="text-xl font-bold text-slate-800">Manage Doctors & Staff</h3>
                    <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-bold uppercase">Total: <?= count($doctorsList) ?></span>
                </div>
                <!-- ADD DOCTOR BUTTON -->
                <button onclick="openAddModal()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg shadow-sm transition flex items-center gap-2 text-sm">
                    + Add New Doctor
                </button>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse whitespace-nowrap">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider">
                            <th class="p-4 font-semibold rounded-tl-lg">Doctor Profile</th>
                            <th class="p-4 font-semibold">Availability</th>
                            <th class="p-4 font-semibold">Status</th>
                            <th class="p-4 font-semibold rounded-tr-lg text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach($doctorsList as $d): ?>
                        <tr class="hover:bg-slate-50 transition">
                            <td class="p-4">
                                <div class="font-bold text-slate-800 text-base">Dr. <?= htmlspecialchars($d['name']) ?></div>
                                <div class="text-xs font-semibold text-blue-600 mt-1"><?= htmlspecialchars($d['specialization']) ?></div>
                            </td>
                            <td class="p-4">
                                <div class="text-sm font-medium text-slate-700">📅 <?= htmlspecialchars($d['working_days']) ?></div>
                                <div class="text-xs text-slate-500 mt-1">⏰ <?= htmlspecialchars($d['working_hours']) ?></div>
                            </td>
                            <td class="p-4">
                                <?php if($d['status'] == 'approved'): ?>
                                    <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-bold uppercase">Active</span>
                                <?php elseif($d['status'] == 'inactive'): ?>
                                    <span class="bg-slate-200 text-slate-600 px-3 py-1 rounded-full text-xs font-bold uppercase">Inactive</span>
                                <?php elseif($d['status'] == 'pending'): ?>
                                    <span class="bg-amber-100 text-amber-700 px-3 py-1 rounded-full text-xs font-bold uppercase">Pending</span>
                                <?php else: ?>
                                    <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs font-bold uppercase">Rejected</span>
                                <?php endif; ?>
                            </td>
                            <td class="p-4 text-right space-x-2">
                                <?php if($d['status'] == 'pending'): ?>
                                    <a href="?action=approve&did=<?= $d['id'] ?>" class="text-green-600 bg-green-50 hover:bg-green-100 px-3 py-1.5 rounded-lg text-sm font-bold transition">Approve</a>
                                <?php endif; ?>
                                
                                <button onclick="openEditModal(<?= htmlspecialchars(json_encode($d)) ?>)" class="text-blue-600 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-lg text-sm font-bold transition">Edit</button>
                                
                                <a href="?action=delete&did=<?= $d['id'] ?>" onclick="return confirm('Are you sure you want to delete this doctor?')" class="text-red-600 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-lg text-sm font-bold transition">Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if(empty($doctorsList)): ?>
                        <tr><td colspan="4" class="p-8 text-center text-slate-400 font-medium">No doctors registered yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- RIGHT SIDE: Downloadable QR Posters -->
        <div class="space-y-6">
            <div class="bg-blue-600 text-white p-6 rounded-2xl shadow-md">
                <h3 class="text-xl font-bold mb-1">Digital QR Standees</h3>
                <p class="text-blue-100 text-sm">Download as PNG & print for doctor cabins.</p>
            </div>

            <?php foreach($doctorsList as $d): 
                if($d['status'] === 'approved'): 
                    $qr_link = "http://" . $_SERVER['HTTP_HOST'] . "/firstdoctor/patient/check_in.php?hid=" . $hid . "&did=" . $d['id'];
                    $qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=400x400&margin=10&data=" . urlencode($qr_link);
                    
                    // 🚀 BASE64 ENCODING FIX: This ensures HTML2Canvas downloads the QR properly
                    $qr_data = @file_get_contents($qr_url);
                    $qr_base64 = $qr_data ? 'data:image/png;base64,' . base64_encode($qr_data) : $qr_url;
            ?>
            
            <div class="bg-white rounded-3xl shadow-lg border border-slate-200 overflow-hidden relative">
                <!-- THE POSTER DESIGN -->
                <div class="p-8 text-center bg-gradient-to-b from-blue-50 to-white" id="qr-poster-<?= $d['id'] ?>" style="background-color: white;">
                    
                    <div class="mb-6">
                        <div class="text-blue-600 font-black text-3xl tracking-tight mb-1" style="color: #2563eb;">FirstDoctor AI</div>
                        <div class="text-slate-500 text-sm font-bold uppercase tracking-widest" style="color: #64748b;"><?= htmlspecialchars($hospital_name) ?></div>
                    </div>
                    
                    <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 mb-6" style="border: 2px solid #e2e8f0; border-radius: 16px;">
                        <h2 class="text-2xl font-black text-slate-800" style="color: #1e293b;">Dr. <?= htmlspecialchars($d['name']) ?></h2>
                        <p class="text-blue-600 font-bold text-sm mt-2" style="color: #2563eb;"><?= htmlspecialchars($d['specialization']) ?></p>
                        <p class="text-slate-500 font-medium text-xs mt-1" style="color: #64748b;"><?= htmlspecialchars($d['working_days']) ?> | <?= htmlspecialchars($d['working_hours']) ?></p>
                    </div>
                    
                    <div class="bg-white p-4 rounded-2xl inline-block shadow-md border border-slate-200 mb-6" style="border: 2px solid #e2e8f0; border-radius: 16px;">
                        <!-- Embeds base64 image to bypass browser cross-origin block -->
                        <img src="<?= $qr_base64 ?>" alt="QR Code" class="w-56 h-56 mx-auto">
                    </div>
                    
                    <div class="bg-blue-600 text-white p-4 rounded-2xl shadow-inner" style="background-color: #2563eb; color: white; border-radius: 16px;">
                        <!-- REMOVED THE SKIP THE LINE TEXT AS REQUESTED -->
                        <p class="font-extrabold text-xl m-0" style="margin: 0; padding: 0;">Scan to Book Consultation</p>
                    </div>
                </div>

                <!-- Download Button -->
                <div class="p-4 bg-slate-50 border-t border-slate-200 flex justify-between items-center gap-4">
                    <button onclick="downloadPNG('qr-poster-<?= $d['id'] ?>', 'Dr_<?= htmlspecialchars($d['name']) ?>')" class="w-2/3 bg-slate-800 hover:bg-slate-900 text-white font-bold py-3.5 rounded-xl shadow-md transition flex justify-center items-center gap-2">
                        ⬇️ Download PNG
                    </button>
                    <a href="<?= $qr_link ?>" target="_blank" class="w-1/3 bg-blue-100 hover:bg-blue-200 text-blue-700 text-center py-3.5 rounded-xl font-bold transition">Test Link</a>
                </div>
            </div>

            <?php 
                endif; 
            endforeach; 
            ?>
        </div>
    </div>

    <!-- 1. ADD DOCTOR MODAL -->
    <div id="addModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden">
            <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                <h3 class="text-xl font-bold text-slate-800">Add New Doctor</h3>
                <button onclick="closeAddModal()" class="text-slate-400 hover:text-red-500 font-bold text-xl">&times;</button>
            </div>
            
            <form method="POST" class="p-6 space-y-4">
                <input type="hidden" name="action" value="add_doctor">
                
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-1">Doctor Name</label>
                    <input type="text" name="name" required class="w-full border border-slate-300 rounded-lg p-3 font-medium focus:ring-2 focus:ring-blue-500 outline-none" placeholder="e.g. Ramesh Kumar">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-1">Login Email</label>
                        <input type="email" name="email" required class="w-full border border-slate-300 rounded-lg p-3 font-medium focus:ring-2 focus:ring-blue-500 outline-none" placeholder="doctor@hospital.com">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-1">Login Password</label>
                        <input type="password" name="password" required class="w-full border border-slate-300 rounded-lg p-3 font-medium focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Enter password">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-1">Specialization / Degrees</label>
                    <input type="text" name="specialization" required class="w-full border border-slate-300 rounded-lg p-3 font-medium focus:ring-2 focus:ring-blue-500 outline-none" placeholder="e.g. MBBS, MD (Cardiology)" value="MBBS, MD (General Medicine)">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-1">Working Days</label>
                        <input type="text" name="working_days" required class="w-full border border-slate-300 rounded-lg p-3 font-medium focus:ring-2 focus:ring-blue-500 outline-none" placeholder="e.g. Mon - Sat" value="Mon - Sat">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-1">Timing</label>
                        <input type="text" name="working_hours" required class="w-full border border-slate-300 rounded-lg p-3 font-medium focus:ring-2 focus:ring-blue-500 outline-none" placeholder="e.g. 10 AM - 5 PM" value="10:00 AM - 06:00 PM">
                    </div>
                </div>

                <div class="pt-4 mt-6 border-t border-slate-100 flex gap-3">
                    <button type="button" onclick="closeAddModal()" class="w-1/3 bg-slate-100 text-slate-600 hover:bg-slate-200 font-bold py-3 rounded-xl transition">Cancel</button>
                    <button type="submit" class="w-2/3 bg-blue-600 text-white hover:bg-blue-700 font-bold py-3 rounded-xl transition shadow-md">+ Add Doctor</button>
                </div>
            </form>
        </div>
    </div>

    <!-- 2. EDIT DOCTOR MODAL -->
    <div id="editModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden">
            <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                <h3 class="text-xl font-bold text-slate-800">Edit Doctor Profile</h3>
                <button onclick="closeEditModal()" class="text-slate-400 hover:text-red-500 font-bold text-xl">&times;</button>
            </div>
            
            <form method="POST" class="p-6 space-y-4">
                <input type="hidden" name="action" value="edit_doctor">
                <input type="hidden" name="did" id="edit_did">
                
                <div>
                    <!-- Name is now fully Editable -->
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-1">Doctor Name (Editable)</label>
                    <input type="text" name="name" id="edit_name" required class="w-full border border-slate-300 rounded-lg p-3 font-bold focus:ring-2 focus:ring-blue-500 outline-none text-slate-700">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-1">Specialization / Degrees</label>
                    <input type="text" name="specialization" id="edit_spec" required class="w-full border border-slate-300 rounded-lg p-3 font-medium focus:ring-2 focus:ring-blue-500 outline-none">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-1">Working Days</label>
                        <input type="text" name="working_days" id="edit_days" required class="w-full border border-slate-300 rounded-lg p-3 font-medium focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-1">Timing</label>
                        <input type="text" name="working_hours" id="edit_hours" required class="w-full border border-slate-300 rounded-lg p-3 font-medium focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-1">Account Status</label>
                    <select name="status" id="edit_status" class="w-full border border-slate-300 rounded-lg p-3 font-bold focus:ring-2 focus:ring-blue-500 outline-none cursor-pointer bg-white">
                        <option value="approved">Approved (Active)</option>
                        <option value="inactive">Inactive (On Leave/Disabled)</option>
                        <option value="pending">Pending</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>

                <div class="pt-4 mt-6 border-t border-slate-100 flex gap-3">
                    <button type="button" onclick="closeEditModal()" class="w-1/3 bg-slate-100 text-slate-600 hover:bg-slate-200 font-bold py-3 rounded-xl transition">Cancel</button>
                    <button type="submit" class="w-2/3 bg-blue-600 text-white hover:bg-blue-700 font-bold py-3 rounded-xl transition shadow-md">💾 Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        // Modal Logic
        const editModal = document.getElementById('editModal');
        const addModal = document.getElementById('addModal');
        
        function openAddModal() {
            addModal.classList.add('modal-active');
        }
        function closeAddModal() {
            addModal.classList.remove('modal-active');
        }

        function openEditModal(doctorData) {
            document.getElementById('edit_did').value = doctorData.id;
            document.getElementById('edit_name').value = doctorData.name;
            document.getElementById('edit_spec').value = doctorData.specialization;
            document.getElementById('edit_days').value = doctorData.working_days;
            document.getElementById('edit_hours').value = doctorData.working_hours;
            document.getElementById('edit_status').value = doctorData.status;
            
            editModal.classList.add('modal-active');
        }

        function closeEditModal() {
            editModal.classList.remove('modal-active');
        }

        // Download PNG using html2canvas
        function downloadPNG(posterId, fileName) {
            const element = document.getElementById(posterId);
            
            // Show loading state
            const btn = event.currentTarget;
            const originalText = btn.innerHTML;
            btn.innerHTML = "⏳ Generating PNG...";
            
            html2canvas(element, {
                scale: 3, 
                backgroundColor: "#ffffff"
            }).then(canvas => {
                let link = document.createElement('a');
                link.download = fileName.replace(/\s+/g, '_') + '_QR_Poster.png';
                link.href = canvas.toDataURL('image/png');
                link.click();
                
                btn.innerHTML = originalText;
            }).catch(err => {
                alert("Error generating image.");
                btn.innerHTML = originalText;
            });
        }
    </script>
</body>
</html>