<?php
/*
 * @filesource portfolio/models/sitemap.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Portfolio\Sitemap;

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
  public static function getAll($ids)
  {
    $model = new static;
    return $model->db()->createQuery()
        ->select('id', 'module_id', 'create_date')
        ->from('portfolio')
        ->where(array('module_id', $ids))
        ->cacheOn()
        ->execute();
  }
}