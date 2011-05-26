-- phpMyAdmin SQL Dump
-- version 3.3.7
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: May 25, 2011 at 06:20 PM
-- Server version: 5.1.41
-- PHP Version: 5.3.2-1ubuntu4.7

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `civicrm_trunk`
--

--
-- Dumping data for table `civicrm_action_schedule`
--

INSERT INTO `civicrm_action_schedule` (`id`, `name`, `title`, `recipient`, `entity_value`, `entity_status`, `first_action_offset`, `first_action_unit`, `first_action_condition`, `is_repeat`, `repetition_start_frequency_unit`, `repetition_start_frequency_interval`, `repetition_end_frequency_unit`, `repetition_end_frequency_interval`, `repetition_end_action`, `is_active`, `recipient_manual`, `body_text`, `body_html`, `subject`, `record_activity`, `mapping_id`, `group_id`) VALUES
(1, 'equipment_form_ daily_reminder', 'Equipment form, daily reminder', '3', '7', '2', 2, 'day', 'before', 1, 'day', 1, 'day', 3, 'after', 1, NULL, 'This is a Text Body. If you see this message its text message that you reading.', 'This a html message. If you see this message its html message that you reading.', 'Action cron activity mail', 1, 1, NULL);
