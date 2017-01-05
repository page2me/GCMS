<?php
/*
 * @filesource product/controllers/index.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Product\Index;

use \Kotchasan\Http\Request;
use \Gcms\Gcms;

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
    if ($request->get('alias')->exists() || $request->get('id')->exists()) {
      // หน้าแสดงบทความ
      if (MAIN_INIT === 'amphtml') {
        $page = createClass('Product\Amp\View')->index($request, $index);
      } else {
        $page = createClass('Product\View\View')->index($request, $index);
      }
    } elseif (MAIN_INIT === 'indexhtml') {
      // ตรวจสอบโมดูลและอ่านข้อมูลโมดูล
      $index = \Index\Module\Model::getDetails($index);
      if ($index) {
        // รายการสินค้า
        $stories = \Product\Stories\Model::stories($request, $index);
        if (!empty($stories)) {
          $page = createClass('Product\Stories\View')->index($request, $stories);
        }
      }
    }
    if (empty($page)) {
      // ไม่พบหน้าที่เรียก (product)
      $page = createClass('Index\PageNotFound\Controller')->init('product');
    }
    return $page;
  }

  /**
   * ฟังก์ชั่นสร้าง URL
   *
   * @param string $module ชื่อโมดูล
   * @param string $alias alias ของบทความ
   * @param int $id ID
   * @param boolean $encode (option) true=เข้ารหัสด้วย rawurlencode ด้วย (default true)
   * @return string
   */
  public static function url($module, $alias, $id, $encode = true)
  {
    if (self::$cfg->module_url == 1) {
      return Gcms::createUrl($module, $alias, 0, 0, '', $encode);
    } else {
      return Gcms::createUrl($module, '', 0, $id, '', $encode);
    }
  }
}