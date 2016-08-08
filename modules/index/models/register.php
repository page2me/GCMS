<?php
/*
 * @filesource index/models/register.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Register;

use \Kotchasan\Language;
use \Kotchasan\Http\Request;
use \Kotchasan\Validator;
use \Kotchasan\Text;
use \Gcms\Email;
use \Kotchasan\Antispam;

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
    // referer, session
    if ($request->initSession() && $request->isReferer()) {
      $antispam = new Antispam($request->post('register_antispamid')->toString());
      if (!$antispam->valid($request->post('register_antispam')->toString())) {
        // Antispam ไม่ถูกต้อง
        $ret['ret_register_antispam'] = 'this';
        $ret['input'] = 'register_antispam';
      } else {
        // รับค่าจากการ POST
        $save = array();
        foreach ($request->getParsedBody() as $key => $value) {
          $k = str_replace('register_', '', $key);
          switch ($k) {
            case 'email':
              $save['email'] = $request->post($key)->username();
              break;
            case 'phone1':
            case 'idcard':
              $save[$k] = $request->post($key)->number();
              break;
            case 'invite':
              $save[$k] = $request->post($key)->toInt();
              break;
            case 'password':
            case 'repassword':
            case 'accept':
            case 'next':
              $$k = $request->post($key)->toString();
              break;
          }
        }
        if ($accept === '1') {
          // ชื่อตาราง user
          $user_table = $this->getFullTableName('user');
          // database connection
          $db = $this->db();
          // ตรวจสอบค่าที่ส่งมา
          $input = false;
          // อีเมล์
          if (empty($save['email'])) {
            $ret['ret_register_email'] = 'this';
            $input = !$input ? 'register_email' : $input;
          } elseif (!Validator::email($save['email'])) {
            $ret['ret_register_email'] = str_replace(':name', Language::get('Email'), Language::get('Invalid :name'));
            $input = !$input ? 'register_email' : $input;
          } else {
            // ตรวจสอบอีเมล์ซ้ำ
            $search = $db->first($user_table, array('email', $save['email']));
            if ($search !== false) {
              $ret['ret_register_email'] = str_replace(':name', Language::get('Email'), Language::get('This :name is already registered'));
              $input = !$input ? 'register_email' : $input;
            } else {
              $ret['ret_register_email'] = '';
            }
          }
          // password
          if (mb_strlen($password) < 4) {
            // รหัสผ่านต้องไม่น้อยกว่า 4 ตัวอักษร
            $ret['ret_register_password'] = 'this';
            $input = !$input ? 'register_password' : $input;
          } elseif ($repassword != $password) {
            // ถ้าต้องการเปลี่ยนรหัสผ่าน กรุณากรอกรหัสผ่านสองช่องให้ตรงกัน
            $ret['ret_register_repassword'] = 'this';
            $input = !$input ? 'register_repassword' : $input;
          } else {
            $save['password'] = md5($password.$save['email']);
            $ret['ret_register_password'] = '';
            $ret['ret_register_repassword'] = '';
          }
          // phone1
          if (!empty($save['phone1'])) {
            if (!preg_match('/[0-9]{9,10}/', $save['phone1'])) {
              $ret['ret_register_phone1'] = str_replace(':name', Language::get('phone number'), Language::get('Invalid :name'));
              $input = !$input ? 'register_phone1' : $input;
            } else {
              // ตรวจสอบโทรศัพท์
              $search = $db->first($user_table, array('phone1', $save['phone1']));
              if ($search !== false) {
                $ret['ret_register_phone1'] = str_replace(':name', Language::get('phone number'), Language::get('This :name is already registered'));
                $input = !$input ? 'register_phone1' : $input;
              } else {
                $ret['ret_register_phone1'] = '';
              }
            }
          } elseif (self::$cfg->member_phone == 2) {
            $ret['ret_register_phone1'] = 'this';
            $input = !$input ? 'register_phone1' : $input;
          }
          // idcard
          if (!empty($save['idcard'])) {
            if (!Validator::idCard($save['idcard'])) {
              $ret['ret_register_idcard'] = str_replace(':name', Language::get('Identification number'), Language::get('Invalid :name'));
              $input = !$input ? 'register_idcard' : $input;
            } else {
              // ตรวจสอบ idcard ซ้ำ
              $search = $db->first($user_table, array('idcard', $save['idcard']));
              if ($search !== false) {
                $ret['ret_register_idcard'] = str_replace(':name', Language::get('Identification number'), Language::get('This :name is already registered'));
                $input = !$input ? 'register_idcard' : $input;
              } else {
                $ret['ret_register_idcard'] = '';
              }
            }
          } elseif (self::$cfg->member_idcard == 2) {
            $ret['ret_idcard'] = 'this';
            $input = !$input ? 'idcard' : $input;
          }
          // invite
          if (isset($save['invite'])) {
            $ret['ret_invite'] = '';
            if (!empty($save['invite'])) {
              $search = $db->first($user_table, $save['invite']);
              if ($search === false) {
                $ret['ret_register_invite'] = str_replace(':name', Language::get('Invitation code'), Language::get('Invalid :name'));
                $input = !$input ? 'register_invite' : $input;
              }
            }
          }
          if (!$input) {
            $save['create_date'] = time();
            $save['subscrib'] = 1;
            $save['status'] = 0;
            list($displayname, $domain) = explode('@', $save['email']);
            $save['displayname'] = $displayname;
            $a = 1;
            while (true) {
              if (false === $db->first($user_table, array('displayname', $save['displayname']))) {
                break;
              } else {
                $a++;
                $save['displayname'] = $displayname.$a;
              }
            }
            // รหัสยืนยัน
            $save['activatecode'] = empty(self::$cfg->user_activate) ? '' : Text::rndname(32);
            // บันทึกลงฐานข้อมูล
            $save['id'] = $db->insert($user_table, $save);
            // ส่งอีเมล์
            $replace = array(
              '/%EMAIL%/' => $save['email'],
              '/%PASSWORD%/' => $password,
              '/%ID%/' => $save['activatecode']
            );
            Email::send(empty(self::$cfg->user_activate) ? 2 : 1, 'member', $replace, $save['email']);
            if (empty(self::$cfg->user_activate)) {
              // login
              $save['password'] = $password;
              $_SESSION['login'] = $save;
              // แสดงข้อความตอบรับการสมัครสมาชิก
              $ret['alert'] = str_replace(':email', $save['email'], Language::get('Registration information sent to :email complete. We will take you to edit your profile'));
              // ถ้าไม่มีการกำหนดหน้าถัดไปมา ไปแก้ไขข้อมูลส่วนตัว
              $ret['location'] = isset($next) ? $next : WEB_URL.'index.php?module=editprofile';
            } else {
              // แสดงข้อความตอบรับการสมัครสมาชิก
              $ret['alert'] = str_replace(':email', $save['email'], Language::get('Register successfully, We have sent complete registration information to :email'));
              // ถ้าไม่มีการกำหนดหน้าถัดไปมา กลับไปหน้าหลักเว็บไซต์
              $ret['location'] = isset($next) ? $next : WEB_URL.'index.php';
            }
            // clear antispam
            $antispam->delete();
          } else {
            $ret['input'] = $input;
          }
        }
      }
    }
    // คืนค่าเป็น JSON
    if (!empty($ret)) {
      echo json_encode($ret);
    }
  }
}