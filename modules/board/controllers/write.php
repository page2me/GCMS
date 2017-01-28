<?php
/*
 * @filesource board/controllers/write.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Board\Write;

use \Kotchasan\Http\Request;

/**
 * Controller หลัก สำหรับแสดง frontend ของ GCMS
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Kotchasan\Controller
{

  /**
   * เขียนความคิดเห็น
   *
   * @param Request $request
   * @param object $module ข้อมูลโมดูลจาก database
   * @return object
   */
  public function init(Request $request, $module)
  {
    // รายการที่แก้ไข
    $qid = $request->request('id')->toInt();
    // ตรวจสอบโมดูลและอ่านข้อมูลโมดูล
    if ($qid > 0) {
      // แก้ไข
      $index = \Board\Module\Model::getQuestionById($qid, $module);
      if ($index) {
        // ฟอร์มแก้ไขกระทู้
        $page = createClass('Board\Writeedit\View')->index($request, $index);
      }
    } else {
      // ใหม่
      $index = \Board\Module\Model::get($request, $module);
      if ($index) {
        // ฟอร์มโพสต์กระทู้
        $page = createClass('Board\Write\View')->index($request, $index);
      }
    }
    if (empty($page)) {
      // ไม่พบหน้าที่เรียก (board)
      $page = createClass('Index\PageNotFound\Controller')->init('board');
    }
    return $page;
  }
}