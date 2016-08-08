<?php
/*
 * @filesource index/models/updateprofile.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Updateprofile;

use \Kotchasan\Login;
use \Kotchasan\Language;
use \Kotchasan\File;
use \Kotchasan\Http\Request;

/**
 * บันทึกข้อมูลสมาชิก
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Model
{

  /**
   * บันทึก
   */
  public function save(Request $request)
  {
    $ret = array();
    // referer, session, member
    if ($request->initSession() && $request->isReferer() && $login = Login::isMember()) {
      if ($login['email'] == 'demo' || !empty($login['fb'])) {
        $ret['alert'] = Language::get('Unable to complete the transaction');
      } else {
        // รับค่าจากการ POST
        $save = array(
          'email' => $request->post('register_email')->url(),
          'displayname' => $request->post('register_displayname')->topic(),
          'sex' => $request->post('register_sex')->topic(),
          'website' => str_replace(array('http://', 'https://', 'ftp://'), array('', '', ''), $request->post('register_website')->url()),
          'pname' => $request->post('register_pname')->topic(),
          'fname' => $request->post('register_fname')->topic(),
          'lname' => $request->post('register_lname')->topic(),
          'company' => $request->post('register_company')->topic(),
          'phone1' => $request->post('register_phone1')->number(),
          'phone2' => $request->post('register_phone2')->number(),
          'subscrib' => $request->post('register_subscrib')->toBoolean(),
          'address1' => $request->post('register_address1')->topic(),
          'address2' => $request->post('register_address2')->topic(),
          'provinceID' => $request->post('register_provinceID')->number(),
          'province' => $request->post('register_province')->topic(),
          'zipcode' => $request->post('register_zipcode')->number(),
          'country' => $request->post('register_country')->topic(),
          'status' => $request->post('register_status')->toInt(),
          'birthday' => $request->post('register_birthday')->date(),
          'admin_access' => $request->post('register_admin_access')->toBoolean()
        );
        // ชื่อตาราง user
        $user_table = $this->getFullTableName('user');
        // database connection
        $db = $this->db();
        // ตรวจสอบค่าที่ส่งมา
        $id = $request->post('register_id')->toInt();
        if ($id == 0) {
          // ใหม่
          $user = (object)array(
              'id' => 0,
              'email' => '',
              'fb' => 0
          );
        } else {
          // แก้ไข
          $user = $db->first($user_table, $id);
        }
        if (!$user) {
          // ไม่พบสมาชิกที่แก้ไข
          $ret['alert'] = Language::get('not a registered user');
        } else {
          $isAdmin = Login::isAdmin();
          // ไม่ใช่แอดมิน ใช้อีเมล์เดิมจากฐานข้อมูล
          if (!$isAdmin && $user->id > 0) {
            $save['email'] = $user->email;
          }
          // ตรวจสอบค่าที่ส่งมา
          $input = false;
          $requirePassword = false;
          // อีเมล์
          if (empty($save['email'])) {
            $ret['ret_register_email'] = 'this';
            $input = !$input ? 'register_email' : $input;
          } else {
            // ตรวจสอบอีเมล์ซ้ำ
            $search = $db->first($user_table, array('email', $save['email']));
            if ($search !== false && $user->id != $search->id) {
              $ret['ret_register_email'] = str_replace(':name', Language::get('Email'), Language::get('This :name is already registered'));
              $input = !$input ? 'register_email' : $input;
            } else {
              $requirePassword = $user->email !== $save['email'];
              $ret['ret_register_email'] = '';
            }
          }
          // ชื่อเรียก
          if (!empty($save['displayname'])) {
            // ตรวจสอบ ชื่อเรียก
            $search = $db->first($user_table, array('displayname', $save['displayname']));
            if ($search !== false && $user->id != $search->id) {
              $ret['ret_register_displayname'] = str_replace(':name', Language::get('Name'), Language::get('This :name is already registered'));
              $input = !$input ? 'register_displayname' : $input;
            } else {
              $ret['ret_register_displayname'] = '';
            }
          }
          // โทรศัพท์
          if (!empty($save['phone1'])) {
            if (!preg_match('/[0-9]{9,10}/', $save['phone1'])) {
              $ret['ret_register_phone1'] = str_replace(':name', Language::get('phone number'), Language::get('Invalid :name'));
              $input = !$input ? 'register_phone1' : $input;
            } else {
              // ตรวจสอบโทรศัพท์
              $search = $db->first($user_table, array('phone1', $save['phone1']));
              if ($search !== false && $user->id != $search->id) {
                $ret['ret_register_phone1'] = str_replace(':name', Language::get('phone number'), Language::get('This :name is already registered'));
                $input = !$input ? 'register_phone1' : $input;
              } else {
                $ret['ret_register_phone1'] = '';
              }
            }
          }
          // password
          $password = $request->post('register_password')->topic();
          $repassword = $request->post('register_repassword')->topic();
          if (!empty($password) || !empty($repassword)) {
            if (mb_strlen($password) < 4) {
              // รหัสผ่านต้องไม่น้อยกว่า 4 ตัวอักษร
              $ret['ret_register_password'] = 'this';
              $input = !$input ? 'register_password' : $input;
            } elseif ($repassword != $password) {
              // ถ้าต้องการเปลี่ยนรหัสผ่าน กรุณากรอกรหัสผ่านสองช่องให้ตรงกัน
              $ret['ret_register_repassword'] = 'this';
              $input = !$input ? 'register_repassword' : $input;
            } else {
              $ret['ret_register_password'] = '';
              $ret['ret_register_repassword'] = '';
              $save['password'] = md5($password.$save['email']);
              $requirePassword = false;
            }
          }
          // มีการเปลี่ยน email ต้องการรหัสผ่าน
          if (!$input && $requirePassword) {
            $ret['ret_register_password'] = 'this';
            $input = !$input ? 'register_password' : $input;
          }
          // อัปโหลดไฟล์
          foreach ($request->getUploadedFiles() as $item => $file) {
            if ($file->hasUploadFile()) {
              if (!File::makeDirectory(ROOT_PATH.self::$cfg->usericon_folder)) {
                // ไดเรคทอรี่ไม่สามารถสร้างได้
                $ret['ret_'.$item] = sprintf(Language::get('Directory %s cannot be created or is read-only.'), self::$cfg->usericon_folder);
                $input = !$input ? $item : $input;
              } else {
                if (!empty($user->icon)) {
                  // ลบไฟล์เดิม
                  @unlink(ROOT_PATH.self::$cfg->usericon_folder.$user->icon);
                }
                try {
                  // อัปโหลด thumbnail
                  $save['icon'] = $user->id.'.jpg';
                  $file->cropImage(self::$cfg->user_icon_typies, ROOT_PATH.self::$cfg->usericon_folder.$save['icon'], self::$cfg->user_icon_w, self::$cfg->user_icon_h);
                } catch (\Exception $exc) {
                  // ไม่สามารถอัปโหลดได้
                  $ret['ret_'.$item] = Language::get($exc->getMessage());
                  $input = !$input ? $item : $input;
                }
              }
            }
          }
          if (!$input) {
            // ไม่ใช่แอดมิน
            if (!$isAdmin) {
              unset($save['status']);
              unset($save['point']);
              unset($save['admin_access']);
            }
            // social ห้ามแก้ไข
            if (!empty($user->fb)) {
              unset($save['email']);
              unset($save['password']);
            }
            if ($login['id'] == $id || $id == 1) {
              unset($save['admin_access']);
            }
            // บันทึก
            if ($id == 0) {
              // ใหม่
              $id = $db->insert($user_table, $save);
              // ไปหน้ารายการสมาชิก
              $ret['location'] = $request->getUri()->postBack('index.php', array('module' => 'member', 'id' => null, 'page' => null));
            } else {
              // แก้ไข
              $db->update($user_table, $id, $save);
              if ($login['id'] == $id) {
                // ตัวเอง
                if (isset($save['password'])) {
                  if (isset($save['email'])) {
                    $_SESSION['login']['email'] = $save['email'];
                  }
                  $_SESSION['login']['password'] = $password;
                }
                // reload หน้าเว็บ
                $ret['location'] = 'reload';
              } else {
                // กลับไปหน้าก่อนหน้า
                $ret['location'] = $request->getUri()->postBack('index.php', array('id' => null));
              }
            }
            // คืนค่า
            $ret['alert'] = Language::get('Saved successfully');
          } else {
            // error
            $ret['input'] = $input;
          }
        }
      }
    } else {
      $ret['alert'] = Language::get('Unable to complete the transaction');
    }
    // คืนค่าเป็น JSON
    if (!empty($ret)) {
      echo json_encode($ret);
    }
  }
}