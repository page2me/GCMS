<?php
/*
 * @filesource board/models/sitemap.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Board\Sitemap;

/**
 * กระทู้ทั้งหมด
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Model
{

  /**
   * กระทู้ทั้งหมด
   *
   * @param array $ids แอเรย์ของ module_id
   * @return array
   */
  public static function getStories($ids)
  {
    if (defined('MAIN_INIT')) {
      $model = new static;
      return $model->db()->createQuery()
          ->select('id', 'module_id', 'last_update', 'comment_date')
          ->from('board_q')
          ->where(array('module_id', $ids))
          ->cacheOn()
          ->execute();
    } else {
      // เรียก method โดยตรง
      new \Kotchasan\Http\NotFound('Do not call method directly');
    }
  }
}