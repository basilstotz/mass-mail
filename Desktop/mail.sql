-- phpMyAdmin SQL Dump
-- version 3.3.2deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Sep 15, 2011 at 12:40 PM
-- Server version: 5.1.41
-- PHP Version: 5.3.2-1ubuntu4.9

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `mail`
--

-- --------------------------------------------------------

--
-- Table structure for table `mail_log`
--

CREATE TABLE IF NOT EXISTS `mail_log` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `datum` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `smtp_user` varchar(64) NOT NULL,
  `mail_user` int(8) NOT NULL,
  `sender` varchar(64) NOT NULL,
  `recipient` varchar(64) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1013 ;

-- --------------------------------------------------------

--
-- Table structure for table `mail_queue`
--

CREATE TABLE IF NOT EXISTS `mail_queue` (
  `id` bigint(20) NOT NULL DEFAULT '0',
  `create_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `time_to_send` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `sent_time` datetime DEFAULT NULL,
  `id_user` bigint(20) NOT NULL DEFAULT '0',
  `ip` varchar(20) NOT NULL DEFAULT 'unknown',
  `sender` varchar(50) NOT NULL DEFAULT '',
  `recipient` text NOT NULL,
  `headers` text NOT NULL,
  `body` longtext NOT NULL,
  `try_sent` tinyint(4) NOT NULL DEFAULT '0',
  `delete_after_send` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `id` (`id`),
  KEY `time_to_send` (`time_to_send`),
  KEY `id_user` (`id_user`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `mail_queue_seq`
--

CREATE TABLE IF NOT EXISTS `mail_queue_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1071 ;

-- --------------------------------------------------------

--
-- Stand-in structure for view `mail_recent_log`
--
CREATE TABLE IF NOT EXISTS `mail_recent_log` (
`id` int(8)
,`datum` timestamp
,`smtp_user` varchar(64)
,`mail_user` int(8)
,`sender` varchar(64)
,`recipient` varchar(64)
);
-- --------------------------------------------------------

--
-- Structure for view `mail_recent_log`
--
DROP TABLE IF EXISTS `mail_recent_log`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `mail_recent_log` AS select `mail_log`.`id` AS `id`,`mail_log`.`datum` AS `datum`,`mail_log`.`smtp_user` AS `smtp_user`,`mail_log`.`mail_user` AS `mail_user`,`mail_log`.`sender` AS `sender`,`mail_log`.`recipient` AS `recipient` from `mail_log` where ((unix_timestamp() - unix_timestamp(`mail_log`.`datum`)) < 86400);
