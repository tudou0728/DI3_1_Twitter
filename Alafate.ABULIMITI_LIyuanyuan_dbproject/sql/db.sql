-- phpMyAdmin SQL Dump
-- version 4.6.4
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Generation Time: Jan 21, 2017 at 11:47 AM
-- Server version: 5.6.33
-- PHP Version: 7.0.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `application`
--

-- --------------------------------------------------------

--
-- Table structure for table `FOLLOW`
--

CREATE TABLE `FOLLOW` (
  `IDU_USER2` int(2) NOT NULL,
  `IDU_USER1` int(2) NOT NULL,
  `FDATE` datetime DEFAULT NULL,
  `LOOKORNOT` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `HASHTAG`
--

CREATE TABLE `HASHTAG` (
  `IDHT` int(2) NOT NULL,
  `NAMEHT` char(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `INCLUDE`
--

CREATE TABLE `INCLUDE` (
  `IDHT` int(2) NOT NULL,
  `IDT` int(2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `LOVE`
--

CREATE TABLE `LOVE` (
  `IDU` int(2) NOT NULL,
  `IDT` int(2) NOT NULL,
  `LDATE` datetime DEFAULT NULL,
  `LLOOKORNOT` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `MENTION`
--

CREATE TABLE `MENTION` (
  `IDT` int(2) NOT NULL,
  `IDU` int(2) NOT NULL,
  `MLOOKORNOT` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `TWEET`
--

CREATE TABLE `TWEET` (
  `IDT` int(2) NOT NULL,
  `IDU` int(2) NOT NULL,
  `IDT_TWEET2` int(2) NOT NULL,
  `NOMET` varchar(255) DEFAULT NULL,
  `PDATE` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `USER`
--

CREATE TABLE `USER` (
  `IDU` int(2) NOT NULL,
  `NAMEU` char(255) DEFAULT NULL,
  `PWD` char(255) DEFAULT NULL,
  `EMAIL` char(255) DEFAULT NULL,
  `USERNAME` char(255) DEFAULT NULL,
  `AVATAR` longblob
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `FOLLOW`
--
ALTER TABLE `FOLLOW`
  ADD PRIMARY KEY (`IDU_USER2`,`IDU_USER1`),
  ADD KEY `FK_FOLLOW_USER1` (`IDU_USER1`);

--
-- Indexes for table `HASHTAG`
--
ALTER TABLE `HASHTAG`
  ADD PRIMARY KEY (`IDHT`);

--
-- Indexes for table `INCLUDE`
--
ALTER TABLE `INCLUDE`
  ADD PRIMARY KEY (`IDHT`,`IDT`),
  ADD KEY `FK_INCLUDE_TWEET` (`IDT`);

--
-- Indexes for table `LOVE`
--
ALTER TABLE `LOVE`
  ADD PRIMARY KEY (`IDU`,`IDT`),
  ADD KEY `FK_LOVE_TWEET` (`IDT`);

--
-- Indexes for table `MENTION`
--
ALTER TABLE `MENTION`
  ADD PRIMARY KEY (`IDT`,`IDU`),
  ADD KEY `FK_MENTION_USER` (`IDU`);

--
-- Indexes for table `TWEET`
--
ALTER TABLE `TWEET`
  ADD PRIMARY KEY (`IDT`),
  ADD KEY `tweet_ibfk_1` (`IDU`),
  ADD KEY `tweet_ibfk_2` (`IDT_TWEET2`);

--
-- Indexes for table `USER`
--
ALTER TABLE `USER`
  ADD PRIMARY KEY (`IDU`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `HASHTAG`
--
ALTER TABLE `HASHTAG`
  MODIFY `IDHT` int(2) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `TWEET`
--
ALTER TABLE `TWEET`
  MODIFY `IDT` int(2) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `USER`
--
ALTER TABLE `USER`
  MODIFY `IDU` int(2) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=112;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `FOLLOW`
--
ALTER TABLE `FOLLOW`
  ADD CONSTRAINT `follow_ibfk_1` FOREIGN KEY (`IDU_USER2`) REFERENCES `USER` (`IDU`),
  ADD CONSTRAINT `follow_ibfk_2` FOREIGN KEY (`IDU_USER1`) REFERENCES `USER` (`IDU`);

--
-- Constraints for table `INCLUDE`
--
ALTER TABLE `INCLUDE`
  ADD CONSTRAINT `include_ibfk_1` FOREIGN KEY (`IDHT`) REFERENCES `HASHTAG` (`IDHT`),
  ADD CONSTRAINT `include_ibfk_2` FOREIGN KEY (`IDT`) REFERENCES `TWEET` (`IDT`);

--
-- Constraints for table `LOVE`
--
ALTER TABLE `LOVE`
  ADD CONSTRAINT `love_ibfk_1` FOREIGN KEY (`IDU`) REFERENCES `USER` (`IDU`),
  ADD CONSTRAINT `love_ibfk_2` FOREIGN KEY (`IDT`) REFERENCES `TWEET` (`IDT`);

--
-- Constraints for table `MENTION`
--
ALTER TABLE `MENTION`
  ADD CONSTRAINT `mention_ibfk_1` FOREIGN KEY (`IDT`) REFERENCES `TWEET` (`IDT`),
  ADD CONSTRAINT `mention_ibfk_2` FOREIGN KEY (`IDU`) REFERENCES `USER` (`IDU`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
