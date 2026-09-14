<?php
ob_start(); // PREVENTS PHP ERRORS FROM BREAKING THE HTML DESIGN
require_once '../config/db.php';
$pid =$_GET['pid'] ?? null;
if (!$pid) die("Invalid Patient Session");

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
    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . trim($apiKey);
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $res = curl_exec($ch); 
    $curl_err = curl_error($ch);
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Triage - FirstDoctor</title>
    <!-- Tailwind CSS -->
    <script src="[https://cdn.tailwindcss.com](https://cdn.tailwindcss.com)"></script>
    <style>
        .pulse-ring { animation: pulse 2s infinite; }
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.7); }
            70% { box-shadow: 0 0 0 15px rgba(59, 130, 246, 0); }
            100% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0); }
        }
        body { font-family: sans-serif; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-4">

    <div class="bg-white p-6 md:p-10 rounded-3xl shadow-xl w-full max-w-xl border border-slate-100">
        
        <?php if($success): ?>
            <div class="text-center py-10">
                <div class="w-20 h-20 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-4 text-4xl shadow-sm">✅</div>
                <h2 class="text-2xl font-bold text-slate-800 mb-2">Check-in Complete!</h2>
                <p class="text-slate-500 mb-8">Your symptoms have been securely analyzed. The doctor is reviewing your file.</p>
                <a href="dashboard.php" class="inline-block bg-blue-600 hover:bg-blue-700 transition text-white px-8 py-3.5 rounded-xl font-bold shadow-md w-full">Go to My Dashboard</a>
            </div>
        <?php else: ?>

            <div class="flex justify-between items-center mb-8 border-b pb-4">
                <h2 class="text-lg font-bold text-slate-800">AI Medical Assistant</h2>
                <select id="langSelect" class="bg-slate-100 border border-slate-200 text-slate-700 text-sm rounded-lg focus:ring-blue-500 p-2.5 cursor-pointer outline-none font-medium">
                    <option value="hi-IN" selected>Hindi (हिंदी) - Default</option>
                    <option value="en-US">English (US)</option>
                    <option value="mr-IN">Marathi (मराठी)</option>
                    <option value="gu-IN">Gujarati (ગુજરાતી)</option>
                    <option value="ta-IN">Tamil (தமிழ்)</option>
                    <option value="te-IN">Telugu (తెలుగు)</option>
                    <option value="bn-IN">Bengali (বাংলা)</option>
                </select>
            </div>

            <div id="startOverlay" class="text-center py-10">
                <div class="w-28 h-28 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-6 text-6xl shadow-inner">🤖</div>
                <h3 class="text-2xl font-bold text-slate-800 mb-2">Ready for your Check-up?</h3>
                <p class="text-slate-500 mb-8">Tap below to wake up your AI Doctor.</p>
                <button onclick="startAiDoctor()" class="bg-blue-600 hover:bg-blue-700 text-white w-full font-bold py-4 rounded-xl shadow-lg transition text-lg flex justify-center items-center gap-2">
                    Tap to Start Consultation 🎙️
                </button>
            </div>

            <div id="aiInterface" class="hidden">
                <div class="flex flex-col items-center justify-center mb-6">
                    <div id="aiAvatar" class="w-24 h-24 bg-blue-600 text-white rounded-full flex items-center justify-center text-5xl shadow-lg transition-all duration-300">👨‍⚕️</div>
                    <div class="mt-5 bg-slate-50 p-4 rounded-2xl rounded-tl-none relative w-full text-center border border-slate-200 shadow-sm">
                        <p id="aiTranscript" class="text-blue-800 font-semibold text-lg">...</p>
                    </div>
                </div>

                <form method="POST" id="symptomForm" class="space-y-5">
                    <div class="relative">
                        <textarea id="symptomsInput" name="symptoms" rows="4" class="w-full border border-slate-300 p-4 rounded-2xl focus:ring-2 focus:ring-blue-500 outline-none resize-none text-slate-700 font-medium shadow-sm" placeholder="Type your symptoms here or tap the mic to speak..."></textarea>
                    </div>

                    <div class="flex gap-3">
                        <button type="button" id="micBtn" onclick="toggleMic()" class="bg-rose-50 border border-rose-200 text-rose-600 hover:bg-rose-100 w-1/3 flex flex-col items-center justify-center py-3.5 rounded-xl transition shadow-sm font-bold">
                            <span class="text-2xl mb-1">🎤</span>
                            <span id="micStatus" class="text-sm">Speak</span>
                        </button>
                        
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white w-2/3 py-3.5 rounded-xl shadow-md transition font-bold text-lg">
                            Submit to Doctor
                        </button>
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
            startOverlay.style.display = 'none';
            aiInterface.style.display = 'block';
            speakWelcomeMessage();
        }

        langSelect.addEventListener('change', () => {
            if (aiInterface.style.display === 'block') { speakWelcomeMessage(); }
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
                micBtn.classList.replace('bg-rose-50', 'bg-rose-600');
                micBtn.classList.replace('text-rose-600', 'text-white');
                micBtn.classList.add('animate-pulse');
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
            micBtn.classList.replace('bg-rose-600', 'bg-rose-50');
            micBtn.classList.replace('text-white', 'text-rose-600');
            micBtn.classList.remove('animate-pulse');
        }
    </script>
</body>
</html>