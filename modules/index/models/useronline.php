<?php
/*
 * @filesource index/models/useronline.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Useronline;

use \Kotchasan\Http\Request;
use \Kotchasan\Login;

/**
 * Useronline
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Model
{

  /**
   * Useronline
   *
   * @param array $query_string
   */
  public function index(Request $request)
  {
    // ตรวจสอบ Referer
    if ($request->initSession() && $request->isReferer()) {
      // ตัวแปรป้องกันการเรียกหน้าเพจโดยตรง
      define('MAIN_INIT', __FILE__);
      // เวลาปัจจุบัน
      $time = time();
      // sesssion ปัจจุบัน
      $session_id = session_id();
      // เวลาหมดอายุ
      $validtime = $time - self::$cfg->counter_gap;
      // ตาราง useronline
      $useronline = $this->getFullTableName('useronline');
      // ลบคนที่หมดเวลาและตัวเอง
      $this->db()->delete($useronline, array(array('time', '<', $validtime), array('session', $session_id)), 0, 'OR');
      // เพิ่มตัวเอง
      $save = array(
        'time' => $time,
        'session' => $session_id,
        'ip' => $request->getClientIp()
      );
      $login = Login::isMember();
      if ($login) {
        $save['member_id'] = (int)$login['id'];
        $save['displayname'] = $login['displayname'] == '' ? $login['email'] : $login['displayname'];
      }
      $this->db()->insert($useronline, $save);
      // คืนค่า user online
      $ret = array(
        'time' => $time
      );
      // โหลด useronline ของ module
      $dir = ROOT_PATH.'modules/';
      $f = @opendir($dir);
      if ($f) {
        while (false !== ($text = readdir($f))) {
          if ($text != "." && $text != "..") {
            if (is_dir($dir.$text)) {
              if (is_file($dir.$text.'/controllers/useronline.php')) {
                include $dir.$text.'/controllers/useronline.php';
                $class = ucfirst($text).'\Useronline\Controller';
                if (method_exists($class, 'index')) {
                  $ret = createClass($class)->index($ret);
                }
              }
            }
          }
        }
        closedir($f);
      }
      // คืนค่า JSON
      echo json_encode($ret);
    }
  }
}