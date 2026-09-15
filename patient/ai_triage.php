<?php
error_reporting(0);
ini_set('display_errors', 0);
ob_start();
require_once '../config/db.php';

$pid = $_GET['pid'] ?? null;
if (!$pid) {
    ob_end_clean();
    die("Invalid Patient Session");
}

// API Key Fetch Logic
$apiKey = getenv('GEMINI_API_KEY');
if (empty($apiKey) && file_exists('../.env')) {
    $lines = file('../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), 'GEMINI_API_KEY') === 0) {
            $apiKey = trim(explode('=', $line, 2)[1], '"\' ');
        }
    }
}

$success = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['symptoms'])) {
    $sym = $_POST['symptoms'];

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
    curl_close($ch);
    $ai_sum = "Needs Evaluation";
    $ai_rx = "Pending Dr. Review";
    $urgen = 1;

    if ($res) {
        $result = json_decode($res, true);
        if (isset($result['error'])) {
            $ai_sum = "API ERROR: " . $result['error']['message'];
            $ai_rx = "Please check your Gemini API Key.";
        } elseif (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
            $raw_text = $result['candidates'][0]['content']['parts'][0]['text'];

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

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --primary-soft: #eff6ff;
            --primary-border: #dbeafe;
            --text: #0f172a;
            --muted: #64748b;
            --muted-light: #94a3b8;
            --border: #e2e8f0;
            --surface: #ffffff;
            --background: #f4f8fc;
            --success: #16a34a;
            --success-soft: #ecfdf5;
            --danger: #ef4444;
            --danger-soft: #fff1f2;
            --shadow: 0 24px 70px rgba(15, 23, 42, 0.08);
            --shadow-soft: 0 10px 30px rgba(37, 99, 235, 0.08);
        }

        * {
            box-sizing: border-box;
        }

        html {
            min-height: 100%;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Inter", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            color: var(--text);
            background:
                radial-gradient(circle at 50% -10%, rgba(37, 99, 235, 0.09), transparent 34%),
                linear-gradient(180deg, #f8fbff 0%, var(--background) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 28px 18px;
        }

        button,
        textarea,
        select {
            font: inherit;
        }

        .page-shell {
            width: 100%;
            max-width: 780px;
        }

        .main-card {
            position: relative;
            width: 100%;
            background: rgba(255, 255, 255, 0.96);
            border: 1px solid rgba(226, 232, 240, 0.9);
            border-radius: 30px;
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .top-accent {
            height: 5px;
            width: 100%;
            background: linear-gradient(90deg, #2563eb 0%, #3b82f6 50%, #60a5fa 100%);
        }

        .card-inner {
            padding: 34px 38px 38px;
        }

        .brand-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            padding-bottom: 24px;
            border-bottom: 1px solid #edf2f7;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 13px;
        }

        .brand-icon {
            width: 43px;
            height: 43px;
            border-radius: 13px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--primary);
            color: #fff;
            font-size: 22px;
            box-shadow: 0 8px 18px rgba(37, 99, 235, 0.22);
        }

        .brand-copy h1 {
            margin: 0;
            font-size: 18px;
            line-height: 1.2;
            font-weight: 800;
            letter-spacing: -0.35px;
        }

        .brand-copy p {
            margin: 4px 0 0;
            font-size: 12px;
            color: var(--muted);
            font-weight: 500;
        }

        .language-wrap {
            position: relative;
        }

        .language-label {
            display: block;
            margin: 0 0 5px 2px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: var(--muted-light);
        }

        select.custom-select {
            min-width: 148px;
            appearance: none;
            -webkit-appearance: none;
            border: 1px solid var(--border);
            background: #f8fafc;
            color: #334155;
            border-radius: 11px;
            padding: 10px 34px 10px 12px;
            font-size: 13px;
            font-weight: 600;
            outline: none;
            cursor: pointer;
            background-image:
                linear-gradient(45deg, transparent 50%, #64748b 50%),
                linear-gradient(135deg, #64748b 50%, transparent 50%);
            background-position:
                calc(100% - 15px) 15px,
                calc(100% - 11px) 15px;
            background-size: 4px 4px, 4px 4px;
            background-repeat: no-repeat;
            transition: .2s ease;
        }

        select.custom-select:hover {
            border-color: #bfdbfe;
            background-color: #f1f5f9;
        }

        select.custom-select:focus {
            border-color: #60a5fa;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, .10);
        }

        .start-section {
            padding: 46px 20px 34px;
            text-align: center;
        }

        .hero-avatar {
            position: relative;
            width: 132px;
            height: 132px;
            margin: 0 auto 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: #eaf3ff;
            border: 10px solid #f7fbff;
            box-shadow:
                0 0 0 1px #dbeafe,
                0 15px 35px rgba(37, 99, 235, .10);
        }

        .hero-avatar::before {
            content: "";
            position: absolute;
            inset: 12px;
            border-radius: 50%;
            background: rgba(255, 255, 255, .55);
        }

        .hero-avatar span {
            position: relative;
            z-index: 1;
            font-size: 58px;
            line-height: 1;
        }

        .start-section h2 {
            margin: 0;
            font-size: 28px;
            line-height: 1.2;
            letter-spacing: -0.8px;
            font-weight: 800;
            color: #0f172a;
        }

        .start-section p {
            margin: 11px auto 28px;
            max-width: 480px;
            color: var(--muted);
            font-size: 15px;
            line-height: 1.7;
        }

        .btn-start {
            width: 100%;
            min-height: 58px;
            border: 0;
            border-radius: 15px;
            background: linear-gradient(135deg, #2563eb, #2f67e8);
            color: #fff;
            font-size: 16px;
            font-weight: 750;
            letter-spacing: -.1px;
            cursor: pointer;
            box-shadow: 0 12px 25px rgba(37, 99, 235, .20);
            transition: transform .2s ease, box-shadow .2s ease, background .2s ease;
        }

        .btn-start:hover {
            background: linear-gradient(135deg, #1d4ed8, #2563eb);
            transform: translateY(-1px);
            box-shadow: 0 16px 30px rgba(37, 99, 235, .24);
        }

        .btn-start:active {
            transform: translateY(0);
        }

        .start-trust {
            margin-top: 17px;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 7px;
            color: #94a3b8;
            font-size: 11px;
            font-weight: 600;
        }

        .trust-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #22c55e;
        }

        .ai-interface {
            padding-top: 30px;
        }

        .doctor-panel {
            text-align: center;
            margin-bottom: 27px;
        }

        .ai-avatar {
            position: relative;
            width: 94px;
            height: 94px;
            margin: 0 auto 18px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(145deg, #2563eb, #1d4ed8);
            border: 7px solid #eff6ff;
            color: white;
            font-size: 39px;
            box-shadow:
                0 0 0 1px #dbeafe,
                0 12px 30px rgba(37, 99, 235, .22);
            transition: .25s ease;
        }

        .ai-avatar.pulse-ring {
            animation: pulseAvatar 1.8s infinite;
        }

        .ai-bubble {
            position: relative;
            width: 100%;
            min-height: 78px;
            padding: 19px 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 17px;
            border: 1px solid #dbeafe;
            background: linear-gradient(180deg, #f8fbff, #f5f9ff);
            color: #1e40af;
            box-shadow: inset 0 1px 2px rgba(15, 23, 42, .02);
        }

        .ai-bubble::before {
            content: "";
            position: absolute;
            top: -8px;
            left: 50%;
            width: 15px;
            height: 15px;
            background: #f8fbff;
            border-left: 1px solid #dbeafe;
            border-top: 1px solid #dbeafe;
            transform: translateX(-50%) rotate(45deg);
        }

        .ai-bubble p {
            position: relative;
            z-index: 1;
            margin: 0;
            font-size: 14px;
            line-height: 1.65;
            font-weight: 650;
        }

        .input-section {
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            padding: 7px;
            background: #fff;
            box-shadow: 0 7px 24px rgba(15, 23, 42, .045);
        }

        .input-label {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 9px 13px 5px;
            color: #475569;
            font-size: 12px;
            font-weight: 700;
        }

        .input-label span:last-child {
            color: #94a3b8;
            font-weight: 500;
        }

        .textarea-custom {
            display: block;
            width: 100%;
            min-height: 132px;
            resize: vertical;
            border: 0;
            outline: none;
            padding: 11px 13px 13px;
            background: transparent;
            color: #334155;
            font-size: 15px;
            line-height: 1.6;
        }

        .textarea-custom::placeholder {
            color: #9aa8ba;
        }

        .textarea-custom:focus {
            outline: none;
        }

        .action-row {
            display: grid;
            grid-template-columns: 150px 1fr;
            gap: 9px;
            padding: 7px;
            margin-top: 2px;
        }

        .btn-mic,
        .btn-submit {
            min-height: 58px;
            border-radius: 14px;
            cursor: pointer;
            transition: .2s ease;
        }

        .btn-mic {
            border: 1px solid #fecdd3;
            background: var(--danger-soft);
            color: #be123c;
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: center;
            gap: 9px;
            font-weight: 750;
            font-size: 14px;
        }

        .btn-mic:hover {
            background: #ffe4e6;
            border-color: #fda4af;
        }

        .btn-mic span:first-child {
            font-size: 21px;
        }

        .btn-mic.recording {
            background: #e11d48;
            color: #fff;
            border-color: #e11d48;
            animation: pulse 1.5s infinite;
        }

        .btn-submit {
            border: 0;
            background: linear-gradient(135deg, #2563eb, #2f67e8);
            color: #fff;
            font-size: 15px;
            font-weight: 750;
            box-shadow: 0 9px 20px rgba(37, 99, 235, .17);
        }

        .btn-submit:hover {
            background: linear-gradient(135deg, #1d4ed8, #2563eb);
            transform: translateY(-1px);
            box-shadow: 0 13px 24px rgba(37, 99, 235, .21);
        }

        .privacy-note {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            margin-top: 18px;
            color: #94a3b8;
            font-size: 11px;
            font-weight: 500;
        }

        .privacy-note strong {
            color: #64748b;
        }

        .success-section {
            text-align: center;
            padding: 48px 20px 35px;
        }

        .success-icon {
            width: 86px;
            height: 86px;
            margin: 0 auto 22px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--success-soft);
            border: 8px solid #f5fffa;
            box-shadow: 0 0 0 1px #bbf7d0, 0 14px 30px rgba(22, 163, 74, .10);
            font-size: 37px;
        }

        .success-section h2 {
            margin: 0;
            font-size: 27px;
            font-weight: 800;
            letter-spacing: -.6px;
        }

        .success-section p {
            max-width: 480px;
            margin: 11px auto 27px;
            color: var(--muted);
            line-height: 1.65;
            font-size: 14px;
        }

        .success-section .btn-start {
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
        }

        .footer-line {
            text-align: center;
            padding: 0 0 2px;
            color: #a0aec0;
            font-size: 10px;
            font-weight: 500;
        }

        .hidden {
            display: none !important;
        }

        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(225, 29, 72, .48);
            }
            70% {
                box-shadow: 0 0 0 13px rgba(225, 29, 72, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(225, 29, 72, 0);
            }
        }

        @keyframes pulseAvatar {
            0% {
                box-shadow:
                    0 0 0 0 rgba(37, 99, 235, .40),
                    0 12px 30px rgba(37, 99, 235, .18);
            }
            70% {
                box-shadow:
                    0 0 0 18px rgba(37, 99, 235, 0),
                    0 12px 30px rgba(37, 99, 235, .18);
            }
            100% {
                box-shadow:
                    0 0 0 0 rgba(37, 99, 235, 0),
                    0 12px 30px rgba(37, 99, 235, .18);
            }
        }

        @media (max-width: 640px) {
            body {
                padding: 12px;
                align-items: flex-start;
            }

            .main-card {
                margin-top: 8px;
                border-radius: 23px;
            }

            .card-inner {
                padding: 23px 18px 25px;
            }

            .brand-row {
                align-items: flex-start;
                padding-bottom: 20px;
            }

            .brand-copy h1 {
                font-size: 16px;
            }

            .brand-copy p {
                font-size: 10px;
            }

            select.custom-select {
                min-width: 128px;
                font-size: 12px;
            }

            .start-section {
                padding: 35px 5px 24px;
            }

            .hero-avatar {
                width: 116px;
                height: 116px;
                margin-bottom: 21px;
            }

            .hero-avatar span {
                font-size: 50px;
            }

            .start-section h2 {
                font-size: 23px;
            }

            .start-section p {
                font-size: 13px;
                margin-bottom: 23px;
            }

            .btn-start {
                min-height: 54px;
                font-size: 14px;
            }

            .ai-interface {
                padding-top: 25px;
            }

            .ai-avatar {
                width: 86px;
                height: 86px;
                font-size: 34px;
            }

            .action-row {
                grid-template-columns: 1fr;
            }

            .btn-mic,
            .btn-submit {
                min-height: 54px;
            }

            .privacy-note {
                text-align: center;
                line-height: 1.5;
            }
        }
    </style>
</head>

<body>
    <div class="page-shell">
        <div class="main-card">
            <div class="top-accent"></div>

            <div class="card-inner">

                <?php if ($success): ?>

                    <div class="success-section">
                        <div class="success-icon">✓</div>

                        <h2>Check-in Complete!</h2>

                        <p>
                            Your symptoms have been securely analyzed.
                            The doctor is reviewing your file.
                        </p>

                        <a href="dashboard.php" class="btn-start">
                            Go to My Dashboard
                        </a>
                    </div>

                <?php else: ?>

                    <div class="brand-row">
                        <div class="brand">
                            <div class="brand-icon">✚</div>

                            <div class="brand-copy">
                                <h1>AI Medical Assistant</h1>
                                <p>FirstDoctor · Smart patient check-in</p>
                            </div>
                        </div>

                        <div class="language-wrap">
                            <label class="language-label" for="langSelect">Language</label>

                            <select id="langSelect" class="custom-select">
                                <option value="hi-IN" selected>Hindi (हिंदी)</option>
                                <option value="en-US">English (US)</option>
                                <option value="mr-IN">Marathi (मराठी)</option>
                                <option value="gu-IN">Gujarati (ગુજરાતી)</option>
                            </select>
                        </div>
                    </div>

                    <div id="startOverlay" class="start-section">
                        <div class="hero-avatar">
                            <span>🤖</span>
                        </div>

                        <h2>Ready for your Check-up?</h2>

                        <p>
                            Tell our AI Doctor what you're experiencing.
                            You can type your symptoms or speak naturally using your microphone.
                        </p>

                        <button type="button" onclick="startAiDoctor()" class="btn-start">
                            Start AI Consultation &nbsp;🎙️
                        </button>

                        <div class="start-trust">
                            <span class="trust-dot"></span>
                            <span>Secure patient check-in</span>
                            <span>•</span>
                            <span>Doctor review enabled</span>
                        </div>
                    </div>

                    <div id="aiInterface" class="ai-interface hidden">
                        <div class="doctor-panel">
                            <div id="aiAvatar" class="ai-avatar">👨‍⚕️</div>

                            <div class="ai-bubble">
                                <p id="aiTranscript">...</p>
                            </div>
                        </div>

                        <form method="POST" id="symptomForm">
                            <div class="input-section">
                                <div class="input-label">
                                    <span>Describe your symptoms</span>
                                    <span>Voice or text</span>
                                </div>

                                <textarea
                                    id="symptomsInput"
                                    name="symptoms"
                                    rows="5"
                                    class="textarea-custom"
                                    placeholder="Example: I have had a headache since morning, with mild fever..."
                                ></textarea>

                                <div class="action-row">
                                    <button
                                        type="button"
                                        id="micBtn"
                                        onclick="toggleMic()"
                                        class="btn-mic"
                                    >
                                        <span>🎤</span>
                                        <span id="micStatus">Speak</span>
                                    </button>

                                    <button type="submit" class="btn-submit">
                                        Submit to Doctor
                                    </button>
                                </div>
                            </div>

                            <div class="privacy-note">
                                <span>🔒</span>
                                <span>Your information is submitted for <strong>doctor review</strong>.</span>
                            </div>
                        </form>
                    </div>

                <?php endif; ?>

                <div class="footer-line">
                    FirstDoctor AI Medical Assistant
                </div>
            </div>
        </div>
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
            startOverlay.classList.add('hidden');
            aiInterface.classList.remove('hidden');
            speakWelcomeMessage();
        }

        langSelect.addEventListener('change', () => {
            if (!aiInterface.classList.contains('hidden')) {
                speakWelcomeMessage();
            }
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

                utterance.onend = function() {
                    aiAvatar.classList.remove('pulse-ring');
                };

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

                if (finalTranscript !== '') {
                    symptomsInput.value += finalTranscript;
                }
            };

            recognition.onerror = function() {
                stopMic();
            };

            recognition.onend = function() {
                stopMic();
            };
        } else {
            micBtn.style.display = 'none';
        }

        function toggleMic() {
            if (!recognition) return;

            if (isRecording) {
                stopMic();
            } else {
                recognition.lang = langSelect.value;
                recognition.start();
            }
        }

        function stopMic() {
            if (isRecording) recognition.stop();

            isRecording = false;
            micStatus.innerText = "Speak";
            micBtn.classList.remove('recording');
        }
    </script>
</body>
</html>
