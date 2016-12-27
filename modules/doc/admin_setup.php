<?php
	// modules/doc/admin_setup.php
	if (MAIN_INIT == 'admin' && $isMember) {
		unset($url_query['qid']);
		// โมดูลที่เรียก
		$id = gcms::getVars($_GET, 'id', 0);
		// ตรวจสอบโมดูลที่เรียก
		$sql = "SELECT * FROM `".DB_MODULES."` WHERE `id`=$id AND `owner`='doc' LIMIT 1";
		$index = $db->customQuery($sql);
		$index = sizeof($index) == 1 ? $index[0] : false;
		if ($index) {
			// อ่าน config ของโมดูล
			gcms::r2config($index['config'], $index);
			// ตรวจสอบสถานะที่สามารถเข้าหน้านี้ได้
			if (!gcms::canConfig($index, 'can_write')) {
				$index = false;
			}
		}
		if (!$index) {
			$title = $lng['LNG_DATA_NOT_FOUND'];
			$content[] = '<aside class=error>'.$title.'</aside>';
		} else {
			// ค่าที่ส่งมา
			$q = array();
			// หมวดที่เลือก
			$cat = gcms::getVars($_GET, 'cat', 0);
			// หมวดหมู่
			$categories = array();
			$sql = "SELECT `category_id`,`topic` FROM `".DB_CATEGORY."` WHERE `module_id`='$index[id]' ORDER BY `category_id`";
			foreach ($db->customQuery($sql) AS $item) {
				if ($cat == 0) {
					$cat = $item['category_id'];
				}
				$categories[$item['category_id']] = gcms::ser2Str($item, 'topic');
			}
			if ($cat > 0) {
				$q[] = "P.`category_id`=$cat";
			}
			$q[] = "P.`module_id`='$index[id]'";
			$q[] = "P.`index`='0'";
			$q[] = "D.`language` IN ('".LANGUAGE."','')";
			// default query
			$sql1 = "FROM `".DB_INDEX_DETAIL."` AS D";
			$sql1 .= " INNER JOIN `".DB_INDEX."` AS P ON P.`id`=D.`id` AND P.`module_id`='$index[id]'";
			// ข้อความค้นหา
			$search = preg_replace('/[\+\s]+/u', ' ', $db->sql_trim_str($_GET, 'search', ''));
			if (mb_strlen($search) > 2) {
				$question = addslashes($search);
				$sql1 .= " INNER JOIN `".DB_INDEX_DETAIL."` AS D2 ON D2.`id`=D.`id` AND (D2.`topic` LIKE '%$question%' OR D2.`detail` LIKE '%$question%')";
				$url_query['search'] = urlencode($search);
			}
			$where = " WHERE ".implode(' AND ', $q);
			// จำนวนรายการทั้งหมด
			$sql = "SELECT COUNT(*) AS `count` $sql1 $where";
			$count = $db->customQuery($sql);
			// รายการต่อหน้า
			$list_per_page = gcms::getVars('GET,COOKIE', 'count,doc_listperpage', 30);
			$list_per_page = max(10, $list_per_page);
			// หน้าที่เลือก
			$page = max(1, gcms::getVars($_GET, 'page', 1));
			// ตรวจสอบหน้าที่เลือกสูงสุด
			$totalpage = round($count[0]['count'] / $list_per_page);
			$totalpage += ($totalpage * $list_per_page < $count[0]['count']) ? 1 : 0;
			$page = max(1, $page > $totalpage ? $totalpage : $page);
			$start = $list_per_page * ($page - 1);
			// คำนวณรายการที่แสดง
			$s = $start < 0 ? 0 : $start + 1;
			$e = min($count[0]['count'], $s + $list_per_page - 1);
			$patt2 = array('/{SEARCH}/', '/{COUNT}/', '/{PAGE}/', '/{TOTALPAGE}/', '/{START}/', '/{END}/');
			$replace2 = array($search, $count[0]['count'], $page, $totalpage, $s, $e);
			// save cookie
			setCookie('doc_listperpage', $list_per_page, time() + 3600 * 24 * 365);
			// title
			$m = ucwords($index['module']);
			$title = "$lng[LNG_CREATE]-$lng[LNG_EDIT] $lng[LNG_CONTENTS] $m";
			$a = array();
			$a[] = '<span class=icon-help>{LNG_MODULES}</span>';
			$a[] = '<a href="{URLQUERY?module=doc-config&id='.$index['id'].'}">'.$m.'</a>';
			if ($cat > 0) {
				$a[] = $categories[$cat];
			}
			$a[] = '{LNG_CONTENTS}';
			// แสดงผล
			$content[] = '<div class=breadcrumbs><ul><li>'.implode('</li><li>', $a).'</li></ul></div>';
			$content[] = '<section>';
			$content[] = '<header><h1 class=icon-list>'.$title.'</h1></header>';
			// form
			$content[] = '<form class=table_nav method=get action=index.php>';
			// หมวดหมู่
			$content[] = '<fieldset>';
			$content[] = '<label>{LNG_CATEGORY} <select name=cat>';
			foreach ($categories AS $c => $item) {
				$sel = $cat == $c ? ' selected' : '';
				$content[] = '<option value='.$c.$sel.'>'.$item.'</option>';
			}
			$content[] = '</select></label>';
			$content[] = '</fieldset>';
			// submit
			$content[] = '<fieldset>';
			$content[] = '<input type=submit class="button go" value="{LNG_GO}">';
			$content[] = '</fieldset>';
			// search
			$content[] = '<fieldset class=search>';
			$content[] = '<label accesskey=f><input type=text name=search value="'.$search.'" placeholder="{LNG_SEARCH_TITLE}" title="{LNG_SEARCH_TITLE}"></label>';
			$content[] = '<input type=submit value="&#xE607;" title="{LNG_SEARCH}">';
			$content[] = '<input type=hidden name=module value=doc-setup>';
			$content[] = '<input type=hidden name=page value=1>';
			$content[] = '<input type=hidden name=id value='.$index['id'].'>';
			$content[] = '</fieldset>';
			$content[] = '</form>';
			// ตารางข้อมูล
			$content[] = '<table id=tbl_list class="tbl_list fullwidth">';
			$content[] = '<caption>'.preg_replace($patt2, $replace2, $search != '' ? $lng['SEARCH_RESULT'] : $lng['ALL_ITEMS']).'</caption>';
			$content[] = '<thead>';
			$content[] = '<tr>';
			$content[] = '<th id=c0 scope=col>{LNG_TOPIC}</th>';
			$content[] = '<th id=c1 scope=col class=check-column><a class="checkall icon-uncheck"></a></th>';
			$content[] = '<th id=c2 scope=col colspan=2></th>';
			$content[] = '<th id=c5 scope=col class=tablet>{LNG_CATEGORY}</th>';
			$content[] = '<th id=c8 scope=col class="center tablet">{LNG_LAST_UPDATE}</th>';
			$content[] = '<th id=c9 scope=col class="center tablet">{LNG_VIEWS}</th>';
			$content[] = '<th id=c10 scope=col></th>';
			$content[] = '</tr>';
			$content[] = '</thead>';
			$content[] = '<tbody>';
			if ($count[0]['count'] > 0) {
				// รายการทั้งหมด
				$sql = "SELECT P.`id`,P.`category_id`,P.`published`,P.`last_update`,P.`visited`,D.`topic`";
				$sql .= " $sql1 $where ORDER BY P.`create_date` ASC";
				foreach ($db->customQuery($sql) AS $item) {
					$id = $item['id'];
					$tr = '<tr id=M_'.$id.' class=sort>';
					$tr .= '<th headers=c0 id=r'.$id.' class=topic scope=row><a href="../index.php?module='.$index['module'].'&amp;id='.$id.'" title="{LNG_PREVIEW}" target=_blank>'.$item['topic'].'</a></th>';
					$tr .= '<td headers="r'.$id.' c1" class=check-column><a id=check_'.$id.' class=icon-uncheck></a></td>';
					$tr .= '<td headers="r'.$id.' c2" class=menu><a id=move_'.$id.' title="{LNG_DRAG_MOVE}" class=icon-move></a></td>';
					$tr .= '<td headers="r'.$id.' c2" class=menu><span class="icon-published'.$item['published'].'" title="'.$lng['LNG_PUBLISHEDS'][$item['published']].'"></span></td>';
					$tr .= '<td headers="r'.$id.' c5" class=mobile>';
					if (isset($categories[$item['category_id']])) {
						$tr .= '<a href="{URLQUERY?cat='.$item['category_id'].'}" title="{LNG_SELECT_ITEM}">'.$categories[$item['category_id']].'</a>';
					}
					$tr .= '</td>';
					$tr .= '<td headers="r'.$id.' c8" class="date tablet">'.gcms::mktime2date($item['last_update'], 'd M Y H:i').'</td>';
					$tr .= '<td headers="r'.$id.' c9" class="visited tablet">'.$item['visited'].'</td>';
					$tr .= '<td headers="r'.$id.' c10" class=menu><a href="{URLQUERY?module=doc-write&src=doc-setup&spage='.$page.'&qid='.$item['id'].'}" title="{LNG_EDIT}" class=icon-edit></a></td>';
					$tr .= '</tr>';
					$content[] = $tr;
				}
			}
			$content[] = '</tbody>';
			$content[] = '<tfoot>';
			$content[] = '<tr>';
			$content[] = '<td headers=c0>&nbsp;</td>';
			$content[] = '<td headers=c1 class=check-column><a class="checkall icon-uncheck"></a></td>';
			$content[] = '<td headers=c2 colspan=6></td>';
			$content[] = '</tr>';
			$content[] = '</tfoot>';
			$content[] = '</table>';
			$content[] = '<div class=table_nav>';
			// sel action
			$content[] = '<fieldset>';
			$sel = array();
			$sel[] = '<select id=sel_action>';
			// delete
			$sel[] = '<option value=delete_'.$index['id'].'>{LNG_DELETE}</option>';
			// published
			foreach ($lng['LNG_PUBLISHEDS'] AS $i => $value) {
				$sel[] = '<option value=published_'.$index['id'].'_'.$i.'>'.$value.'</option>';
			}
			$sel[] = '</select>';
			$action = gcms::getVars($_GET, 'action', '');
			$content[] = str_replace('value='.$action.'>', 'value='.$action.' selected>', implode('', $sel));
			$content[] = '<label accesskey=e for=sel_action class="button go" id=btn_action><span>{LNG_SELECT_ACTION}</span></label>';
			$content[] = '</fieldset>';
			// add
			$content[] = '<fieldset>';
			$content[] = '<a class="button add" href="{URLQUERY?module=doc-write&src=doc-setup}"><span class=icon-plus>{LNG_DOCUMENT_WRITE}</span></a>';
			$content[] = '</fieldset>';
			$content[] = '</div>';
			$content[] = '</section>';
			$content[] = '<script>';
			$content[] = 'callAction("btn_action", function() {return $E("sel_action").value}, "tbl_list", "'.WEB_URL.'/modules/doc/admin_action.php");';
			$content[] = 'inintDocWrite("tbl_list", '.$index['id'].');';
			$content[] = '</script>';
			// หน้านี้
			$url_query['module'] = 'doc-setup';
			$url_query['cat'] = $cat;
		}
	} else {
		$title = $lng['LNG_DATA_NOT_FOUND'];
		$content[] = '<aside class=error>'.$title.'</aside>';
	}
