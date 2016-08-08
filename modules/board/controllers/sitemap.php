<?php
/*
 * @filesource board/controllers/sitemap.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Board\Sitemap;

use \Board\Index\Controller AS Module;

/**
 * sitemap.xml
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Kotchasan\Controller
{

  /**
   * แสดงผล sitemap.xml
   *
   * @param array $ids แอเรย์ของ module_id
   * @param array $modules แอเรย์ของ module ที่ติดตั้งแล้ว
   * @param string $date วันที่วันนี้
   * @return array
   */
  public function init($ids, $modules, $date)
  {
    $result = array();
    foreach (\Board\Sitemap\Model::getStories($ids) as $item) {
      $result[] = (object)array(
          'url' => Module::url($modules[$item->module_id], 0, $item->id),
          'date' => date("Y-m-d", ($item->comment_date == 0 ? $item->last_update : $item->comment_date))
      );
    }
    return $result;
  }
}