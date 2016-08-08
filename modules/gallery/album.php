<?php
// modules/gallery/album.php
if (defined('MAIN_INIT')) {
	$qs = array();
	// อัลบัมของสมาชิกที่เลือก
	$mid = gcms::getVars($_REQUEST, 'mid', 0);
	if ($mid > 0) {
		$qs[] = "mid=$mid";
	}
	// ตรวจสอบโมดูล
	$sql = "SELECT I.`module_id`,M.`module`,D.`detail`,D.`topic`,D.`description`,D.`keywords`";
	$sql .= " FROM `".DB_INDEX_DETAIL."` AS D";
	$sql .= " INNER JOIN `".DB_INDEX."` AS I ON I.`id`=D.`id` AND I.`module_id`=D.`module_id` AND I.`index`='1' AND I.`language`=D.`language`";
	$sql .= " INNER JOIN `".DB_MODULES."` AS M ON M.`id`=D.`module_id` AND M.`owner`='gallery'";
	$sql .= " WHERE D.`language` IN ('".LANGUAGE."','') LIMIT 1";
	$index = $cache->get($sql);
	if (!$index) {
		$index = $db->customQuery($sql);
		$cache->save($sql, $index);
	}
	if (sizeof($index) == 1) {
		$index = $index[0];
		// breadcrumbs
		$breadcrumb = gcms::loadtemplate($index['module'], '', 'breadcrumb');
		$breadcrumbs = array();
		// หน้าหลัก
		$breadcrumbs['HOME'] = gcms::breadcrumb('icon-home', $canonical, $install_modules[$module_list[0]]['menu_tooltip'], $install_modules[$module_list[0]]['menu_text'], $breadcrumb);
		// โมดูล
		if ($index['module'] != $module_list[0]) {
			if (isset($install_modules[$index['module']]['menu_text'])) {
				$m = $install_modules[$index['module']]['menu_text'];
				$t = $install_modules[$index['module']]['menu_tooltip'];
			} else {
				$m = ucwords($index['module']);
				$t = $m;
			}
			$canonical = gcms::getURL($index['module']);
			$breadcrumbs['MODULE'] = gcms::breadcrumb('', $canonical, $t, $m, $breadcrumb);
		}
		// ทั้งหมด
		$sql = "SELECT COUNT(*) AS `count` FROM `".DB_GALLERY_ALBUM."` AS C";
		$sql .= " WHERE C.`module_id`='$index[module_id]'".($mid > 0 ? " AND C.`member_id`='$mid'" : '');
		// ตรวจสอบข้อมูลจาก cache
		$count = $cache->get($sql);
		if (!$count) {
			$count = $db->customQuery($sql);
			$cache->save($sql, $count);
		}
		if ($count[0]['count'] == 0) {
			$content = '<div class=error>'.$lng['LNG_LIST_EMPTY'].'</div>';
		} else {
			// จำนวนที่ต้องการ
			$list_per_page = $config['gallery_rows'] * $config['gallery_cols'];
			// หน้าที่เรียก
			$page = gcms::getVars($_REQUEST, 'page', 0);
			$totalpage = round($count[0]['count'] / $list_per_page);
			$totalpage += ($totalpage * $list_per_page < $count[0]['count']) ? 1 : 0;
			$page = $page > $totalpage ? $totalpage : $page;
			$page = $page < 1 ? 1 : $page;
			$start = $list_per_page * ($page - 1);
			// query
			$sql = "SELECT `image` FROM `".DB_GALLERY."` WHERE `album_id`=A.`id` AND `module_id`=A.`module_id` ORDER BY `count` LIMIT 1";
			$sql = "SELECT A.`id`,A.`topic`,A.`detail`,A.`count`,A.`visited`,($sql) AS `image` FROM `".DB_GALLERY_ALBUM."` AS A";
			$sql .= " WHERE A.`module_id`='$index[module_id]'".($mid > 0 ? " AND A.`member_id`='$mid'" : '');
			$sql .= " ORDER BY A.`id` DESC LIMIT $start,$list_per_page";
			$list = $cache->get($sql);
			if (!$list) {
				$list = $db->customQuery($sql);
				$cache->save($sql, $list);
			}
			$items = array();
			$patt = array('/{ID}/', '/{SRC}/', '/{URL}/', '/{TOPIC(-([0-9]+))?}/e', '/{DETAIL}/', '/{COUNT}/', '/{VISITED}/');
			$skin = gcms::loadtemplate($index['module'], 'gallery', 'albumitem');
			foreach ($list AS $i => $item) {
				$replace = array();
				$replace[] = $item['id'];
				$replace[] = is_file(DATA_PATH."gallery/$item[id]/thumb_$item[image]") ? DATA_URL."gallery/$item[id]/thumb_$item[image]" : WEB_URL.'/modules/gallery/img/nopicture.png';
				$replace[] = gcms::getURL($index['module'], '', 0, 0, "id=$item[id]");
				$replace[] = create_function('$matches', 'return gcms::cutstring("'.$item['topic'].'",isset($matches[2]) ? (int)$matches[2] : 0);');
				$replace[] = $item['detail'];
				$replace[] = $item['count'];
				$replace[] = $item['visited'];
				$items[] = gcms::pregReplace($patt, $replace, $skin);
			}
			// URL สำหรับแบ่งหน้า
			$qs[] = 'page=%d';
			$url = '<a href="'.gcms::getURL($index['module'], '', 0, 0, implode('&amp;', $qs)).'">%d</a>';
			// แสดงผล list รายการ
			$patt = array('/{BREADCRUMS}/', '/{TOPIC}/', '/{DETAIL}/', '/{LIST}/', '/{SPLITPAGE}/', '/{COLS}/');
			$replace = array();
			$replace[] = implode("\n", $breadcrumbs);
			$replace[] = $index['topic'];
			$replace[] = nl2br($index['detail']);
			$replace[] = implode("\n", $items);
			$replace[] = gcms::pagination($totalpage, $page, $url);
			$replace[] = $config['gallery_cols'];
			$content = preg_replace($patt, $replace, gcms::loadtemplate($index['module'], 'gallery', 'album'));
		}
		// title,keywords,description
		$title = $index['topic'];
		$keywords = $index['keywords'];
		$description = $index['description'];
		// เลือกเมนู
		$menu = $install_modules[$index['module']]['alias'];
		$menu = $menu == '' ? $index['module'] : $menu;
	} else {
		$title = $lng['LNG_DATA_NOT_FOUND'];
		$content = '<div class=error>'.$title.'</div>';
	}
}
