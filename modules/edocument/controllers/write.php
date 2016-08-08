<?php
/*
 * @filesource edocument/controllers/write.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Edocument\Write;

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
   * Controller หลักของโมดูล ใช้เพื่อตรวจสอบว่าจะเรียกหน้าไหนมาแสดงผล
   *
   * @param Request $request
   * @param object $index ข้อมูลโมดูล
   * @return object
   */
  public function init(Request $request, $index)
  {
    // ตรวจสอบโมดูลและอ่านข้อมูลโมดูล
    $index = \Edocument\Write\Model::getForWrite($index, $request->request('id')->toInt());
    if ($index) {
      // แก้ไข, ใหม่
      $page = createClass('Edocument\Write\View')->index($request, $index);
    } else {
      // 404
      $page = createClass('Index\PageNotFound\Controller')->init($request, 'edocument');
    }
    return $page;
  }
}