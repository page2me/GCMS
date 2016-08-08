<?php
// load.php
if (defined('MAIN_INIT')) {
	if (is_dir(ROOT_PATH.'admin/install') || (isset($config['maintenance_mode']) && $config['maintenance_mode'] == 1 && !gcms::isAdmin())) {
		$config['admin_skin'] = gcms::getVars($config, 'admin_skin', 'v8');
		// มีโฟลเดอร์ install เข้าสู่โหมดบำรุงรักษา
		$main_patt = array('/{TITLE}/', '/{CONTENT}/', '/{LANGUAGE}/', '/{WEBURL}/', '/{SKIN}/');
		$main_replace = array();
		$main_replace[] = strip_tags($config['web_title']);
		$main_replace[] = empty($lng['MAINTENANCE_DETAIL']) ? '<p style="padding: 20px; text-align: center; font-weight: bold;">ปิดปรับปรุงเว็บไซต์ชั่วคราว กรุณาลองใหม่ในอีกสักครู่...</p>' : $lng['MAINTENANCE_DETAIL'];
		$main_replace[] = LANGUAGE;
		$main_replace[] = WEB_URL;
		$main_replace[] = SKIN;
		echo preg_replace($main_patt, $main_replace, gcms::loadfile(ROOT_PATH."admin/skin/$config[admin_skin]/template.html"));
	} elseif (isset($config['show_intro']) && $config['show_intro'] == 1 && str_replace(array(BASE_PATH, '/'), '', $_SERVER[REQUEST_URI]) == '') {
		$config['admin_skin'] = gcms::getVars($config, 'admin_skin', 'v8');
		// intro page
		$main_patt = array('/{TITLE}/', '/{CONTENT}/', '/{LANGUAGE}/', '/{WEBURL}/', '/{SKIN}/');
		$main_replace = array();
		$main_replace[] = strip_tags($config['web_title']);
		$main_replace[] = $lng['INTRO_PAGE_DETAIL'];
		$main_replace[] = LANGUAGE;
		$main_replace[] = WEB_URL;
		$main_replace[] = SKIN;
		echo preg_replace($main_patt, $main_replace, gcms::loadfile(ROOT_PATH."admin/skin/$config[admin_skin]/template.html"));
	} else {
		// invite
		if (isset($_GET['invite'])) {
			setCookie(PREFIX.'_invite', $_GET['invite'], time() + 3600 * 24 * 30);
		}
		// query จาก URL ที่ส่งมา
		$urls = array();
		foreach ($_GET AS $key => $value) {
			if (!in_array($key, array('action', 'lang', 'f', 'c'))) {
				$urls[$key] = "$key=$value";
			}
		}
		// แอเรย์สำหรับ ส่วนต่างๆ META, CSS และ Javasript, เมนูหลัก, เมนูข้าง
		$meta = array();
		// javascript
		$script = array();
		// เมนูหลัก
		foreach ($lng['MENU_PARENTS'] AS $key => $value) {
			$mainmenu[$key] = array();
		}
		// กรุณาอย่าเอาออก
		$meta['generator'] = '<meta name=generator content="GCMS AJAX CMS design by http://gcms.in.th">';
		$meta['rss'] = '<link rel=alternate type="application/rss+xml" title="'.sprintf($lng['LNG_RSS_MENU'], strip_tags($config['web_title'])).'" href="'.WEB_URL.'/menu.rss">';
		$image_logo = '';
		$image_src = '';
		if (!empty($config['logo']) && is_file(DATA_PATH.'image/'.$config['logo'])) {
			// logo swf ใส่ลงใน #logo เท่านั้น
			$ext = explode('.', $config['logo']);
			$ext = strtolower(end($ext));
			if ($ext == 'swf') {
				$info = gcms::imageInfo(DATA_PATH."image/$config[logo]");
				$script[] = '$G(window).Ready(function(){';
				$script[] = 'if ($E("logo")) {';
				$script[] = "new GMedia('logo_swf', '".DATA_URL."image/$config[logo]', $info[width], $info[height]).write('logo');";
				$script[] = '}';
				$script[] = '});';
			} else {
				$image_src = DATA_URL.'image/'.$config['logo'];
				$image_logo = '<img src="'.$image_src.'" alt="{WEBTITLE}">';
			}
		}
		// canonical
		$canonical = WEB_URL.'/index.php';
		// โมดูลที่เรียกมา
		$module = '';
		if (isset($_REQUEST['module'])) {
			$module = gcms::getVars($_REQUEST, 'module', '');
		} else {
			$request_uri = explode('?', rawurldecode($_SERVER['REQUEST_URI']));
			if (preg_match('/^\/(.*)\.html$/u', str_replace(BASE_PATH, '', $request_uri[0]), $match)) {
				$module = $match[1];
			}
		}
		// โหลดเมนูทั้งหมดเรียงตามลำดับเมนู (รายการแรกคือหน้า Home)
		$sql = "SELECT M.`id` AS `module_id`,M.`module`,M.`owner`,M.`config`,U.`index_id`,U.`parent`,U.`level`,U.`menu_text`,U.`menu_tooltip`,U.`accesskey`,U.`menu_url`,U.`menu_target`,U.`alias`,U.`published`";
		$sql .= ",(CASE U.`parent` WHEN 'MAINMENU' THEN 0 WHEN 'BOTTOMMENU' THEN 1 WHEN 'SIDEMENU' THEN 2 ELSE 3 END ) AS `pos`";
		$sql .= " FROM `".DB_MENUS."` AS U";
		$sql .= " LEFT JOIN `".DB_INDEX."` AS I ON I.`id`=U.`index_id` AND I.`index`='1' AND I.`language` IN ('".LANGUAGE."','')";
		$sql .= " LEFT JOIN `".DB_MODULES."` AS M ON M.`id`=I.`module_id`";
		$sql .= " WHERE U.`language` IN ('".LANGUAGE."','')";
		$sql .= " ORDER BY `pos` ASC,U.`parent` ASC ,U.`menu_order` ASC";
		$menus = $cache->get($sql);
		if (!$menus) {
			$menus = $db->customQuery($sql);
			$cache->save($sql, $menus);
		}
		foreach ($menus AS $item) {
			if (!isset($install_modules[$item['module']]) && $item['module'] != '') {
				$install_modules[$item['module']] = $item;
				$install_owners[$item['owner']][] = $item['module'];
				$module_list[] = $item['module'];
			}
		}
		// โหลดโมดูลทั้งหมดที่ติดตั้ง
		$sql = "SELECT `id` AS `module_id`,`module`,`owner`,`config` FROM `".DB_MODULES."`";
		$_modules = $cache->get($sql);
		if (!$_modules) {
			$_modules = $db->customQuery($sql);
			$cache->save($sql, $_modules);
		}
		foreach ($_modules AS $item) {
			if (!isset($install_modules[$item['module']])) {
				$install_modules[$item['module']] = $item;
				$install_owners[$item['owner']][] = $item['module'];
				$module_list[] = $item['module'];
			}
		}
		// โมดูลที่ติดตั้ง
		$dir = ROOT_PATH.'modules/';
		$f = @opendir($dir);
		if ($f) {
			while (false !== ($text = readdir($f))) {
				if ($text != '.' && $text != '..') {
					if (is_dir($dir.$text)) {
						if (!isset($install_owners[$text])) {
							$install_owners[$text] = array();
						}
						$module_list[] = $text;
					}
				}
			}
			closedir($f);
		}
		// จัดลำดับโมดูลตามเมนู
		foreach ($menus AS $i => $item) {
			if ($item['level'] == 0) {
				$mainmenu[$item['parent']]['toplevel'][$i] = $item;
			} else {
				$mainmenu[$item['parent']][$toplevel[$item['level'] - 1]][$i] = $item;
			}
			$toplevel[$item['level']] = $i;
		}
		// ตรวจสอบโมดูลจาก URL ของเมนูรายการแรกสุด
		if ($module == '') {
			if (!empty($menus[0]['menu_url'])) {
				list($a, $b) = explode('?', $menus[0]['menu_url']);
				if ($b != '') {
					foreach (explode('&amp;', $b) AS $c) {
						list($d, $e) = explode('=', $c);
						$$d = $e;
					}
				}
				if ($module == '' && preg_match('/^(.*\/)?('.implode('|', $module_list).')\.html$/', $a, $match)) {
					$module = $match[2];
				}
			}
		}
		// ไม่มีโมดูลใช้โมดูลแรกสุด
		$module = $module == '' ? $module_list[0] : $module;
		$script[] = 'window.FIRST_MODULE = "'.$module_list[0].'";';
		if (!empty($config['facebook']['appId'])) {
			$script[] = 'inintFacebook("'.$config['facebook']['appId'].'", "'.LANGUAGE.'");';
		}
		// ตรวจสอบโมดูลที่เรียก
		include (ROOT_PATH.'module.php');
		// ตรวจสอบ cron
		if ($config['cron'] == 1) {
			if ((int)@file_get_contents(DATA_PATH.'index.php') != date('d', $mmktime)) {
				$ftp->fwrite(DATA_PATH.'index.php', 'wb', date('d-m-Y H:i:s', $mmktime));
				$cron = true;
			} else {
				$cron = false;
			}
		}
		// โหลด config,ไฟล์ inint, cron ของโมดูลที่ติดตั้ง
		foreach ($install_owners AS $owner => $items) {
			if (is_file(ROOT_PATH."modules/$owner/config.php")) {
				include_once (ROOT_PATH."modules/$owner/config.php");
			}
			if (is_file(ROOT_PATH."modules/$owner/inint.php")) {
				include_once (ROOT_PATH."modules/$owner/inint.php");
			}
			if ($cron && is_file(ROOT_PATH."modules/$owner/cron.php")) {
				include_once (ROOT_PATH."modules/$owner/cron.php");
			}
		}
		// โหลดโมดูล login
		include ROOT_PATH.'modules/member/login.php';
		$mainlogin = $content;
		// login
		$isMember = gcms::isMember();
		// admin
		$isAdmin = gcms::isAdmin();
		// บันทึก counter และ useronline
		include ROOT_PATH.'counter.php';
		include ROOT_PATH.'useronline.php';
		// ค่า title,description และ keyword ของเว็บหลัก
		$title = $config['web_title'];
		$description = $config['web_description'];
		$keywords = $config['web_description'];
		// แสดงผล template หลัก
		$main_patt = array();
		if (!empty($config['google_site_verification'])) {
			$meta['google-site-verification'] = '<meta name=google-site-verification content="'.$config['google_site_verification'].'">';
		}
		if (!empty($config['google_profile'])) {
			$meta['author'] = '<link rel=author href="https://plus.google.com/'.$config['google_profile'].'">';
			$meta['publisher'] = '<link rel=publisher href="https://plus.google.com/'.$config['google_profile'].'">';
		}
		// ตัวแปรหลังจากแสดงผลแล้ว
		$custom_patt = array();
		if (is_file(ROOT_PATH."modules/$modules[2]/index.php")) {
			// เรียกหน้าหลักโมดูล
			include ROOT_PATH."modules/$modules[2]/index.php";
		} else {
			$title = $lng['PAGE_NOT_FOUND'];
			$content = '<div class=error>'.$title.'</div>';
		}
		// เนื้อหา
		$main_patt['/{CONTENT}/'] = $content;
		// ฟอร์ม login
		$main_patt['/{LOGIN}/'] = $mainlogin;
		// เมนู
		foreach ($mainmenu AS $parent => $items) {
			if ($parent != '') {
				$mymenu = '';
				if (isset($items['toplevel'])) {
					foreach ($items['toplevel'] AS $level => $name) {
						if (isset($items[$level]) && sizeof($items[$level]) > 0) {
							$mymenu .= gcms::getMenu($name, true).'<ul>';
							foreach ($items[$level] AS $level2 => $item2) {
								if ($item2['published'] != 0) {
									if (isset($items[$level2]) && sizeof($items[$level2]) > 0) {
										$mymenu .= gcms::getMenu($item2, true).'<ul>';
										foreach ($items[$level2] AS $item3) {
											$mymenu .= gcms::getMenu($item3).'</li>';
										}
										$mymenu .= '</ul></li>';
									} else {
										$mymenu .= gcms::getMenu($item2).'</li>';
									}
								}
							}
							$mymenu .= '</ul></li>';
						} elseif ($name['published'] != 0) {
							$mymenu .= gcms::getMenu($name).'</li>';
						}
					}
				}
				$main_patt['/{'.$parent.'}/'] = $mymenu;
			}
		}
		if ($menu != '' && !empty($main_patt['/{MAINMENU}/'])) {
			// ตรวจสอบเมนูที่เลือก
			$menu = '/class="('.preg_replace('/([\/\-])/', '\\1', preg_quote($menu)).')(.*?)"/';
			if (!preg_match($menu, $main_patt['/{MAINMENU}/'])) {
				// ถ้าไม่มีจะใช้่โมดูลแรกสุด
				$menu = $install_modules[$module_list[0]];
				$menu = empty($menu['alias']) ? $menu['module'] : $menu['alias'];
				$menu = '/class="('.preg_replace('/([\/\-])/u', '\\1', preg_quote($menu)).')(.*?)"/';
			}
			$main_patt['/{MAINMENU}/'] = preg_replace($menu, 'class="\\1 select\\2"', $main_patt['/{MAINMENU}/']);
		}
		// เวอร์ชั่น
		$main_patt['/{VERSION}/'] = VERSION;
		// ข้อความบน title bar
		$main_patt['/{TITLE}/'] = strip_tags($title);
		// ภาษาที่ติดตั้ง
		$languages = array();
		$skin = gcms::loadfile(ROOT_PATH.SKIN.'language.html');
		foreach ($config['languages'] AS $language) {
			$languages[] = preg_replace('/{LNG}/', $language, $skin);
		}
		$script[] = 'changeLanguage("'.implode(',', $config['languages']).'");';
		$main_patt['/{LANGUAGES}/'] = implode('', $languages);
		// ขนาดตัวอักษร
		$main_patt['/{FONTSIZE}/'] = '<a class="font_size small" title="{LNG_CHANGE_FONT_SMALL}">A<sup>-</sup></a><a class="font_size normal" title="{LNG_CHANGE_FONT_NORMAL}">A</a><a class="font_size large" title="{LNG_CHANGE_FONT_LARGE}">A<sup>+</sup></a>';
		// widgets
		$main_patt['/{WIDGET_([A-Z]+)(([\s_])(.*))?}/e'] = OLD_PHP ? 'gcms::getWidgets(array(1=>\'$1\',3=>\'$3\',4=>\'$4\'))' : 'gcms::getWidgets';
		// ภาษา
		$main_patt['/{(LNG_[A-Z0-9_]+)}/e'] = OLD_PHP ? '$lng[\'$1\']' : 'gcms::getLng';
		// logo
		$main_patt['/{LOGO}/'] = $image_logo;
		// meta, keywords และ description
		$meta['description'] = '<meta name=description content="'.$description.'">';
		$meta['keywords'] = '<meta name=keywords content="'.$keywords.'">';
		$meta['canonical'] = '<link rel=canonical href="'.$canonical.'">';
		// image_src
		if (!empty($image_src)) {
			$meta['image_src'] = '<link rel=image_src href="'.$image_src.'">';
			$meta['og:image'] = '<meta property="og:image" content="'.$image_src.'">';
		}
		if (is_file(DATA_PATH.'image/facebook_photo.jpg')) {
			$image_src = DATA_URL.'image/facebook_photo.jpg';
			$meta['facebook_photo'] = '<meta property="og:image" content="'.$image_src.'">';
		}
		if (!empty($config['facebook']['appId'])) {
			$meta['og:app_id'] = '<meta property="fb:app_id" content="'.$config['facebook']['appId'].'">';
			$script[] = 'window.FB_APPID = "'.$config['facebook']['appId'].'";';
		}
		$meta['og:url'] = '<meta property="og:url" content="'.$canonical.'">';
		$meta['og:title'] = '<meta property="og:title" content="'.$title.'">';
		$meta['og:site_name'] = '<meta property="og:site_name" content="'.strip_tags($config['web_title']).'">';
		$meta['og:type'] = '<meta property="og:type" content="article">';
		$main_patt['/{URL}/'] = $canonical;
		$main_patt['/{XURL}/'] = rawurlencode($canonical);
		$main_patt['/{META}/'] = implode("\n", $meta);
		// javascript
		$main_patt['/{SCRIPT}/'] = implode("\n", $script);
		// เวลาประมวลผล
		$main_patt['/{ELAPSED}/'] = sprintf('%.3f', microtime(true) - BEGIN_TIME);
		// จำนวน query
		$main_patt['/{QURIES}/'] = $db->query_count();
		// path ของ tempalate
		$main_patt['/{SKIN}/'] = SKIN;
		// ภาษาที่เลือก
		$main_patt['/{LANGUAGE}/'] = LANGUAGE;
		// URL ของเว็บไซต์
		$main_patt['/{WEBURL}/'] = WEB_URL;
		// URL ของ datas/
		$main_patt['/{DATAURL}/'] = DATA_URL;
		// ชื่อเว็บ
		$main_patt['/{WEBTITLE}/'] = $config['web_title'];
		$main_patt['/{SITENAME}/'] = strip_tags($config['web_title']);
		// คำอธิบายย่อของเว็บ
		$main_patt['/{WEBDESCRIPTION}/'] = $config['web_description'];
		// ตัวแปรหลังจากแสดงผลแล้ว
		$main_patt = array_merge($main_patt, $custom_patt);
		// แสดงผล
		echo gcms::pregReplace(array_keys($main_patt), array_values($main_patt), gcms::loadtemplate('index', '', 'index'));
	}
}
