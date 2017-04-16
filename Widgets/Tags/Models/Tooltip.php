<?php
/**
 * @filesource Widgets/Tags/Controllers/Tooltip.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Widgets\Tags\Models;

/**
 * Controller สำหรับจัดการการตั้งค่าเริ่มต้น
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Tooltip extends \Kotchasan\Model
{

  /**
   * อ่านข้อมูลที่ $id
   * 
   * @param int $id
   * @return array
   */
  public static function get($id)
  {
    $model = new static;
    return $model->db()->createQuery()
        ->select()
        ->from('tags')
        ->where($id)
        ->toArray()
        ->cacheOn()
        ->first();
  }
}