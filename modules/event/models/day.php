<?php
/**
 * @filesource event/models/day.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Event\Day;

use \Kotchasan\Http\Request;

/**
 * อ่านข้อมูลโมดูล
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Model
{

  /**
   * ลิสต์รายการอีเว้นต์รายวันที่เลือก
   *
   * @param Request $request
   * @param Object $index
   * @return Object
   */
  public static function get(Request $request, $index)
  {
    if (preg_match('/^([0-9]{4,4})\-([0-9]{1,2})\-([0-9]{1,2})$/', $request->request('d')->toString(), $match)) {
      $index->year = (int)$match[1];
      $index->month = (int)$match[2];
      $index->day = (int)$match[3];
      $model = new static;
      $index->items = $model->db()->createQuery()
        ->select('id', 'color', 'topic', 'description', 'begin_date', 'end_date')
        ->from('event')
        ->where(array(
          array('YEAR(`begin_date`)', $index->year),
          array('MONTH(`begin_date`)', $index->month),
          array('DAY(`begin_date`)', $index->day),
          array('module_id', (int)$index->module_id)
        ))
        ->order('begin_date', 'end_date')
        ->cacheOn()
        ->execute();
      return $index;
    }
    return null;
  }
}