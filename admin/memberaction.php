<?php
// admin/memberaction.php
header("content-type: text/html; charset=UTF-8");
// inint
include '../bin/inint.php';
// action
$action = gcms::getVars($_POST, 'action', '');
// ตรวจสอบ id
$ids = array();
foreach (explode(',', $_POST['id']) AS $id) {
	// ไม่สามารถแก้ไขตัวเองได้
	if ($_SESSION['login']['id'] != $id) {
		$ids[] = (int)$id;
	}
}
// id ของ สมาชิกทั้งหมดที่ส่งมา
$ids = implode(',', $ids);
// ตรวจสอบ referer และ admin
if (gcms::isReferer() && gcms::isAdmin() && $ids != '') {
	if (isset($_SESSION['login']['account']) && $_SESSION['login']['account'] == 'demo') {
		echo $lng['ACTION_FORBIDDEN'];
	} else {
		if ($action == 'delete') {
			// ลบสมาชิกที่เลือก
			$sql = "SELECT `icon` FROM `".DB_USER."` WHERE `id` IN ($ids) AND `id`!=1 AND `icon`!=''";
			foreach ($db->customQuery($sql) AS $item) {
				// ลบรูปภาพสมาชิก
				@unlink(USERICON_FULLPATH.$item['icon']);
			}
			// ลบสมาชิก
			$db->query("DELETE FROM `".DB_USER."` WHERE `id` IN ($ids) AND `id`!=1");
		} elseif ($action == 'activate' || $action == 'sendpassword') {
			// ส่งอีเมล์ยืนยันสมาชิก อีกครั้ง
			$sql = "SELECT `id`,`email`,`activatecode` FROM `".DB_USER."` WHERE `id` IN ($ids) AND `fb`='0'";
			foreach ($db->customQuery($sql) AS $item) {
				unset($replace);
				// สุ่มรหัสผ่านใหม่
				$password = gcms::rndname(6);
				// ข้อมูลอีเมล์
				$replace = array();
				$replace['/%PASSWORD%/'] = $password;
				$replace['/%EMAIL%/'] = $item['email'];
				if ($action == 'activate' || $item['activatecode'] != '') {
					// activate หรือ ยังไม่ได้ activate
					$save['activatecode'] = $item['activatecode'] == '' ? gcms::rndname(32) : $item['activatecode'];
					$replace['/%ID%/'] = $save['activatecode'];
					// send mail
					$err = gcms::sendMail(1, 'member', $replace, $item['email']);
				} else {
					// send mail
					$err = gcms::sendMail(3, 'member', $replace, $item['email']);
				}
				if ($err == '') {
					// อัปเดทรหัสผ่านใหม่
					$save['password'] = md5($password.$item['email']);
					// บันทึก
					$db->edit(DB_USER, $item['id'], $save);
				} else {
					echo $err;
				}
			}
		} elseif ($action == 'accept') {
			// ยอมรับสมาชิกที่เลือก
			$sql = "UPDATE `".DB_USER."` SET `activatecode`='' WHERE `id` IN ($ids) AND `fb`='0'";
			$db->query($sql);
		} elseif ($action == 'ban') {
			// ระงับสมาชิกชั่วคราว
			$value = gcms::getVars($_POST, 'value', 0);
			$db->query("UPDATE `".DB_USER."` SET `ban_date`='$mmktime',`ban_count`='$value' WHERE `id` IN ($ids) AND `fb`='0'");
		} elseif ($action == 'unban') {
			// ยกเลิกการระงับสมาชิกชั่วคราว
			$sql = "UPDATE `".DB_USER."` SET `ban_date`='0',`ban_count`='0' WHERE `id` IN ($ids) AND `fb`='0'";
			$db->query($sql);
		} elseif ($action == 'status') {
			// เปลี่ยนสถานะ
			$value = gcms::getVars($_POST, 'value', 0);
			$db->query("UPDATE `".DB_USER."` SET `status`='$value' WHERE `id` IN ($ids) AND `fb`='0'");
		}
	}
} else {
	echo $lng['ACTION_FORBIDDEN'];
}
