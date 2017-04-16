<?php
/**
 * @filesource Widgets/Tags/Controllers/Index.php
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
class Index extends \Kotchasan\Model
{

  public static function get()
  {
    $model = new static;
    return $model->db()->createQuery()
        ->select()
        ->from('tags')
        ->order('count')
        ->toArray()
        ->cacheOn()
        ->execute();
  }
}