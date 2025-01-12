-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 12, 2025 at 04:09 AM
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
-- Database: `meter_app`
--

-- --------------------------------------------------------

--
-- Table structure for table `anomaly_logs`
--

CREATE TABLE `anomaly_logs` (
  `id` int(11) NOT NULL,
  `device_id` int(11) NOT NULL,
  `anomaly_type` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `detected_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `devices`
--

CREATE TABLE `devices` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `device_recognitions`
--

CREATE TABLE `device_recognitions` (
  `id` int(11) NOT NULL,
  `device_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `recognition_time` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `energy_insights`
--

CREATE TABLE `energy_insights` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `device_id` int(11) NOT NULL,
  `insight` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `energy_usage`
--

CREATE TABLE `energy_usage` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `energy_consumed` float NOT NULL,
  `bill_amount` float NOT NULL,
  `timestamp` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `energy_usage`
--

INSERT INTO `energy_usage` (`id`, `user_id`, `energy_consumed`, `bill_amount`, `timestamp`) VALUES
(1, 1, 150.5, 450.75, '2024-12-26 17:53:03'),
(2, 1, 120.3, 360.9, '2024-12-26 17:53:03'),
(3, 1, 180, 540, '2024-12-26 17:53:03');

-- --------------------------------------------------------

--
-- Table structure for table `energy_usages`
--

CREATE TABLE `energy_usages` (
  `id` int(11) NOT NULL,
  `device_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `usage_date` date NOT NULL,
  `energy_consumed` float NOT NULL,
  `cost` float NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `forecasts`
--

CREATE TABLE `forecasts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `forecast_date` date NOT NULL,
  `energy_predicted` float NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `status` enum('unread','read') DEFAULT 'unread',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `optimizations`
--

CREATE TABLE `optimizations` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `suggestion` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `regression_models`
--

CREATE TABLE `regression_models` (
  `id` int(11) NOT NULL,
  `model_name` varchar(255) NOT NULL,
  `variables` text NOT NULL,
  `coefficients` text NOT NULL,
  `accuracy` float NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `profile_picture` varchar(255) DEFAULT 'default.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`, `updated_at`, `profile_picture`) VALUES
(1, 'Admin', 'admin@admin.com', '$2y$10$QxQu80FRL7Xg1j.7gHoqaOuaoS3Zw58eylQhKqbPFOm/gBOtEBTRe', 'admin', '2024-12-18 07:18:58', '2024-12-20 08:44:59', 'default.png'),
(2, 'User', 'user@example.com', '$2y$10$QxQu80FRL7Xg1j.7gHoqaOuaoS3Zw58eylQhKqbPFOm/gBOtEBTRe', 'user', '2024-12-18 07:18:58', '2024-12-20 08:45:04', 'default.png'),
(3, 'mosnanoi37', 'mos@test.com', '$2y$10$QxQu80FRL7Xg1j.7gHoqaOuaoS3Zw58eylQhKqbPFOm/gBOtEBTRe', 'user', '2024-12-18 08:30:33', '2024-12-20 08:45:12', 'default.png'),
(4, 'test', 'test@test.com', '$2y$10$QxQu80FRL7Xg1j.7gHoqaOuaoS3Zw58eylQhKqbPFOm/gBOtEBTRe', 'user', '2024-12-20 08:08:38', '2024-12-20 08:45:19', 'default.png'),
(5, 'mos', 'mos@mos.com', '$2y$10$QxQu80FRL7Xg1j.7gHoqaOuaoS3Zw58eylQhKqbPFOm/gBOtEBTRe', 'user', '2024-12-20 08:29:05', '2024-12-20 08:45:24', 'default.png'),
(6, '1234', '1234@1234.com', '$2y$10$QxQu80FRL7Xg1j.7gHoqaOuaoS3Zw58eylQhKqbPFOm/gBOtEBTRe', 'user', '2024-12-20 08:41:36', '2024-12-20 08:45:30', 'default.png'),
(7, '5678', '5678@5678.com', '$2y$10$Enb6QMxCV28Gh3jhuSn97uL9O5kEpd5IYgtHLeVKQD5CxR2DJngu.', 'user', '2024-12-26 07:56:12', '2024-12-26 07:56:12', 'default.png');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `anomaly_logs`
--
ALTER TABLE `anomaly_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_anomaly_device` (`device_id`);

--
-- Indexes for table `devices`
--
ALTER TABLE `devices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `device_recognitions`
--
ALTER TABLE `device_recognitions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_device_recognition` (`device_id`,`user_id`);

--
-- Indexes for table `energy_insights`
--
ALTER TABLE `energy_insights`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `device_id` (`device_id`);

--
-- Indexes for table `energy_usage`
--
ALTER TABLE `energy_usage`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `energy_usages`
--
ALTER TABLE `energy_usages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_device_id` (`device_id`);

--
-- Indexes for table `forecasts`
--
ALTER TABLE `forecasts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_forecast_date` (`forecast_date`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_notification_user` (`user_id`);

--
-- Indexes for table `optimizations`
--
ALTER TABLE `optimizations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `regression_models`
--
ALTER TABLE `regression_models`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `anomaly_logs`
--
ALTER TABLE `anomaly_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `devices`
--
ALTER TABLE `devices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `device_recognitions`
--
ALTER TABLE `device_recognitions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `energy_insights`
--
ALTER TABLE `energy_insights`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `energy_usage`
--
ALTER TABLE `energy_usage`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `energy_usages`
--
ALTER TABLE `energy_usages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `forecasts`
--
ALTER TABLE `forecasts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `optimizations`
--
ALTER TABLE `optimizations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `regression_models`
--
ALTER TABLE `regression_models`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `anomaly_logs`
--
ALTER TABLE `anomaly_logs`
  ADD CONSTRAINT `anomaly_logs_ibfk_1` FOREIGN KEY (`device_id`) REFERENCES `devices` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `devices`
--
ALTER TABLE `devices`
  ADD CONSTRAINT `devices_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `device_recognitions`
--
ALTER TABLE `device_recognitions`
  ADD CONSTRAINT `device_recognitions_ibfk_1` FOREIGN KEY (`device_id`) REFERENCES `devices` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `device_recognitions_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `energy_insights`
--
ALTER TABLE `energy_insights`
  ADD CONSTRAINT `energy_insights_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `energy_insights_ibfk_2` FOREIGN KEY (`device_id`) REFERENCES `devices` (`id`);

--
-- Constraints for table `energy_usages`
--
ALTER TABLE `energy_usages`
  ADD CONSTRAINT `energy_usages_ibfk_1` FOREIGN KEY (`device_id`) REFERENCES `devices` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `energy_usages_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `forecasts`
--
ALTER TABLE `forecasts`
  ADD CONSTRAINT `forecasts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `optimizations`
--
ALTER TABLE `optimizations`
  ADD CONSTRAINT `optimizations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
