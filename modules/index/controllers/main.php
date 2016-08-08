<?php
/*
 * @filesource index/controllers/main.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Main;

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
    // เรียกจากโมดูล index
    $index = null;
    if (!empty($module->module_id)) {
      $index = \Index\Index\Model::getIndex((int)$module->module_id);
    } elseif (!empty($module->id)) {
      $index = \Index\Index\Model::getIndexById((int)$module->id);
    }
    if ($index) {
      // view (index)
      return createClass('Index\Index\View')->render($index);
    }
    return null;
  }
}