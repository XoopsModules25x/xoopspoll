
#
# Table structure for table `xoopspoll_option`
#

CREATE TABLE xoopspoll_option (
  option_id int(10) unsigned NOT null auto_increment,
  poll_id mediumint(8) unsigned NOT null default '0',
  option_text varchar(255) NOT null default '',
  option_count smallint(5) unsigned NOT null default '0',
  option_color varchar(25) NOT null default '',
  PRIMARY KEY  (`option_id`),
  KEY `poll_id` (`poll_id`)
) ENGINE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `xoopspoll_desc`
#

CREATE TABLE xoopspoll_desc (
  poll_id mediumint(8) unsigned NOT null auto_increment,
  question varchar(255) NOT null default '',
  description tinytext NOT null,
  user_id int(5) unsigned NOT null default '0',
  start_time int(10) unsigned NOT null default '0',
  end_time int(10) unsigned NOT null default '0',
  votes smallint(5) unsigned NOT null default '0',
  voters smallint(5) unsigned NOT null default '0',
  multiple tinyint(1) unsigned NOT null default '0',
  multilimit tinyint(63) unsigned NOT NULL default '0',
  anonymous tinyint(1) unsigned NOT null default '0',
  display tinyint(1) unsigned NOT null default '0',
  visibility int(3) unsigned NOT null default '0',
  weight smallint(5) unsigned NOT null default '0',
  mail_status tinyint(1) unsigned NOT null default '0',
  mail_voter tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`poll_id`),
  KEY `end_time` (`end_time`),
  KEY `mailer` (`end_time`, `mail_status`),
  KEY `display` (`display`, `start_time`, `end_time`),
  FULLTEXT KEY `question` (`question`, `description`)
) ENGINE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `xoopspoll_log`
#

CREATE TABLE xoopspoll_log (
  log_id int(10) unsigned NOT null auto_increment,
  poll_id mediumint(8) unsigned NOT null default '0',
  option_id int(10) unsigned NOT null default '0',
  ip char(15) NOT null default '',
  user_id int(5) unsigned NOT null default '0',
  time int(10) unsigned NOT null default '0',
  PRIMARY KEY  (`log_id`),
  KEY `poll_id` (`poll_id`),
  KEY `poll_id_user_id` (`poll_id`, `user_id`, `time`),
  KEY `poll_id_ip` (`poll_id`, `ip`, `time`)
) ENGINE=MyISAM;
# --------------------------------------------------------

INSERT INTO xoopspoll_desc VALUES (null, 'What do you think about XOOPS?', 'A simple survey about the content management script used on this site.', 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(DATE_ADD(NOW(), INTERVAL 10 DAY)), 0, 0, 0, 0, 0, 1, 0, 0, 0, 0);
INSERT INTO xoopspoll_option VALUES (1, 1, 'Excellent!', 0, 'aqua.gif');
INSERT INTO xoopspoll_option VALUES (2, 1, 'Cool', 0, 'blue.gif');
INSERT INTO xoopspoll_option VALUES (3, 1, 'Hmm... not bad', 0, 'brown.gif');
INSERT INTO xoopspoll_option VALUES (4, 1, 'What is this?', 0, 'darkgreen.gif');
