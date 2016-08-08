<?php
// widgets/shoutbox/chat.php
header("content-type: text/html; charset=UTF-8");
// inint
include ('../../bin/inint.php');
// referer
if (gcms::isReferer()) {
	$id = gcms::getVars($_POST, 'id', 0);
	$ret = array();
	if (isset($_SESSION['login'])) {
		// login
		$login = gcms::getVars($_SESSION, 'login', array('id' => 0, 'status' => -1, 'email' => '', 'password' => ''));
		$ret['user'] = empty($login['displayname']) ? trim("$login[fname] $login[lname]") : $login['displayname'];
		if (empty($ret['user'])) {
			$ds = explode('@', $login['email']);
			$ret['user'] = $ds[0];
		}
	}
	$ret['time'] = $mmktime;
	$result = array();
	// ส่งข้อความล่าสุดกลับ
	$config['shoutbox_lines'] = max(10, (int)$config['shoutbox_lines']);
	$sql = "SELECT * FROM `".DB_SHOUTBOX."` WHERE `time`>$id ORDER BY `time` DESC,`id` DESC".($id == 0 ? " LIMIT $config[shoutbox_lines]" : '');
	foreach ($db->customQuery($sql) AS $item) {
		if (!isset($ret['id'])) {
			$ret['id'] = $item['time'];
		}
		$result[] = gcms::CheckRude($item['text'])."\t$item[sender]\t$item[time]";
	}
	if (sizeof($result) > 0) {
		$ret['content'] = rawurlencode(implode("\n", $result));
	}
	// คืนค่าเป็น JSON
	echo gcms::array2json($ret);
}
