<?php
// modules/document/admin_setup.php
if (MAIN_INIT == 'admin' && $isMember) {
	unset($url_query['qid']);
	// โมดูลที่เรียก
	$id = gcms::getVars($_GET, 'id', 0);
	// ตรวจสอบโมดูลที่เรียก
	$sql = "SELECT * FROM `".DB_MODULES."` WHERE `id`=$id AND `owner`='document' LIMIT 1";
	$index = $db->customQuery($sql);
	$index = sizeof($index) == 1 ? $index[0] : false;
	if ($index) {
		// อ่าน config ของโมดูล
		gcms::r2config($index['config'], $index);
		// ตรวจสอบสถานะที่สามารถเข้าหน้านี้ได้
		$can_write = gcms::canConfig($index, 'can_write');
		$moderator = gcms::canConfig($index, 'moderator');
		if (!$moderator && !$can_write) {
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
		if ($cat > 0) {
			$q[] = "P.`category_id`=$cat";
		}
		if ($can_write && !$moderator) {
			$q[] = 'P.`member_id`='.$_SESSION['login']['id'];
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
		$list_per_page = gcms::getVars('GET,COOKIE', 'count,document_listperpage', 30);
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
		setCookie('document_listperpage', $list_per_page, time() + 3600 * 24 * 365);
		// หมวดหมู่
		$categories = array();
		$sql = "SELECT `category_id`,`topic` FROM `".DB_CATEGORY."` WHERE `module_id`='$index[id]' ORDER BY `category_id`";
		foreach ($db->customQuery($sql) AS $item) {
			$categories[$item['category_id']] = gcms::ser2Str($item, 'topic');
		}
		// title
		$m = ucwords($index['module']);
		$title = "$lng[LNG_CREATE]-$lng[LNG_EDIT] $lng[LNG_CONTENTS] $m";
		$a = array();
		$a[] = '<span class=icon-documents>{LNG_MODULES}</span>';
		$a[] = '<a href="{URLQUERY?module=document-config&id='.$index['id'].'}">'.$m.'</a>';
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
		// รายการต่อหน้า
		$content[] = '<fieldset>';
		$content[] = '<label>{LNG_LIST_PER_PAGE} <select name=count>';
		foreach (array(10, 20, 30, 40, 50, 100) AS $item) {
			$sel = $item == $list_per_page ? ' selected' : '';
			$content[] = '<option value='.$item.$sel.'>'.$item.' {LNG_ITEMS}</option>';
		}
		$content[] = '</select></label>';
		$content[] = '</fieldset>';
		// หมวดหมู่
		$content[] = '<fieldset>';
		$content[] = '<label>{LNG_CATEGORY} <select name=cat>';
		$content[] = '<option value=0>{LNG_ALL} {LNG_CATEGORY}</option>';
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
		$content[] = '<input type=hidden name=module value=document-setup>';
		$content[] = '<input type=hidden name=page value=1>';
		$content[] = '<input type=hidden name=id value='.$index['id'].'>';
		$content[] = '</fieldset>';
		$content[] = '</form>';
		// ตารางข้อมูล
		$content[] = '<table id=document class="tbl_list fullwidth">';
		$content[] = '<caption>'.preg_replace($patt2, $replace2, $search != '' ? $lng['SEARCH_RESULT'] : $lng['ALL_ITEMS']).'</caption>';
		$content[] = '<thead>';
		$content[] = '<tr>';
		$content[] = '<th id=c0 scope=col>{LNG_TOPIC}</th>';
		$content[] = '<th id=c1 scope=col class=check-column><a class="checkall icon-uncheck"></a></th>';
		$content[] = '<th id=c2 scope=col class=mobile colspan=4></th>';
		$content[] = '<th id=c5 scope=col class=tablet>{LNG_CATEGORY}</th>';
		$content[] = '<th id=c6 scope=col class=tablet>{LNG_SENDER}</th>';
		$content[] = '<th id=c7 scope=col class="center tablet">{LNG_ARTICLE_DATE}</th>';
		$content[] = '<th id=c8 scope=col class="center tablet">{LNG_LAST_UPDATE}</th>';
		$content[] = '<th id=c9 scope=col class="center tablet">{LNG_VIEWS}</th>';
		$content[] = '<th id=c10 scope=col></th>';
		$content[] = '</tr>';
		$content[] = '</thead>';
		$content[] = '<tbody>';
		if ($count[0]['count'] > 0) {
			// รายการทั้งหมด
			$sql = "SELECT P.`id`,P.`category_id`,P.`can_reply`,P.`published`,P.`show_news`,P.`last_update`,P.`create_date`,P.`member_id`,P.`email`,P.`picture`,P.`visited`,D.`topic`";
			$sql .= ",U.`status`,CASE WHEN ISNULL(U.`id`) THEN P.`email` WHEN U.`displayname`='' THEN U.`email` ELSE U.`displayname` END AS `sender`";
			$sql .= " $sql1 LEFT JOIN `".DB_USER."` AS U ON U.`id`=P.`member_id`";
			$sql .= " $where ORDER BY P.`id` DESC LIMIT $start,$list_per_page";
			foreach ($db->customQuery($sql) AS $item) {
				$id = $item['id'];
				$tr = '<tr id=M_'.$id.'>';
				$tr .= '<th headers=c0 id=r'.$id.' class=topic scope=row><a href="../index.php?module='.$index['module'].'&amp;id='.$id.'" title="{LNG_PREVIEW}" target=_blank>'.$item['topic'].'</a></th>';
				$tr .= '<td headers="r'.$id.' c1" class=check-column><a id=check_'.$id.' class=icon-uncheck></a></td>';
				if (!empty($index['img_typies']) && is_file(DATA_PATH."document/$item[picture]")) {
					$tr .= '<td headers="r'.$id.' c2" class="menu mobile"><img src="'.DATA_URL.'document/'.$item['picture'].'" title="'.$lng['LNG_THUMBNAILS'][1].'" width=22 height=22 alt=thumbnail>';
				} else {
					$tr .= '<td headers="r'.$id.' c2" class="menu mobile"><span class=icon-thumbnail title="'.$lng['LNG_THUMBNAILS'][0].'"></span>';
				}
				$tr .= '<td headers="r'.$id.' c2" class="menu mobile"><span class="icon-reply reply'.$item['can_reply'].'" title="'.$lng['LNG_CAN_REPLIES'][$item['can_reply']].'"></span></td>';
				$tr .= '<td headers="r'.$id.' c2" class="menu mobile"><span class="icon-published'.$item['published'].'" title="'.$lng['LNG_PUBLISHEDS'][$item['published']].'"></span></td>';
				$tr .= '<td headers="r'.$id.' c2" class="menu mobile"><span class="icon-widgets reply'.(preg_match('/news=1/', $item['show_news']) ? 1 : 0).'"></span></td>';
				$tr .= '<td headers="r'.$id.' c5" class=mobile>';
				if (isset($categories[$item['category_id']])) {
					$category = $categories[$item['category_id']];
					$tr .= '<a href="{URLQUERY?cat='.$item['category_id'].'}" title="{LNG_SELECT_ITEM}">'.gcms::cutstring($category, 10).'</a>';
				}
				$tr .= '</td>';
				$tr .= '<td headers="r'.$id.' c6" class="username tablet"><a href="index.php?module=editprofile&amp;id='.$item['member_id'].'" class=status'.$item['status'].' title="{LNG_MEMBER_PROFILE}">'.$item['sender'].'</a></td>';
				$tr .= '<td headers="r'.$id.' c7" class="date tablet">'.gcms::mktime2date($item['create_date'], 'd M Y H:i').'</td>';
				$tr .= '<td headers="r'.$id.' c8" class="date tablet">'.gcms::mktime2date($item['last_update'], 'd M Y H:i').'</td>';
				$tr .= '<td headers="r'.$id.' c9" class="visited tablet">'.$item['visited'].'</td>';
				$tr .= '<td headers="r'.$id.' c10" class=menu><a href="{URLQUERY?module=document-write&src=document-setup&spage='.$page.'&qid='.$item['id'].'}" title="{LNG_EDIT}" class=icon-edit></a></td>';
				$tr .= '</tr>';
				$content[] = $tr;
			}
		}
		$content[] = '</tbody>';
		$content[] = '<tfoot>';
		$content[] = '<tr>';
		$content[] = '<td headers=c0>&nbsp;</td>';
		$content[] = '<td headers=c1 class=check-column><a class="checkall icon-uncheck"></a></td>';
		$content[] = '<td headers=c2 colspan=10></td>';
		$content[] = '</tr>';
		$content[] = '</tfoot>';
		$content[] = '</table>';
		// แบ่งหน้า
		$url = '<a href="{URLQUERY?id='.$index['id'].'&page=%d}" title="{LNG_DISPLAY_PAGE} %d">%d</a>';
		$content[] = '<div class=splitpage>'.gcms::pagination($totalpage, $page, $url).'</div>';
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
		// can_reply
		foreach ($lng['LNG_CAN_REPLIES'] AS $i => $value) {
			$sel[] = '<option value=canreply_'.$index['id'].'_'.$i.'>'.$value.'</option>';
		}
		$sel[] = '</select>';
		$action = gcms::getVars($_GET, 'action', '');
		$content[] = str_replace('value='.$action.'>', 'value='.$action.' selected>', implode('', $sel));
		$content[] = '<label accesskey=e for=sel_action class="button go" id=btn_action><span>{LNG_SELECT_ACTION}</span></label>';
		$content[] = '</fieldset>';
		// add
		$content[] = '<fieldset>';
		$content[] = '<a class="button add" href="{URLQUERY?module=document-write&src=document-setup}"><span class=icon-plus>{LNG_DOCUMENT_WRITE}</span></a>';
		$content[] = '</fieldset>';
		$content[] = '</div>';
		$content[] = '</section>';
		$content[] = '<script>';
		$content[] = '$G(window).Ready(function(){';
		$content[] = "inintCheck('document');";
		$content[] = "inintTR('document', /M_[0-9]+/);";
		$content[] = 'callAction("btn_action", function(){return $E("sel_action").value}, "document", "'.WEB_URL.'/modules/document/admin_action.php");';
		$content[] = '});';
		$content[] = '</script>';
		// หน้านี้
		$url_query['module'] = 'document-setup';
		$url_query['id'] = $index['id'];
	}
} else {
	$title = $lng['LNG_DATA_NOT_FOUND'];
	$content[] = '<aside class=error>'.$title.'</aside>';
}
