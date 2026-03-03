-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 03, 2026 at 06:07 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `hostel-management-system`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `booking_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `status` enum('Pending','Approved','Rejected','CheckedIn','CheckedOut') DEFAULT 'Pending',
  `check_in_date` date DEFAULT NULL,
  `check_out_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`booking_id`, `student_id`, `room_id`, `status`, `check_in_date`, `check_out_date`) VALUES
(1, 1, 1, 'Approved', NULL, NULL),
(3, 2, 1, 'Approved', NULL, NULL),
(4, 6, 20, 'Approved', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `complaints`
--

CREATE TABLE `complaints` (
  `complaint_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `tutor_id` int(11) DEFAULT NULL,
  `description` text NOT NULL,
  `status` enum('Open','InProgress','Resolved') DEFAULT 'Open',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `complaints`
--

INSERT INTO `complaints` (`complaint_id`, `student_id`, `tutor_id`, `description`, `status`, `created_at`) VALUES
(1, 1, NULL, 'Fan isn\'t working for 2 days.', 'Open', '2026-03-02 19:28:35'),
(2, 6, 6, 'Fan is not working for 3 days.', 'Resolved', '2026-03-03 07:02:22'),
(3, 1, 6, 'bijoy 24 hall room number 2004 celling fan is not working for two day.', 'Open', '2026-03-03 13:21:59');

-- --------------------------------------------------------

--
-- Table structure for table `fee_structure`
--

CREATE TABLE `fee_structure` (
  `fee_id` int(11) NOT NULL,
  `hostel_id` int(11) DEFAULT NULL,
  `payment_type` varchar(50) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `academic_year` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hostels`
--

CREATE TABLE `hostels` (
  `hostel_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `total_rooms` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hostels`
--

INSERT INTO `hostels` (`hostel_id`, `name`, `location`, `total_rooms`) VALUES
(1, 'Bijoy-24 Hall', 'Main Campus', 100),
(2, 'Sher-E-Bangla Hall', 'Main Campus', 80),
(4, 'Kabi Sufia Kamal Hall', 'Main Campus', 70),
(6, 'Tapashi Rabeya Basri Hall', 'Main Campus', 80);

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `item_id` int(11) NOT NULL,
  `hostel_id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `quantity` int(11) DEFAULT 0,
  `item_condition` enum('Good','NeedsRepair','Damaged') DEFAULT 'Good'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `logs`
--

CREATE TABLE `logs` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(255) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `logs`
--

INSERT INTO `logs` (`log_id`, `user_id`, `action`, `timestamp`) VALUES
(1, 3, 'Applied for room booking', '2026-03-01 06:09:24'),
(2, 5, 'Submitted Hall Admission Form', '2026-03-02 18:49:54'),
(3, 2, 'Submitted payment: HallFee ৳3000 via bKash (TXN: 7EYV4KJQV)', '2026-03-02 19:26:40'),
(4, 2, 'Filed a complaint', '2026-03-02 19:28:35'),
(5, 5, 'Submitted payment: HallFee ৳3000 via bKash (TXN: 7EYV4KJWE)', '2026-03-03 07:01:36'),
(6, 5, 'Filed a complaint', '2026-03-03 07:02:22'),
(7, 6, 'Updated complaint #2 to InProgress', '2026-03-03 08:56:36'),
(8, 6, 'Updated complaint #2 to Resolved', '2026-03-03 08:56:49'),
(9, 2, 'Filed a complaint', '2026-03-03 13:22:00'),
(10, 6, 'Updated complaint #3 to InProgress', '2026-03-03 13:24:28'),
(11, 6, 'Updated complaint #3 to Resolved', '2026-03-03 13:24:35'),
(12, 6, 'Updated complaint #3 to Open', '2026-03-03 13:24:40'),
(13, 9, 'Verified payment ID: 1', '2026-03-03 15:10:46'),
(14, 9, 'Rejected payment ID: 2', '2026-03-03 15:11:21'),
(15, 2, 'Submitted payment: HallFee ৳3000 via bKash (TXN: 7EYV4KJRT)', '2026-03-03 15:22:52'),
(16, 9, 'Verified payment ID: 3', '2026-03-03 15:23:45'),
(17, 2, 'Submitted payment: HallFee ৳200 via bKash (TXN: 7EYV4KJNM)', '2026-03-03 16:12:49'),
(18, 9, 'Verified payment ID: 4', '2026-03-03 16:13:28'),
(19, 2, 'Submitted payment: HallFee ৳3000 via bKash (TXN: 7EYV4KJGT)', '2026-03-03 16:51:00'),
(20, 9, 'Verified payment ID: 5', '2026-03-03 16:51:29'),
(21, 9, 'Added new room — Hostel ID: 2, Floor: 2, Capacity: 3', '2026-03-03 17:05:53');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `payment_type` varchar(50) DEFAULT NULL,
  `month` varchar(20) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `payment_date` date NOT NULL,
  `status` enum('Pending','Completed','Failed') DEFAULT 'Pending',
  `verified_by` int(11) DEFAULT NULL,
  `receipt_no` varchar(50) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payment_id`, `student_id`, `payment_type`, `month`, `amount`, `payment_method`, `transaction_id`, `payment_date`, `status`, `verified_by`, `receipt_no`, `note`, `verified_at`) VALUES
(1, 1, 'HallFee', '2026-03', 3000.00, 'bKash', '7EYV4KJQV', '2026-03-03', '', 9, 'BUHMS-FECA0E5C', '', '2026-03-03 21:10:46'),
(2, 6, 'HallFee', '2026-03', 3000.00, 'bKash', '7EYV4KJWE', '2026-03-03', '', 9, 'BUHMS-8DA64490', '', '2026-03-03 21:11:20'),
(3, 1, 'HallFee', '2026-03', 3000.00, 'bKash', '7EYV4KJRT', '2026-03-03', '', 9, 'BUHMS-6E497E9B', '', '2026-03-03 21:23:45'),
(4, 1, 'HallFee', '2026-03', 200.00, 'bKash', '7EYV4KJNM', '2026-03-03', '', 9, 'BUHMS-5B54CFAA', '', '2026-03-03 22:13:28'),
(5, 1, 'HallFee', '2026-03', 3000.00, 'bKash', '7EYV4KJGT', '2026-03-03', '', 9, 'BUHMS-29EA757A', '', '2026-03-03 22:51:29');

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `room_id` int(11) NOT NULL,
  `hostel_id` int(11) NOT NULL,
  `floor` int(11) DEFAULT NULL,
  `capacity` int(11) DEFAULT 1,
  `status` enum('Available','Occupied') DEFAULT 'Available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`room_id`, `hostel_id`, `floor`, `capacity`, `status`) VALUES
(1, 1, 1, 4, 'Occupied'),
(2, 1, 1, 4, 'Available'),
(3, 1, 1, 4, 'Available'),
(4, 1, 1, 5, 'Available'),
(5, 1, 1, 5, 'Available'),
(6, 1, 1, 6, 'Available'),
(7, 1, 1, 6, 'Available'),
(8, 1, 2, 4, 'Available'),
(9, 1, 2, 4, 'Available'),
(10, 1, 2, 5, 'Available'),
(11, 1, 2, 5, 'Available'),
(12, 1, 2, 6, 'Available'),
(13, 1, 2, 6, 'Available'),
(14, 1, 3, 4, 'Available'),
(15, 1, 3, 4, 'Available'),
(16, 1, 3, 5, 'Available'),
(17, 1, 3, 5, 'Available'),
(18, 1, 3, 6, 'Available'),
(19, 1, 3, 6, 'Available'),
(20, 2, 1, 4, 'Occupied'),
(21, 2, 1, 4, 'Available'),
(22, 2, 1, 5, 'Available'),
(23, 2, 1, 5, 'Available'),
(24, 2, 1, 6, 'Available'),
(25, 2, 1, 6, 'Available'),
(26, 2, 2, 4, 'Available'),
(27, 2, 2, 4, 'Available'),
(28, 2, 2, 5, 'Available'),
(29, 2, 2, 5, 'Available'),
(30, 2, 2, 6, 'Available'),
(31, 2, 2, 6, 'Available'),
(32, 4, 1, 4, 'Available'),
(33, 4, 1, 4, 'Available'),
(34, 4, 1, 5, 'Available'),
(35, 4, 1, 5, 'Available'),
(36, 4, 1, 6, 'Available'),
(37, 4, 1, 6, 'Available'),
(38, 4, 2, 4, 'Available'),
(39, 4, 2, 4, 'Available'),
(40, 4, 2, 5, 'Available'),
(41, 4, 2, 5, 'Available'),
(42, 4, 2, 6, 'Available'),
(43, 4, 2, 6, 'Available'),
(44, 6, 1, 4, 'Available'),
(45, 6, 1, 4, 'Available'),
(46, 6, 1, 5, 'Available'),
(47, 6, 1, 5, 'Available'),
(48, 6, 1, 6, 'Available'),
(49, 6, 1, 6, 'Available'),
(50, 6, 2, 4, 'Available'),
(51, 6, 2, 4, 'Available'),
(52, 6, 2, 5, 'Available'),
(53, 6, 2, 5, 'Available'),
(54, 6, 2, 6, 'Available'),
(55, 6, 2, 6, 'Available'),
(56, 1, 4, 6, 'Available'),
(57, 1, 4, 6, 'Available'),
(58, 1, 0, 0, 'Available'),
(59, 1, 0, 0, 'Available'),
(60, 1, 0, 0, 'Available'),
(61, 1, 4, 4, 'Available'),
(62, 1, 4, 4, 'Available'),
(63, 1, 2, 5, 'Available'),
(64, 1, 2, 5, 'Available'),
(65, 2, 2, 3, 'Available');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `student_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `year` int(11) DEFAULT NULL,
  `hostel_id` int(11) DEFAULT NULL,
  `room_id` int(11) DEFAULT NULL,
  `student_reg_id` varchar(50) DEFAULT NULL,
  `blood_group` varchar(5) DEFAULT NULL,
  `contact_no` varchar(20) DEFAULT NULL,
  `father_name` varchar(100) DEFAULT NULL,
  `father_contact` varchar(20) DEFAULT NULL,
  `mother_name` varchar(100) DEFAULT NULL,
  `mother_contact` varchar(20) DEFAULT NULL,
  `permanent_address` text DEFAULT NULL,
  `reason_for_stay` text DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`student_id`, `user_id`, `year`, `hostel_id`, `room_id`, `student_reg_id`, `blood_group`, `contact_no`, `father_name`, `father_contact`, `mother_name`, `mother_contact`, `permanent_address`, `reason_for_stay`, `department`) VALUES
(1, 2, 2021, 1, 1, '22CSE047', 'B+', '01303541291', 'Habibur Rahman', '01992223531', 'Nilufa Yesmin', '01714882866', 'Uttara, Dhaka,', 'Financial Inability', 'Computer Science And Engineering'),
(2, 3, 2022, 1, 1, '230102035', 'B+', '01303541291', 'Sakib Alom', '01992223531', 'Rahima Khatun', '01714882866', 'Norshindi, Dhaka.', 'Financial Problem.', 'Computer Science And Engineering'),
(6, 5, NULL, 2, 20, '230102039', 'B+', '01644789099', 'Golam Rasul', '01345783462', 'Morsheda', '01846271220', 'Betagi, Borguna.', 'Financial Problem.', 'Computer Science and Engineering');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('Student','Provost','AdminOfficer','AssistantRegistrar','HouseTutor') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `password_hash`, `role`, `created_at`) VALUES
(1, 'Md Arif Ul Islam', 'ariful@bu.ac.bd', '$2y$10$mba9BZyMOkJvQXjsvTuPLupPgu5XJmr/e1Yjkn61KxK3A/YDrRh1y', 'Provost', '2026-02-28 23:05:55'),
(2, 'Arafat Rahaman', 'arafat@bu.ac.bd', '$2y$10$mba9BZyMOkJvQXjsvTuPLupPgu5XJmr/e1Yjkn61KxK3A/YDrRh1y', 'Student', '2026-02-28 23:05:55'),
(3, 'Md Shahin Alom', 'shahin@bu.ac.bd', '$2y$10$fGtYq.7pWfq9ZVEjLd7Ynu7uYxKHyhalzKxj0e2XlTziW0cjoZg1S', 'Student', '2026-03-01 06:03:26'),
(5, 'Md Shihab', 'shihab@bu.ac.bd', '$2y$10$5DZz0h7utoEBSb51UjXzKue9tR/lg8Km9qGWnZg39A9yKC.0FLnSq', 'Student', '2026-03-02 18:47:42'),
(6, 'Syed Ashik-E-Elahi', 'ashik@bu.ac.bd', '$2y$10$FoMQPRnOum9vTq5NL2oVBOWHYAgcEuZtt6/H8VxqBLKGAoNEKJwsO', 'HouseTutor', '2026-03-03 08:55:46'),
(7, 'Md. Humayun Kabir', 'humayun@bu.ac.bd', '$2y$10$NKrz9RprCQBtlH1OounNue66HVET1q1m1Qvd.CYxCs08ixUNI9SvO', 'AssistantRegistrar', '2026-03-03 08:58:46'),
(9, 'Md. Mizanur Rahman Khan', 'mizanur@bu.ac.bd', '$2y$10$8kxEy8UxJ10F15d.P.qBBeXIjRxyfEJRGVMYTEKWoDUM.R1cmw94m', 'AdminOfficer', '2026-03-03 09:01:43');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`booking_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `room_id` (`room_id`);

--
-- Indexes for table `complaints`
--
ALTER TABLE `complaints`
  ADD PRIMARY KEY (`complaint_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `tutor_id` (`tutor_id`);

--
-- Indexes for table `fee_structure`
--
ALTER TABLE `fee_structure`
  ADD PRIMARY KEY (`fee_id`),
  ADD KEY `hostel_id` (`hostel_id`);

--
-- Indexes for table `hostels`
--
ALTER TABLE `hostels`
  ADD PRIMARY KEY (`hostel_id`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `hostel_id` (`hostel_id`);

--
-- Indexes for table `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`room_id`),
  ADD KEY `hostel_id` (`hostel_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`student_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `hostel_id` (`hostel_id`),
  ADD KEY `room_id` (`room_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `complaints`
--
ALTER TABLE `complaints`
  MODIFY `complaint_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `fee_structure`
--
ALTER TABLE `fee_structure`
  MODIFY `fee_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hostels`
--
ALTER TABLE `hostels`
  MODIFY `hostel_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `logs`
--
ALTER TABLE `logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `room_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`),
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`room_id`);

--
-- Constraints for table `complaints`
--
ALTER TABLE `complaints`
  ADD CONSTRAINT `complaints_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`),
  ADD CONSTRAINT `complaints_ibfk_2` FOREIGN KEY (`tutor_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `fee_structure`
--
ALTER TABLE `fee_structure`
  ADD CONSTRAINT `fee_structure_ibfk_1` FOREIGN KEY (`hostel_id`) REFERENCES `hostels` (`hostel_id`);

--
-- Constraints for table `inventory`
--
ALTER TABLE `inventory`
  ADD CONSTRAINT `inventory_ibfk_1` FOREIGN KEY (`hostel_id`) REFERENCES `hostels` (`hostel_id`);

--
-- Constraints for table `logs`
--
ALTER TABLE `logs`
  ADD CONSTRAINT `logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`);

--
-- Constraints for table `rooms`
--
ALTER TABLE `rooms`
  ADD CONSTRAINT `rooms_ibfk_1` FOREIGN KEY (`hostel_id`) REFERENCES `hostels` (`hostel_id`);

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `students_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `students_ibfk_2` FOREIGN KEY (`hostel_id`) REFERENCES `hostels` (`hostel_id`),
  ADD CONSTRAINT `students_ibfk_3` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`room_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
