
#
# Table structure for table 'sys_querybuilder'
#
CREATE TABLE sys_querybuilder (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	affected_table varchar(255) DEFAULT '' NOT NULL,
	queryname varchar(255) DEFAULT '' NOT NULL,
	where_parts text,
	user varchar(255) DEFAULT '' NOT NULL,
	hidden tinyint(1) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(1) unsigned DEFAULT '0' NOT NULL,

	PRIMARY KEY (uid),
);
