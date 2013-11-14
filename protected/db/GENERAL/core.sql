
CREATE TABLE IF NOT EXISTS `ITEMS` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `type` char(40) NOT NULL,
  `name_ro` text NOT NULL,
  `name_en` text NOT NULL,
  `SEO` text,
  `opt` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `TREE` (
  `Pid` int(3) NOT NULL,
  `Cid` int(3) NOT NULL,
  `poz` int(2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `MENUS` (
  `id` int(3) NOT NULL,
  `idM` int(2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `session_data` (
  `session_id` varchar(32) NOT NULL DEFAULT '',
  `hash` varchar(32) NOT NULL DEFAULT '',
  `session_data` blob NOT NULL,
  `session_expire` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
