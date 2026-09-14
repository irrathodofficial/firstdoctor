<?php
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE); // HIDES PHP 8.1 WARNINGS THAT BREAK DESIGN
ob_start();
require_once '../config/db.php';

if (!isset($_SESSION['doctor_id'])) { header("Location: ../auth/doctor_auth.php"); exit; }
$did =$_SESSION['doctor_id'];
$hid =$_SESSION['doc_hospital_id'];

// Language Change Logic 
if(isset($_POST['update_lang'])) {$lang = $_POST['lang'];$pdo->prepare("UPDATE doctors SET preferred_lang=? WHERE id=?")->execute([$lang,$did]);
    setcookie('googtrans', '/en/' . $lang, time() + (86400 * 30), "/");
    header("Location: dashboard.php"); 
    exit;
}

$doc =$pdo->prepare("SELECT name, preferred_lang FROM doctors WHERE id=?");
$doc->execute([$did]);
$doctor =$doc->fetch();

if (!isset($_COOKIE['googtrans']) || $_COOKIE['googtrans'] !== '/en/' .$doctor['preferred_lang']) {
    setcookie('googtrans', '/en/' . $doctor['preferred_lang'], time() + (86400 * 30), "/");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['approve_rx'])) {$pdo->prepare("UPDATE patients SET ai_prescription=?, vitals_bp=?, vitals_sugar=?, status='completed', pharmacy_status='sent' WHERE id=?")
        ->execute([$_POST['rx_text'],$_POST['vitals_bp'], $_POST['vitals_sugar'],$_POST['pid']]);
    header("Location: dashboard.php"); 
    exit;
}

// FETCH QUEUE: Newest patients first
$patients =$pdo->prepare("SELECT * FROM patients WHERE doctor_id=? AND status IN ('waiting', 'vitals_done') ORDER BY id DESC");
$patients->execute([$did]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Dashboard</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            body * { visibility: hidden; }
            .print-area, .print-area * { visibility: visible; }
            .print-area { position: absolute; left: 0; top: 0; width: 100%; }
            .no-print { display: none !important; }
        }
        .skiptranslate iframe, .goog-te-banner-frame { display: none !important; }
        body { top: 0px !important; font-family: sans-serif; }
    </style>
</head>
<body class="bg-slate-100 min-h-screen">

    <div id="google_translate_element" style="display:none;"></div>
    <script type="text/javascript">
        var prefLang = '<?= $doctor['preferred_lang'] ?? 'en' ?>';
        var expectedCookie = '/en/' + prefLang;
        if (document.cookie.indexOf('googtrans=' + expectedCookie) === -1) {
            document.cookie = "googtrans=" + expectedCookie + "; path=/";
            if(prefLang !== 'en') { window.location.reload(); }
        }
        function googleTranslateElementInit() {
            new google.translate.TranslateElement({pageLanguage: 'en', autoDisplay: false}, 'google_translate_element');
        }
    </script>
    <script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>

    <!-- Navbar -->
    <nav class="bg-white shadow px-6 py-4 flex justify-between items-center no-print">
        <h1 class="text-xl font-bold text-blue-600">Dr. <?= htmlspecialchars($doctor['name'] ?? 'Doctor') ?>'s Triage</h1>
        
        <div class="flex items-center gap-4">
            <form method="POST" class="flex items-center gap-2">
                <span class="text-xs font-bold text-slate-500">Dashboard Language:</span>
                <input type="hidden" name="update_lang" value="1">
                <select name="lang" onchange="this.form.submit()" class="border border-slate-300 rounded p-1.5 text-sm outline-none cursor-pointer font-medium bg-slate-50">
                    <option value="en" <?= ($doctor['preferred_lang']??'en')=='en'?'selected':'' ?>>English</option>
                    <option value="hi" <?= ($doctor['preferred_lang']??'en')=='hi'?'selected':'' ?>>Hindi (हिंदी)</option>
                    <option value="mr" <?= ($doctor['preferred_lang']??'en')=='mr'?'selected':'' ?>>Marathi (मराठी)</option>
                    <option value="gu" <?= ($doctor['preferred_lang']??'en')=='gu'?'selected':'' ?>>Gujarati</option>
                </select>
            </form>
            <a href="profile.php" class="text-blue-600 text-sm font-bold underline hover:text-blue-800">Edit Profile</a>
            <a href="../auth/logout.php" class="bg-red-50 text-red-600 px-3 py-1.5 rounded-lg text-sm font-bold hover:bg-red-100 transition shadow-sm">Logout</a>
        </div>
    </nav>

    <div class="max-w-6xl mx-auto p-6 grid gap-6 no-print">
        <?php foreach($patients as$p): ?>
        
        <div class="bg-white rounded-xl shadow-lg border border-slate-200 overflow-hidden print-area" id="patient-<?= $p['id'] ?>">
            
            <form method="POST">
                <input type="hidden" name="pid" value="<?= $p['id'] ?>">
                
                <div class="bg-slate-50 p-4 flex flex-col md:flex-row justify-between md:items-center border-b gap-4">
                    <div>
                        <h2 class="text-xl font-bold text-slate-800 uppercase"><?= htmlspecialchars($p['name'] ?? 'Unknown') ?></h2>
                        <p class="text-sm text-slate-500">Token: <span class="font-bold text-slate-700"><?= htmlspecialchars($p['token_number'] ?? '') ?></span> \vert{} Contact: <?= htmlspecialchars($p['contact'] ?? '') ?></p>
                    </div>
                    
                    <div class="flex gap-4">
                        <div class="bg-rose-50 text-rose-700 px-3 py-2 rounded-lg text-sm border border-rose-100 flex items-center gap-2 shadow-sm">
                            <span class="font-bold">BP:</span> 
                            <input type="text" name="vitals_bp" value="<?= htmlspecialchars($p['vitals_bp'] ?? '') ?>" placeholder="e.g. 120/80" class="border border-rose-200 rounded px-2 py-1 w-24 outline-none text-slate-800 focus:ring-2 focus:ring-rose-400">
                        </div>
                        <div class="bg-amber-50 text-amber-700 px-3 py-2 rounded-lg text-sm border border-amber-100 flex items-center gap-2 shadow-sm">
                            <span class="font-bold">Sugar:</span> 
                            <input type="text" name="vitals_sugar" value="<?= htmlspecialchars($p['vitals_sugar'] ?? '') ?>" placeholder="e.g. 95" class="border border-amber-200 rounded px-2 py-1 w-20 outline-none text-slate-800 focus:ring-2 focus:ring-amber-400">
                        </div>
                    </div>
                </div>

                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <div>
                            <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Patient Said (Original):</h4>
                            <p class="text-sm bg-slate-50 p-3 rounded border italic font-medium text-slate-700">"<?= htmlspecialchars($p['symptoms_raw'] ?? 'No symptoms reported.') ?>"</p>
                        </div>
                        <div>
                            <div class="flex justify-between items-center mb-1">
                                <h4 class="text-xs font-bold text-blue-600 uppercase tracking-wider">AI Clinical Summary:</h4>
                                <button type="button" onclick="playVoice('<?= addslashes(str_replace(["\r", "\n"], ' ', $p['ai_summary'] ?? '')) ?>', '<?=$doctor['preferred_lang'] ?? 'en' ?>')" class="no-print bg-blue-100 hover:bg-blue-200 text-blue-700 text-xs px-3 py-1 rounded-full font-bold flex items-center gap-1 transition shadow-sm">
                                    🔊 Listen
                                </button>
                            </div>
                            <p class="text-sm text-slate-800 bg-blue-50/50 p-4 rounded-xl border border-blue-100 leading-relaxed font-medium shadow-inner"><?= htmlspecialchars($p['ai_summary'] ?? 'Needs Evaluation') ?></p>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between items-center mb-1">
                            <h4 class="text-xs font-bold text-emerald-600 uppercase tracking-wider">Edit & Finalize Prescription:</h4>
                            <span class="text-xs text-slate-400 italic">Add Medicines/Tests</span>
                        </div>
                        
                        <textarea name="rx_text" rows="10" class="w-full bg-slate-50 border border-slate-300 rounded-xl p-4 text-sm font-mono focus:ring-2 focus:ring-emerald-500 outline-none mb-4 text-slate-800 leading-relaxed shadow-inner"><?= htmlspecialchars($p['ai_prescription'] ?? 'Pending Dr. Review') ?></textarea>

                        <div class="flex gap-3 no-print">
                            <button type="button" onclick="printRx('patient-<?= $p['id'] ?>')" class="w-1/3 bg-slate-800 hover:bg-slate-900 text-white font-bold py-3 rounded-xl shadow-md transition flex justify-center items-center gap-2">
                                🖨️ Print Rx
                            </button>
                            <button type="submit" name="approve_rx" class="w-2/3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 rounded-xl shadow-md transition flex justify-center items-center gap-2">
                                ✅ Approve & Send
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <?php endforeach; ?>
        
        <?php if(empty($patients)): ?>
            <div class="text-center p-12 bg-white rounded-xl shadow-sm border border-slate-200 text-slate-400 font-medium text-lg">No patients waiting in queue.</div>
        <?php endif; ?>
    </div>

    <script>
        function playVoice(text, lang) {
            if ('speechSynthesis' in window) {
                window.speechSynthesis.cancel();
                const utterance = new SpeechSynthesisUtterance(text);
                let voiceLang = 'en-US';
                if(lang === 'hi') voiceLang = 'hi-IN';
                if(lang === 'mr') voiceLang = 'mr-IN';
                if(lang === 'gu') voiceLang = 'gu-IN';
                
                utterance.lang = voiceLang;
                window.speechSynthesis.speak(utterance);
            }
        }
        function printRx(divId) {
            let allCards = document.querySelectorAll('.print-area');
            allCards.forEach(card => card.classList.remove('print-area'));
            document.getElementById(divId).classList.add('print-area');
            window.print();
        }
    </script>
</body>
</html>