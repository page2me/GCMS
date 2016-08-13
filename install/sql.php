DROP TABLE IF EXISTS `{prefix}_board_q`;
CREATE TABLE `{prefix}_board_q` (`id` int(11) NOT NULL auto_increment,`module_id` int(11) NOT NULL,`category_id` int(11) NOT NULL,`sender` varchar(50) collate utf8_unicode_ci NOT NULL,`member_id` int(11) NOT NULL,`email` varchar(255) collate utf8_unicode_ci NOT NULL,`ip` varchar(50) collate utf8_unicode_ci NOT NULL,`create_date` int(11) unsigned NOT NULL,`last_update` int(11) NOT NULL,`visited` smallint(6),`comments` smallint(3),`comment_id` int(11),`commentator` varchar(50) collate utf8_unicode_ci,`commentator_id` int(11),`comment_date` int(11),`picture` text collate utf8_unicode_ci,`pictureW` int(11),`pictureH` int(11),`hassubpic` smallint(3),`can_reply` tinyint(1) unsigned NOT NULL DEFAULT '1',`published` tinyint(1) unsigned NOT NULL DEFAULT '1',`pin` tinyint(1) unsigned NOT NULL DEFAULT '0',`locked` tinyint(1) unsigned NOT NULL DEFAULT '0',`related` varchar(149) collate utf8_unicode_ci,`topic` varchar(64) collate utf8_unicode_ci NOT NULL,`detail` text collate utf8_unicode_ci NOT NULL,PRIMARY KEY (`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
DROP TABLE IF EXISTS `{prefix}_board_r`;
CREATE TABLE `{prefix}_board_r` (`id` int(11) NOT NULL auto_increment,`module_id` int(11) NOT NULL,`index_id` int(11) NOT NULL,`detail` text collate utf8_unicode_ci NOT NULL,`sender` varchar(50) collate utf8_unicode_ci NOT NULL,`member_id` int(11) NOT NULL,`email` varchar(255) collate utf8_unicode_ci NOT NULL,`ip` varchar(50) collate utf8_unicode_ci NOT NULL,`last_update` int(11) NOT NULL,`picture` text collate utf8_unicode_ci,`pictureW` int(11),`pictureH` int(11),PRIMARY KEY (`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
DROP TABLE IF EXISTS `{prefix}_category`;
CREATE TABLE `{prefix}_category` (`id` int(11) unsigned NOT NULL auto_increment,`module_id` int(11) unsigned NOT NULL,`category_id` int(11) unsigned NOT NULL,`group_id` int(11) unsigned NOT NULL,`config` text collate utf8_unicode_ci NOT NULL,`c1` int(11) unsigned NOT NULL,`c2` int(11) unsigned NOT NULL,`topic` text collate utf8_unicode_ci NOT NULL,`detail` text collate utf8_unicode_ci NOT NULL,`icon` text collate utf8_unicode_ci NOT NULL,`published` enum('0','1') collate utf8_unicode_ci NOT NULL DEFAULT '1',PRIMARY KEY (`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
DROP TABLE IF EXISTS `{prefix}_comment`;
CREATE TABLE `{prefix}_comment` (`id` int(11) NOT NULL auto_increment,`module_id` int(11) NOT NULL,`index_id` int(11) NOT NULL,`detail` text collate utf8_unicode_ci NOT NULL,`sender` varchar(50) collate utf8_unicode_ci NOT NULL,`member_id` int(11) NOT NULL,`email` varchar(255) collate utf8_unicode_ci NOT NULL,`ip` varchar(50) collate utf8_unicode_ci NOT NULL,`last_update` int(11) NOT NULL,`picture` text collate utf8_unicode_ci,`pictureW` int(11),`pictureH` int(11),PRIMARY KEY (`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
DROP TABLE IF EXISTS `{prefix}_counter`;
CREATE TABLE `{prefix}_counter` (`id` int(11) NOT NULL auto_increment,`counter` int(11) NOT NULL,`visited` int(11) NOT NULL,`pages_view` int(11) NOT NULL,`time` int(11) NOT NULL,`date` date NOT NULL,PRIMARY KEY (`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
DROP TABLE IF EXISTS `{prefix}_download`;
CREATE TABLE `{prefix}_download` (`id` int(11) unsigned NOT NULL auto_increment,`module_id` int(11) NOT NULL,`category_id` int(11) unsigned,`member_id` int(11) unsigned NOT NULL,`detail` varchar(200) collate utf8_general_ci NOT NULL,`last_update` int(11) unsigned NOT NULL,`name` varchar(50) collate utf8_general_ci NOT NULL,`ext` varchar(5) collate utf8_general_ci NOT NULL,`size` int(50) unsigned NOT NULL,`file` varchar(255) collate utf8_general_ci NOT NULL,`downloads` int(11) unsigned NOT NULL,PRIMARY KEY (`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
DROP TABLE IF EXISTS `{prefix}_edocument`;
CREATE TABLE `{prefix}_edocument` (`id` int(11) unsigned NOT NULL auto_increment,`module_id` int(11) unsigned NOT NULL,`sender_id` int(11) unsigned NOT NULL,`reciever` text collate utf8_unicode_ci NOT NULL,`last_update` int(11) unsigned NOT NULL,`downloads` int(11) unsigned NOT NULL,`document_no` varchar(20) collate utf8_unicode_ci NOT NULL,`detail` text collate utf8_unicode_ci NOT NULL,`topic` varchar(50) collate utf8_unicode_ci NOT NULL,`ext` varchar(4) collate utf8_unicode_ci NOT NULL,`size` double unsigned NOT NULL,`file` varchar(15) collate utf8_unicode_ci NOT NULL,`ip` varchar(50) collate utf8_unicode_ci,PRIMARY KEY (`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
DROP TABLE IF EXISTS `{prefix}_edocument_download`;
CREATE TABLE `{prefix}_edocument_download` (`id` int(10) unsigned NOT NULL auto_increment,`module_id` int(10) unsigned NOT NULL,`document_id` int(10) unsigned NOT NULL,`member_id` int(10) unsigned NOT NULL,`downloads` int(10) unsigned NOT NULL,`last_update` int(10) unsigned NOT NULL,PRIMARY KEY (`id`)) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
DROP TABLE IF EXISTS `{prefix}_emailtemplate`;
CREATE TABLE `{prefix}_emailtemplate` (`id` int(10) unsigned NOT NULL auto_increment,`module` varchar(20) collate utf8_unicode_ci NOT NULL,`email_id` int(10) unsigned NOT NULL,`language` varchar(2) collate utf8_unicode_ci NOT NULL,`from_email` text collate utf8_unicode_ci NOT NULL,`copy_to` text collate utf8_unicode_ci NOT NULL,`name` text collate utf8_unicode_ci NOT NULL,`subject` text collate utf8_unicode_ci NOT NULL,`detail` text collate utf8_unicode_ci NOT NULL,`last_update` int(11) unsigned NOT NULL,`last_send` datetime NOT NULL,PRIMARY KEY (`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
DROP TABLE IF EXISTS `{prefix}_eventcalendar`;
CREATE TABLE `{prefix}_eventcalendar` (`id` int(11) unsigned NOT NULL auto_increment,`module_id` int(11) unsigned NOT NULL,`topic` varchar(64) collate utf8_unicode_ci NOT NULL,`detail` text collate utf8_unicode_ci NOT NULL,`description` varchar(149) collate utf8_unicode_ci NOT NULL,`keywords` varchar(149) collate utf8_unicode_ci NOT NULL,`member_id` int(11) unsigned NOT NULL,`create_date` datetime NOT NULL,`last_update` int(11) unsigned NOT NULL,`begin_date` datetime NOT NULL,`end_date` datetime NOT NULL,`color` varchar(7) collate utf8_unicode_ci NOT NULL,`published` tinyint(1) unsigned NOT NULL,`published_date` date NOT NULL,PRIMARY KEY (`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
DROP TABLE IF EXISTS `{prefix}_gallery`;
CREATE TABLE `{prefix}_gallery` (`id` int(11) unsigned NOT NULL auto_increment,`module_id` int(11) unsigned NOT NULL,`album_id` int(11) unsigned NOT NULL,`image` varchar(15) collate utf8_unicode_ci NOT NULL,`last_update` int(11) unsigned NOT NULL,`count` int(11) unsigned NOT NULL,PRIMARY KEY (`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
DROP TABLE IF EXISTS `{prefix}_gallery_album`;
CREATE TABLE `{prefix}_gallery_album` (`id` int(11) unsigned NOT NULL auto_increment,`module_id` int(11) unsigned NOT NULL,`topic` varchar(64) collate utf8_unicode_ci NOT NULL,`detail` varchar(200) collate utf8_unicode_ci NOT NULL,`last_update` int(11) unsigned NOT NULL,`count` int(11) unsigned NOT NULL,`visited` int(11) unsigned NOT NULL,PRIMARY KEY (`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
DROP TABLE IF EXISTS `{prefix}_index`;
CREATE TABLE `{prefix}_index` (`id` int(11) unsigned NOT NULL auto_increment,`index` tinyint(1) unsigned NOT NULL DEFAULT '0',`module_id` int(11) unsigned NOT NULL,`category_id` int(11) unsigned,`language` varchar(2) collate utf8_unicode_ci,`sender` varchar(50) collate utf8_unicode_ci NOT NULL,`member_id` int(11) unsigned NOT NULL,`email` varchar(255) collate utf8_unicode_ci NOT NULL,`ip` varchar(50) collate utf8_unicode_ci NOT NULL,`create_date` int(11) unsigned NOT NULL,`last_update` int(11) unsigned NOT NULL,`visited` int(11) unsigned,`visited_today` int(11) unsigned,`comments` smallint(3) unsigned,`comment_id` int(11) unsigned,`commentator` varchar(50) collate utf8_unicode_ci,`commentator_id` int(11),`comment_date` int(11),`picture` text collate utf8_unicode_ci,`pictureW` int(11),`pictureH` int(11),`hassubpic` smallint(3),`can_reply` tinyint(1) unsigned NOT NULL DEFAULT '0',`show_news` text collate utf8_unicode_ci,`published` tinyint(1) unsigned NOT NULL DEFAULT '1',`pin` tinyint(1) unsigned NOT NULL DEFAULT '0',`locked` tinyint(1) unsigned NOT NULL DEFAULT '0',`published_date` date NOT NULL,`alias` varchar(64) collate utf8_unicode_ci,PRIMARY KEY (`id`,`module_id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
DROP TABLE IF EXISTS `{prefix}_index_detail`;
CREATE TABLE `{prefix}_index_detail` (`id` int(11) unsigned NOT NULL,`module_id` int(11) unsigned NOT NULL,`language` varchar(2) collate utf8_unicode_ci NOT NULL,`topic` varchar(255) collate utf8_unicode_ci NOT NULL,`description` varchar(255) collate utf8_unicode_ci NOT NULL,`detail` text collate utf8_unicode_ci NOT NULL,`keywords` varchar(255) collate utf8_unicode_ci NOT NULL,`relate` varchar(255) collate utf8_unicode_ci NOT NULL,PRIMARY KEY (`id`,`module_id`,`language`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
DROP TABLE IF EXISTS `{prefix}_menus`;
CREATE TABLE `{prefix}_menus` (`id` int(11) unsigned NOT NULL auto_increment,`index_id` int(11) unsigned NOT NULL,`parent` varchar(20) collate utf8_unicode_ci NOT NULL,`level` smallint(2) unsigned NOT NULL,`language` varchar(2) collate utf8_unicode_ci NOT NULL,`menu_text` varchar(100) collate utf8_unicode_ci NOT NULL,`menu_tooltip` varchar(100) collate utf8_unicode_ci NOT NULL,`accesskey` varchar(1) collate utf8_unicode_ci NOT NULL,`menu_order` int(11) unsigned NOT NULL,`menu_url` varchar(255) collate utf8_unicode_ci NOT NULL,`menu_target` varchar(6) collate utf8_unicode_ci NOT NULL,`alias` varchar(20) collate utf8_unicode_ci NOT NULL,`published` enum('0','1','2','3') collate utf8_unicode_ci NOT NULL DEFAULT '1',PRIMARY KEY (`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
DROP TABLE IF EXISTS `{prefix}_modules`;
CREATE TABLE `{prefix}_modules` (`id` int(11) unsigned NOT NULL auto_increment,`owner` varchar(20) collate utf8_unicode_ci NOT NULL,`module` varchar(64) collate utf8_unicode_ci NOT NULL,`config` text collate utf8_unicode_ci,PRIMARY KEY (`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
DROP TABLE IF EXISTS `{prefix}_personnel`;
CREATE TABLE `{prefix}_personnel` (`id` int(11) unsigned NOT NULL auto_increment,`module_id` int(11) unsigned NOT NULL,`category_id` int(11) unsigned NOT NULL,`name` varchar(50) collate utf8_general_ci NOT NULL,`position` varchar(100) collate utf8_general_ci NOT NULL,`detail` varchar(255) collate utf8_general_ci NOT NULL,`address` varchar(255) collate utf8_general_ci NOT NULL,`phone` varchar(20) collate utf8_general_ci NOT NULL,`email` varchar(255) collate utf8_general_ci NOT NULL,`picture` varchar(15) collate utf8_general_ci NOT NULL,`order` tinyint(2) unsigned NOT NULL,PRIMARY KEY (`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
DROP TABLE IF EXISTS `{prefix}_tags`;
CREATE TABLE `{prefix}_tags` (`id` int(11) NOT NULL auto_increment,`tag` text collate utf8_unicode_ci NOT NULL,`count` int(11) NOT NULL,PRIMARY KEY (`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
DROP TABLE IF EXISTS `{prefix}_textlink`;
CREATE TABLE `{prefix}_textlink` (`id` int(11) NOT NULL auto_increment,`text` text collate utf8_unicode_ci NOT NULL,`url` text collate utf8_unicode_ci NOT NULL,`publish_start` int(11) NOT NULL,`publish_end` int(11) NOT NULL,`logo` text collate utf8_unicode_ci NOT NULL,`width` int(11) NOT NULL,`height` int(11) NOT NULL,`type` varchar(11) collate utf8_unicode_ci NOT NULL,`name` varchar(11) collate utf8_unicode_ci NOT NULL,`published` smallint(1) NOT NULL DEFAULT '1',`link_order` smallint(2) NOT NULL,`last_preview` int(11) unsigned,`description` varchar(49) collate utf8_unicode_ci NOT NULL,`template` text collate utf8_unicode_ci,`target` varchar(6) collate utf8_unicode_ci NOT NULL,PRIMARY KEY (`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
DROP TABLE IF EXISTS `{prefix}_user`;
CREATE TABLE `{prefix}_user` (`id` int(11) unsigned NOT NULL auto_increment,`password` varchar(32) collate utf8_unicode_ci NOT NULL,`pname` varchar(50) collate utf8_unicode_ci NOT NULL,`fname` varchar(50) collate utf8_unicode_ci,`lname` varchar(50) collate utf8_unicode_ci,`displayname` varchar(50) collate utf8_unicode_ci,`sex` varchar(1) collate utf8_unicode_ci,`email` varchar(255) collate utf8_unicode_ci NOT NULL,`idcard` varchar(13) collate utf8_unicode_ci,`birthday` date,`website` varchar(255) collate utf8_unicode_ci,`company` varchar(64) collate utf8_unicode_ci,`icon` varchar(24) collate utf8_unicode_ci,`create_date` int(11) unsigned NOT NULL,`visited` int(11) unsigned,`lastvisited` int(11) unsigned,`ip` varchar(50) collate utf8_unicode_ci,`ban` int(11) NOT NULL DEFAULT '0',`point` int(11),`post` int(11) unsigned,`reply` int(11) unsigned,`address1` varchar(64) collate utf8_unicode_ci,`address2` varchar(64) collate utf8_unicode_ci,`provinceID` smallint(3) unsigned,`province` varchar(64) collate utf8_unicode_ci,`zipcode` varchar(5) collate utf8_unicode_ci,`country` varchar(2) collate utf8_unicode_ci,`phone1` varchar(20) collate utf8_unicode_ci,`phone2` varchar(20) collate utf8_unicode_ci,`activatecode` varchar(32) collate utf8_unicode_ci NOT NULL,`status` tinyint(1) unsigned NOT NULL,`invite_id` int(11) unsigned,`subscrib` enum('1','0') collate utf8_unicode_ci NOT NULL DEFAULT '1',`fb` enum('0','1') collate utf8_unicode_ci NOT NULL DEFAULT '0',`session_id` varchar(32) collate utf8_unicode_ci,`admin_access` enum('0','1') collate utf8_unicode_ci NOT NULL DEFAULT '0',PRIMARY KEY (`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
DROP TABLE IF EXISTS `{prefix}_useronline`;
CREATE TABLE `{prefix}_useronline` (`id` int(11) NOT NULL auto_increment,`member_id` int(11) NOT NULL,`displayname` text collate utf8_unicode_ci NOT NULL,`icon` text collate utf8_unicode_ci NOT NULL,`time` int(11) NOT NULL,`session` varchar(32) collate utf8_unicode_ci NOT NULL,`ip` varchar(50) collate utf8_unicode_ci NOT NULL,PRIMARY KEY (`id`,`session`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
DROP TABLE IF EXISTS `{prefix}_video`;
CREATE TABLE `{prefix}_video` (`id` int(11) unsigned NOT NULL auto_increment,`module_id` int(11) unsigned NOT NULL,`youtube` varchar(11) collate utf8_unicode_ci NOT NULL,`topic` text collate utf8_unicode_ci NOT NULL,`description` text collate utf8_unicode_ci NOT NULL,`views` int(11) unsigned NOT NULL,`last_update` int(11) unsigned NOT NULL,PRIMARY KEY (`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
INSERT INTO `{prefix}_emailtemplate` (`module`, `email_id`, `language`, `from_email`, `copy_to`, `name`, `subject`, `detail`, `last_update`, `last_send`) VALUES ('member','1','th','','','ตอบรับการสมัครสมาชิกใหม่ (ยืนยันสมาชิก)','ตอบรับการสมัครสมาชิก %WEBTITLE%','<div style="padding: 10px; background-color: rgb(247, 247, 247);">\r\n<table style="border-collapse: collapse;">\r\n	<tbody>\r\n		<tr>\r\n			<th style="border-width: 1px; border-style: none solid; border-color: rgb(59, 89, 152); padding: 5px; text-align: left; color: rgb(255, 255, 255); font-family: tahoma; font-size: 9pt; background-color: rgb(59, 89, 152);">ยินดีต้อนรับสมาชิกใหม่ %WEBTITLE%</th>\r\n		</tr>\r\n		<tr>\r\n			<td style="border-width: 1px; border-style: none solid solid; border-color: rgb(204, 204, 204) rgb(204, 204, 204) rgb(59, 89, 152); padding: 15px; line-height: 1.8em; font-family: tahoma; font-size: 9pt;">ขอขอบคุณสำหรับการลงทะเบียนกับเรา บัญชีใหม่ของคุณได้รับการติดตั้งเรียบร้อยแล้วและคุณสามารถเข้าระบบได้โดยใช้รายละเอียดด้านล่างนี้<br>\r\n			<br>\r\n			ชื่อสมาชิก : <strong>%EMAIL%</strong><br>\r\n			รหัสผ่าน&nbsp; : <strong>%PASSWORD%</strong><br>\r\n			<br>\r\n			ก่อนอื่นคุณต้องกลับไปยืนยันการสมัครสมาชิกที่ <a href="%WEBURL%index.php?module=activate&amp;id=%ID%" rel="nofollow">%WEBURL%index.php?module=activate&amp;id=%ID%</a></td>\r\n		</tr>\r\n		<tr>\r\n			<td style="padding: 15px; color: rgb(153, 153, 153); font-family: tahoma; font-size: 8pt;">ด้วยความขอบคุณ <a href="mailto:%ADMINEMAIL%" rel="nofollow">เว็บมาสเตอร์</a></td>\r\n		</tr>\r\n	</tbody>\r\n</table>\r\n</div>','1470502166','0000-00-00 00:00:00');
INSERT INTO `{prefix}_emailtemplate` (`module`, `email_id`, `language`, `from_email`, `copy_to`, `name`, `subject`, `detail`, `last_update`, `last_send`) VALUES ('member','2','th','','','ตอบรับการสมัครสมาชิกใหม่ (ไม่ต้องยืนยันสมาชิก)','ตอบรับการสมัครสมาชิก %WEBTITLE%','<div style="padding: 10px; background-color: rgb(247, 247, 247);">\r\n<table style="border-collapse: collapse;">\r\n	<tbody>\r\n		<tr>\r\n			<th style="border-width: 1px; border-style: none solid; border-color: rgb(59, 89, 152); padding: 5px; text-align: left; color: rgb(255, 255, 255); font-family: tahoma; font-size: 9pt; background-color: rgb(59, 89, 152);">ยินดีต้อนรับสมาชิกใหม่ %WEBTITLE%</th>\r\n		</tr>\r\n		<tr>\r\n			<td style="border-width: 1px; border-style: none solid solid; border-color: rgb(204, 204, 204) rgb(204, 204, 204) rgb(59, 89, 152); padding: 15px; line-height: 1.8em; font-family: tahoma; font-size: 9pt;">ขอขอบคุณสำหรับการลงทะเบียนกับเรา บัญชีใหม่ของคุณได้รับการติดตั้งเรียบร้อยแล้วและคุณสามารถเข้าระบบได้โดยใช้รายละเอียดด้านล่างนี้<br />\r\n			<br />\r\n			ชื่อสมาชิก : <strong>%EMAIL%</strong><br />\r\n			รหัสผ่าน&nbsp; : <strong>%PASSWORD%</strong><br />\r\n			<br />\r\n			คุณสามารถกลับไปเข้าระบบได้ที่ <a href="%WEBURL%">%WEBURL%</a></td>\r\n		</tr>\r\n		<tr>\r\n			<td style="padding: 15px; color: rgb(153, 153, 153); font-family: tahoma; font-size: 8pt;">ด้วยความขอบคุณ <a href="mailto:%ADMINEMAIL%">เว็บมาสเตอร์</a></td>\r\n		</tr>\r\n	</tbody>\r\n</table>\r\n</div>\r\n','0','0000-00-00 00:00:00');
INSERT INTO `{prefix}_emailtemplate` (`module`, `email_id`, `language`, `from_email`, `copy_to`, `name`, `subject`, `detail`, `last_update`, `last_send`) VALUES ('member','3','th','','','ขอรหัสผ่านใหม่','รหัสผ่านของคุณใน %WEBTITLE%','<div style="padding: 10px; background-color: rgb(247, 247, 247);">\r\n<table style="border-collapse: collapse;">\r\n	<tbody>\r\n		<tr>\r\n			<th style="border-width: 1px; border-style: none solid; border-color: rgb(59, 89, 152); padding: 5px; text-align: left; color: rgb(255, 255, 255); font-family: tahoma; font-size: 9pt; background-color: rgb(59, 89, 152);">รหัสผ่านของคุณใน %WEBTITLE%</th>\r\n		</tr>\r\n		<tr>\r\n			<td style="border-width: 1px; border-style: none solid solid; border-color: rgb(204, 204, 204) rgb(204, 204, 204) rgb(59, 89, 152); padding: 15px; line-height: 1.8em; font-family: tahoma; font-size: 9pt;">รหัสผ่านใหม่ของคุณถูกส่งมาจากระบบอัตโนมัติ เมื่อ %TIME%<br />\r\n			ไม่ว่าคุณจะได้ทำการขอรหัสผ่านใหม่หรือไม่ก็ตาม โปรดใช้รหัสผ่านใหม่นี้กับบัญชีของคุณ<br />\r\n			(ถ้าคุณไม่ได้ดำเนินการนี้ด้วยตัวเอง อาจมีผู้พยายามเข้าไปเปลี่ยนแปลงข้อมูลส่วนตัวของคุณ)<br />\r\n			<br />\r\n			ชื่อผู้ใช้ : <strong>%EMAIL%</strong><br />\r\n			รหัสผ่าน : <strong>%PASSWORD%</strong><br />\r\n			<br />\r\n			คุณสามารถกลับไปเข้าระบบและแก้ไขข้อมูลส่วนตัวของคุณใหม่ได้ที่ <a href="%WEBURL%">%WEBURL%</a></td>\r\n		</tr>\r\n		<tr>\r\n			<td style="padding: 15px; color: rgb(153, 153, 153); font-family: tahoma; font-size: 8pt;">ด้วยความขอบคุณ <a href="mailto:%ADMINEMAIL%">เว็บมาสเตอร์</a></td>\r\n		</tr>\r\n	</tbody>\r\n</table>\r\n</div>\r\n','0','0000-00-00 00:00:00');
INSERT INTO `{prefix}_tags` (`id`, `tag`, `count`) VALUES ('1','GCMS','1');
INSERT INTO `{prefix}_textlink` (`id`, `text`, `url`, `publish_start`, `publish_end`, `logo`, `width`, `height`, `type`, `name`, `published`, `link_order`, `last_preview`, `description`, `template`, `target`) VALUES ('1','Goragod.com','http://www.goragod.com/','0','0','','0','0','list','list','1','0','0','','','');
