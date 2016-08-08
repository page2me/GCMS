<?php
/*
 * @filesource edocument/controllers/report.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Edocument\Report;

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
    $index = \Edocument\Report\Model::get($request, $index);
    if (empty($index)) {
      // 404
      $page = createClass('Index\PageNotFound\Controller')->init($request, 'edocument');
    } else {
      // รายการไฟล์ดาวน์โหลด
      $page = createClass('Edocument\Report\View')->index($request, $index);
    }
    return $page;
  }
}