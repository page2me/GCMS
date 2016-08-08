<?php
/*
 * @filesource board/controllers/edit.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Board\Edit;

use \Kotchasan\Http\Request;

/**
 * แก้ไขกระทู้และความคิดเห็น
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Kotchasan\Controller
{

  /**
   * แก้ไขกระทู้และความคิดเห็น
   *
   * @param Request $request
   * @param object $module ข้อมูลโมดูลจาก database
   * @return object
   */
  public function init(Request $request, $module)
  {
    // รายการที่แก้ไข
    $qid = $request->get('qid')->toInt();
    $rid = $request->get('rid')->toInt();
    // ตรวจสอบโมดูลและอ่านข้อมูลโมดูล
    if ($qid > 0) {
      $index = \Board\Module\Model::getQuestionById($qid, $module);
    } elseif ($rid > 0) {
      $index = \Board\Module\Model::getCommentById($rid, $module);
    }
    if (empty($index)) {
      // 404
      $page = createClass('Index\PageNotFound\Controller')->init($request, 'board');
    } elseif ($qid > 0) {
      // ฟอร์มแก้ไขกระทู้
      $page = createClass('Board\Writeedit\View')->index($request, $index);
    } elseif ($rid > 0) {
      // ฟอร์มแก้ไขความคิดเห็น
      $page = createClass('Board\Replyedit\View')->index($request, $index);
    }
    return $page;
  }
}