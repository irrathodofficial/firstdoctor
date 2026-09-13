<?php
require_once '../config/db.php';
$pid = $_GET['pid'] ?? null;
if (!$pid) die("Invalid Patient");

$apiKey = getenv('GEMINI_API_KEY');
$success = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['symptoms'])) {
    $sym = $_POST['symptoms'];
    $prompt = "You are a clinical AI. Analyze patient symptoms: '$sym'. Return strictly JSON (no markdown): {\"summary\": \"detailed summary\", \"urgency\": 3, \"prescription\": \"Rx details\"}";
    
    $ch = curl_init("https://generativelanguage.googleapis.com/v1beta/models/gemini-3.6-flash:generateContent?key=$apiKey");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(["contents" => [["parts" => [["text" => $prompt]]]]]));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $res = curl_exec($ch); curl_close($ch);
    
    $ai_sum = "Needs Evaluation"; $ai_rx = "Pending Dr. Review"; $urgency = 1;
    if ($res) {
        $result = json_decode($res, true);
        if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
            $raw = trim(str_replace(["```json", "```"], "", $result['candidates'][0]['content']['parts'][0]['text']));
            $parsed = json_decode($raw, true);
            if ($parsed) {
                $ai_sum = $parsed['summary']; $urgency = $parsed['urgency']; $ai_rx = $parsed['prescription'];
            }
        }
    }
    $pdo->prepare("UPDATE patients SET symptoms_raw=?, ai_summary=?, ai_prescription=?, urgency_level=? WHERE id=?")->execute([$sym, $ai_sum, $ai_rx, $urgency, $pid]);
    $success = true;
}
?>
<!DOCTYPE html><html><head><script src="https://cdn.tailwindcss.com"></script></head>
<body class="bg-slate-100 min-h-screen p-4 flex justify-center items-center">
    <div class="bg-white p-6 rounded shadow max-w-lg w-full">
        <?php if($success): ?>
            <h2 class="text-xl font-bold text-green-600 mb-2">Check-in Complete!</h2>
            <p>Your details have been sent to the doctor's screen.</p>
        <?php else: ?>
            <h2 class="text-xl font-bold mb-4">Tell us your symptoms (Any Language)</h2>
            <form method="POST">
                <textarea name="symptoms" rows="4" class="w-full border p-3 rounded mb-4" placeholder="Mujhe 2 din se bukhar hai..."></textarea>
                <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded font-bold">Submit to Doctor</button>
            </form>
        <?php endif; ?>
    </div>
</body></html>