<?php
// modules/gallery/list.php
if (defined('MAIN_INIT')) {
	$qs = array();
	// อัลบัมที่เลือก
	$id = gcms::getVars($_REQUEST, 'id', 0);
	if ($id > 0) {
		$qs[] = "id=$id";
	}
	// ตรวจสอบโมดูลและอัลบัมที่เลือก
	$sql = "SELECT M.`id` AS `module_id`,M.`module`,C.`topic`,C.`detail`,C.`id`,C.`visited`";
	$sql .= " FROM `".DB_GALLERY_ALBUM."` AS C";
	$sql .= " INNER JOIN `".DB_MODULES."` AS M ON M.`id`=C.`module_id` AND M.`owner`='gallery' ";
	$sql .= " WHERE C.`id`=$id LIMIT 1";
	$index = $cache->get($sql);
	if (!$index) {
		$index = $db->customQuery($sql);
		$cache->save($sql, $index);
	}
	if (sizeof($index) == 1) {
		$index = $index[0];
		// อัปเดทเปิดดู
		$index['visited'] ++;
		$db->edit(DB_GALLERY_ALBUM, $id, array('visited' => $index['visited']));
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
		$canonical = gcms::getURL($index['module'], '', 0, 0, "id=$id");
		if (!empty($index['category'])) {
			// อัลบัม
			$breadcrumbs['CATEGORY'] = gcms::breadcrumb('', $canonical, $index['category'], $index['category'], $breadcrumb);
		}
		// ทั้งหมด
		$sql = "SELECT COUNT(*) AS `count` FROM `".DB_GALLERY."`";
		$sql .= " WHERE `module_id`='$index[module_id]' AND `album_id`='$id'";
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
			$sql = "SELECT * FROM `".DB_GALLERY."`";
			$sql .= " WHERE `module_id`='$index[module_id]' AND `album_id`='$id'";
			$sql .= " ORDER BY `count` ASC LIMIT $start,$list_per_page";
			$list = $cache->get($sql);
			if (!$list) {
				$list = $db->customQuery($sql);
				$cache->save($sql, $list);
			}
			$items = array();
			$patt = array('/{ID}/', '/{SRC}/', '/{URL}/');
			$skin = gcms::loadtemplate($index['module'], 'gallery', 'listitem');
			foreach ($list AS $i => $item) {
				$replace = array();
				$replace[] = $item['id'];
				$replace[] = is_file(DATA_PATH."gallery/$item[album_id]/thumb_$item[image]") ? DATA_URL."gallery/$item[album_id]/thumb_$item[image]" : WEB_URL.'/modules/gallery/img/nopicture.png';
				$replace[] = DATA_URL."gallery/$item[album_id]/$item[image]";
				$items[] = preg_replace($patt, $replace, $skin);
			}
			// URL สำหรับแบ่งหน้า
			$qs[] = 'page=%d';
			$url = '<a href="'.gcms::getURL($index['module'], '', 0, 0, implode('&amp;', $qs)).'">%d</a>';
			// แสดงผล list รายการ
			$patt = array('/{BREADCRUMS}/', '/{LIST}/', '/{TOPIC}/', '/{DETAIL}/', '/{SPLITPAGE}/', '/{COLS}/', '/{ID}/');
			$replace = array();
			$replace[] = implode("\n", $breadcrumbs);
			$replace[] = implode("\n", $items);
			$replace[] = $index['topic'];
			$replace[] = nl2br($index['detail']);
			$replace[] = gcms::pagination($totalpage, $page, $url);
			$replace[] = $config['gallery_cols'];
			$replace[] = $index['id'];
			$content = preg_replace($patt, $replace, gcms::loadtemplate($index['module'], 'gallery', 'list'));
		}
		// title,keywords,description
		$title = $index['topic'];
		$keywords = $index['topic'];
		$description = $index['detail'];
		// เลือกเมนู
		$menu = empty($install_modules[$index['module']]['alias']) ? $index['module'] : $install_modules[$index['module']]['alias'];
	} else {
		$title = $lng['LNG_DATA_NOT_FOUND'];
		$content = '<div class=error>'.$title.'</div>';
	}
}
