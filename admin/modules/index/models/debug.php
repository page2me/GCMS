<?php
/*
 * @filesource index/models/debug.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Debug;

use \Kotchasan\Login;

/**
 * get debug datas
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\KBase
{

  /**
   * ฟังก์ชั่นจัดการ debug อ่าน,ลบ
   */
  public function action()
  {
    // referer, session, admin
    if (self::$request->initSession() && self::$request->isReferer() && $login = Login::isAdmin()) {
      if ($login['email'] != 'demo' && empty($login['fb'])) {
        // action
        $action = self::$request->post('action')->toString();
        // file debug
        $debug = ROOT_PATH.DATA_FOLDER.'logs/error_log.php';
        if (is_file($debug)) {
          if ($action == 'get') {
            // อ่าน debug
            $t = self::$request->post('t')->toString();
            foreach (file($debug) as $i => $row) {
              if (preg_match('/^\[([0-9\-:\s]+)\][\s]+([A-Z]+):[\s]+(.*)/', trim($row), $match)) {
                if ($match[1] > $t) {
                  echo "$match[1]\t$match[2]\t$match[3]\n";
                }
              }
            }
          } elseif ($action == 'clear') {
            // ลบไฟล์ debug
            unlink($debug);
          }
        }
      }
    }
  }
}