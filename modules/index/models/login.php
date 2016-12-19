<?php
/*
 * @filesource index/models/login.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Login;

use \Gcms\Login;
use \Kotchasan\Language;
use \Kotchasan\Template;

/**
 * Controller หลัก สำหรับแสดง frontend ของ GCMS
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\KBase
{

  /**
   * ฟังก์ชั่นตรวจสอบการ Login
   */
  public function chklogin()
  {
    if (self::$request->initSession() && self::$request->isSafe()) {
      // กำหนด skin ให้กับ template
      Template::init(self::$cfg->skin);
      // ตรวจสอบการ login
      Login::create();
      // ตรวจสอบสมาชิก
      $login = Login::isMember();
      if ($login) {
        $name = trim($login['fname'].' '.$login['lname']);
        $ret = array(
          'alert' => Language::replace('Welcome %s, login complete', array('%s' => empty($name) ? $login['email'] : $name)),
          'content' => rawurlencode(\Index\Login\Controller::init($login)),
          'action' => self::$request->post('login_action', self::$cfg->login_action)->toString()
        );
        // clear
        self::$request->removeToken();
      } else {
        $ret = array(
          'alert' => Login::$login_message,
          'input' => Login::$login_input
        );
      }
      // คืนค่า JSON
      echo json_encode($ret);
    }
  }
}