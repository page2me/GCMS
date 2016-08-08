<?php
// modules/document/categories.php
if (defined('MAIN_INIT') && is_array($index)) {
	// list รายการหมวดหมู่
	$listitem = gcms::loadtemplate($index['module'], 'document', 'categoryitem');
	$patt = array('/{THUMB}/', '/{URL}/', '/{TOPIC}/', '/{COUNT}/', '/{COMMENTS}/', '/{DETAIL}/');
	$sql = "SELECT * FROM `".DB_CATEGORY."` WHERE `module_id`='$index[id]' AND `published`='1' ORDER BY `category_id` DESC";
	$datas = $cache->get($sql);
	if (!$datas) {
		$datas = $db->customQuery($sql);
		$cache->save($sql, $datas);
	}
	foreach ($datas AS $item) {
		$replace = array();
		$icon = gcms::ser2Str($item, 'icon');
		if ($icon != '' && is_file(DATA_PATH."document/$icon")) {
			$replace[] = DATA_URL."document/$icon";
		} else {
			$replace[] = WEB_URL."/$index[default_icon]";
		}
		$replace[] = gcms::getURL($index['module'], '', $item['category_id']);
		$replace[] = gcms::ser2Str($item, 'topic');
		$replace[] = $item['c1'];
		$replace[] = $item['c2'];
		$replace[] = gcms::ser2Str($item, 'detail');
		$list[] = preg_replace($patt, $replace, $listitem);
	}
	// canonical
	if ($index['module'] != $module_list[0]) {
		$canonical = gcms::getURL($index['module']);
	}
}
