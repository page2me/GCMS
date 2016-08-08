<?php
// widgets/shoutbox/admin_history.php
if (MAIN_INIT == 'admin' && $isAdmin) {
	// ลบข้อความที่เก่ากว่ากำหนด
	$d = date('d', $mmktime);
	if ($_COOKIE['gshoutbox_date'] != $d) {
		setCookie('gshoutbox_date', $d, time() + 3600 * 24 * 365);
		$valid_time = $mmktime - (max(1, (int)$config['shoutbox_history']) * 86400);
		$db->query("DELETE FROM `".DB_SHOUTBOX."` WHERE `time`< $valid_time");
	}
	// คำสั่งที่ทำงานล่าสุด
	$action = gcms::getVars($_GET, 'action', '');
	// จำนวนทั้งหมด
	$sql = "SELECT COUNT(*) AS `count` FROM `".DB_SHOUTBOX."`";
	$count = $db->customQuery($sql);
	// รายการต่อหน้า
	$list_per_page = gcms::getVars('GET,COOKIE', 'count,shoutbox_listperpage', 30);
	$list_per_page = max(10, $list_per_page);
	setCookie('shoutbox_listperpage', $list_per_page, time() + 3600 * 24 * 365);
	// หน้าที่เลือก
	$page = gcms::getVars($_GET, 'page', 1);
	$page = ($page < 1) ? 1 : $page;
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
	// title
	$title = $lng['LNG_CHAT_HISTORY'];
	$a = array();
	$a[] = '<span class=icon-widgets>{LNG_WIDGETS}</span>';
	$a[] = '<a href="{URLQUERY?module=shoutbox-setup}">{LNG_SHOUTBOX}</a>';
	$a[] = '{LNG_CHAT_HISTORY}';
	// แสดงผล
	$content[] = '<div class=breadcrumbs><ul><li>'.implode('</li><li>', $a).'</li></ul></div>';
	$content[] = '<section>';
	$content[] = '<header><h1 class=icon-chat>'.$title.'</h1></header>';
	$content[] = '<form class=table_nav method=get action=index.php>';
	// รายการต่อหน้า
	$content[] = '<fieldset>';
	$content[] = '<label>{LNG_LIST_PER_PAGE} <select name=count>';
	foreach (array(10, 20, 30, 40, 50, 100) AS $item) {
		$sel = $item == $list_per_page ? 'selected' : '';
		$content[] = '<option value='.$item.' '.$sel.'>'.$item.' {LNG_ITEMS}</option>';
	}
	$content[] = '</select></label>';
	$content[] = '<input type=submit class="button go" value="{LNG_GO}">';
	$content[] = '<input name=module type=hidden value=shoutbox-history>';
	$content[] = '</fieldset>';
	$content[] = '</form>';
	$content[] = '<table id=shoutbox class="tbl_list fullwidth">';
	$content[] = '<caption>'.preg_replace($patt2, $replace2, $lng['ALL_ITEMS']).'</caption>';
	$content[] = '<thead>';
	$content[] = '<tr>';
	$content[] = '<th scope=col id=c0>{LNG_CREATED}</th>';
	$content[] = '<th scope=col id=c1 class=check-column><a class="checkall icon-uncheck"></a></th>';
	$content[] = '<th scope=col id=c2>{LNG_SENDER}</th>';
	$content[] = '<th scope=col id=c3>{LNG_DETAIL}</th>';
	$content[] = '</tr>';
	$content[] = '</thead>';
	$content[] = '<tbody>';
	// shoutbox
	$sql = "SELECT * FROM `".DB_SHOUTBOX."` ORDER BY `time` DESC LIMIT $start, $list_per_page";
	foreach ($db->customQuery($sql) AS $item) {
		$id = $item['id'];
		$tr = '<tr id=L_'.$id.'>';
		$tr .= '<td headers="r'.$id.' c0">'.gcms::mktime2date($item['time'], 'd M Y H:i:s').'</td>';
		$tr .= '<td headers="r'.$id.' c1" class=check-column><a id=check_'.$id.' class=icon-uncheck></a></td>';
		$tr .= '<td headers="r'.$id.' c2">'.$item['sender'].'</td>';
		$tr .= '<td headers="r'.$id.' c3">'.$item['text'].'</td>';
		$tr .= '</tr>';
		$content[] = $tr;
	}
	$content[] = '</tbody>';
	$content[] = '<tfoot>';
	$content[] = '<tr>';
	$content[] = '<td headers=c0>&nbsp;</td>';
	$content[] = '<td headers=c1 class=check-column><a class="checkall icon-uncheck"></a></td>';
	$content[] = '<td headers=c2 colspan=2>&nbsp;</td>';
	$content[] = '</tr>';
	$content[] = '</tfoot>';
	$content[] = '</table>';
	// แบ่งหน้า
	$url = '<a href="{URLQUERY?module=shoutbox-history&page=%d}" title="{LNG_DISPLAY_PAGE} %d">%d</a>';
	$content[] = '<div class=splitpage>'.gcms::pagination($totalpage, $page, $url).'</div>';
	$content[] = '<div class=table_nav>';
	// sel action
	$content[] = '<select id=sel_action><option value=delete>{LNG_DELETE}</option></select>';
	$content[] = '<label for=sel_action accesskey=e class="button ok" id=shoutbox_action><span>{LNG_SELECT_ACTION}</span></label>';
	$content[] = '</div>';
	$content[] = '</section>';
	$content[] = '<script>';
	$content[] = '$G(window).Ready(function(){';
	$content[] = "inintCheck('shoutbox');";
	$content[] = "inintTR('shoutbox', /L_[0-9]+/);";
	$content[] = 'callAction("shoutbox_action", function(){return $E("sel_action").value}, "shoutbox", "'.WEB_URL.'/widgets/shoutbox/admin_action.php");';
	$content[] = '});';
	$content[] = '</script>';
	// หน้านี้
	$url_query['module'] = 'shoutbox-history';
	$url_query['page'] = $page;
} else {
	$title = $lng['LNG_DATA_NOT_FOUND'];
	$content[] = '<aside class=error>'.$title.'</aside>';
}
