--
-- Table structure for table `#__jaamazons3_account`
--

CREATE TABLE IF NOT EXISTS `#__jaamazons3_account` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `acc_label` varchar(255) NOT NULL,
  `acc_name` varchar(100) NOT NULL,
  `acc_accesskey` varchar(100) NOT NULL,
  `acc_secretkey` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__jaamazons3_bucket`
--

CREATE TABLE IF NOT EXISTS `#__jaamazons3_bucket` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `acc_id` int(11) NOT NULL,
  `bucket_name` varchar(255) NOT NULL,
  `bucket_acl` varchar(100) NOT NULL COMMENT 'it is able received following values: public, private or open',
  `bucket_protocol` varchar(20) NOT NULL,
  `bucket_url_format` varchar(20) NOT NULL,
  `bucket_cloudfront_domain` varchar(255) NOT NULL,
  `last_sync` datetime NOT NULL COMMENT 'log a time of last get list file from this bucket',
  PRIMARY KEY (`id`),
  UNIQUE KEY `bucket_name` (`bucket_name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__jaamazons3_disabled`
--

CREATE TABLE IF NOT EXISTS `#__jaamazons3_disabled` (
  `profile_id` int(11) NOT NULL,
  `path` varchar(255) NOT NULL,
  PRIMARY KEY (`profile_id`,`path`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__jaamazons3_file`
--

CREATE TABLE IF NOT EXISTS `#__jaamazons3_file` (
  `bucket_id` int(11) NOT NULL,
  `path` varchar(255) NOT NULL,
  `last_update` datetime NOT NULL,
  `file_checksum` varchar(100) NOT NULL COMMENT 'the md5 sum of file',
  `file_original_checksum` varchar(100) NOT NULL,
  `file_exists` tinyint(1) NOT NULL,
  PRIMARY KEY (`bucket_id`,`path`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__jaamazons3_profile`
--

CREATE TABLE IF NOT EXISTS `#__jaamazons3_profile` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bucket_id` int(11) NOT NULL,
  `profile_name` varchar(255) NOT NULL,
  `allowed_extension` text NOT NULL,
  `site_path` varchar(255) NOT NULL,
  `site_url` text NOT NULL,
  `use_smushit` tinyint(1) NOT NULL,
  `cache_lifetime` int(11) NOT NULL,
  `cron_enable` tinyint(1) NOT NULL,
  `cron_minute` int(11) NOT NULL,
  `cron_hour` int(11) NOT NULL,
  `cron_day` int(11) NOT NULL,
  `cron_last_run` datetime NOT NULL,
  `is_default` tinyint(1) NOT NULL,
  `profile_status` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `#__jaamazons3_profile`
--

INSERT IGNORE INTO `#__jaamazons3_profile` (`id`, `bucket_id`, `profile_name`, `allowed_extension`, `site_path`, `site_url`, `cron_enable`, `cron_minute`, `cron_hour`, `cron_day`, `cron_last_run`, `is_default`, `profile_status`) VALUES
(1, 0, 'Default', 'js,css,jpg,gif,png,bmp,doc,pdf', '{jpath_root}', '{juri_root}', 0, 30, 0, 0, '0000-00-00 00:00:00', 1, 1);


--
-- Dumping data for table `#__jaamazons3_disabled`
--

INSERT IGNORE INTO `#__jaamazons3_disabled` (`profile_id`, `path`) VALUES
(1, 'administrator'),
(1, 'cache'),
(1, 'includes'),
(1, 'language'),
(1, 'libraries'),
(1, 'logs'),
(1, 'media'),
(1, 'resources'),
(1, 'xmlrpc'),
(1, '_installation'),
(2, 'administrator'),
(2, 'components'),
(2, 'images'),
(2, 'includes'),
(2, 'language'),
(2, 'libraries'),
(2, 'logs'),
(2, 'media'),
(2, 'modules'),
(2, 'plugins'),
(2, 'resources'),
(2, 'templates'),
(2, 'xmlrpc'),
(2, '_installation');