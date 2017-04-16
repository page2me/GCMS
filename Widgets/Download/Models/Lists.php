<?php
/**
 * @filesource Widgets/Download/Models/Lists.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Widgets\Download\Models;

/**
 * รายการไฟล์ดาวน์โหลด
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Lists extends \Kotchasan\Model
{

  /**
   * รายการไฟล์ดาวน์โหลด
   *
   * @param int $module_id
   * @param string $categories
   * @param int $limit
   * @return array
   */
  public static function get($module_id, $categories, $limit)
  {
    // query
    $model = new static;
    $where = array(
      array('module_id', (int)$module_id),
    );
    if (!empty($categories)) {
      $where[] = "`category_id` IN ($categories)";
    }
    return $model->db()->createQuery()
        ->select()
        ->from('download')
        ->where($where)
        ->order('last_update DESC')
        ->limit((int)$limit)
        ->cacheOn()
        ->execute();
  }
}