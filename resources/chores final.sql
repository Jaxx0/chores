-- phpMyAdmin SQL Dump
-- version 4.6.6deb5
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 15, 2017 at 08:42 AM
-- Server version: 10.1.26-MariaDB-1
-- PHP Version: 7.0.22-3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `chores`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `category` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `clients`
--

CREATE TABLE `clients` (
  `client_id` int(11) NOT NULL,
  `client_name` varchar(45) NOT NULL,
  `passwd` text,
  `api_key` text,
  `email` varchar(45) DEFAULT NULL,
  `cell_no` text,
  `location` varchar(45) DEFAULT NULL,
  `first_name` varchar(45) DEFAULT NULL,
  `last_name` varchar(45) DEFAULT NULL,
  `image` varchar(200) DEFAULT NULL,
  `status` int(11) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `client_rating`
--

CREATE TABLE `client_rating` (
  `client_rating_id` int(10) UNSIGNED NOT NULL,
  `client_id` int(11) NOT NULL,
  `proff_id` int(11) NOT NULL,
  `rating` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `job_categories`
--

CREATE TABLE `job_categories` (
  `job_categories_id` int(11) NOT NULL,
  `job_type` varchar(45) NOT NULL,
  `description_text` text NOT NULL,
  `proff_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `job_description`
--

CREATE TABLE `job_description` (
  `job_description_id` int(11) NOT NULL,
  `job_post_id` int(11) NOT NULL,
  `text` text,
  `quantity` varchar(45) DEFAULT NULL,
  `image` varchar(200) DEFAULT NULL,
  `amount` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `job_post`
--

CREATE TABLE `job_post` (
  `job_post_id` int(11) NOT NULL,
  `client_id` int(11) DEFAULT NULL,
  `location` varchar(45) NOT NULL,
  `apartment_name` varchar(45) NOT NULL,
  `house_no` varchar(100) NOT NULL,
  `contact_cell_no` text NOT NULL,
  `job_date` varchar(45) NOT NULL,
  `job_time` varchar(45) NOT NULL,
  `job category` varchar(45) NOT NULL,
  `proff_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `client_id` int(11) DEFAULT NULL,
  `proff_id` int(11) DEFAULT NULL,
  `job_post_id` int(11) DEFAULT NULL,
  `job_description_id` int(11) DEFAULT NULL,
  `payment_date` varchar(45) NOT NULL,
  `payment_time` varchar(45) NOT NULL,
  `transaction_reference` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `proffesionals`
--

CREATE TABLE `proffesionals` (
  `proff_id` int(11) NOT NULL,
  `proff_name` varchar(45) DEFAULT NULL,
  `passwd` text,
  `api_key` text,
  `email` varchar(45) DEFAULT NULL,
  `cell_no` varchar(45) DEFAULT NULL,
  `national_id` varchar(45) NOT NULL,
  `location` varchar(45) DEFAULT NULL,
  `availability_status` varchar(45) DEFAULT NULL,
  `image` varchar(200) DEFAULT NULL,
  `first_name` varchar(45) DEFAULT NULL,
  `last_name` varchar(45) DEFAULT NULL,
  `gender` varchar(45) DEFAULT NULL,
  `status` int(11) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `proffesional_status`
--

CREATE TABLE `proffesional_status` (
  `proff_status_id` int(11) NOT NULL,
  `proff_id` int(11) NOT NULL,
  `proff_text` text,
  `proff_image` text,
  `proff_video` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `proffessional_rating`
--

CREATE TABLE `proffessional_rating` (
  `proff_rating_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `proff_id` int(11) NOT NULL,
  `rating` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `categories_id_UNIQUE` (`category_id`);

--
-- Indexes for table `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`client_id`),
  ADD UNIQUE KEY `client_id_UNIQUE` (`client_id`);

--
-- Indexes for table `client_rating`
--
ALTER TABLE `client_rating`
  ADD PRIMARY KEY (`client_rating_id`),
  ADD UNIQUE KEY `rating_id_UNIQUE` (`client_rating_id`),
  ADD KEY `fk_client rating_clients1_idx` (`client_id`),
  ADD KEY `fk_client rating_proffesionals1_idx` (`proff_id`);

--
-- Indexes for table `job_categories`
--
ALTER TABLE `job_categories`
  ADD PRIMARY KEY (`job_categories_id`),
  ADD UNIQUE KEY `job_categories_id_UNIQUE` (`job_categories_id`),
  ADD KEY `fk_job categories_proffesionals1_idx` (`proff_id`);

--
-- Indexes for table `job_description`
--
ALTER TABLE `job_description`
  ADD PRIMARY KEY (`job_description_id`),
  ADD UNIQUE KEY `job_description_id_UNIQUE` (`job_description_id`),
  ADD KEY `fk_job description_job post1_idx` (`job_post_id`);

--
-- Indexes for table `job_post`
--
ALTER TABLE `job_post`
  ADD PRIMARY KEY (`job_post_id`),
  ADD UNIQUE KEY `job_post_id_UNIQUE` (`job_post_id`),
  ADD KEY `fk_job post_clients1_idx` (`client_id`),
  ADD KEY `fk_job post_proffesionals1_idx` (`proff_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD UNIQUE KEY `payment_id_UNIQUE` (`payment_id`),
  ADD KEY `fk_payments_clients_idx` (`client_id`),
  ADD KEY `fk_payments_proffesionals1_idx` (`proff_id`),
  ADD KEY `fk_payments_job post1_idx` (`job_post_id`);

--
-- Indexes for table `proffesionals`
--
ALTER TABLE `proffesionals`
  ADD PRIMARY KEY (`proff_id`),
  ADD UNIQUE KEY `proff_id_UNIQUE` (`proff_id`);

--
-- Indexes for table `proffesional_status`
--
ALTER TABLE `proffesional_status`
  ADD PRIMARY KEY (`proff_status_id`),
  ADD UNIQUE KEY `proff_status_id_UNIQUE` (`proff_status_id`),
  ADD KEY `fk_proffesional status_proffesionals1_idx` (`proff_id`);

--
-- Indexes for table `proffessional_rating`
--
ALTER TABLE `proffessional_rating`
  ADD PRIMARY KEY (`proff_rating_id`),
  ADD UNIQUE KEY `proff_rating_id_UNIQUE` (`proff_rating_id`),
  ADD KEY `fk_proffessional rating_clients1_idx` (`client_id`),
  ADD KEY `fk_proffessional rating_proffesionals1_idx` (`proff_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `clients`
--
ALTER TABLE `clients`
  MODIFY `client_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `client_rating`
--
ALTER TABLE `client_rating`
  MODIFY `client_rating_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `job_categories`
--
ALTER TABLE `job_categories`
  MODIFY `job_categories_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `job_description`
--
ALTER TABLE `job_description`
  MODIFY `job_description_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `job_post`
--
ALTER TABLE `job_post`
  MODIFY `job_post_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `proffesionals`
--
ALTER TABLE `proffesionals`
  MODIFY `proff_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `proffesional_status`
--
ALTER TABLE `proffesional_status`
  MODIFY `proff_status_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `proffessional_rating`
--
ALTER TABLE `proffessional_rating`
  MODIFY `proff_rating_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `client_rating`
--
ALTER TABLE `client_rating`
  ADD CONSTRAINT `fk_client rating_clients1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`client_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_client rating_proffesionals1` FOREIGN KEY (`proff_id`) REFERENCES `proffesionals` (`proff_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `job_categories`
--
ALTER TABLE `job_categories`
  ADD CONSTRAINT `fk_job categories_proffesionals1` FOREIGN KEY (`proff_id`) REFERENCES `proffesionals` (`proff_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `job_description`
--
ALTER TABLE `job_description`
  ADD CONSTRAINT `fk_job description_job post1` FOREIGN KEY (`job_post_id`) REFERENCES `job_post` (`job_post_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `job_post`
--
ALTER TABLE `job_post`
  ADD CONSTRAINT `fk_job post_clients1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`client_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_job post_proffesionals1` FOREIGN KEY (`proff_id`) REFERENCES `proffesionals` (`proff_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `fk_payments_clients` FOREIGN KEY (`client_id`) REFERENCES `clients` (`client_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_payments_job post1` FOREIGN KEY (`job_post_id`) REFERENCES `job_post` (`job_post_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_payments_proffesionals1` FOREIGN KEY (`proff_id`) REFERENCES `proffesionals` (`proff_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `proffesional_status`
--
ALTER TABLE `proffesional_status`
  ADD CONSTRAINT `fk_proffesional status_proffesionals1` FOREIGN KEY (`proff_id`) REFERENCES `proffesionals` (`proff_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `proffessional_rating`
--
ALTER TABLE `proffessional_rating`
  ADD CONSTRAINT `fk_proffessional rating_clients1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`client_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_proffessional rating_proffesionals1` FOREIGN KEY (`proff_id`) REFERENCES `proffesionals` (`proff_id`) ON DELETE CASCADE ON UPDATE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
