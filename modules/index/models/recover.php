<?php
/*
 * @filesource index/models/recover.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Recover;

use \Kotchasan\Http\Request;
use \Kotchasan\Text;
use \Kotchasan\Language;
use \Gcms\Email;

/**
 * ขอรหัสผ่านใหม่
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
    // referer, session
    if ($request->initSession() && $request->isReferer()) {
      $ret = array();
      // ค่าที่ส่งมา
      $email = $request->post('forgot_email')->url();
      if ($email === '') {
        $ret['ret_forgot_email'] = 'Please fill in';
      } else {
        $search = $this->db()->createQuery()
          ->from('user')
          ->where(array(array('email', $email), array('fb', '0')))
          ->toArray()
          ->first('id', 'email');
        if ($search === false) {
          $ret['ret_forgot_email'] = Language::get('not a registered user');
        }
      }
      if (empty($ret)) {
        // รหัสผ่านใหม่
        $password = Text::rndname(6);
        // ข้อมูลอีเมล์
        $replace = array(
          '/%PASSWORD%/' => $password,
          '/%EMAIL%/' => $search['email']
        );
        // send mail
        $err = Email::send(3, 'member', $replace, $search['email']);
        if (empty($err)) {
          // อัปเดทรหัสผ่านใหม่
          $save = array('password' => md5($password.$search['email']));
          $this->db()->createQuery()->update('user')->set($save)->where($search['id'])->execute();
          // คืนค่า
          $ret['alert'] = Language::get('Your message was sent successfully');
          $location = $request->post('modal')->url();
          $ret['location'] = $location === 'true' ? 'close' : $location;
        } else {
          $ret['ret_forgot_email'] = $err;
        }
      }
      // คืนค่าเป็น JSON
      echo json_encode($ret);
    }
  }
}