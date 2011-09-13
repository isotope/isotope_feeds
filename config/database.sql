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


-- --------------------------------------------------------


--
-- Table `tl_layout`
--

CREATE TABLE `tl_layout` (
  `productfeeds` blob NULL,
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------


--
-- Table `tl_iso_products`
--

CREATE TABLE `tl_iso_products` (
  `gid_condition` varchar(64) NOT NULL default '',
  `gid_availability` varchar(64) NOT NULL default '',
  `gid_brand` varchar(255) NOT NULL default '',
  `gid_gtin` varchar(64) NOT NULL default '',
  `gid_mpn` varchar(64) NOT NULL default '',
  `gid_google_product_category` int(10) unsigned NOT NULL default '0',
  `gid_product_type` blob NULL,
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table `tl_google_taxonomy`
--

CREATE TABLE `tl_google_taxonomy` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `pid` int(11) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `fullname` varchar(255) NOT NULL default '',
  `depth` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `pid` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------