<?php
// modules/document/stories.php
if (defined('MAIN_INIT') && is_array($index)) {
	// อ่านจำนวนเรื่องทั้งหมด
	if (!isset($ds) && empty($tag)) {
		// list รายการเรื่องปกติ
		$sqls[] = "D.`module_id`='$index[id]'";
		if ($cat_count > 0) {
			$sqls[] = "I.`category_id` IN ($cat)";
		}
	}
	$sqls[] = "D.`language` IN('".LANGUAGE."','')";
	if (!empty($tag)) {
		// แสดงรายการตาม relate
		$sqls[] = "D.`relate` LIKE '%$tag%'";
	}
	$where = 'WHERE '.implode(' AND ', $sqls);
	// default query
	$sql1 = " FROM `".DB_INDEX_DETAIL."` AS D ";
	$sql1 .= " INNER JOIN `".DB_MODULES."` AS M ON M.`id`=D.`module_id` AND M.`owner`='document'";
	$sql1 .= " INNER JOIN `".DB_INDEX."` AS I ON I.`id`=D.`id` AND I.`module_id`=D.`module_id` AND I.`index`='0' AND I.`published`='1' AND I.`published_date`<='".date('Y-m-d', $mmktime)."'";
	// จำนวนข้อมูลทั้งหมด
	$sql = "SELECT COUNT(*) AS `count` $sql1 $where";
	$count = $cache->get($sql);
	if (!$count) {
		$count = $db->customQuery($sql);
		$count = $count[0];
		$cache->save($sql, $count);
	}
	if ($count['count'] > 0) {
		// หน้าที่เรียก
		$totalpage = round($count['count'] / $index['list_per_page']);
		$totalpage += ($totalpage * $index['list_per_page'] < $count['count']) ? 1 : 0;
		$page = $page > $totalpage ? $totalpage : $page;
		$page = $page < 1 ? 1 : $page;
		$start = $index['list_per_page'] * ($page - 1);
		// เรียงลำดับ
		$sorts = array('I.`last_update` DESC,I.`id` DESC', 'I.`create_date` DESC,I.`id` DESC', 'I.`published_date` DESC,I.`last_update` DESC', 'I.`id` DESC');
		// query
		$sql = "SELECT M.`module`,I.`id`,D.`topic`,I.`alias`,D.`description`,I.`last_update`,I.`create_date`,I.`comment_date`,I.`visited`,I.`comments`,I.`picture`,I.`member_id`,U.`status`,U.`displayname`,U.`email`";
		$sql .= " $sql1 LEFT JOIN `".DB_USER."` AS U ON U.`id`=I.`member_id` $where";
		$sql .= " ORDER BY ".$sorts[$index['sort']]." LIMIT $start,$index[list_per_page]";
		$datas = $cache->get($sql);
		if (!$datas) {
			$datas = $db->customQuery($sql);
			$cache->save($sql, $datas);
		}
		// วันที่สำหรับเครื่องหมาย new
		$valid_date = $mmktime - $index['new_date'];
		// อ่านรายการลงใน $list
		$listitem = gcms::loadtemplate($index['module'], 'document', 'listitem');
		$patt = array('/{ID}/', '/{URL}/', '/{TOPIC}/', '/{DETAIL}/', '/{UID}/', '/{SENDER}/', '/{STATUS}/',
			'/{DATE}/', '/{DATEISO}/', '/{VISITED}/', '/{COMMENTS}/', '/{THUMB}/', '/{ICON}/');
		foreach ($datas AS $item) {
			$replace = array();
			$replace[] = $item['id'];
			if ($config['module_url'] == '1') {
				$replace[] = gcms::getURL($item['module'], $item['alias']);
			} else {
				$replace[] = gcms::getURL($item['module'], '', 0, $item['id']);
			}
			$replace[] = $item['topic'];
			$replace[] = $item['description'];
			$replace[] = $item['member_id'];
			$replace[] = empty($item['displayname']) ? $item['email'] : $item['displayname'];
			$replace[] = $item['status'];
			$replace[] = gcms::mktime2date($item['create_date'], 'd M Y');
			$replace[] = date(DATE_ISO8601, $item['create_date']);
			$replace[] = number_format($item['visited']);
			$replace[] = number_format($item['comments']);
			if (!empty($item['picture']) && is_file(DATA_PATH."document/$item[picture]")) {
				$replace[] = DATA_URL."document/$item[picture]";
			} elseif (!empty($index['icon']) && is_file(DATA_PATH."document/$index[icon]")) {
				$replace[] = DATA_URL."document/$index[icon]";
			} else {
				$replace[] = WEB_URL."/$index[default_icon]";
			}
			if ($item['create_date'] > $valid_date && $item['comment_date'] == 0) {
				$replace[] = ' new';
			} elseif ($item['last_update'] > $valid_date || $item['comment_date'] > $valid_date) {
				$replace[] = ' update';
			} else {
				$replace[] = '';
			}
			$list[] = preg_replace($patt, $replace, $listitem);
		}
		// URL สำหรับ แบ่งหน้า และ canonical
		$c = $cat_count > 0 ? "&amp;cat=$cat" : '';
		if (empty($tag)) {
			$url = '<a href="'.gcms::getURL($index['module'], '', 0, 0, "page=%d$c").'">%d</a>';
			$canonical = gcms::getURL($index['module'], '', 0, 0, "page=$page$c");
		} else {
			$url = '<a href="'.gcms::getURL('tag', $tag, 0, 0, "page=%d$c").'">%d</a>';
			$canonical = gcms::getURL('tag', $tag, 0, 0, "page=$page$c");
		}
		// แบ่งหน้า
		$splitpage = gcms::pagination($totalpage, $page, $url);
	}
}
