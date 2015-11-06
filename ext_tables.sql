#
# Table structure for table 'tx_singlesignon_usermap'
#
CREATE TABLE tx_singlesignon_usermap (
  uid int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
  pid int(11) unsigned DEFAULT '0' NOT NULL,
  tstamp int(11) unsigned DEFAULT '0' NOT NULL,
  crdate int(11) unsigned DEFAULT '0' NOT NULL,
  cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
  deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
  hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
  mapping_id int(11) unsigned DEFAULT '0' NOT NULL,
  fe_uid int(11) unsigned DEFAULT '0' NOT NULL,
  mapping_username tinytext NOT NULL,
  PRIMARY KEY (uid),
  KEY parent (pid)
);


#
# Table structure for table 'tx_singlesignon_properties'
#
CREATE TABLE tx_singlesignon_properties (
  uid int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
  pid int(11) unsigned DEFAULT '0' NOT NULL,
  tstamp int(11) unsigned DEFAULT '0' NOT NULL,
  crdate int(11) unsigned DEFAULT '0' NOT NULL,
  cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
  deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
  hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
  mapping_tablename tinytext NOT NULL,
  mapping_defaultmapping tinytext NOT NULL,
  allowall tinyint(3) unsigned DEFAULT '0' NOT NULL,
  sysfolder_id int(11) DEFAULT '0' NOT NULL,
  PRIMARY KEY (uid),
  KEY parent (pid)
);

#
# Table structure for table 'tx_singlesignon_sessions'
#
CREATE TABLE tx_singlesignon_sessions (
  session_hash varchar(32) DEFAULT '' NOT NULL,
  user_id int(11) unsigned DEFAULT '0' NOT NULL,
  app_id varchar(128) DEFAULT '' NOT NULL,
  data mediumblob,
  timestamp int(11) unsigned DEFAULT '0' NOT NULL,
  PRIMARY KEY (session_hash,user_id,app_id)
) ENGINE=InnoDB;

