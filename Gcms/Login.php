<?php
/*
 * @filesource Gcms/Login.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Gcms;

use \Kotchasan\Model;
use \Kotchasan\Language;
use \Kotchasan\Http\Request;
use \Kotchasan\Text;
use \Gcms\Email;

/**
 * คลาสสำหรับตรวจสอบการ Login สำหรับ GCMS
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Login extends \Kotchasan\Login implements \Kotchasan\LoginInterface
{

  /**
   * ฟังก์ชั่นตรวจสอบสมาชิกกับฐานข้อมูล
   *
   * @param string $username
   * @param string $password
   * @return array|string คืนค่าข้อมูลสมาชิก (array) ไม่พบคืนค่าข้อความผิดพลาด (string)
   */
  public static function checkMember($username, $password)
  {
    $where = array();
    foreach (self::$cfg->login_fields as $field) {
      $where[] = array($field, $username);
    }
    // model
    $model = new Model;
    $query = $model->db()->createQuery()
      ->select()
      ->from('user')
      ->where($where, 'OR')
      ->order('status DESC')
      ->toArray();
    $login_result = null;
    foreach ($query->execute() as $item) {
      if ($item['password'] == md5($password.$item['email'])) {
        $login_result = $item;
        break;
      }
    }
    if ($login_result === null) {
      // user หรือ password ไม่ถูกต้อง
      self::$login_input = isset($item) ? 'password' : 'username';
      return isset($item) ? str_replace(':name', Language::get('Password'), Language::get('Incorrect :name')) : Language::get('not a registered user');
    } elseif (!empty($login_result['activatecode'])) {
      // ยังไม่ได้ activate
      self::$login_input = 'username';
      return Language::get('No confirmation email, please check your e-mail');
    } elseif (!empty($login_result['ban'])) {
      // ติดแบน
      self::$login_input = 'username';
      return Language::get('Members were suspended');
    } else {
      return $login_result;
    }
  }

  /**
   * ฟังก์ชั่นตรวจสอบการ login
   *
   * @param string $username
   * @param string $password
   * @return string|array เข้าระบบสำเร็จคืนค่าแอเรย์ข้อมูลสมาชิก, ไม่สำเร็จ คืนค่าข้อความผิดพลาด
   */
  public function checkLogin($username, $password)
  {
    if (!empty(self::$cfg->demo_mode) && $username == 'demo' && $password == 'demo') {
      // login เป็น demo
      $login_result = array(
        'id' => 0,
        'email' => 'demo',
        'password' => 'demo',
        'displayname' => 'demo',
        'fname' => '',
        'lname' => '',
        'admin_access' => 1,
        'fb' => 0,
        'status' => 1
      );
    } else {
      // ตรวจสอบสมาชิกกับฐานข้อมูล
      $login_result = self::checkMember($username, $password);
      if (is_string($login_result)) {
        return $login_result;
      } else {
        // model
        $model = new Model;
        // ตรวจสอบการ login มากกว่า 1 ip
        $ip = self::$request->getClientIp();
        if (self::$cfg->member_only_ip && !empty($ip)) {
          $online = $model->db()->createQuery()
            ->from('useronline')
            ->where(array(
              array('member_id', (int)$login_result['id']),
              array('ip', 'NOT IN', array('', $ip))
            ))
            ->order('time DESC')
            ->toArray()
            ->first('time');
          if ($online && time() - (int)$online['time'] < self::$cfg->count_gap) {
            // login ต่าง ip กัน
            return Language::get('Members of this system already');
          }
        }
        // current session
        $session_id = session_id();
        // อัปเดทการเยี่ยมชม
        if ($session_id != $login_result['session_id']) {
          $login_result['visited'] ++;
          $model->db()->createQuery()
            ->update('user')
            ->set(array(
              'session_id' => $session_id,
              'visited' => $login_result['visited'],
              'lastvisited' => time(),
              'ip' => $ip
            ))
            ->where((int)$login_result['id'])
            ->execute();
        }
      }
    }
    return $login_result;
  }

  /**
   * ตรวจสอบความสามารถในการเข้าระบบแอดมิน
   *
   * @return array|null คืนค่าข้อมูลสมาชิก (แอเรย์) ถ้าสามารถเข้าระบบแอดมินได้ ไม่ใช่คืนค่า null
   */
  public static function adminAccess()
  {
    $login = self::isMember();
    return isset($login['admin_access']) && $login['admin_access'] == 1 ? $login : null;
  }

  /**
   * ฟังก์ชั่นส่งอีเมล์ลืมรหัสผ่าน
   */
  public function forgot(Request $request)
  {
    // ค่าที่ส่งมา
    $email = $request->post('login_username')->url();
    if (empty($email)) {
      if ($request->post('action')->toString() === 'forgot') {
        self::$login_message = Language::get('Please fill out this form');
      }
    } else {
      self::$text_username = $email;
      // ค้นหาอีเมล์หรือโทรศัพท์
      $model = new Model;
      $user_table = $model->getFullTableName('user');
      $search = $model->db()->first($user_table, array(array('email', $email), array('fb', '0')));
      if ($search === false) {
        self::$login_message = Language::get('not a registered user');
      } else {
        // รหัสผ่านใหม่
        $password = Text::rndname(6);
        // ข้อมูลอีเมล์
        $replace = array(
          '/%PASSWORD%/' => $password,
          '/%EMAIL%/' => $search->email
        );
        // send mail
        $err = Email::send(3, 'member', $replace, $search->email);
        if (empty($err)) {
          // อัปเดทรหัสผ่านใหม่
          $model->db()->update($user_table, (int)$search->id, array('password' => md5($password.$search->email)));
          // คืนค่า
          self::$login_message = Language::get('Your message was sent successfully');
          self::$request = $request->withParsedBody(array('action' => 'login'));
        } else {
          self::$login_message = $err;
        }
      }
    }
  }
}