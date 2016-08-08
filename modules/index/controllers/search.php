<?php
/*
 * @filesource index/controllers/search.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Search;

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
   * แสดงผลโมดูล Index
   *
   * @param Request $request
   * @param Object $module ข้อมูลโมดูลจาก database
   * @return object||null คืนค่าข้อมูลหน้าที่เรียก ไม่พบคืนค่า null
   */
  public function init(Request $request, $module)
  {
    return createClass('Index\Search\View')->render(\Index\Search\Model::findAll($request, $module));
  }
}