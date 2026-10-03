-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 03, 2026 at 02:46 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bestcare`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `appointment_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `appointment_date` date NOT NULL,
  `appointment_time` time NOT NULL,
  `status` enum('Pending','Approved','Completed','Cancelled') NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`appointment_id`, `patient_id`, `doctor_id`, `appointment_date`, `appointment_time`, `status`, `created_at`) VALUES
(10, 5, 6, '2026-08-02', '08:30:00', 'Completed', '2026-08-02 00:52:09'),
(11, 5, 6, '2026-08-07', '17:14:00', 'Cancelled', '2026-08-02 17:12:11'),
(12, 5, 6, '2026-08-08', '10:20:00', 'Cancelled', '2026-08-02 17:16:25'),
(13, 5, 6, '2026-08-07', '16:05:00', 'Cancelled', '2026-08-06 15:54:21'),
(14, 5, 6, '2026-08-08', '10:15:00', 'Cancelled', '2026-08-06 17:14:42'),
(15, 5, 6, '2026-08-09', '18:05:00', 'Approved', '2026-08-08 20:04:18'),
(16, 5, 6, '2026-08-10', '11:28:00', 'Completed', '2026-08-10 11:27:16'),
(17, 6, 6, '2026-09-09', '15:00:00', 'Completed', '2026-09-09 14:57:29'),
(18, 6, 6, '2026-09-10', '12:40:00', 'Completed', '2026-09-10 00:40:38'),
(19, 6, 7, '2026-09-15', '12:48:00', 'Approved', '2026-09-10 00:48:15');

-- --------------------------------------------------------

--
-- Table structure for table `bills`
--

CREATE TABLE `bills` (
  `bill_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `appointment_id` int(11) NOT NULL,
  `consultation_fee` decimal(10,2) NOT NULL,
  `laboratory_fee` decimal(10,2) NOT NULL,
  `medicine_fee` decimal(10,2) NOT NULL,
  `other_fee` decimal(10,2) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `payment_status` enum('Pending','Paid') NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bills`
--

INSERT INTO `bills` (`bill_id`, `patient_id`, `appointment_id`, `consultation_fee`, `laboratory_fee`, `medicine_fee`, `other_fee`, `total_amount`, `payment_status`, `created_at`) VALUES
(2, 5, 10, 1500.00, 0.00, 750.00, 650.00, 2900.00, 'Paid', '2026-08-02 05:30:50');

-- --------------------------------------------------------

--
-- Table structure for table `contact_queries`
--

CREATE TABLE `contact_queries` (
  `contact_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `subject` varchar(150) NOT NULL,
  `message` text NOT NULL,
  `status` enum('Pending','Replied') NOT NULL DEFAULT 'Pending',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_queries`
--

INSERT INTO `contact_queries` (`contact_id`, `name`, `email`, `subject`, `message`, `status`, `created_at`) VALUES
(1, 'test', 'test@gmail.com', 'test', 'test submit a general query works', 'Pending', '2026-07-23 18:24:44'),
(2, 'test test', 'test2@gmail.com', 'test2', 'test 2 Submit a General Query', 'Pending', '2026-07-23 18:25:44');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `department_id` int(11) NOT NULL,
  `department_name` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`department_id`, `department_name`, `description`, `status`) VALUES
(1, 'Cardiology', 'Care and treatment for heart and cardiovascular conditions.', 'Active'),
(2, 'Neurology', 'Assessment and treatment of nervous-system conditions.', 'Active'),
(3, 'Pediatrics', 'Medical care and health support for infants and children.', 'Active'),
(4, 'Orthopedics', 'Treatment for bones, joints, muscles, and movement conditions', 'Active'),
(5, 'ENT', 'Diagnosis and treatment for ear, nose, and throat conditions.', 'Active'),
(6, 'Gynecology', 'Specialized healthcare services and support for women.', 'Active');

-- --------------------------------------------------------

--
-- Table structure for table `doctors`
--

CREATE TABLE `doctors` (
  `doctor_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `specialization` varchar(100) NOT NULL,
  `availability` varchar(100) NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `doctors`
--

INSERT INTO `doctors` (`doctor_id`, `user_id`, `department_id`, `first_name`, `last_name`, `phone`, `specialization`, `availability`, `created_at`) VALUES
(5, 10, 1, 'Amila', 'Watawaththa', '0775858529', 'Cardiothoracic', 'Monday-Friday 8AM-5PM', '2026-08-02 00:15:48'),
(6, 12, 5, 'Chaminda', 'Uggaldeniya', '0775858526', 'ENT Surgeon', 'Saturday-Monday 8AM-5PM', '2026-08-02 00:50:01'),
(7, 13, 1, 'Ajith', 'Karunarathne', '0775858525', 'Cardiothoracic Surgeon', 'Saturday-Monday 8AM-5PM', '2026-08-02 04:50:18');

-- --------------------------------------------------------

--
-- Table structure for table `laboratory_reports`
--

CREATE TABLE `laboratory_reports` (
  `report_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `test_name` varchar(100) NOT NULL,
  `report_file` varchar(255) NOT NULL,
  `status` enum('Pending','Completed') NOT NULL,
  `report_date` date NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `laboratory_reports`
--

INSERT INTO `laboratory_reports` (`report_id`, `patient_id`, `doctor_id`, `test_name`, `report_file`, `status`, `report_date`, `created_at`) VALUES
(1, 5, 6, 'test', 'lab_5_28e6257a81ba086b79b8581c.pdf', 'Completed', '2026-08-01', '2026-08-02 19:59:20');

-- --------------------------------------------------------

--
-- Table structure for table `medical_records`
--

CREATE TABLE `medical_records` (
  `record_id` int(11) NOT NULL,
  `appointment_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `diagnosis` text NOT NULL,
  `treatment` text NOT NULL,
  `notes` text NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `medical_records`
--

INSERT INTO `medical_records` (`record_id`, `appointment_id`, `patient_id`, `doctor_id`, `diagnosis`, `treatment`, `notes`, `created_at`) VALUES
(2, 10, 5, 6, 'Diagnosis: Acute Otitis Media (Right Ear) — ICD-10: H66.001', 'Amoxicillin/Clavulanate (Augmentin) 875mg/125mg orally twice daily for 7 days\r\nIbuprofen 400mg orally every 6 hours as needed for pain\r\nOtic analgesic drops (Auralgan) 2–3 drops as needed', 'Patient presented with severe right ear pain and mild fever ($100.8^\\circ\\text{F}$) for 2 days. Otoscopic examination shows a bulging, erythematous tympanic membrane with reduced mobility and visible purulent effusion. No mastoid tenderness or outer ear canal swelling observed. Advised rest, increased fluid intake, and follow-up in 10 days if symptoms do not improve.', '2026-08-02 19:14:04'),
(3, 17, 6, 6, 'dydwdwidw\r\nwdwdjwnd\r\nhdbwd', 'dwdbwndw\r\nwdbwjnd\r\nhwbdjwd', 'wdywhbd ywdgwbd ywd yg', '2026-09-09 15:00:21'),
(4, 16, 5, 6, 'Acute allergic rhinitis with nasal congestion and mild sinus inflammation.', 'Saline nasal spray — 2–3 times daily\r\nIntranasal corticosteroid spray as prescribed\r\nOral antihistamine as prescribed for allergy symptoms\r\nAdequate fluids and rest\r\nAvoid known allergens such as dust, smoke, and strong fragrances\r\nFollow-up with ENT specialist if symptoms persist or worsen', 'Acute allergic rhinitis with nasal congestion and mild sinus inflammation.', '2026-09-10 09:58:32');

-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

CREATE TABLE `patients` (
  `patient_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `gender` enum('Male','Female','Other') NOT NULL,
  `dob` date NOT NULL,
  `phone` varchar(15) NOT NULL,
  `email` varchar(100) NOT NULL,
  `address` text NOT NULL,
  `blood_group` varchar(5) NOT NULL,
  `emergency_contact` varchar(15) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patients`
--

INSERT INTO `patients` (`patient_id`, `user_id`, `first_name`, `last_name`, `gender`, `dob`, `phone`, `email`, `address`, `blood_group`, `emergency_contact`, `created_at`) VALUES
(5, 11, 'Vidun', 'Wathsuka', 'Male', '2026-03-19', '0775857777', 'vidu19@gmail.com', '102/16 Akurassa Mathara', 'A+', '0765858997', '2026-08-02 00:28:10'),
(6, 14, 'Nuwan', 'Thennakon', 'Male', '1998-02-12', '0775857000', 'nuwa7@gmail.com', '50/4 Beliatta Thangalla', 'B-', '0765858911', '2026-09-09 14:55:42');

-- --------------------------------------------------------

--
-- Table structure for table `patient_queries`
--

CREATE TABLE `patient_queries` (
  `query_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `doctor_id` int(11) DEFAULT NULL,
  `subject` varchar(150) NOT NULL,
  `message` text NOT NULL,
  `reply` text DEFAULT NULL,
  `status` enum('Pending','Answered','Closed') NOT NULL DEFAULT 'Pending',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `replied_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patient_queries`
--

INSERT INTO `patient_queries` (`query_id`, `patient_id`, `doctor_id`, `subject`, `message`, `reply`, `status`, `created_at`, `replied_at`) VALUES
(4, 5, 6, 'test', 'Sample test massage', 'Sample reply', 'Answered', '2026-08-02 20:43:21', NULL),
(5, 6, 6, 'test', 'thebhb h bhbhb', 'efefefhjenfe uifefe i uhuehfi e', 'Answered', '2026-09-09 15:02:00', NULL),
(6, 6, 7, 'test', 'vfbhfbfebfef eveevevv eev', 'bhbhbwf hwbdwbdh', 'Answered', '2026-09-09 21:02:57', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `prescriptions`
--

CREATE TABLE `prescriptions` (
  `prescription_id` int(11) NOT NULL,
  `record_id` int(11) NOT NULL,
  `medicine` varchar(150) NOT NULL,
  `dosage` varchar(100) NOT NULL,
  `duration` varchar(100) NOT NULL,
  `instructions` text NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `prescriptions`
--

INSERT INTO `prescriptions` (`prescription_id`, `record_id`, `medicine`, `dosage`, `duration`, `instructions`, `created_at`) VALUES
(1, 2, 'Amoxicillin', '875 mg', '7 days', 'Twice daily (every 12 hours) with meals', '2026-08-02 19:36:04');

-- --------------------------------------------------------

--
-- Table structure for table `resources`
--

CREATE TABLE `resources` (
  `resource_id` int(11) NOT NULL,
  `resource_name` varchar(100) NOT NULL,
  `resource_type` enum('Room','Bed') NOT NULL,
  `status` enum('Available','Occupied') NOT NULL,
  `quantity` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `resources`
--

INSERT INTO `resources` (`resource_id`, `resource_name`, `resource_type`, `status`, `quantity`, `created_at`) VALUES
(1, 'ICU Bed', 'Bed', 'Available', 10, '2026-08-01 23:49:00');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `role_id` int(11) NOT NULL,
  `role_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`role_id`, `role_name`) VALUES
(1, 'Administrator'),
(2, 'Doctor'),
(3, 'Patient');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `role_id`, `username`, `email`, `password`, `status`, `created_at`) VALUES
(1, 1, 'Admin01', 'bcadmin01@gmail.com', '$2y$10$f4w0z2X9kcm53Rn7CKdXk.LH5QInuv8wijN3M5ivfFNbGJGQmO.0u', 'Active', '2026-07-27 02:57:39'),
(10, 2, 'Amila77', 'amila77@gmail.com', '$2y$10$uuO4bA54yJO1yQnxP5sddOEUH6Exs4GQTKTzhBYVZ7M0wwXd6a2Nm', 'Active', '2026-08-02 00:15:48'),
(11, 3, 'vidu19', 'vidu19@gmail.com', '$2y$10$EC4Acnp//oHiAURzy.OwnuIJGvFiOTB5.ul3LxOT9DbX9dR.76NL6', 'Active', '2026-08-02 00:28:10'),
(12, 2, 'Chaminda02', 'chaminda02@gmail.com', '$2y$10$Ndrb0r2./oe9Y9w3a6eubuBmERn4s1mrcSs5tUDqREuT08Ar8q9JO', 'Active', '2026-08-02 00:50:01'),
(13, 2, 'Ajith67', 'ajith67@gmail.com', '$2y$10$h2gXxFa0dym1qBbkvr14I.TBB0sN2VnlJv7SJwb67MA9RnN1f4KT2', 'Active', '2026-08-02 04:50:18'),
(14, 3, 'Nuwa7', 'nuwa7@gmail.com', '$2y$10$lV1nP06kHLHVy5BUlwD5.efLcKpgOhQxoJU2k96j0Z4ycp5lqPhIi', 'Active', '2026-09-09 14:55:42');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`appointment_id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `doctor_id` (`doctor_id`);

--
-- Indexes for table `bills`
--
ALTER TABLE `bills`
  ADD PRIMARY KEY (`bill_id`);

--
-- Indexes for table `contact_queries`
--
ALTER TABLE `contact_queries`
  ADD PRIMARY KEY (`contact_id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`department_id`);

--
-- Indexes for table `doctors`
--
ALTER TABLE `doctors`
  ADD PRIMARY KEY (`doctor_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `department_id` (`department_id`);

--
-- Indexes for table `laboratory_reports`
--
ALTER TABLE `laboratory_reports`
  ADD PRIMARY KEY (`report_id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `doctor_id` (`doctor_id`);

--
-- Indexes for table `medical_records`
--
ALTER TABLE `medical_records`
  ADD PRIMARY KEY (`record_id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `appointment_id` (`appointment_id`),
  ADD KEY `doctor_id` (`doctor_id`);

--
-- Indexes for table `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`patient_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `patient_queries`
--
ALTER TABLE `patient_queries`
  ADD PRIMARY KEY (`query_id`),
  ADD KEY `fk_patient_queries_patient` (`patient_id`),
  ADD KEY `fk_patient_queries_doctor` (`doctor_id`);

--
-- Indexes for table `prescriptions`
--
ALTER TABLE `prescriptions`
  ADD PRIMARY KEY (`prescription_id`),
  ADD KEY `record_id` (`record_id`);

--
-- Indexes for table `resources`
--
ALTER TABLE `resources`
  ADD PRIMARY KEY (`resource_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`role_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `role_id` (`role_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `appointment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `bills`
--
ALTER TABLE `bills`
  MODIFY `bill_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `contact_queries`
--
ALTER TABLE `contact_queries`
  MODIFY `contact_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `department_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `doctors`
--
ALTER TABLE `doctors`
  MODIFY `doctor_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `laboratory_reports`
--
ALTER TABLE `laboratory_reports`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `medical_records`
--
ALTER TABLE `medical_records`
  MODIFY `record_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `patients`
--
ALTER TABLE `patients`
  MODIFY `patient_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `patient_queries`
--
ALTER TABLE `patient_queries`
  MODIFY `query_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `prescriptions`
--
ALTER TABLE `prescriptions`
  MODIFY `prescription_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `resources`
--
ALTER TABLE `resources`
  MODIFY `resource_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`),
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`doctor_id`);

--
-- Constraints for table `doctors`
--
ALTER TABLE `doctors`
  ADD CONSTRAINT `doctors_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `doctors_ibfk_2` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`);

--
-- Constraints for table `laboratory_reports`
--
ALTER TABLE `laboratory_reports`
  ADD CONSTRAINT `laboratory_reports_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`),
  ADD CONSTRAINT `laboratory_reports_ibfk_2` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`doctor_id`);

--
-- Constraints for table `medical_records`
--
ALTER TABLE `medical_records`
  ADD CONSTRAINT `medical_records_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`),
  ADD CONSTRAINT `medical_records_ibfk_2` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`appointment_id`),
  ADD CONSTRAINT `medical_records_ibfk_3` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`doctor_id`);

--
-- Constraints for table `patients`
--
ALTER TABLE `patients`
  ADD CONSTRAINT `patients_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `patient_queries`
--
ALTER TABLE `patient_queries`
  ADD CONSTRAINT `fk_patient_queries_doctor` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`doctor_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_patient_queries_patient` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `prescriptions`
--
ALTER TABLE `prescriptions`
  ADD CONSTRAINT `prescriptions_ibfk_1` FOREIGN KEY (`record_id`) REFERENCES `medical_records` (`record_id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
