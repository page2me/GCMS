<?php
/**
 * @filesource Widgets/Download/Models/Download.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Widgets\Download\Models;

/**
 * ไฟล์ดาวน์โหลด
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Download extends \Kotchasan\Model
{

  /**
   * ไฟล์ดาวน์โหลด
   *
   * @param int $id
   * @return array
   */
  public static function get($id)
  {
    // query
    $model = new static;
    return $model->db()->createQuery()->from('download')->where((int)$id)->cacheOn()->toArray()->first();
  }
}