-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Sep 15, 2026 at 04:14 PM
-- Server version: 8.4.7
-- PHP Version: 8.2.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `firstdoctor`
--

-- --------------------------------------------------------

--
-- Table structure for table `doctors`
--

DROP TABLE IF EXISTS `doctors`;
CREATE TABLE IF NOT EXISTS `doctors` (
  `id` int NOT NULL AUTO_INCREMENT,
  `hospital_id` int NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `preferred_lang` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'en',
  `status` enum('pending','approved','rejected') COLLATE utf8mb4_unicode_ci DEFAULT 'approved',
  `specialization` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT 'MBBS, MD (General Medicine)',
  `working_days` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT 'Mon - Sat',
  `working_hours` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '10:00 AM - 06:00 PM',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `hospital_id` (`hospital_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `doctors`
--

INSERT INTO `doctors` (`id`, `hospital_id`, `name`, `email`, `password`, `preferred_lang`, `status`, `specialization`, `working_days`, `working_hours`) VALUES
(1, 1, 'Ishwar Rathod', 'dr.ramesh@cityhospital.com', '$2y$10$tpVD61XJLSeltd3t/5.qWucLdn2VETN7XJsuT8oIALgA2/UVZPR.K', 'en', 'approved', 'MBBS, MD (General Medicine)', 'Mon - Sat', '10:00 AM - 06:00 PM');

-- --------------------------------------------------------

--
-- Table structure for table `hospitals`
--

DROP TABLE IF EXISTS `hospitals`;
CREATE TABLE IF NOT EXISTS `hospitals` (
  `id` int NOT NULL AUTO_INCREMENT,
  `hospital_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `hospitals`
--

INSERT INTO `hospitals` (`id`, `hospital_name`, `email`, `password`) VALUES
(1, 'City Hospital', 'admin@cityhospital.com', '$2y$10$tpVD61XJLSeltd3t/5.qWucLdn2VETN7XJsuT8oIALgA2/UVZPR.K');

-- --------------------------------------------------------

--
-- Table structure for table `hospital_staff`
--

DROP TABLE IF EXISTS `hospital_staff`;
CREATE TABLE IF NOT EXISTS `hospital_staff` (
  `id` int NOT NULL AUTO_INCREMENT,
  `hospital_id` int NOT NULL,
  `role` enum('reception','pharmacy') COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `hospital_id` (`hospital_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `hospital_staff`
--

INSERT INTO `hospital_staff` (`id`, `hospital_id`, `role`, `name`, `email`, `password`) VALUES
(1, 1, 'reception', 'Priya (Reception)', 'reception@cityhospital.com', '$2y$10$tpVD61XJLSeltd3t/5.qWucLdn2VETN7XJsuT8oIALgA2/UVZPR.K'),
(2, 1, 'pharmacy', 'Rahul (Medical)', 'medical@cityhospital.com', '$2y$10$tpVD61XJLSeltd3t/5.qWucLdn2VETN7XJsuT8oIALgA2/UVZPR.K');

-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

DROP TABLE IF EXISTS `patients`;
CREATE TABLE IF NOT EXISTS `patients` (
  `id` int NOT NULL AUTO_INCREMENT,
  `hospital_id` int NOT NULL,
  `doctor_id` int DEFAULT NULL,
  `patient_user_id` int DEFAULT NULL,
  `token_number` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `symptoms_raw` text COLLATE utf8mb4_unicode_ci,
  `ai_summary` text COLLATE utf8mb4_unicode_ci,
  `ai_prescription` text COLLATE utf8mb4_unicode_ci,
  `urgency_level` int DEFAULT '1',
  `vitals_bp` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vitals_sugar` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('waiting','vitals_done','completed') COLLATE utf8mb4_unicode_ci DEFAULT 'waiting',
  `pharmacy_status` enum('pending','sent','dispensed') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `hospital_id` (`hospital_id`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `patients`
--

INSERT INTO `patients` (`id`, `hospital_id`, `doctor_id`, `patient_user_id`, `token_number`, `name`, `contact`, `symptoms_raw`, `ai_summary`, `ai_prescription`, `urgency_level`, `vitals_bp`, `vitals_sugar`, `status`, `pharmacy_status`, `created_at`) VALUES
(1, 1, 1, NULL, 'T-1001', 'Rahul Kumar', '9876543210', 'Mujhe 2 din se bukhar aur sardi hai.', 'Patient has mild fever and cold for 2 days. Needs evaluation.', 'Rx:\r\n1. Paracetamol 650mg - 1 TDS\r\n2. Cetirizine 10mg - 1 at night', 2, '', '', 'completed', 'sent', '2026-09-14 08:03:27'),
(2, 1, 1, 1, 'T-8255', 'ISHWAR RAMESH RATHOD', '7798503504', 'mujhe 10 din se bhot jyada fever he and mujhe na nind nahi arahi he 3 dins e thodi khasi he and gale me kharash he and meri back bhot jyada pain ho rhi he 8 din se uppar back stiffness he and heart mme bhot jyada tightness araha he ', 'Needs Evaluation', 'Pending Dr. Review', 1, '', '', 'completed', 'sent', '2026-09-14 08:08:04'),
(3, 1, 1, 1, 'T-2969', 'ISHWAR RAMESH RATHOD', '7798503504', '\"mujhe 10 din se bhot jyada fever he and mujhe na nind nahi arahi he 3 dins e thodi khasi he and gale me kharash he and meri back bhot jyada pain ho rhi he 8 din se uppar back stiffness he and heart mme bhot jyada tightness araha he \"', 'Needs Evaluation - AI parsing failed.', 'Pending Dr. Review - Please manually prescribe.', 1, '120', '95', 'completed', 'sent', '2026-09-14 08:22:08'),
(4, 1, 1, 1, 'T-1599', 'ISHWAR RAMESH RATHOD', '7798503504', 'mujhe 10 din se bhot jyada fever he and mujhe na nind nahi arahi he 3 dins e thodi khasi he and gale me kharash he and meri back bhot jyada pain ho rhi he 8 din se uppar back stiffness he and heart mme bhot jyada tightness araha he', 'Needs Evaluation', 'Pending Dr. Review', 1, '', '', 'completed', 'sent', '2026-09-14 08:24:58'),
(5, 1, 1, 1, 'T-7303', 'ISHWAR RAMESH RATHOD', '7798503504', 'mujhe 10 din se bhot jyada fever he and mujhe na nind nahi arahi he 3 dins e thodi khasi he and gale me kharash he and meri back bhot jyada pain ho rhi he 8 din se uppar back stiffness he and heart mme bhot jyada tightness araha he', 'Needs Evaluation', 'Pending Dr. Review', 1, '', '', 'completed', 'sent', '2026-09-14 08:31:18'),
(6, 1, 1, 1, 'T-8741', 'ISHWAR RAMESH RATHOD', '7798503504', 'mujhe 10 din se bhot jyada fever he and mujhe na nind nahi arahi he 3 dins e thodi khasi he and gale me kharash he and meri back bhot jyada pain ho rhi he 8 din se uppar back stiffness he and heart mme bhot jyada tightness araha he\r\n\r\n', 'Patient presents with a 10-day history of persistent high-grade fever, severe upper back pain and stiffness lasting 8 days, 3 days of mild cough and sore throat, associated insomnia, and acute, severe chest/heart tightness. The combination of prolonged fever with acute chest tightness and upper back stiffness represents a critical presentation. \n\nTop Suspected Diagnoses:\n1. Acute Myopericarditis or Viral Myocarditis secondary to systemic viral/bacterial infection.\n2. Acute Coronary Syndrome (ACS) triggered by systemic inflammation/fever stress.\n3. Severe Lower Respiratory Tract Infection / Pneumonia with pleuritic chest pain and secondary back pain.\n4. Systemic Infectious Process (e.g., Enteric Fever/Typhoid, Severe Dengue, or Infective Endocarditis).\n5. Infectious Spinal Pathology (e.g., Spondylodiscitis / Spinal Epidural Abscess) given upper back stiffness and fever.', '[INVESTIGATIONS]\r\n1. Emergency 12-lead Electrocardiogram (ECG) immediately.\r\n2. Cardiac Biomarkers: High-Sensitivity Cardiac Troponin-I / Troponin-T, CPK-MB.\r\n3. 2D Echocardiogram.\r\n4. Urgent Chest X-Ray (PA view) or CT Chest.\r\n5. Complete Blood Count (CBC), C-Reactive Protein (CRP), ESR.\r\n6. Blood Cultures (2 sets), Typhidot/Widal test, Dengue NS1/IgM/IgG.\r\n7. Comprehensive Metabolic Panel (Renal Function Test, Liver Function Test, Serum Electrolytes).\r\n\r\n[MEDICATIONS]\r\n1. Tab. Paracetamol 650 mg - 1 Tablet TDS (after food) for fever control.\r\n2. Tab. Pantoprazole 40 mg - 1 Tablet OD (30 mins before food) for gastric protection.\r\n*Note: Oral polypharmacy should be deferred until immediate emergency room (ER) stabilization, ECG clearance, and ruling out underlying cardiac or severe systemic pathology.\r\n\r\n[PRECAUTIONS & LIFESTYLE]\r\n1. EMERGENCY WARNING: Seek immediate medical care at the nearest Emergency Department / ICU. Do not delay.\r\n2. Strict bed rest; avoid any physical exertion, heavy lifting, or sudden movements.\r\n3. Do not take self-prescribed NSAIDs (e.g., Ibuprofen, Diclofenac) or aspirin without an ECG and cardiac workup.\r\n4. Monitor vital signs (Heart rate, Blood Pressure, SpO2, Temperature) continuously.\r\n\r\n[FOLLOW-UP]\r\nImmediate Emergency Room evaluation required today. Re-evaluate within 12 to 24 hours after ER triage and stabilization.', 1, '', '', 'completed', 'sent', '2026-09-14 08:35:35'),
(7, 1, 1, 1, 'T-1835', 'ISHWAR RAMESH RATHOD', '7798503504', NULL, NULL, 'Pending Dr. Review', 1, '', '', 'completed', 'sent', '2026-09-14 08:40:13'),
(8, 1, 1, 1, 'T-8927', 'ISHWAR RAMESH RATHOD', '7798503504', NULL, NULL, 'Pending Dr. Review', 1, '', '', 'completed', 'sent', '2026-09-14 08:50:23'),
(9, 1, 1, 2, 'T-4117', 'Neha', '8767145379', 'mujhe 10 din se bhot jyada fever he and mujhe na nind nahi arahi he 3 dins e thodi khasi he and gale me kharash he and meri back bhot jyada pain ho rhi he 8 din se uppar back stiffness he and heart mme bhot jyada tightness araha he ', 'API ERROR: models/gemini-1.5-flash is not found for API version v1beta, or is not supported for generateContent. Call ModelService.ListModels to see the list of available models and their supported methods.', 'Please check your Gemini API Key.', 1, '120', '95', 'completed', 'sent', '2026-09-14 14:51:52'),
(10, 1, 1, 3, 'T-6702', 'Nandini Pawar', '7798504505', 'mujhe 10 din se bhot jyada fever he and mujhe na nind nahi arahi he 3 dins e thodi khasi he and gale me kharash he and meri back bhot jyada pain ho rhi he 8 din se uppar back stiffness he and heart mme bhot jyada tightness araha he', 'The patient presents with a complex, potentially life-threatening symptom profile characterized by a high-grade fever lasting 10 days, severe upper back pain and stiffness for 8 days, insomnia, mild cough with throat irritation, and acute severe chest tightness (\'heart tightness\'). The combination of prolonged pyrexia of unknown origin (PUO), neck/upper back stiffness, and acute cardiac symptoms necessitates immediate emergency clinical evaluation. Primary suspected diagnoses include: 1. Acute Myopericarditis or Infective Endocarditis (secondary to viral or bacterial infection); 2. Acute Coronary Syndrome (ACS) / Myocardial Infarction triggered by systemic inflammation or sepsis; 3. Severe Pneumonia with Pleurisy or Parapneumonic Effusion; 4. Spinal Epidural Abscess or Spondylodiscitis with meningeal irritation.', '[INVESTIGATIONS]\r\n- Immediate 12-Lead Electrocardiogram (ECG)\r\n- Stat Cardiac Biomarkers (Troponin-I, Troponin-T, CK-MB)\r\n- Bedside 2D Echocardiography\r\n- Chest X-Ray (PA View)\r\n- Complete Blood Count (CBC) with Differential, CRP, ESR\r\n- Blood Cultures (2 sets from different sites)\r\n- Serum Electrolytes, Renal Function Tests (RFT), Liver Function Tests (LFT)\r\n- D-Dimer\r\n- MRI Thoracic/Cervical Spine (if epidural abscess or discitis is clinically suspected)\r\n\r\n[MEDICATIONS]\r\n*Immediate Emergency Department transfer is required. Initial supportive pharmacotherapy under medical supervision:* \r\n- Tab. Paracetamol 650 mg | 1 tablet TDS (after food) for fever control (Maximum 3g/day)\r\n- Tab. Pantoprazole 40 mg | 1 tablet OD (30 mins before breakfast) for gastric protection\r\n- Empiric IV Antibiotic therapy and emergency anti-ischemic/anti-inflammatory regimens to be initiated immediately upon ER triage.\r\n\r\n[PRECAUTIONS & LIFESTYLE]\r\n- IMMEDIATE EMERGENCY TRANSPORT: Proceed to the nearest hospital Emergency Room immediately via ambulance; do not drive.\r\n- Strict bed rest; absolute restriction of any physical exertion.\r\n- Avoid taking over-the-counter pain relievers (like Ibuprofen/Diclofenac) prior to cardiac and renal clearance.\r\n- Continuous monitoring of vital signs (SpO2, Pulse Rate, Blood Pressure, and Temperature).\r\n\r\n[FOLLOW-UP]\r\n- Immediate (0 Hours): Emergency Room admission.\r\n- Urgent clinical re-assessment within 2 to 4 hours based on stat ECG, Troponin, and imaging results.', 5, '', '', 'completed', 'sent', '2026-09-14 15:03:45'),
(11, 1, 1, 1, 'T-1819', 'ISHWAR RAMESH RATHOD', '7798503504', 'mujhe 5 din se pet me dukh rha he kabhi rat me thoda dard ho rha he aur kabhi din me jyad ho rha he', 'Patient reports a 5-day history of persistent, fluctuating abdominal pain with diurnal variations—noting milder pain during the night and heightened intensity during the day. Primary differential diagnoses include: 1. Peptic Ulcer Disease / Acute Gastritis (common given the temporal variation and 5-day duration), 2. Symptomatic Cholelithiasis / Biliary Colic, 3. Functional Dyspepsia or Irritable Bowel Syndrome (IBS), and 4. Subacute Gastroenteritis or Intestinal Dysbiosis.', '[INVESTIGATIONS]\r\n1. Complete Blood Count (CBC) with Differential\r\n2. C-Reactive Protein (CRP)\r\n3. Liver Function Tests (LFT) & Serum Amylase/Lipase\r\n4. Ultrasound (USG) Whole Abdomen and Pelvis\r\n5. Urine Routine & Microscopy\r\n\r\n[MEDICATIONS]\r\n1. Tab. Pantoprazole 40 mg - 1 Tablet, Once Daily (OD), 30 minutes before breakfast x 10 days\r\n2. Tab. Dicyclomine 10 mg + Paracetamol 325 mg - 1 Tablet, Twice Daily (BD) as needed for severe pain (SOS), post-meals x 5 days\r\n3. Syp. Magaldrate + Simethicone (10 mL) - 2 teaspoons, Three Times Daily (TDS), 1 hour after meals and at bedtime x 7 days\r\n\r\n[PRECAUTIONS & LIFESTYLE]\r\n1. Consume a bland, soft diet (e.g., rice gruel, toast, oats). Avoid spicy, oily, fried, or highly acidic foods.\r\n2. Avoid coffee, tea, carbonated beverages, alcohol, and smoking.\r\n3. Do not take non-steroidal anti-inflammatory drugs (NSAIDs like Ibuprofen/Naproxen) without medical supervision as they can worsen gastric mucosal erosion.\r\n4. Maintain adequate hydration with clear fluids.\r\n5. Seek immediate emergency evaluation if you experience red-flag symptoms: persistent vomiting, high-grade fever, black/tarry stools, blood in vomit, or sudden localized severe pain (e.g., right lower quadrant).\r\n\r\n[FOLLOW-UP]\r\nRe-evaluate in 3 to 5 days with Ultrasound Abdomen and routine blood lab reports, or earlier if pain escalates or red-flag symptoms emerge.', 3, '', '', 'completed', 'sent', '2026-09-15 15:06:22');

-- --------------------------------------------------------

--
-- Table structure for table `patient_users`
--

DROP TABLE IF EXISTS `patient_users`;
CREATE TABLE IF NOT EXISTS `patient_users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `phone` (`phone`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `patient_users`
--

INSERT INTO `patient_users` (`id`, `phone`, `password`, `name`) VALUES
(1, '7798503504', '$2y$10$dOGFYubkvhqHwAXdV5G.l.HwWA2kPTpdix/n6fJI9SMhO.HCgNKjS', 'ISHWAR RAMESH RATHOD'),
(2, '8767145379', '$2y$10$lZAbaWWExWDWmG5B1.McsOfeNIl.qMgGDCRGJOlQg1IXsnWBRfexG', 'Neha'),
(3, '7798504505', '$2y$10$DMQ0XdW4SkIaKOhwep6s9OZyV0x16DnNWh0XeJk8wsqmH0WF8w8em', 'Nandini Pawar');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
