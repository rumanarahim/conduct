-- phpMyAdmin SQL Dump
-- version 4.1.14
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Aug 24, 2015 at 02:47 PM
-- Server version: 5.6.17
-- PHP Version: 5.5.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `conduct`
--
CREATE DATABASE IF NOT EXISTS `conduct` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `conduct`;

-- --------------------------------------------------------

--
-- Table structure for table `agents`
--

CREATE TABLE IF NOT EXISTS `agents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `mobile` varchar(255) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  `active` int(1) NOT NULL DEFAULT '1',
  `assigned` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

--
-- Dumping data for table `agents`
--

INSERT INTO `agents` (`id`, `first_name`, `last_name`, `email`, `mobile`, `created`, `modified`, `active`, `assigned`) VALUES
(1, 'Chris', 'Conduct', 'rumana.rhm@gmail.com', '9726893743', '2013-10-24 16:44:23', '2013-10-24 16:44:23', 1, 0),
(2, 'Charlie', 'Conduct', 'rumana.rhm@gmail.com', '8383739948', '2013-10-24 16:47:00', '2013-10-24 16:47:00', 0, 0),
(3, 'Simon', 'Conduct', 'rumana.rhm@gmail.com', '8284934859', '2013-10-24 16:58:46', '2013-10-24 16:58:46', 1, 0),
(4, 'Steve', 'Conduct', 'rumana.rhm@gmail.com', '8284934859', '2013-10-24 17:01:38', '2013-10-24 17:01:38', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `backlog`
--

CREATE TABLE IF NOT EXISTS `backlog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `agent_id` int(11) DEFAULT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `mobile` varchar(45) DEFAULT NULL,
  `message` text,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_leads_agents` (`agent_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=20 ;

-- --------------------------------------------------------

--
-- Table structure for table `leads`
--

CREATE TABLE IF NOT EXISTS `leads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `agent_id` int(11) DEFAULT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `mobile` varchar(45) DEFAULT NULL,
  `message` text,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  `lead_email_sent` int(1) NOT NULL DEFAULT '0',
  `agent_email_sent` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_leads_agents` (`agent_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=28 ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `leads`
--
ALTER TABLE `leads`
  ADD CONSTRAINT `fk_leads_agents` FOREIGN KEY (`agent_id`) REFERENCES `agents` (`id`) ON DELETE SET NULL;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
