<?php
/**
 * @filesource documentation/models/sitemap.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Documentation\Sitemap;

/**
 * Model สำหรับ Sitemap
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Model
{

  /**
   * query ข้อมูลทั้งหมดสำหรับสร้าง sitemap
   *
   * @param array $ids แอเรย์ของ module_id
   * @return array
   */
  public static function getStories($ids)
  {
    $model = new static;
    return $model->db()->createQuery()
        ->select('id', 'module_id', 'alias', 'create_date')
        ->from('index')
        ->where(array(array('module_id', $ids), array('index', 0), array('published', 1)))
        ->cacheOn()
        ->execute();
  }

  /**
   * หมวดหมู่ทั้งหมด
   *
   * @param array $ids แอเรย์ของ module_id
   * @return array
   */
  public static function getCategories($ids)
  {
    $model = new static;
    return $model->db()->createQuery()
        ->select('category_id', 'module_id')
        ->from('category')
        ->where(array(array('module_id', $ids), array('published', '1')))
        ->cacheOn()
        ->execute();
  }
}