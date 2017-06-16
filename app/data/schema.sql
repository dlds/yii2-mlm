-- phpMyAdmin SQL Dump
-- version 4.7.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jun 16, 2017 at 11:03 AM
-- Server version: 5.6.35
-- PHP Version: 7.1.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `yii2_mlm`
--

-- --------------------------------------------------------

--
-- Table structure for table `participant`
--

CREATE TABLE `participant` (
  `id` int(11) UNSIGNED NOT NULL,
  `lft` int(11) NOT NULL,
  `rgt` int(11) NOT NULL,
  `depth` smallint(6) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `rwd_custom`
--

CREATE TABLE `rwd_custom` (
  `id` int(10) UNSIGNED NOT NULL,
  `usr_rewarded_id` int(11) UNSIGNED NOT NULL,
  `subject_id` int(11) DEFAULT NULL,
  `subject_type` enum('subject') DEFAULT NULL,
  `value` double NOT NULL,
  `status` enum('pending','approved','denied','completed') NOT NULL DEFAULT 'pending',
  `is_locked` tinyint(1) NOT NULL,
  `approved_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `rwd_extra`
--

CREATE TABLE `rwd_extra` (
  `id` int(10) UNSIGNED NOT NULL,
  `usr_rewarded_id` int(11) UNSIGNED NOT NULL,
  `rwd_basic_id` int(10) UNSIGNED NOT NULL,
  `value` float NOT NULL,
  `status` enum('pending','approved','denied','completed') NOT NULL DEFAULT 'pending',
  `is_locked` tinyint(1) NOT NULL,
  `approved_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `subject`
--

CREATE TABLE `subject` (
  `id` int(10) UNSIGNED NOT NULL,
  `participant_id` int(11) UNSIGNED DEFAULT NULL,
  `amount` float NOT NULL,
  `amount_vat` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `participant`
--
ALTER TABLE `participant`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `rwd_custom`
--
ALTER TABLE `rwd_custom`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `subject_id` (`subject_id`,`subject_type`) USING BTREE,
  ADD KEY `fk_rwd_custom_usr_identity1_idx` (`usr_rewarded_id`);

--
-- Indexes for table `rwd_extra`
--
ALTER TABLE `rwd_extra`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usr_rewarded_id_UNIQUE` (`usr_rewarded_id`,`rwd_basic_id`),
  ADD KEY `fk_rwd_extra_usr_identity1_idx` (`usr_rewarded_id`),
  ADD KEY `fk_rwd_extra_rwd_basic1_idx` (`rwd_basic_id`);

--
-- Indexes for table `subject`
--
ALTER TABLE `subject`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_participant_idx` (`participant_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `participant`
--
ALTER TABLE `participant`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `rwd_custom`
--
ALTER TABLE `rwd_custom`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `rwd_extra`
--
ALTER TABLE `rwd_extra`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `subject`
--
ALTER TABLE `subject`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `rwd_custom`
--
ALTER TABLE `rwd_custom`
  ADD CONSTRAINT `fk_rwd_custom_usr_identity1` FOREIGN KEY (`usr_rewarded_id`) REFERENCES `participant` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `rwd_extra`
--
ALTER TABLE `rwd_extra`
  ADD CONSTRAINT `fk_rwd_extra_rwd_basic1` FOREIGN KEY (`rwd_basic_id`) REFERENCES `rwd_basic` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_rwd_extra_usr_identity1` FOREIGN KEY (`usr_rewarded_id`) REFERENCES `participant` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `subject`
--
ALTER TABLE `subject`
  ADD CONSTRAINT `fk_subject_participant` FOREIGN KEY (`participant_id`) REFERENCES `participant` (`id`) ON UPDATE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
