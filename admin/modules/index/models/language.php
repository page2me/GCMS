<?php
/*
 * @filesource index/models/language.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Language;

use \Kotchasan\Login;
use \Kotchasan\ArrayTool;
use \Kotchasan\Language;

/**
 * บันทึกรายการภาษา
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\KBase
{

  /**
   * รับค่าจาก action
   */
  public function action()
  {
    $ret = array();
    // referer, session, admin
    if (self::$request->initSession() && self::$request->isReferer() && $login = Login::isAdmin()) {
      if (empty($login['fb'])) {
        // ค่าที่ส่งมา
        $type = self::$request->post('type')->toString();
        $type = $type == 'js' ? 'js' : 'php';
        $id = self::$request->post('id')->toString();
        $action = self::$request->post('action')->toString();
        if ($action == 'delete') {
          // โหลดภาษา
          $datas = Language::installed($type);
          // ลบรายการที่ส่งมา
          $datas = ArrayTool::delete($datas, $id);
          // save
          $error = Language::save($datas, $type);
          if (empty($error)) {
            $ret['location'] = 'reload';
          } else {
            $ret['alert'] = $error;
          }
        }
      }
    } else {
      $ret['alert'] = Language::get('Unable to complete the transaction');
    }
    echo json_encode($ret);
  }
}