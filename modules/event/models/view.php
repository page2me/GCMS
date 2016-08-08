<?php
/*
 * @filesource event/models/view.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Event\View;

use \Kotchasan\Http\Request;

/**
 * อ่านข้อมูลโมดูลและบทความที่เลือก
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Model
{

  /**
   * อ่านรายการที่เลือก
   *
   * @param Request $request
   * @param Object $index
   * @return Object
   */
  public static function get(Request $request, $index)
  {
    $model = new static;
    $search = $model->db()->createQuery()
      ->from('eventcalendar D')
      ->join('user U', 'LEFT', array('U.id', 'D.member_id'))
      ->where(array(array('D.id', $request->request('id')->toInt()), array('D.module_id', (int)$index->module_id)))
      ->cacheOn()
      ->toArray()
      ->first('D.*', 'U.fname', 'U.lname', 'U.email', 'U.status');
    if ($search) {
      foreach ($search as $key => $value) {
        $index->$key = $value;
      }
      return $index;
    }
    return null;
  }
}