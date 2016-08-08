<?php
/*
 * @filesource gallery/models/sitemap.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Gallery\Sitemap;

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
   * อัลบัมทั้งหมด
   *
   * @param array $ids แอเรย์ของ module_id
   * @param string $date วันที่วันนี้
   * @return array
   */
  public static function getAlbums($ids, $date)
  {
    if (defined('MAIN_INIT')) {
      $model = new static;
      return $model->db()->createQuery()
          ->select('id', 'module_id', 'last_update')
          ->from('gallery_album')
          ->where(array('module_id', $ids))
          ->cacheOn()
          ->execute();
    } else {
      // เรียก method โดยตรง
      new \Kotchasan\Http\NotFound('Do not call method directly');
    }
  }
}