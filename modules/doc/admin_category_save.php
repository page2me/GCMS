<?php
	// modules/doc/admin_category_save.php
	header("content-type: text/html; charset=UTF-8");
	// inint
	include '../../bin/inint.php';
	// referer, member
	if (gcms::isReferer() && gcms::isMember()) {
		if (isset($_SESSION['login']['account']) && $_SESSION['login']['account'] == 'demo') {
			$ret = array('error' => 'EX_MODE_ERROR');
		} else {
			// ตรวจสอบโมดูลที่เรียก
			$sql = "SELECT `id`,`module`,`config` FROM `".DB_MODULES."`";
			$sql .= " WHERE `id`=".(int)$_POST['module_id']." AND `owner`='doc' LIMIT 1";
			$index = $db->customQuery($sql);
			if (sizeof($index) == 1) {
				$index = $index[0];
				// อ่าน config ของโมดูล
				gcms::r2config($index['config'], $index);
				// ตรวจสอบสถานะที่สามารถเข้าหน้านี้ได้
				if (!gcms::canConfig($index, 'can_write')) {
					$index = false;
				}
			} else {
				$index = false;
			}
			if ($index) {
				$error = false;
				$input = false;
				$save = array();
				foreach ($_POST['category_id'] AS $key => $value) {
					$value = (int)$value;
					if ($value < 1) {
						$error = !$error ? 'ID_EMPTY' : $error;
						$input = !$input ? 'category_id_'.$key : $input;
					} else if (isset($save[$value])) {
						$error = !$error ? 'ID_EXISTS' : $error;
						$input = !$input ? 'category_id_'.$key : $input;
					} else {
						foreach ($config['languages'] AS $l) {
							$save[$value]['topic'][$l] = $db->sql_trim_str(gcms::oneLine($_POST['topic_'.$l][$key]));
						}
					}
				}
				if (!$error) {
					// remove old category
					$db->query("DELETE FROM `".DB_CATEGORY."` WHERE `module_id`='$index[id]'");
					// add new category
					foreach ($save AS $i => $item) {
						$datas['module_id'] = $index['id'];
						$datas['category_id'] = $i;
						$datas['topic'] = serialize($item['topic']);
						$db->add(DB_CATEGORY, $datas);
					}
					$ret['error'] = 'SAVE_COMPLETE';
					$ret['location'] = 'reload';
				} else {
					$ret['error'] = $error;
					$ret['input'] = $input;
				}
			} else {
				// ไม่สามารถเขียนหรือแก้ไขได้
				$ret['error'] = 'NOT_ALLOWED';
			}
		}
	} else {
		$ret = array('error' => 'ACTION_ERROR');
	}
	// คืนค่าเป็น JSON
	echo gcms::array2json($ret);
