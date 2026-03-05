-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: mysql
-- Generation Time: Mar 05, 2026 at 06:46 AM
-- Server version: 8.0.43
-- PHP Version: 8.2.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `swasthyatrack`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `admin_id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`admin_id`, `user_id`, `name`, `email`) VALUES
(1, 1, 'Super Admin', 'admin@swasthya.com'),
(2, 17, 'Admin2', 'fa@gaw.com');

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `appointment_id` int NOT NULL,
  `doctor_id` int NOT NULL,
  `patient_name` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `gender` enum('male','female','other') NOT NULL,
  `appointment_date` date NOT NULL,
  `message` text,
  `status` enum('Booked','Cancelled','Completed') DEFAULT 'Booked',
  `comment` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `user_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`appointment_id`, `doctor_id`, `patient_name`, `phone`, `email`, `address`, `gender`, `appointment_date`, `message`, `status`, `comment`, `created_at`, `updated_at`, `user_id`) VALUES
(54, 33, 'Unish Rajak', '9818164355', 'unishrajak0@gmail.com', 'Lainchaur,Kathmandu', 'male', '2026-02-09', 'dry cough and fever.', 'Completed', 'Medicine Name,Dosage,Frequency,Duration\r\nTab. Paracetamol (500mg),1 Tablet,Three times a day (After food),3 Days (If fever > 100∘F)\r\nSyp. Chlorpheniramine / Dextromethorphan,10 ml,Three times a day,5 Days (For dry cough)\r\nTab. Pantoprazole (40 mg),1 Tablet,Once a day (Before breakfast),5 Days\r\nTab. Vitamin C (500mg),1 Tablet,Once a day (Chewable),10 Days', '2026-02-09 05:25:32', '2026-02-09 05:36:11', 52),
(55, 37, 'Unish Rajak', '9818164355', 'unishrajak0@gmail.com', 'Lainchaur,Kathmandu', 'male', '2026-02-09', 'total knee replacement surgery.', 'Completed', 'Medicine Name,Dosage,Frequency,Duration\r\nTab. Etoricoxib (90mg),1 Tablet,Once a day (After food),10 Days (For pain)\r\nTab. Glucosamine & Chondroitin,1 Tablet,Twice a day,1 Month\r\nTab. Pantoprazole (40mg),1 Tablet,Before breakfast,10 Days\r\nTab. Calcium Carbonate (500mg),1 Tablet,Once a day,1 Month', '2026-02-09 05:27:07', '2026-02-09 05:40:05', 52),
(56, 38, 'Laxmi Devi Rajbanshi', '9803445522', 'laxmi.raj@yahoo.com', 'Biratnagar-12, Morang', 'male', '2026-02-11', 'Post-surgery follow-up', 'Cancelled', NULL, '2026-02-09 05:29:31', '2026-02-09 05:32:13', 52),
(57, 31, 'Nischal Basnet', '9851223344', 'nischal.b@example.com.np', 'Sanepa, Lalitpur', 'male', '2026-02-12', 'burn injury on the left forearm.', 'Cancelled', NULL, '2026-02-09 05:31:11', '2026-02-09 05:40:37', 52),
(58, 33, 'Unish Rajak', '9818164355', 'unishrajak0@gmail.com', 'Lainchaur,Kathmandu', 'male', '2026-02-19', 'burn injury on the left forearm.', 'Booked', NULL, '2026-02-09 05:40:59', '2026-02-09 05:40:59', 52),
(59, 33, 'sabin', '1111111111', 'sabin@ga.com', 'Lainchaur,Kathmandu', 'male', '2026-02-19', 'demo1', 'Booked', NULL, '2026-02-09 06:30:00', '2026-02-09 06:30:00', NULL),
(60, 33, 'subham', '2222222222', 'subham@guaokl.com', 'ktm', 'male', '2026-02-21', 'demo2', 'Completed', '', '2026-02-09 06:30:28', '2026-03-05 06:21:28', NULL),
(61, 33, 'aasish', '3333333333', 'aasish@heofp.com', 'Lainchaur,Kathmandu', 'male', '2026-02-18', 'demo3', 'Booked', NULL, '2026-02-09 06:32:12', '2026-02-09 06:32:12', NULL),
(67, 33, 'Unish Rajak', '9876543266', 'rajak.unish0@gmail.com', 'ktm', 'male', '2026-02-17', 'headache', 'Cancelled', NULL, '2026-02-17 09:19:30', '2026-03-04 11:34:24', 52);

-- --------------------------------------------------------

--
-- Table structure for table `beds`
--

CREATE TABLE `beds` (
  `bed_id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `patient_name` varchar(100) DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `contact` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `reason_for_admission` text,
  `reserved_date` date DEFAULT NULL,
  `type` enum('General','Semi-Private','Private') NOT NULL DEFAULT 'General',
  `status` enum('Empty','Reserved','Occupied','Out of Order') NOT NULL DEFAULT 'Empty',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `beds`
--

INSERT INTO `beds` (`bed_id`, `user_id`, `patient_name`, `gender`, `contact`, `email`, `address`, `reason_for_admission`, `reserved_date`, `type`, `status`, `created_at`, `updated_at`) VALUES
(21, 52, 'Unish Rajak', 'male', '9818164355', 'unishrajak0@gmail.com', 'Lainchaur,Kathmandu', 'knee surgery', '2026-02-19', 'Semi-Private', 'Reserved', '2025-10-10 14:20:10', '2026-02-09 06:44:29'),
(22, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Private', 'Empty', '2025-10-10 14:20:19', '2026-03-04 11:38:20'),
(23, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Private', 'Empty', '2025-10-10 16:32:21', '2026-02-09 06:45:29'),
(24, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Semi-Private', 'Empty', '2026-01-06 10:25:25', '2026-01-06 10:25:25'),
(25, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'General', 'Out of Order', '2026-02-09 06:39:49', '2026-02-09 06:42:05'),
(26, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'General', 'Empty', '2026-02-09 06:42:16', '2026-02-09 06:42:16');

-- --------------------------------------------------------

--
-- Table structure for table `doctors`
--

CREATE TABLE `doctors` (
  `doctor_id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `designation` varchar(100) DEFAULT NULL,
  `council_number` varchar(50) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `doctors`
--

INSERT INTO `doctors` (`doctor_id`, `user_id`, `name`, `email`, `department`, `designation`, `council_number`, `phone`, `photo`) VALUES
(22, 51, 'Suman Adhikari', 'suman.adhikari@meddemo.np', 'General Medicine', 'Medical Officer', 'NMC-45871', '9812344694', 'doctor_1770568122_6988b9ba5b7a6.png'),
(23, 53, 'Ramesh Koirala', 'ramesh.koirala@meddemo.np', 'Cardiology', 'Consultant', 'NMC-36290', '9801121357', 'doctor_1770609389_69895aeda5308.png'),
(24, 54, 'Nisha Shrestha', 'nisha.shrestha@meddemo.np', 'Pediatrics', 'Senior Consultant', 'NMC-40122', '9845565801', 'doctor_1770609347_69895ac3ea68b.png'),
(26, 56, 'Anil Poudel', 'anil.poudel@meddemo.np', 'Neurology', 'Senior Consultant', 'NMC-41209', '9819987799', 'doctor_1770609513_69895b69899d8.png'),
(27, 57, 'Sarita Gurung', 'sarita.gurung@meddemo.np', 'Gynecology', 'Consultant', 'NMC-37654', '9854432244', 'doctor_1770609644_69895beca669d.png'),
(30, 60, 'Meena Rai', 'meena.rai@meddemo.np', 'Psychiatry', 'Senior Consultant', 'NMC-42177', '9841121923', 'doctor_1770609900_69895cec74c83.png'),
(31, 61, 'Kavita Acharya', 'kavita.acharya@meddemo.np', 'Ophthalmology', 'Consultant', 'NMC-39564', '9847788014', 'doctor_1770610117_69895dc56534a.png'),
(32, 62, 'Pratima Karki', 'pratima.karki@meddemo.np', 'Pathology', 'Senior Consultant', 'NMC-42871', '9856676907', 'doctor_1770610352_69895eb0d25a3.png'),
(33, 63, 'Laxmi Neupane', 'laxmi.neupane@meddemo.np', 'Endocrinology', 'Consultant', 'NMC-40267', '9849899135', 'doctor_1770610523_69895f5bdfd57.png'),
(34, 64, 'Michael Thompson', 'michael.thompson@meddemo.np', 'Emergency Medicine', 'International Advisor', 'NMC-F-00121', '9818898026', 'doctor_1770610766_6989604e68cba.png'),
(35, 65, 'Li Wei', 'li.wei@meddemo.np', 'Radiology', 'Visiting Radiologist', 'NMC-F-00134', '9841121812', 'doctor_1770610908_698960dca992d.png'),
(36, 66, 'Sarah Johnson', 'sarah.johnson@meddemo.np', 'Oncology', 'Research Fellow', 'NMC-F-00149', '9804454685', 'doctor_1770611026_6989615285d37.png'),
(37, 67, 'Jun Park', 'jun.park@meddemo.np', 'Orthopedics', 'Visiting Surgeon', 'NMC-F-00178', '9816676024', 'doctor_1770611153_698961d10184f.png'),
(38, 68, 'Ananya Iyer', 'ananya.iyer@meddemo.np', 'Neurology', 'Neurologist', 'NMC-47309', '9866676905', 'doctor_1770611460_69896304d435d.png'),
(39, 69, 'Elena Petrova', 'elena.petrova@meddemo.np', 'Endocrinology', 'Academic Collaborator', 'NMC-F-00192', '9845565146', 'doctor_1770611548_6989635ca7084.png'),
(40, 70, 'Prakash Joshi', 'prakash.joshi@meddemo.np', 'Radiology', 'Medical Officer', 'NMC-45903', '9806676912', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `doctor_education`
--

CREATE TABLE `doctor_education` (
  `education_id` int NOT NULL,
  `doctor_id` int NOT NULL,
  `degree` varchar(150) NOT NULL,
  `institution` varchar(200) DEFAULT NULL,
  `year_of_completion` year DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `doctor_education`
--

INSERT INTO `doctor_education` (`education_id`, `doctor_id`, `degree`, `institution`, `year_of_completion`) VALUES
(39, 22, 'MBBS', 'Tribhuvan University, IOM', '2017'),
(48, 24, 'MBBS', 'Tribhuvan University, IOM', '2012'),
(49, 24, 'MD Pediatrics', 'Patan Academy of Health Sciences', '2017'),
(50, 23, 'MBBS', 'Kathmandu University School of Medical Sciences', '2014'),
(51, 23, 'MD Cardiology', 'B.P. Koirala Institute of Health Sciences', '2019'),
(52, 26, 'MBBS', 'Tribhuvan University, IOM', '2010'),
(53, 26, 'DM Neurology', 'All India Institute of Medical Sciences', '2016'),
(59, 30, 'MBBS', 'Tribhuvan University, IOM', '2009'),
(60, 30, 'MD Psychiatry', 'Patan Academy of Health Sciences', '2014'),
(61, 31, 'MBBS', 'Tribhuvan University, IOM', '2013'),
(62, 31, 'MS Ophthalmology', 'BP Koirala Lions Centre for Ophthalmic Studies', '2018'),
(63, 32, 'MBBS', 'Tribhuvan University, IOM', '2008'),
(64, 32, 'MD Pathology', 'B.P. Koirala Institute of Health Sciences', '2013'),
(65, 33, 'MBBS', 'Tribhuvan University, IOM', '2014'),
(66, 33, 'DM Endocrinology', 'Institute of Medicine, TU', '2020'),
(67, 34, 'MD', 'University of Toronto', '2008'),
(68, 34, 'Fellowship Emergency Med', 'Johns Hopkins University', '2012'),
(69, 35, 'MBBS', 'Peking University Health Science Center', '2010'),
(70, 35, 'MD Radiology', 'Fudan University', '2015'),
(71, 36, 'MD', 'University of California, San Diego', '2011'),
(72, 36, 'Fellowship Oncology', 'Mayo Clinic', '2016'),
(73, 37, 'MD', 'Seoul National University', '2010'),
(74, 37, 'MS Orthopedic Surgery', 'Yonsei University', '2015'),
(75, 38, 'MBBS', 'Christian Medical College, Vellore', '2012'),
(76, 38, 'DM Neurology', 'National Institute of Mental Health & Neurosciences', '2018'),
(77, 39, 'MD', 'First Moscow State Medical University', '2009'),
(78, 39, 'PhD Endocrinology', 'Karolinska Institute', '2016'),
(79, 27, 'MBBS', 'B.P. Koirala Institute of Health Sciences', '2014'),
(80, 27, 'MD Gynecology', 'Tribhuvan University, IOM', '2019'),
(81, 40, 'MBBS', 'Lumbini Medical College', '2019');

-- --------------------------------------------------------

--
-- Table structure for table `nurses`
--

CREATE TABLE `nurses` (
  `nurse_id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `nurses`
--

INSERT INTO `nurses` (`nurse_id`, `user_id`, `name`, `email`, `phone`) VALUES
(4, 71, 'Smriti Chaudhary', 'smritichaudhary@demo.com', '9841756379');

-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

CREATE TABLE `patients` (
  `patient_id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `patients`
--

INSERT INTO `patients` (`patient_id`, `user_id`, `name`, `email`, `gender`, `phone`) VALUES
(14, 52, 'Unish Rajak', 'unishrajak0@gmail.com', 'male', '(981) 816-4355');

-- --------------------------------------------------------

--
-- Table structure for table `prescriptions`
--

CREATE TABLE `prescriptions` (
  `prescription_id` int NOT NULL,
  `user_id` int NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `uploaded_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `prescriptions`
--

INSERT INTO `prescriptions` (`prescription_id`, `user_id`, `file_name`, `file_path`, `uploaded_at`) VALUES
(20, 52, 'Radiology Report', '../pdf/report_69897c1d638a29.84578809.pdf', '2026-02-09 06:18:05'),
(21, 52, 'Pathology Report', '../pdf/report_69897c2b90f376.59798966.pdf', '2026-02-09 06:18:19'),
(22, 52, 'Laboratory Report', '../pdf/report_69897c3d99d992.36783919.pdf', '2026-02-09 06:18:37'),
(23, 52, 'Operative Report', '../pdf/report_69897d38a2b1c1.29345379.pdf', '2026-02-09 06:22:48');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('patient','doctor','nurse','admin') NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `role`, `created_at`) VALUES
(1, 'admin', '$2y$10$mGCLBzQc.CqdJChZGMjE8eEnaxIFf6bygRsN8dZ0OwPnErXy8EIUi', 'admin', '2025-08-29 15:57:20'),
(17, 'admin2', '$2y$10$jXyH0emGHIUveR9wvUAS6e4f3vSxbdtBpoTxv6ahSs4p1FDB/pEeO', 'admin', '2025-09-01 10:29:07'),
(51, 'sumanadh', '$2y$10$qpEAHZa.9h6epp7zUEcaQeOrC.m.pV0Sxi5U.AiWp4rMSBm7H4LbG', 'doctor', '2026-02-08 16:28:42'),
(52, 'UnishRzk', '$2y$10$gkBxE/QyttFJg6c0AsyyNOW4hLP3gNnrP2/W3vaYp50kARFOabEXO', 'patient', '2026-02-08 16:29:48'),
(53, 'rkoirala', '$2y$10$lU1uP6dpSxUkD5Phfr1Z3eY1LepKlH68pKpv5ftxV5ffNDIO8YTYe', 'doctor', '2026-02-08 16:33:39'),
(54, 'nshrestha', '$2y$10$J9h6nyu1e2n692QJ6bPouOEja85zvs9yLZ1hp0PZWoc01.ueVQfm.', 'doctor', '2026-02-08 16:35:59'),
(56, 'apoudel', '$2y$10$fTH1ZgxrWdr3KWPAUM5V7e2QhLZn0Hj0UHJ11tsr2b9npY47mSUJi', 'doctor', '2026-02-09 03:58:33'),
(57, 'sgurung', '$2y$10$QEt7A7bMHkU0hwnnzlL5AesWWYzlZjYn2ws6aGE2dEYaf3OHItp6S', 'doctor', '2026-02-09 04:00:44'),
(60, 'mrai', '$2y$10$xoYNHY6KDlynILN/lmhB0ujQZZg4Dp33DxdGaWDNo3e.1cmI/dqZ.', 'doctor', '2026-02-09 04:05:00'),
(61, 'kacharya', '$2y$10$jup1ANY.3UcmZj2v8r9YJOJx6swijmQRzg0Ao6W0QVbOsNx7djb2a', 'doctor', '2026-02-09 04:08:37'),
(62, 'pkarki', '$2y$10$basmm/zqeGI4B2kc2Qy1jO../vwAMsLpqdCH0/9lMGuIYlotougSG', 'doctor', '2026-02-09 04:12:32'),
(63, 'lneupane', '$2y$10$a.UXvZTSUISXNWEJccIaeOxNXkyJZfJrxvXJ0NTzu1eWCmGHbAXqC', 'doctor', '2026-02-09 04:15:23'),
(64, 'mthompson', '$2y$10$.WTeTGw11gbtVz.41ojeYupWdJgJlE/Qwu8MThdezkeDSeIyZ0DhW', 'doctor', '2026-02-09 04:19:26'),
(65, 'liwei', '$2y$10$jOp7UQfBTcuol/P1w/erZuKf4TIsAN2nHDL5CrXy6ijUgqTfr3IMi', 'doctor', '2026-02-09 04:21:48'),
(66, 'sjohnson', '$2y$10$M4xBRZdwbK7Al6bFSljOaOmf3yMd/i4mXBWf6hdj8YwZyWu77sQ66', 'doctor', '2026-02-09 04:23:46'),
(67, 'jpark', '$2y$10$2tkYuUqYCiWbRbiGY88Ha.kPoLy5rwW2fmwR1hOkc03wm9.ewF1oe', 'doctor', '2026-02-09 04:25:53'),
(68, 'aiyer', '$2y$10$MOKgPfzCaOG9kGuodePctuZ6d7D3qKxoHe/GV1NCp.cHJ6J4xyvEW', 'doctor', '2026-02-09 04:31:00'),
(69, 'epetrova', '$2y$10$RcsW.4ta.SG8tPXGOcA.euWBy.c/SbGlh95vI4AWm77gm6HFGB10S', 'doctor', '2026-02-09 04:32:28'),
(70, 'pjoshi', '$2y$10$7qWlx.PfN/.vpQlGKBydtOzjQCUVOywYNle.Q3jCoVkC1sEoYpdBO', 'doctor', '2026-02-09 04:43:01'),
(71, 'schaudhary', '$2y$10$k97h73rTUbrr/lMpyNWrD.Xqf2yzn7srdb3/yBu4dGqz1u9a8Mcxa', 'nurse', '2026-02-09 06:16:29');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`appointment_id`),
  ADD KEY `doctor_id` (`doctor_id`),
  ADD KEY `appointments_user_fk` (`user_id`);

--
-- Indexes for table `beds`
--
ALTER TABLE `beds`
  ADD PRIMARY KEY (`bed_id`),
  ADD KEY `beds_user_fk` (`user_id`);

--
-- Indexes for table `doctors`
--
ALTER TABLE `doctors`
  ADD PRIMARY KEY (`doctor_id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `doctor_education`
--
ALTER TABLE `doctor_education`
  ADD PRIMARY KEY (`education_id`),
  ADD KEY `doctor_id` (`doctor_id`);

--
-- Indexes for table `nurses`
--
ALTER TABLE `nurses`
  ADD PRIMARY KEY (`nurse_id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`patient_id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `prescriptions`
--
ALTER TABLE `prescriptions`
  ADD PRIMARY KEY (`prescription_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `admin_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `appointment_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

--
-- AUTO_INCREMENT for table `beds`
--
ALTER TABLE `beds`
  MODIFY `bed_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `doctors`
--
ALTER TABLE `doctors`
  MODIFY `doctor_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `doctor_education`
--
ALTER TABLE `doctor_education`
  MODIFY `education_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=82;

--
-- AUTO_INCREMENT for table `nurses`
--
ALTER TABLE `nurses`
  MODIFY `nurse_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `patients`
--
ALTER TABLE `patients`
  MODIFY `patient_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `prescriptions`
--
ALTER TABLE `prescriptions`
  MODIFY `prescription_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=75;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admins`
--
ALTER TABLE `admins`
  ADD CONSTRAINT `admins_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`doctor_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `appointments_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `beds`
--
ALTER TABLE `beds`
  ADD CONSTRAINT `beds_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `doctors`
--
ALTER TABLE `doctors`
  ADD CONSTRAINT `doctors_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `doctor_education`
--
ALTER TABLE `doctor_education`
  ADD CONSTRAINT `doctor_education_ibfk_1` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`doctor_id`) ON DELETE CASCADE;

--
-- Constraints for table `nurses`
--
ALTER TABLE `nurses`
  ADD CONSTRAINT `nurses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `patients`
--
ALTER TABLE `patients`
  ADD CONSTRAINT `patients_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `prescriptions`
--
ALTER TABLE `prescriptions`
  ADD CONSTRAINT `prescriptions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
