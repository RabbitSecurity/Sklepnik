-- phpMyAdmin SQL Dump
-- version 4.9.5
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Feb 26, 2021 at 10:46 PM
-- Server version: 10.3.27-MariaDB-log-cll-lve
-- PHP Version: 7.3.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `rimskace_sklepnik`
--

-- --------------------------------------------------------

--
-- Table structure for table `sklepnik_delegati`
--

CREATE TABLE `sklepnik_delegati` (
  `id` int(11) NOT NULL,
  `ime` varchar(250) COLLATE utf8_slovenian_ci NOT NULL,
  `priimek` varchar(250) COLLATE utf8_slovenian_ci NOT NULL,
  `email` varchar(250) COLLATE utf8_slovenian_ci NOT NULL,
  `rod` varchar(250) COLLATE utf8_slovenian_ci NOT NULL,
  `rod_kratica` varchar(50) COLLATE utf8_slovenian_ci NOT NULL,
  `obmocje` varchar(250) COLLATE utf8_slovenian_ci NOT NULL,
  `obmocje_kratica` varchar(50) COLLATE utf8_slovenian_ci NOT NULL,
  `funkcija` varchar(100) COLLATE utf8_slovenian_ci NOT NULL,
  `dogodek_id` int(11) NOT NULL,
  `registriran` enum('ne','da') COLLATE utf8_slovenian_ci NOT NULL DEFAULT 'ne',
  `zadnjic_aktiven` datetime NOT NULL,
  `zadnji_ping` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE current_timestamp(),
  `login_key` varchar(250) COLLATE utf8_slovenian_ci NOT NULL,
  `vote_key` varchar(250) COLLATE utf8_slovenian_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `sklepnik_delegati`
--

INSERT INTO `sklepnik_delegati` (`id`, `ime`, `priimek`, `email`, `rod`, `rod_kratica`, `obmocje`, `obmocje_kratica`, `funkcija`, `dogodek_id`, `registriran`, `zadnjic_aktiven`, `zadnji_ping`, `login_key`, `vote_key`) VALUES
(1, 'Boštjan', 'Zajec', 'bostjan.zajec@gmail.com', 'Rod Sivega volka', 'RSV', 'Mestna Zveza Tabornikov', 'MZT', 'starešina', 1, 'da', '0000-00-00 00:00:00', '2021-02-26 22:46:04', 'ZgOjOTTKm0vUHCpjJQ6pnqxaUvcBuTXZ8K6iiGMZ698Zpl9LYz', 'IcDJG4eXubf3yCIoELl7MYRlN2nfc2acmkXRqVKJyxbmD557D0'),
(4, 'Živa', 'Groza', 'ziva.groza@zivameja.com', 'Rod Beli Kamen', 'RBK', 'Kamniška Zveza Taborikov', 'KZT', 'načelnica', 1, 'ne', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'O8pyBCGP3v7Pxy0P1E6n2Xid3euSBMQvnojESC8Wua9jF4Fhzp', 'plKqTu3Bx1YVpNWyvfh121jTtUkZ8LdqMIu0JHV3GjbUcxh4Zv');

-- --------------------------------------------------------

--
-- Table structure for table `sklepnik_dogodki`
--

CREATE TABLE `sklepnik_dogodki` (
  `id` int(11) NOT NULL,
  `ime` varchar(250) COLLATE utf8_slovenian_ci NOT NULL,
  `sklepcnost_min_delegatov` int(11) NOT NULL DEFAULT 0,
  `sklepcnost_min_rodov` int(11) NOT NULL DEFAULT 0,
  `sklepcnost_min_obmocji` int(11) NOT NULL DEFAULT 0,
  `time_start` datetime NOT NULL,
  `time_end` datetime NOT NULL,
  `access_key` varchar(100) COLLATE utf8_slovenian_ci NOT NULL,
  `admin_username` varchar(100) COLLATE utf8_slovenian_ci NOT NULL,
  `admin_pass_hash` varchar(100) COLLATE utf8_slovenian_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Dumping data for table `sklepnik_dogodki`
--

INSERT INTO `sklepnik_dogodki` (`id`, `ime`, `sklepcnost_min_delegatov`, `sklepcnost_min_rodov`, `sklepcnost_min_obmocji`, `time_start`, `time_end`, `access_key`, `admin_username`, `admin_pass_hash`) VALUES
(1, 'Dogodkovna deklica za vse', 0, 0, 0, '2021-02-26 00:00:00', '2021-02-26 23:59:59', 'VygXgb4iiyoKpWkSdUPTtzBVKp7rtF', 'admin', 'bb6aff6e98c2696d71890dc5e86af9f4babe13ed');

-- --------------------------------------------------------

--
-- Table structure for table `sklepnik_glasovi`
--

CREATE TABLE `sklepnik_glasovi` (
  `id` int(11) NOT NULL,
  `delegat_id` int(11) NOT NULL,
  `sklep_id` int(11) NOT NULL,
  `odgovor` int(11) NOT NULL,
  `time` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `sklepnik_sklepi`
--

CREATE TABLE `sklepnik_sklepi` (
  `id` int(11) NOT NULL,
  `vprasanje` mediumtext COLLATE utf8_slovenian_ci NOT NULL,
  `pojasnilo` mediumtext COLLATE utf8_slovenian_ci NOT NULL,
  `dogodek_id` int(11) NOT NULL,
  `time_start` datetime NOT NULL,
  `time_end` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovenian_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `sklepnik_delegati`
--
ALTER TABLE `sklepnik_delegati`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sklepnik_dogodki`
--
ALTER TABLE `sklepnik_dogodki`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sklepnik_glasovi`
--
ALTER TABLE `sklepnik_glasovi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `delegat_id` (`delegat_id`);

--
-- Indexes for table `sklepnik_sklepi`
--
ALTER TABLE `sklepnik_sklepi`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `sklepnik_delegati`
--
ALTER TABLE `sklepnik_delegati`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `sklepnik_dogodki`
--
ALTER TABLE `sklepnik_dogodki`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `sklepnik_glasovi`
--
ALTER TABLE `sklepnik_glasovi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sklepnik_sklepi`
--
ALTER TABLE `sklepnik_sklepi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
