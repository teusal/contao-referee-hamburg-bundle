-- **********************************************************
-- *                                                        *
-- * IMPORTANT NOTE                                         *
-- *                                                        *
-- * Do not import this file manually but use the TYPOlight *
-- * install tool to create and maintain database tables!   *
-- *                                                        *
-- **********************************************************


-- --------------------------------------------------------

-- 
-- Table `tl_bsa_tauschboerse`
-- 

CREATE TABLE `tl_bsa_tauschboerse` (
	`id` int(10) unsigned NOT NULL auto_increment,
	`tstamp` int(10) unsigned NOT NULL default '0',
	
	`saison` int(10) unsigned NOT NULL default '0',

	`spielkennung` varchar(9) NOT NULL default '',
	`datum` int(11) NULL,
	
	`heimmannschaft` varchar(50) NOT NULL default '',
	`gastmannschaft` varchar(50) NOT NULL default '',
	
	`mannschaftsart` varchar(50) NULL,
	`spielklasse` varchar(50) NULL,
	`spielgebiet` varchar(50) NULL,
	`mannschaftsart` varchar(50) NULL,
	`staffelname` varchar(50) NULL,
	
	`spielstaettenname` varchar(50) NOT NULL default '',

	`verein_abgegeben_id` int(10) unsigned NOT NULL default '0',
	`verein_abgegeben` varchar(20) NOT NULL default '',
	`abgegeben` int(10) unsigned NOT NULL default '0',

	`verein_angenommen` varchar(20) NULL,
	`angenommen` int(10) unsigned NULL,
	
	PRIMARY KEY  (`id`),
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
