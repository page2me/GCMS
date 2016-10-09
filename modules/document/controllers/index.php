<?php
/*
 * @filesource document/controllers/index.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Document\Index;

use \Gcms\Gcms;
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
    // รายการที่เลือก
    $id = $request->request('id')->toInt();
    $document = $request->request('alias')->text();
    // ตรวจสอบโมดูลและอ่านข้อมูลโมดูล
    $index = \Document\Module\Model::get($request, $index);
    if (empty($index)) {
      // 404
      $page = createClass('Index\PageNotFound\Controller')->init($request, 'document');
    } elseif (!empty($document) || !empty($id)) {
      // หน้าแสดงบทความ
      if (MAIN_INIT === 'amphtml') {
        $page = createClass('Document\Amp\View')->index($request, $index);
      } else {
        $page = createClass('Document\View\View')->index($request, $index);
      }
    } elseif (!empty($index->category_id) || empty($index->categories) || empty($index->category_display)) {
      // เลือกหมวดมา หรือไม่มีหมวด หรือปิดการแสดงผลหมวดหมู่ แสดงรายการบทความ
      $stories = \Document\Stories\Model::stories($request, $index);
      if (empty($stories)) {
        // 404
        $page = createClass('Index\PageNotFound\Controller')->init($request, 'document');
      } else {
        $page = createClass('Document\Stories\View')->index($request, $stories);
      }
    } else {
      // หน้าแสดงรายการหมวดหมู่
      $page = createClass('Document\Categories\View')->index($request, $index);
    }
    // menu
    $page->menu = empty($index->alias) ? $index->module : $index->alias;
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