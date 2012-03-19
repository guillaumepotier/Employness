-- phpMyAdmin SQL Dump
-- version 3.3.9.2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Mar 18, 2012 at 04:02 PM
-- Server version: 5.5.9
-- PHP Version: 5.3.6

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `employness`
--

-- --------------------------------------------------------

--
-- Table structure for table `employness_days`
--
-- Creation: Mar 18, 2012 at 03:58 PM
--

DROP TABLE IF EXISTS `employness_days`;
CREATE TABLE IF NOT EXISTS `employness_days` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `day` date NOT NULL,
  `karma` tinyint(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `day` (`day`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `employness_karma`
--
-- Creation: Mar 18, 2012 at 03:59 PM
--

DROP TABLE IF EXISTS `employness_karma`;
CREATE TABLE IF NOT EXISTS `employness_karma` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `user_id` mediumint(9) NOT NULL,
  `day_id` mediumint(9) NOT NULL,
  `karma` tinyint(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_days` (`user_id`,`day_id`),
  KEY `day_id_fk` (`day_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `employness_users`
--
-- Creation: Mar 18, 2012 at 03:57 PM
--

DROP TABLE IF EXISTS `employness_users`;
CREATE TABLE IF NOT EXISTS `employness_users` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `email` varchar(50) COLLATE utf8_bin NOT NULL,
  `password` varchar(50) COLLATE utf8_bin NOT NULL,
  `token` varchar(50) COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `employness_karma`
--
ALTER TABLE `employness_karma`
  ADD CONSTRAINT `day_id_fk` FOREIGN KEY (`day_id`) REFERENCES `employness_days` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `employness_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
