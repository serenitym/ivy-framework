--
-- Table structure for table `auth_users`
--

CREATE TABLE IF NOT EXISTS `auth_users` (
  `uid` int(4) NOT NULL AUTO_INCREMENT COMMENT 'User ID',
  `cid` int(4) DEFAULT NULL COMMENT 'User class, if any (FK)',
  `name` char(20) NOT NULL COMMENT 'User name',
  `active` tinyint(1) NOT NULL COMMENT 'Whether is enabled or not',
  `email` varchar(100) NOT NULL COMMENT 'auth_users',
  `password` text,
  PRIMARY KEY (`uid`),
  KEY `cid` (`cid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `auth_users`
--
ALTER TABLE `auth_users`
  ADD CONSTRAINT `auth_users_ibfk_1` FOREIGN KEY (`cid`) REFERENCES `auth_classes` (`cid`) ON UPDATE CASCADE;



--
-- Table structure for table `auth_user_details`
--

CREATE TABLE IF NOT EXISTS `auth_user_details` (
  `uid` int(4) NOT NULL COMMENT 'User ID (FK)',
  `first_name` varchar(30) NOT NULL COMMENT 'First name',
  `last_name` varchar(50) NOT NULL COMMENT 'Last name',
  `language` char(5) DEFAULT NULL COMMENT 'Language code, i.e. en_US',
  `country` char(4) DEFAULT NULL COMMENT 'Country code, i.e. US',
  `city` varchar(100) DEFAULT NULL COMMENT 'City',
  `title` varchar(70) DEFAULT NULL,
  `bio` text,
  `photo` text,
  `phone` varchar(15) DEFAULT NULL,
  `site` varchar(100) DEFAULT NULL,
  `last_ip` varchar(39) NOT NULL COMMENT 'Last IP used to log in',
  `creation` varchar(100) NOT NULL COMMENT 'When was the user created',
  `last_login` char(32) DEFAULT NULL,
  UNIQUE KEY `uid` (`uid`),
  KEY `sid_details` (`last_login`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
