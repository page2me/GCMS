<?php
/*
 * @filesource document/models/sitemap.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Document\Sitemap;

/**
 * บทความทั้งหมด
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Model
{

  /**
   * บทความทั้งหมด
   *
   * @param array $ids แอเรย์ของ module_id
   * @param string $date วันที่วันนี้
   * @return array
   */
  public static function getStories($ids, $date)
  {
    if (defined('MAIN_INIT')) {
      $model = new static;
      return $model->db()->createQuery()
          ->select('id', 'module_id', 'alias', 'create_date')
          ->from('index')
          ->where(array(array('module_id', $ids), array('index', 0), array('published', 1), array('published_date', '<=', $date)))
          ->cacheOn()
          ->execute();
    } else {
      // เรียก method โดยตรง
      new \Kotchasan\Http\NotFound('Do not call method directly');
    }
  }
}