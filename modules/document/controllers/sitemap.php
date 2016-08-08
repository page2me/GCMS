<?php
/*
 * @filesource document/controllers/sitemap.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Document\Sitemap;

use \Document\Index\Controller AS Module;

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
    foreach (\Document\Sitemap\Model::getStories($ids, $date) as $item) {
      $result[] = (object)array(
          'url' => Module::url($modules[$item->module_id], $item->alias, $item->id),
          'date' => date('Y-m-d', $item->create_date)
      );
    }
    return $result;
  }
}