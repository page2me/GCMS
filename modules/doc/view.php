<?php
	// modules/doc/view.php
	if (defined('MAIN_INIT')) {
		// ค่า ที่ส่งมา
		$id = gcms::getVars($_REQUEST, 'id', 0);
		// query ข้อมูล
		$sql = "SELECT I.`id`,I.`module_id`,I.`category_id`,D.`topic`,D.`description`,D.`detail`";
		$sql .= ",C.`topic` AS `category`,I.`alias`,D.`keywords`,I.`visited`,I.`visited_today`,M.`module`";
		$sql .= " FROM `".DB_INDEX."` AS I";
		$sql .= " INNER JOIN `".DB_MODULES."` AS M ON M.`id`=I.`module_id` AND M.`owner`='doc'";
		$sql .= " INNER JOIN `".DB_INDEX_DETAIL."` AS D ON D.`id`=I.`id` AND D.`module_id`=I.`module_id` AND D.`language` IN ('".LANGUAGE."','')";
		$sql .= " INNER JOIN `".DB_CATEGORY."` AS C ON C.`category_id`=I.`category_id` AND C.`module_id`=I.`module_id`";
		$sql .=!empty($modules[4]) ? " WHERE I.`alias`='".addslashes($modules[4])."'" : " WHERE I.`id`='$id'";
		$sql .= " AND I.`index`='0' LIMIT 1";
		if (isset($_REQUEST['visited'])) {
			// มาจากการ post ไม่ต้องโหลดจากแคช
			$index = $db->customQuery($sql);
			$index = sizeof($index) == 0 ? false : $index[0];
		} else {
			$index = $cache->get($sql);
			if (!$index) {
				$index = $db->customQuery($sql);
				$index = sizeof($index) == 0 ? false : $index[0];
			}
		}
		if (!$index) {
			$title = $lng['PAGE_NOT_FOUND'];
			$content = '<div class=error>'.$title.'</div>';
		} else {

			// อัปเดทการเปิดดู
			if (!isset($_REQUEST['visited'])) {
				$index['visited'] ++;
				$index['visited_today'] ++;
				$db->edit(DB_INDEX, $index['id'], array('visited' => $index['visited'], 'visited_today' => $index['visited_today']));
			}
			// บันทึก cache หลังจากอัปเดทการเปิดดูแล้ว
			$cache->save($sql, $index);
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
			// category
			$index['category'] = gcms::ser2Str($index, 'category');
			$breadcrumbs['CATEGORY'] = gcms::breadcrumb('', gcms::getURL($index['module'], '', $index['category_id']), $index['category'], $index['category'], $breadcrumb);
			// url ของหน้านี้
			if ($config['module_url'] == '1') {
				$canonical = gcms::getURL($index['module'], $index['alias']);
			} else {
				$canonical = gcms::getURL($index['module'], '', 0, $index['id']);
			}
			// current item
			$breadcrumbs['TOPIC'] = gcms::breadcrumb('', $canonical, $index['topic'], $index['topic'], $breadcrumb);
			// แทนที่ลงใน template ของโมดูล
			$patt = array('/{BREADCRUMBS}/', '/{LIST}/', '/{TOPIC}/', '/{DETAIL}/', '/{MODULE}/', '/{QID}/', '/{CATID}/');
			$replace = array();
			$replace[] = implode("\n", $breadcrumbs);
			$replace[] = gcms::showDetail($index['detail'], true, false);
			$replace[] = $index['topic'];
			$replace[] = '';
			$replace[] = $index['module'];
			$replace[] = $index['id'];
			$replace[] = $index['category_id'];
			$content = preg_replace($patt, $replace, gcms::loadtemplate($index['module'], 'doc', 'list'));
			// title,keywords,description
			$title = $index['topic'];
			$keywords = $index['keywords'];
			$description = $index['description'];
			// เลือกเมนู
			$menu = empty($install_modules[$index['module']]['alias']) ? $index['module'] : $install_modules[$index['module']]['alias'];
		}
	}
