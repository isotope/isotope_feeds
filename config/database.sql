-- **********************************************************
-- *                                                        *
-- * IMPORTANT NOTE                                         *
-- *                                                        *
-- * Do not import this file manually but use the TYPOlight *
-- * install tool to create and maintain database tables!   *
-- *                                                        *
-- **********************************************************

--
-- Table `tl_iso_config`
--

CREATE TABLE `tl_iso_config` (
  `addFeed` char(1) NOT NULL default '',
  `feedTypes` blob NULL,
  `feedName` varchar(255) NOT NULL default '',
  `feedBase` varchar(255) NOT NULL default '',
  `feedTitle` varchar(255) NOT NULL default '',
  `feedDesc` varchar(255) NOT NULL default '',
  `feedJumpTo` int(10) unsigned NOT NULL default '0',
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



--
-- Table `tl_layout`
--

CREATE TABLE `tl_layout` (
  `productfeeds` blob NULL,
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ------