<?php
/*
 * @filesource event/models/calendar.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Event\Calendar;

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
        ->select('D.id', 'D.topic', 'D.color', 'M.module', 'DAY(D.`begin_date`) AS `d`')
        ->from('eventcalendar D')
        ->join('modules M', 'INNER', array(array('M.id', 'D.module_id'), array('M.owner', 'event')))
        ->where(array(
          array('MONTH(D.`begin_date`)', $month),
          array('YEAR(D.`begin_date`)', $year)
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