<?php
error_reporting(0);
ini_set('display_errors', 0);
ob_start();
require_once '../config/db.php';

$pid =$_GET['pid'] ?? null;
if (!$pid) {
    ob_end_clean();
    die("Invalid Patient Session");
}

// API Key Fetch Logic
$apiKey = getenv('GEMINI_API_KEY');
if (empty($apiKey) && file_exists('../.env')) {$lines = file('../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as$line) {
        if (strpos(trim($line), 'GEMINI_API_KEY') === 0) {
            $apiKey = trim(explode('=',$line, 2)[1], '"\' ');
        }
    }
}

$success = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['symptoms'])) {
    $sym =$_POST['symptoms'];
    
    // The Elite 100-Year Expert Doctor Prompt
    $prompt = "You are an elite, world-renowned Chief Medical Officer and diagnostic expert with decades of experience. You are acting as a Clinical Decision Support System for an attending physician.
    
    Analyze the following patient symptoms: '$sym'.
    
    Determine the most accurate differential diagnoses and create a master-level treatment plan.
    
    OUTPUT STRICTLY IN ENGLISH AS A VALID JSON OBJECT ONLY. NO MARKDOWN. NO BACKTICKS.
    {
      \"summary\": \"Write a highly detailed, professional clinical summary (History of Present Illness). List the top suspected diagnoses.\",
      \"urgency\": 3,
      \"prescription\": \"[INVESTIGATIONS]\\nSuggest specific blood tests (e.g., CBC, CRP, Trop-I), X-Rays, or ECG.\\n\\n[MEDICATIONS]\\nList exact medicines (Tablets/Syrups) with exact Dosage, Frequency (e.g., 1 BD, 1 TDS), Timing, and Duration.\\n\\n[PRECAUTIONS & LIFESTYLE]\\nSpecific diet restrictions or physical precautions.\\n\\n[FOLLOW-UP]\\nExact follow-up timeline.\"
    }";
    
    $data = ["contents" => [["parts" => [["text" => $prompt]]]]];
    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-3.6-flash:generateContent?key=" . trim($apiKey);
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $res = curl_exec($ch); 
    curl_close($ch);$ai_sum = "Needs Evaluation"; 
    $ai_rx = "Pending Dr. Review"; 
    $urgency = 1;
    
    if ($res) {
        $result = json_decode($res, true);
        if (isset($result['error'])) {$ai_sum = "API ERROR: " . $result['error']['message'];$ai_rx = "Please check your Gemini API Key.";
        } elseif (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
            $raw_text =$result['candidates'][0]['content']['parts'][0]['text'];
            
            // Clean markdown blocks
            $clean_text = preg_replace('/```json|```|```JSON/', '', $raw_text);
            $start = strpos($clean_text, '{');
            $end = strrpos($clean_text, '}');
            
            if ($start !== false && $end !== false) {
                $json_string = substr($clean_text, $start, $end - $start + 1);
                $parsed = json_decode($json_string, true);
                if (json_last_error() === JSON_ERROR_NONE && isset($parsed['summary'])) {
                    $ai_sum = $parsed['summary'];
                    $urgency = $parsed['urgency'] ?? 1;
                    $ai_rx = $parsed['prescription'];
                }
            }
        }
    }
    
    $pdo->prepare("UPDATE patients SET symptoms_raw=?, ai_summary=?, ai_prescription=?, urgency_level=?, status='waiting' WHERE id=?")->execute([$sym, $ai_sum, $ai_rx, $urgency, $pid]);
    $success = true;
}
ob_end_clean();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Triage - FirstDoctor</title>
    <!-- Bulletproof CSS (Bootstrap) -->
    <link href="[https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css](https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css)" rel="stylesheet">
    <style>
        body { background-color: #f8fafc; font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; padding: 20px; }
        .main-card { background: white; border-radius: 24px; padding: 40px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); width: 100%; max-width: 600px; border: 1px solid #f1f5f9; }
        .btn-start { background-color: #2563eb; color: white; border-radius: 12px; padding: 15px; font-weight: bold; font-size: 1.1rem; width: 100%; border: none; box-shadow: 0 4px 6px rgba(37, 99, 235, 0.2); transition: 0.3s; }
        .btn-start:hover { background-color: #1d4ed8; color: white; }
        .btn-mic { background-color: #fff1f2; color: #e11d48; border: 1px solid #ffe4e6; border-radius: 12px; font-weight: bold; transition: 0.3s; display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; padding: 10px;}
        .btn-mic:hover { background-color: #ffe4e6; color: #be123c; }
        .btn-mic.recording { background-color: #e11d48; color: white; animation: pulse 3.6s infinite; }
        .btn-submit { background-color: #2563eb; color: white; border-radius: 12px; font-weight: bold; font-size: 1.1rem; border: none; padding: 15px; transition: 0.3s; }
        .btn-submit:hover { background-color: #1d4ed8; color: white; }
        .ai-avatar-container { display: flex; flex-direction: column; align-items: center; margin-bottom: 24px; }
        .ai-avatar { width: 100px; height: 100px; background-color: #2563eb; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 40px; box-shadow: 0 10px 15px rgba(37,99,235,0.2); transition: 0.3s; }
        .ai-bubble { background-color: #f8fafc; padding: 20px; border-radius: 16px; border-top-left-radius: 0; border: 1px solid #e2e8f0; width: 100%; text-align: center; margin-top: 15px; font-weight: 600; color: #1e40af; box-shadow: inset 0 2px 4px rgba(0,0,0,0.02); }
        .textarea-custom { border-radius: 16px; border: 1px solid #cbd5e1; padding: 20px; font-size: 1rem; color: #334155; box-shadow: inset 0 2px 4px rgba(0,0,0,0.02); resize: none; width: 100%; margin-bottom: 15px; outline: none; }
        .textarea-custom:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2); }
        select.custom-select { background-color: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 8px; padding: 8px 12px; font-weight: 500; color: #475569; outline: none; cursor: pointer; }
        @keyframes pulse { 0% { box-shadow: 0 0 0 0 rgba(225, 29, 72, 0.7); } 70% { box-shadow: 0 0 0 15px rgba(225, 29, 72, 0); } 100% { box-shadow: 0 0 0 0 rgba(225, 29, 72, 0); } }
        .pulse-ring { animation: pulseAvatar 2s infinite; }
        @keyframes pulseAvatar { 0% { box-shadow: 0 0 0 0 rgba(37, 99, 235, 0.7); } 70% { box-shadow: 0 0 0 20px rgba(37, 99, 235, 0); } 100% { box-shadow: 0 0 0 0 rgba(37, 99, 235, 0); } }
    </style>
</head>
<body>

    <div class="main-card">
        
        <?php if($success): ?>
            <div class="text-center py-4">
                <div style="width: 80px; height: 80px; background-color: #d1fae5; color: #059669; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 40px; margin: 0 auto 20px;">✅</div>
                <h2 style="font-weight: bold; color: #1e293b; margin-bottom: 10px;">Check-in Complete!</h2>
                <p style="color: #64748b; margin-bottom: 30px;">Your symptoms have been securely analyzed. The doctor is reviewing your file.</p>
                <a href="dashboard.php" class="btn btn-start d-block text-decoration-none">Go to My Dashboard</a>
            </div>
        <?php else: ?>

            <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
                <h4 style="font-weight: bold; color: #1e293b; margin: 0;">AI Medical Assistant</h4>
                <select id="langSelect" class="custom-select">
                    <option value="hi-IN" selected>Hindi (हिंदी)</option>
                    <option value="en-US">English (US)</option>
                    <option value="mr-IN">Marathi (मराठी)</option>
                    <option value="gu-IN">Gujarati (ગુજરાતી)</option>
                </select>
            </div>

            <div id="startOverlay" class="text-center py-4">
                <div style="width: 120px; height: 120px; background-color: #dbeafe; color: #2563eb; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 60px; margin: 0 auto 20px;">🤖</div>
                <h3 style="font-weight: bold; color: #1e293b; margin-bottom: 10px;">Ready for your Check-up?</h3>
                <p style="color: #64748b; margin-bottom: 30px;">Tap below to wake up your AI Doctor.</p>
                <button onclick="startAiDoctor()" class="btn-start">
                    Tap to Start Consultation 🎙️
                </button>
            </div>

            <div id="aiInterface" class="d-none">
                <div class="ai-avatar-container">
                    <div id="aiAvatar" class="ai-avatar">👨‍⚕️</div>
                    <div class="ai-bubble">
                        <p id="aiTranscript" class="mb-0">...</p>
                    </div>
                </div>

                <form method="POST" id="symptomForm">
                    <textarea id="symptomsInput" name="symptoms" rows="4" class="textarea-custom" placeholder="Type your symptoms here or tap the mic to speak..."></textarea>

                    <div class="row g-2">
                        <div class="col-4">
                            <button type="button" id="micBtn" onclick="toggleMic()" class="btn btn-mic w-100">
                                <span style="font-size: 24px; margin-bottom: 5px;">🎤</span>
                                <span id="micStatus">Speak</span>
                            </button>
                        </div>
                        <div class="col-8">
                            <button type="submit" class="btn btn-submit w-100 h-100">
                                Submit to Doctor
                            </button>
                        </div>
                    </div>
                </form>
            </div>

        <?php endif; ?>
    </div>

    <script>
        const welcomeMessages = {
            'hi-IN': "नमस्ते! मैं आपका एआई डॉक्टर हूँ। कृपया मुझे विस्तार से बताएं कि आपको क्या समस्या है?",
            'en-US': "Hello! I am your AI Doctor. Please tell me your symptoms in detail.",
            'mr-IN': "नमस्कार! मी तुमचा एआय डॉक्टर आहे. कृपया मला तुमचा त्रास सांगा.",
            'gu-IN': "નમસ્તે! હું તમારો એઆઈ ડોક્ટર છું. કૃપા કરીને મને તમારી સમસ્યા જણાવો."
        };

        const langSelect = document.getElementById('langSelect');
        const aiInterface = document.getElementById('aiInterface');
        const startOverlay = document.getElementById('startOverlay');
        const aiAvatar = document.getElementById('aiAvatar');
        const aiTranscript = document.getElementById('aiTranscript');
        const symptomsInput = document.getElementById('symptomsInput');
        const micBtn = document.getElementById('micBtn');
        const micStatus = document.getElementById('micStatus');

        let recognition;
        let isRecording = false;

        function startAiDoctor() {
            startOverlay.classList.add('d-none');
            aiInterface.classList.remove('d-none');
            speakWelcomeMessage();
        }

        langSelect.addEventListener('change', () => {
            if (!aiInterface.classList.contains('d-none')) { speakWelcomeMessage(); }
        });

        function speakWelcomeMessage() {
            if ('speechSynthesis' in window) {
                window.speechSynthesis.cancel();
                const lang = langSelect.value;
                const text = welcomeMessages[lang] || welcomeMessages['en-US'];
                aiTranscript.innerText = text;
                aiAvatar.classList.add('pulse-ring');

                const utterance = new SpeechSynthesisUtterance(text);
                utterance.lang = lang;
                utterance.onend = function() { aiAvatar.classList.remove('pulse-ring'); };
                window.speechSynthesis.speak(utterance);
            }
        }

        if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            recognition = new SpeechRecognition();
            recognition.continuous = true;
            recognition.interimResults = true;

            recognition.onstart = function() {
                isRecording = true;
                micStatus.innerText = "Listening...";
                micBtn.classList.add('recording');
            };

            recognition.onresult = function(event) {
                let finalTranscript = '';
                for (let i = event.resultIndex; i < event.results.length; ++i) {
                    if (event.results[i].isFinal) {
                        finalTranscript += event.results[i][0].transcript + ' ';
                    }
                }
                if (finalTranscript !== '') { symptomsInput.value += finalTranscript; }
            };

            recognition.onerror = function() { stopMic(); };
            recognition.onend = function() { stopMic(); };
        } else {
            micBtn.style.display = 'none';
        }

        function toggleMic() {
            if (!recognition) return;
            if (isRecording) { stopMic(); } 
            else { recognition.lang = langSelect.value; recognition.start(); }
        }

        function stopMic() {
            if(isRecording) recognition.stop();
            isRecording = false;
            micStatus.innerText = "Speak";
            micBtn.classList.remove('recording');
        }
    </script>
</body>
</html>