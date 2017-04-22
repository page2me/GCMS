<?php
/**
 * @filesource event/models/day.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Event\Day;

use \Kotchasan\Http\Request;
use \Kotchasan\Database\Sql;

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
      $index->date = "$match[1]-$match[2]-$match[3]";
      $select = array(
        'id',
        'color',
        'topic',
        'description',
        Sql::DATE('begin_date', 'begin_date'),
        Sql::DATE_FORMAT('begin_date', '%h:%i', 'from'),
        Sql::DATE('end_date', 'end_date'),
        Sql::DATE_FORMAT('end_date', '%h:%i', 'to')
      );
      $model = new static;
      $index->items = $model->db()->createQuery()
        ->select($select)
        ->from('event')
        ->where(array(
          array(Sql::YEAR('begin_date'), $match[1]),
          array(Sql::MONTH('begin_date'), $match[2]),
          array(Sql::DAY('begin_date'), $match[3]),
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