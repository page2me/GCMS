<?php
/**
 * @filesource event/models/calendar.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Event\Calendar;

use \Kotchasan\Database\Sql;

/**
 * ปฎิทิน
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Model
{

  /**
   * query Event รายเดือน
   *
   * @param int $year
   * @param int $month
   */
  public static function get($year, $month)
  {
    $model = new static;
    return $model->db()->createQuery()
        ->select('D.id', 'D.topic', 'M.module', 'D.color', Sql::DAY('D.begin_date', 'd'))
        ->from('event D')
        ->join('modules M', 'INNER', array('M.id', 'D.module_id'))
        ->where(array(
          array(Sql::MONTH('D.begin_date'), $month),
          array(Sql::YEAR('D.begin_date'), $year)
        ))
        ->order('begin_date DESC', 'end_date')
        ->cacheOn()
        ->execute();
  }

  /**
   * URL ของปฏิทิน
   *
   * @param string $module
   * @param int $year
   * @param int $month
   * @param int $day
   * @return string
   */
  public static function getUri($module, $year, $month, $day)
  {
    return WEB_URL."index.php?module=$module&d=$year-$month-$day";
  }
}