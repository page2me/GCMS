<?php exit;?>
CREATE TABLE IF NOT EXISTS `{prefix}_friends` (`id` int(11) NOT NULL auto_increment,`module_id` int(11) NOT NULL,`member_id` int(11) NOT NULL,`create_date` int(11) unsigned NOT NULL,`pin` tinyint(1) NOT NULL DEFAULT '0',`topic` varchar(255) collate utf8_unicode_ci NOT NULL,`province_id` tinyint(3) unsigned NOT NULL,`ip` varchar(50) collate utf8_unicode_ci NOT NULL,PRIMARY KEY (`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
DELETE FROM `{prefix}_language` WHERE `owner` = 'friends';