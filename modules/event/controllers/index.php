<?php
/**
 * @filesource event/controllers/index.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Event\Index;

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
    $index = \Index\Module\Model::getDetails($index);
    if ($index && MAIN_INIT === 'indexhtml') {
      if ($request->request('id')->exists()) {
        // แสดงรายการที่เลือก
        $page = createClass('Event\View\View')->index($request, $index);
      } elseif ($request->request('d')->exists()) {
        // แสดง event รายวัน
        $page = createClass('Event\Day\View')->index($request, $index);
      } else {
        // แสดง event รายเดือน
        $page = createClass('Event\Month\View')->index($request, $index);
      }
    }
    if ($page) {
      return $page;
    }
    // 404
    return createClass('Index\PageNotFound\Controller')->init('event');
  }
}