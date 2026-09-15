<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FirstDoctor AI | The Next-Gen Clinical Assistant</title>
    
    <!-- Tailwind CSS for Futuristic Design -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#06b6d4', // Cyan
                        secondary: '#3b82f6', // Blue
                        dark: '#020617', // Very Dark Slate
                        glass: 'rgba(255, 255, 255, 0.03)',
                    },
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        body {
            background-color: #020617;
            color: white;
            overflow-x: hidden;
        }
        /* Futuristic Background Mesh */
        .bg-mesh {
            position: fixed;
            top: 0; left: 0; width: 100vw; height: 100vh;
            background: 
                radial-gradient(circle at 15% 50%, rgba(6, 182, 212, 0.08), transparent 25%),
                radial-gradient(circle at 85% 30%, rgba(59, 130, 246, 0.08), transparent 25%);
            z-index: -1;
        }
        /* Glassmorphism Components */
        .glass-box {
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(6, 182, 212, 0.15);
            border-top: 1px solid rgba(6, 182, 212, 0.3);
            transition: all 0.4s ease;
        }
        .glass-box:hover {
            box-shadow: 0 0 30px rgba(6, 182, 212, 0.1);
            transform: translateY(-5px);
            border-color: rgba(6, 182, 212, 0.5);
        }
        /* Text Gradients */
        .text-glow {
            background: linear-gradient(to right, #22d3ee, #818cf8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 0 0 30px rgba(34, 211, 238, 0.3);
        }
        /* Timeline Image Glow */
        .timeline-img {
            box-shadow: 0 0 20px rgba(6, 182, 212, 0.2);
            border: 1px solid rgba(6, 182, 212, 0.3);
        }
    </style>
</head>
<body class="antialiased relative selection:bg-primary selection:text-dark">

    <div class="bg-mesh"></div>

  <!-- Navigation Bar -->
    <nav class="border-b border-white/5 relative z-10 bg-dark/50 backdrop-blur-md sticky top-0">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary to-secondary flex items-center justify-center font-black text-xl shadow-[0_0_15px_rgba(6,182,212,0.4)]">
                    +
                </div>
                <span class="text-2xl font-bold tracking-wide">FirstDoctor <span class="text-primary">AI</span></span>
            </div>
            
            <!-- Directing to New Demo QR Page -->
            <a href="demoqr.php" class="group relative px-6 py-2.5 bg-primary/10 text-primary font-bold rounded-full overflow-hidden border border-primary/30 transition hover:border-primary shadow-[0_0_15px_rgba(6,182,212,0.2)]">
                <div class="absolute inset-0 bg-primary/20 translate-y-full group-hover:translate-y-0 transition duration-300"></div>
                <span class="relative flex items-center gap-2">📱 Scan QR (Live Demo)</span>
            </a>
        </div>
    </nav>

    <!-- Hero Section -->
    <main class="flex flex-col items-center justify-center text-center px-4 pt-24 pb-20 relative z-10">
        <div class="inline-block px-5 py-2 rounded-full border border-primary/20 bg-primary/5 text-primary text-sm font-bold tracking-widest uppercase mb-8 shadow-sm">
            ⚡ Revolutionizing Clinical Decision Support
        </div>
        
        <h1 class="text-5xl md:text-7xl font-black tracking-tight mb-6 leading-tight max-w-5xl">
            Patients Can't Always Explain. <br>
            <span class="text-glow">Our AI Understands Perfectly.</span>
        </h1>
        
        <p class="text-lg md:text-xl text-slate-400 mb-12 max-w-3xl font-light leading-relaxed">
            Break the language barrier and eliminate doctor burnout. Patients speak in their native tongue to our AI before entering the cabin. By the time they sit down, the doctor has a <span class="text-white font-bold">expert-level clinical summary</span> ready on their screen.
        </p>
        
        <div class="flex flex-col sm:flex-row gap-5">
            <a href="auth/doctor_auth.php" class="bg-gradient-to-r from-primary to-secondary hover:from-cyan-400 hover:to-blue-500 text-white px-10 py-4 rounded-2xl font-bold text-lg transition shadow-[0_0_30px_rgba(6,182,212,0.3)] flex justify-center items-center gap-3">
                👨‍⚕️ Enter Doctor Portal
            </a>
            <a href="auth/hospital_auth.php" class="glass-box text-white px-10 py-4 rounded-2xl font-bold text-lg flex justify-center items-center gap-3">
                🏥 Hospital Access
            </a>
        </div>
    </main>

    <!-- The Futuristic Workflow Section -->
    <section class="max-w-7xl mx-auto w-full px-6 py-20 relative z-10 border-t border-white/5">
        <div class="text-center mb-20">
            <h2 class="text-3xl md:text-5xl font-bold text-white mb-4">How FirstDoctor AI Works</h2>
            <p class="text-slate-400 text-lg">A seamless, zero-friction experience for both patients and healthcare providers.</p>
        </div>

        <div class="space-y-24">
            
            <!-- Step 1: QR & Voice AI -->
            <div class="flex flex-col md:flex-row items-center gap-12">
                <div class="md:w-1/2">
                    <img src="assets/images/step1_qr.jpg" alt="Patient Scanning QR" class="w-full rounded-2xl timeline-img object-cover h-[300px]" onerror="this.src='https://via.placeholder.com/600x300/0f172a/06b6d4?text=Generating+AI+Image...+[step1_qr.jpg]'">
                </div>
                <div class="md:w-1/2">
                    <div class="text-primary font-bold text-xl mb-2">Step 01</div>
                    <h3 class="text-3xl font-bold mb-4">Smart Check-in & Native Voice AI</h3>
                    <p class="text-slate-400 text-lg leading-relaxed">
                        Patients scan a QR code in the waiting room and speak to our AI in their native language (Hindi, Marathi, Gujarati, etc.). They describe their pain exactly as they feel it, without anxiety or hesitation.
                    </p>
                </div>
            </div>

            <!-- Step 2: Reception Vitals -->
            <div class="flex flex-col md:flex-row-reverse items-center gap-12">
                <div class="md:w-1/2">
                    <img src="assets/images/step2_vitals.jpg" alt="Reception Vitals" class="w-full rounded-2xl timeline-img object-cover h-[300px]" onerror="this.src='https://via.placeholder.com/600x300/0f172a/06b6d4?text=Generating+AI+Image...+[step2_vitals.jpg]'">
                </div>
                <div class="md:w-1/2 text-left md:text-right">
                    <div class="text-primary font-bold text-xl mb-2">Step 02</div>
                    <h3 class="text-3xl font-bold mb-4">Instant Vitals Sync</h3>
                    <p class="text-slate-400 text-lg leading-relaxed">
                        The receptionist quickly checks BP and Sugar levels. This data is instantly synced to the patient's AI file, ready for the doctor's review before the patient even walks into the cabin.
                    </p>
                    <a href="auth/staff_auth.php" class="inline-block mt-4 text-sm text-primary hover:text-white underline">Login to Reception Desk →</a>
                </div>
            </div>

            <!-- Step 3: Doctor Screen -->
            <div class="flex flex-col md:flex-row items-center gap-12">
                <div class="md:w-1/2">
                    <img src="assets/images/step3_doctor.jpg" alt="Doctor AI Screen" class="w-full rounded-2xl timeline-img object-cover h-[300px]" onerror="this.src='https://via.placeholder.com/600x300/0f172a/06b6d4?text=Generating+AI+Image...+[step3_doctor.jpg]'">
                </div>
                <div class="md:w-1/2">
                    <div class="text-primary font-bold text-xl mb-2">Step 03</div>
                    <h3 class="text-3xl font-bold mb-4">100-Year Expert Clinical Summary</h3>
                    <p class="text-slate-400 text-lg leading-relaxed">
                        As the patient enters, the doctor is already looking at an ultra-precise, medically translated clinical summary. No time wasted on repetitive questions. The doctor focuses entirely on examining and healing the patient.
                    </p>
                </div>
            </div>

            <!-- Step 4: Auto Rx -->
            <div class="flex flex-col md:flex-row-reverse items-center gap-12">
                <div class="md:w-1/2">
                    <img src="assets/images/step4_rx.jpg" alt="AI Auto Prescription" class="w-full rounded-2xl timeline-img object-cover h-[300px]" onerror="this.src='https://via.placeholder.com/600x300/0f172a/06b6d4?text=Generating+AI+Image...+[step4_rx.jpg]'">
                </div>
                <div class="md:w-1/2 text-left md:text-right">
                    <div class="text-primary font-bold text-xl mb-2">Step 04</div>
                    <h3 class="text-3xl font-bold mb-4">One-Click Smart Prescriptions</h3>
                    <p class="text-slate-400 text-lg leading-relaxed">
                        The AI pre-generates a master-level treatment plan including Blood Tests, ECGs, and Medications based on the differential diagnosis. The doctor simply reviews, edits if needed, and clicks "Approve".
                    </p>
                </div>
            </div>

            <!-- Step 5: Pharmacy -->
            <div class="flex flex-col md:flex-row items-center gap-12">
                <div class="md:w-1/2">
                    <img src="assets/images/step5_pharmacy.jpg" alt="Pharmacy Screen" class="w-full rounded-2xl timeline-img object-cover h-[300px]" onerror="this.src='https://via.placeholder.com/600x300/0f172a/06b6d4?text=Generating+AI+Image...+[step5_pharmacy.jpg]'">
                </div>
                <div class="md:w-1/2">
                    <div class="text-primary font-bold text-xl mb-2">Step 05</div>
                    <h3 class="text-3xl font-bold mb-4">Seamless Pharmacy Handoff</h3>
                    <p class="text-slate-400 text-lg leading-relaxed">
                        The moment the doctor approves, the digital prescription is instantly flashed on the Pharmacy's screen. The pharmacist prepares the medicines, reducing patient wait time to zero.
                    </p>
                    <a href="auth/staff_auth.php" class="inline-block mt-4 text-sm text-primary hover:text-white underline">Login to Medical Store →</a>
                </div>
            </div>

        </div>
    </section>

    <!-- Footer -->
    <footer class="border-t border-white/10 mt-20 relative z-10 bg-dark/80 backdrop-blur-lg">
        <div class="max-w-7xl mx-auto px-6 py-8 flex flex-col md:flex-row justify-between items-center gap-4 text-sm text-slate-500">
            <div class="flex items-center gap-2 font-bold text-white">
                <div class="w-6 h-6 rounded bg-gradient-to-br from-primary to-secondary flex items-center justify-center text-xs">+</div>
                FirstDoctor AI
            </div>
            <p>&copy; <?= date('Y') ?> Developed for the Future of Healthcare. All rights reserved.</p>
        </div>
    </footer>

</body>
</html>