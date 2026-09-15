<?php
// Demo credentials for standard presentation
$host = $_SERVER['HTTP_HOST'];
// Default redirection to Hospital ID 1 and Doctor ID 1 for Demo
$demo_link = "http://" . $host . "/firstdoctor/patient/check_in.php?hid=1&did=1";
// Generate High-Res QR code
$qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=400x400&margin=10&data=" . urlencode($demo_link);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live QR Demo - FirstDoctor AI</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background-color: #020617;
            background-image: 
                radial-gradient(circle at 15% 50%, rgba(6, 182, 212, 0.08), transparent 25%),
                radial-gradient(circle at 85% 30%, rgba(59, 130, 246, 0.08), transparent 25%);
            font-family: 'Inter', sans-serif;
            color: white;
        }
        .qr-card {
            background: #ffffff;
            box-shadow: 0 0 40px rgba(6, 182, 212, 0.2);
        }
    </style>
</head>
<body class="min-h-screen flex flex-col items-center justify-center p-6">

    <!-- Header info for judges -->
    <div class="text-center mb-8">
        <div class="inline-block px-4 py-1.5 rounded-full border border-cyan-500/30 bg-cyan-500/10 text-cyan-400 text-xs font-bold tracking-widest uppercase mb-4 shadow-[0_0_15px_rgba(6,182,212,0.2)]">
            Live Demonstration
        </div>
        <h1 class="text-3xl md:text-4xl font-black tracking-tight mb-2">Scan with your Mobile</h1>
        <p class="text-slate-400 font-medium">Experience the AI Triage as a Patient</p>
    </div>

    <!-- The QR Poster Design (Same as downloaded PNG) -->
    <div class="max-w-md w-full qr-card rounded-3xl overflow-hidden p-8 text-center relative border-4 border-cyan-500/20">
        
        <!-- Header -->
        <div class="mb-6">
            <div class="text-blue-600 font-black text-3xl tracking-tight mb-1">FirstDoctor AI</div>
            <div class="text-slate-500 text-sm font-bold uppercase tracking-widest">City Hospital (Demo)</div>
        </div>
        
        <!-- Doctor Info -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border-2 border-slate-100 mb-6">
            <h2 class="text-2xl font-black text-slate-800">Dr. Ishwar Rathod</h2>
            <p class="text-blue-600 font-bold text-sm mt-2">MBBS, MD (General Medicine)</p>
            <p class="text-slate-500 font-medium text-xs mt-1">Mon - Sat | 10:00 AM - 06:00 PM</p>
        </div>
        
        <!-- QR Code -->
        <div class="bg-white p-4 rounded-2xl inline-block shadow-md border border-slate-200 mb-6">
            <img src="<?= $qr_url ?>" alt="Demo QR Code" class="w-56 h-56 mx-auto rounded-lg">
        </div>
        
        <!-- Instructions Badge -->
        <div class="bg-blue-600 text-white p-4 rounded-2xl shadow-inner mb-8">
            <p class="font-extrabold text-xl m-0">Scan to Book Consultation</p>
        </div>

        <!-- Manual Link Button -->
        <a href="<?= $demo_link ?>" class="block w-full bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold py-4 rounded-xl transition border border-slate-300 shadow-sm flex items-center justify-center gap-2 text-lg">
            Or Click Here to Enter 🔗
        </a>
    </div>

    <div class="mt-8">
        <a href="index.php" class="text-slate-400 hover:text-cyan-400 font-bold transition flex items-center gap-2">
            ← Back to Homepage
        </a>
    </div>

</body>
</html>