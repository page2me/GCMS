<?php
	// modules/doc/action.php
	header("content-type: text/html; charset=UTF-8");
	// inint
	include '../../bin/inint.php';
	// ตรวจสอบ referer
	if (gcms::isReferer()) {
		if (preg_match('/^category_([0-9]+)_([0-9]+)_([0-9]+)$/', $_POST['id'], $match)) {
			// query ข้อมูล
			$sql = "SELECT I.`id`,I.`module_id`,I.`category_id`,D.`topic`,I.`picture`,D.`description`,D.`detail`,I.`create_date`,I.`last_update`,I.`visited`,I.`visited_today`";
			$sql .= ",I.`comments`,I.`alias`,D.`keywords`,D.`relate`,I.`can_reply`,I.`published`,M.`module`,M.`config`,0 AS `vote`,0 AS `vote_count`";
			$sql .= ",C.`topic` AS `category`,C.`detail` AS `cat_tooltip`,U.`status`,U.`id` AS `member_id`,U.`displayname`,U.`email`";
			$sql .= " FROM `".DB_INDEX."` AS I";
			$sql .= " INNER JOIN `".DB_MODULES."` AS M ON M.`id`=I.`module_id` AND M.`owner`='document'";
			$sql .= " INNER JOIN `".DB_INDEX_DETAIL."` AS D ON D.`id`=I.`id` AND D.`module_id`=I.`module_id` AND D.`language` IN ('".LANGUAGE."','')";
			$sql .= " LEFT JOIN `".DB_USER."` AS U ON U.`id`=I.`member_id`";
			$sql .= " LEFT JOIN `".DB_CATEGORY."` AS C ON C.`category_id`=I.`category_id` AND C.`module_id`=I.`module_id`";
			$sql .=!empty($modules[4]) ? " WHERE I.`alias`='".addslashes($modules[4])."'" : " WHERE I.`id`='$id'";
			$sql .= " AND I.`index`='0' LIMIT 1";
			//
			$sql = "SELECT I.`id`,D.`topic`,D.`detail`,I.`module_id`,M.`module`";
			$sql .= " FROM `".DB_INDEX_DETAIL."` AS D";
			$sql .= " INNER JOIN `".DB_MODULES."` AS M ON M.`id`=D.`module_id` AND M.`owner`='doc'";
			$sql .= " INNER JOIN `".DB_INDEX."` AS I ON I.`id`=D.`id` AND I.`module_id`=D.`module_id` AND I.`language`=D.`language`";
			$sql .= " WHERE I.`category_id`=$match[2] AND I.`module_id`=$match[1] AND I.`index`='0' AND D.`language` IN ('".LANGUAGE."', '')";
			$datas = $cache->get($sql);
			if (!$datas) {
				$datas = $db->customQuery($sql);
				$cache->save($sql, $datas);
			}
			$stories = array();
			$options = array();
			$detail = '';
			foreach ($datas AS $item) {
				$stories[] = '<li><a id=category_'.$match[1].'_'.$match[2].'_'.$item['id'].'>'.$item['topic'].'</a></li>';
				$options[] = '<option value=category_'.$match[1].'_'.$match[2].'_'.$item['id'].'>'.$item['topic'].'</option>';
				if ($item['id'] == $match[3] || ($match[3] == 0 && $detail == '')) {
					if ($match[3] == 0 && $detail == '') {
						$ret['id'] = 'category_'.$match[1].'_'.$match[2].'_'.$item['id'];
					}
					$detail = $item['detail'];
				}
			}
			// แสดงผลหน้าเว็บ
			$patt = array('/{TOPIC}/', '/{DETAIL}/', '/{(LNG_[A-Z0-9_]+)}/e');
			$replace = array();
			$replace[] = $datas['topic'];
			$replace[] = gcms::showDetail($datas['detail'], $canview, false);
			$replace[] = '$lng[$1]';
			$ret['content'] = rawurlencode(preg_replace($patt, $replace, gcms::loadtemplate($datas['module'], 'doc', 'detail')));
			// คืนค่าเป็น JSON
			echo gcms::array2json($ret);
		}
	}
