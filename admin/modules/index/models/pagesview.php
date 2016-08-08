<?php
/*
 * @filesource index/models/pagesview.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Pagesview;

/**
 * อ่านข้อมูลการเยี่ยมชมในเดือนที่เลือก
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Model
{

  /**
   * อ่านข้อมูลการเยี่ยมชมในเดือนที่เลือก
   *
   * @param string $date
   * @return array
   */
  public static function get($date)
  {
    $datas = array();
    if (preg_match('/^([0-9]+)\-([0-9]+)$/', $date, $match)) {
      $y = (int)$match[1];
      $m = (int)$match[2];
      $model = new static;
      $query = $model->db()->createQuery()
        ->select('date', 'SUM(`pages_view`) AS `pages_view`')
        ->from('counter')
        ->where(array(array('YEAR(`date`)', $y), array('MONTH(`date`)', $m)))
        ->groupBy('date')
        ->order('date ASC')
        ->toArray()
        ->cacheOn();
      foreach ($query->execute() as $item) {
        $datas[$item['date']] = $item;
      }
    }
    return $datas;
  }
}