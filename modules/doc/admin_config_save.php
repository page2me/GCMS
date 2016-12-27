<?php
	// modules/doc/admin_config_save.php
	header("content-type: text/html; charset=UTF-8");
	// inint
	include '../../bin/inint.php';
	$ret = array();
	// referer, member
	if (gcms::isReferer() && gcms::isMember()) {
		if (isset($_SESSION['login']['account']) && $_SESSION['login']['account'] == 'demo') {
			$ret['error'] = 'EX_MODE_ERROR';
		} else {
			// ค่าที่ส่งมา
			$can_write = isset($_POST['config_can_write']) ? $_POST['config_can_write'] : array();
			$can_write[] = 1;
			$can_config = isset($_POST['config_can_config']) ? $_POST['config_can_config'] : array();
			$can_config[] = 1;
			// ตรวจสอบรายการที่ต้องการแก้ไข
			$index = $db->getRec(DB_MODULES, $_POST['config_id']);
			if ($index) {
				// config
				gcms::r2config($index['config'], $index);
				if (!gcms::canConfig($index, 'can_config')) {
					$index = false;
				}
			}
			if (!$index) {
				// ไม่พบ หรือไม่สามารถแก้ไขได้
				$ret['error'] = 'ACTION_ERROR';
			} else {
				// save
				$cfg[] = 'can_config='.implode(',', $can_config);
				$cfg[] = 'can_write='.implode(',', $can_write);
				// แก้ไขข้อมูล
				$db->edit(DB_MODULES, $index['id'], array('config' => implode("\n", $cfg)));
				// คืนค่า
				$ret['error'] = 'SAVE_COMPLETE';
				$ret['location'] = 'reload';
			}
		}
	} else {
		$ret['error'] = 'ACTION_ERROR';
	}
	// คืนค่าเป็น JSON
	echo gcms::array2json($ret);
