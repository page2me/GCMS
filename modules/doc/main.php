<?php
	// modules/doc/main.php
	if (defined('MAIN_INIT')) {
		// เลือกไฟล์
		if (isset($_REQUEST['id'])) {
			// แสดงรายการที่เลือก
			include (ROOT_PATH.'modules/doc/view.php');
		} else {
			// แสดงลิสต์รายการ
			include (ROOT_PATH.'modules/doc/list.php');
		}
	}