<?php
// modules/document/calendar.php
if (defined('MAIN_INIT')) {
	$d = gcms::getVars($_REQUEST, 'd', '');
	if ($d == 'today') {
		$d = gcms::mktime2date($mmktime, 'd-m-Y');
	}
	// โมดูลที่เรียก
	if (preg_match('/^([0-3]?[0-9])[\-|\s]([0-1]?[0-9])[\-|\s]([0-9]{4,4})$/', $d, $ds)) {
		include (ROOT_PATH.'modules/document/list.php');
	} else {
		$title = $lng['PAGE_NOT_FOUND'];
		$content = '<div class=error>'.$title.'</div>';
	}
}
