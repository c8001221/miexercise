USE temp_db;

CREATE TABLE IF NOT EXISTS `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uuid` varchar(50) NOT NULL DEFAULT '',
  `start_lat` varchar(50) NOT NULL DEFAULT '',
  `start_long` varchar(50) NOT NULL DEFAULT '',
  `end_lat` varchar(50) NOT NULL DEFAULT '',
  `end_long` varchar(50) NOT NULL DEFAULT '',
  `distance` int(50) DEFAULT '0',
  `create_date` datetime DEFAULT NULL,
  `assign_date` datetime DEFAULT NULL,
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  `status` tinyint(4) DEFAULT '0' COMMENT '0: Unassign, 1: taken',
  PRIMARY KEY (`id`),
  KEY `deleted` (`deleted`),
  KEY `uuid` (`uuid`),
  KEY `pickup` (`uuid`,`status`,`deleted`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 ;
