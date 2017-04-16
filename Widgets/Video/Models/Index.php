<?php
/**
 * @filesource Widgets/Video/Models/Index.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Widgets\Video\Models;

/**
 * Controller หลัก สำหรับแสดงผล Widget
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Index extends \Kotchasan\Model
{

  public static function get($id, $count)
  {
    $model = new static;
    $query = $model->db()->createQuery()
      ->select('id', 'topic', 'youtube')
      ->from('video');
    if ($id > 0) {
      $query->where(array('id', $id));
    } else {
      $query->order('id DESC');
    }
    return $query->limit($count)
        ->toArray()
        ->cacheOn()
        ->execute();
  }
}