<?php
	// modules/doc/admin_action.php
	header("content-type: text/html; charset=UTF-8");
	// inint
	include '../../bin/inint.php';
	// referer, member
	if (gcms::isReferer() && gcms::isMember()) {
		if (empty($_SESSION['login']['account']) || $_SESSION['login']['account'] != 'demo') {
			// ค่าที่ส่งมา
			$action = gcms::getVars($_POST, 'action', '');
			$id = gcms::getVars($_POST, 'id', '');
			$value = gcms::getVars($_POST, 'value', 0);
			$module = gcms::getVars($_POST, 'module', 0);
			// โมดูลที่เรียก
			$index = $db->getRec(DB_MODULES, $module);
			if ($index) {
				// config
				gcms::r2config($index['config'], $index);
				// แอดมิน
				if (gcms::canConfig($index, 'can_write')) {
					if ($action == 'delete') {
						// ลบ
						$db->query("DELETE FROM `".DB_INDEX."` WHERE `id` IN ($id) AND `module_id`='$index[id]'");
						$db->query("DELETE FROM `".DB_INDEX_DETAIL."` WHERE `id` IN ($id) AND `module_id`='$index[id]'");
						// อัปเดทจำนวนเรื่อง และ ความคิดเห็น ในหมวด
						$sql1 = "SELECT COUNT(*) FROM `".DB_INDEX."` WHERE `category_id`=C.`category_id` AND `module_id`='$index[id]' AND `index`='0'";
						$sql2 = "SELECT `id` FROM `".DB_INDEX."` WHERE `category_id`=C.`category_id` AND `module_id`='$index[id]' AND `index`='0'";
						$sql2 = "SELECT COUNT(*) FROM `".DB_COMMENT."` WHERE `index_id` IN ($sql2) AND `module_id`='$index[id]'";
						$sql = "UPDATE `".DB_CATEGORY."` AS C SET C.`c1`=($sql1),C.`c2`=($sql2) WHERE C.`module_id`='$index[id]'";
						$db->query($sql);
					} elseif ($action == 'move') {
						// move menu
						foreach (explode(',', str_replace('M_', '', $_POST['data'])) As $i) {
							$db->query("UPDATE `".DB_INDEX."` SET `create_date`=".$mmktime." WHERE `id`=".(int)$i." AND `module_id`='$index[id]' LIMIT 1");
							$mmktime++;
						}
					} elseif ($action == 'published') {
						// published (บทความ)
						$db->query("UPDATE `".DB_INDEX."` SET `published`='$value' WHERE `id` IN($id) AND `module_id`='$index[id]'");
					}
				}
			}
		}
	}
