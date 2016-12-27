<?php
	// modules/doc/list.php
	if (defined('MAIN_INIT')) {
		// ตรวจสอบโมดูลที่เลือก
		$sql = "SELECT M.`id` AS `module_id`,M.`module`,D.`detail`,D.`keywords`,D.`topic`,D.`description`";
		$sql .= " FROM `".DB_INDEX_DETAIL."` AS D";
		$sql .= " INNER JOIN `".DB_INDEX."` AS I ON I.`id`=D.`id` AND I.`index`='1' AND I.`module_id`=D.`module_id` AND I.`language`=D.`language`";
		$sql .= " INNER JOIN `".DB_MODULES."` AS M ON M.`id`=D.`module_id` AND M.`module`='$module' AND M.`owner`='doc'";
		$sql .= " WHERE D.`language` IN ('".LANGUAGE."', '') LIMIT 1";
		$index = $cache->get($sql);
		if (!$index) {
			$index = $db->customQuery($sql);
			$index = sizeof($index) == 0 ? false : $index[0];
			$cache->save($sql, $index);
		}
		if (!$index) {
			$title = $lng['LNG_DOCUMENT_NOT_FOUND'];
			$content = '<div class=error>'.$title.'</div>';
		} else {
			// breadcrumbs
			$breadcrumb = gcms::loadtemplate('doc', '', 'breadcrumb');
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
			// หมวดที่เลือก
			$cat = gcms::getVars($_REQUEST, 'cat', 0);
			$sql = "SELECT `category_id`,`topic` FROM `".DB_CATEGORY."` WHERE `module_id`='$index[module_id]'";
			if ($cat > 0) {
				$sql .= " AND `category_id`=$cat";
			}
			$sql .= " ORDER BY `category_id` ASC LIMIT 1";
			$category = $cache->get($sql);
			if (!$category) {
				$category = $db->customQuery($sql);
				$cache->save($sql, $category);
			}
			if (sizeof($category) == 1) {
				$index['category'] = gcms::ser2Str($category[0], 'topic');
				$cat = $category[0]['category_id'];
			} else {
				$index['category'] = '';
				$cat = 0;
			}
			$list = array();
			if ($cat > 0) {
				// category
				$breadcrumbs['CATEGORY'] = gcms::breadcrumb('', gcms::getURL($index['module'], '', $cat), $index['category'], $index['category'], $breadcrumb);
				// query
				$sql = "SELECT I.`id`,D.`topic`,I.`alias`,D.`description`";
				$sql .= " FROM `".DB_INDEX."` AS I";
				$sql .= " INNER JOIN `".DB_INDEX_DETAIL."` AS D ON D.`id`=I.`id` AND D.`module_id`=$index[module_id] AND D.`language` IN ('".LANGUAGE."','')";
				$sql.=" WHERE I.`category_id`=$cat AND I.`published`=1";
				$sql .= " ORDER BY I.`create_date` ASC";
				$datas = $cache->get($sql);
				if (!$datas) {
					$datas = $db->customQuery($sql);
					$cache->save($sql, $datas);
				}
				if (sizeof($datas) > 0) {
					// อ่านรายการลงใน $list
					$listitem = gcms::loadtemplate($index['module'], 'doc', 'listitem');
					$patt = array('/{ID}/', '/{URL}/', '/{TOPIC}/', '/{DETAIL}/');
					foreach ($datas AS $item) {
						$replace = array();
						$replace[] = $item['id'];
						if ($config['module_url'] == '1') {
							$replace[] = gcms::getURL($index['module'], $item['alias']);
						} else {
							$replace[] = gcms::getURL($index['module'], '', 0, $item['id']);
						}
						$replace[] = $item['topic'];
						$replace[] = $item['description'];
						$list[] = preg_replace($patt, $replace, $listitem);
					}
				}
			}
			// แสดงผลหน้าเว็บ
			$patt = array('/{BREADCRUMBS}/', '/{LIST}/', '/{TOPIC}/', '/{DETAIL}/', '/{MODULE}/', '/{QID}/', '/{CATID}/');
			$replace = array();
			$replace[] = implode("\n", $breadcrumbs);
			if (sizeof($list) == 0) {
				$replace[] = '<div class=error>{LNG_LIST_EMPTY}</div>';
			} else {
				$replace[] = '<div class="row iconview">'.implode("\n", $list).'</div>';
			}
			$replace[] = $index['category'];
			$replace[] = gcms::showDetail($index['detail'], true, false);
			$replace[] = $index['module'];
			$replace[] = 0;
			$replace[] = $cat;
			$content = preg_replace($patt, $replace, gcms::loadtemplate($index['module'], 'doc', 'list'));
			// title,keywords,description
			$title = $cat > 0 ? $index['category'] : $index['topic'];
			$keywords = $index['keywords'];
			$description = $index['description'];
			// เลือกเมนู
			$menu = empty($install_modules[$index['module']]['alias']) ? $index['module'] : $install_modules[$index['module']]['alias'];
		}
	}