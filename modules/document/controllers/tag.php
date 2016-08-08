<?php
/*
 * @filesource document/controllers/tag.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Document\Tag;

use \Kotchasan\Http\Request;
use \Kotchasan\Language;

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
   * หน้าแสดงบทความจาก Tags
   *
   * @param Object $module ข้อมูลโมดูลจาก database
   * @return Object
   */
  public function init(Request $request, $module)
  {
    // ลิสต์รายการ tag
    $index = \Document\Stories\Model::tags($request, $module);
    if (empty($index)) {
      // 404
      $list = createClass('Index\PageNotFound\Controller')->init($request, 'document');
    } else {
      // ลิสต์รายการ tag
      $index->tag = $index->alias;
      $index->module = 'document';
      $index->list_per_page = 20;
      $index->new_date = 0;
      $index->topic = Language::get('Tags').' '.$index->alias;
      $index->description = $index->topic;
      $index->keywords = $index->topic;
      $index->detail = '';
      $list = createClass('Document\Stories\View')->index($request, $index);
    }
    return $list;
  }
}