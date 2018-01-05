#
# Table structure for table `xoopspoll_option`
#

CREATE TABLE xoopspoll_option (
  option_id    INT(10) UNSIGNED      NOT NULL AUTO_INCREMENT,
  poll_id      MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
  option_text  VARCHAR(255)          NOT NULL DEFAULT '',
  option_count SMALLINT(5) UNSIGNED  NOT NULL DEFAULT '0',
  option_color VARCHAR(25)           NOT NULL DEFAULT '',
  PRIMARY KEY (`option_id`),
  KEY `poll_id` (`poll_id`)
)
  ENGINE = MyISAM;
# --------------------------------------------------------

#
# Table structure for table `xoopspoll_desc`
#

CREATE TABLE xoopspoll_desc (
  poll_id     MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
  question    VARCHAR(255)          NOT NULL DEFAULT '',
  description TINYTEXT              NOT NULL,
  user_id     INT(5) UNSIGNED       NOT NULL DEFAULT '0',
  start_time  INT(10) UNSIGNED      NOT NULL DEFAULT '0',
  end_time    INT(10) UNSIGNED      NOT NULL DEFAULT '0',
  votes       SMALLINT(5) UNSIGNED  NOT NULL DEFAULT '0',
  voters      SMALLINT(5) UNSIGNED  NOT NULL DEFAULT '0',
  multiple    TINYINT(1) UNSIGNED   NOT NULL DEFAULT '0',
  multilimit  TINYINT(63) UNSIGNED  NOT NULL DEFAULT '0',
  anonymous   TINYINT(1) UNSIGNED   NOT NULL DEFAULT '0',
  display     TINYINT(1) UNSIGNED   NOT NULL DEFAULT '0',
  visibility  INT(3) UNSIGNED       NOT NULL DEFAULT '0',
  weight      SMALLINT(5) UNSIGNED  NOT NULL DEFAULT '0',
  mail_status TINYINT(1) UNSIGNED   NOT NULL DEFAULT '0',
  mail_voter  TINYINT(1) UNSIGNED   NOT NULL DEFAULT '0',
  PRIMARY KEY (`poll_id`),
  KEY `end_time` (`end_time`),
  KEY `mailer` (`end_time`, `mail_status`),
  KEY `display` (`display`, `start_time`, `end_time`),
  FULLTEXT KEY `question` (`question`, `description`)
)
  ENGINE = MyISAM;
# --------------------------------------------------------

#
# Table structure for table `xoopspoll_log`
#

CREATE TABLE xoopspoll_log (
  log_id    INT(10) UNSIGNED      NOT NULL AUTO_INCREMENT,
  poll_id   MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
  option_id INT(10) UNSIGNED      NOT NULL DEFAULT '0',
  ip        CHAR(15)              NOT NULL DEFAULT '',
  user_id   INT(5) UNSIGNED       NOT NULL DEFAULT '0',
  time      INT(10) UNSIGNED      NOT NULL DEFAULT '0',
  PRIMARY KEY (`log_id`),
  KEY `poll_id` (`poll_id`),
  KEY `poll_id_user_id` (`poll_id`, `user_id`, `time`),
  KEY `poll_id_ip` (`poll_id`, `ip`, `time`)
)
  ENGINE = MyISAM;
# --------------------------------------------------------

INSERT INTO xoopspoll_desc VALUES (NULL, 'What do you think about XOOPS?', 'A simple survey about the content management script used on this site.', 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(DATE_ADD(NOW(), INTERVAL 10 DAY)), 0, 0, 0, 0, 0, 1, 0, 0, 0, 0);
INSERT INTO xoopspoll_option VALUES (1, 1, 'Excellent!', 0, 'aqua.gif');
INSERT INTO xoopspoll_option VALUES (2, 1, 'Cool', 0, 'blue.gif');
INSERT INTO xoopspoll_option VALUES (3, 1, 'Hmm... not bad', 0, 'brown.gif');
INSERT INTO xoopspoll_option VALUES (4, 1, 'What is this?', 0, 'darkgreen.gif');
