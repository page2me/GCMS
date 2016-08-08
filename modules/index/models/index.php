<?php
/*
 * @filesource index/models/index.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Index;

use \Kotchasan\Date;
use \Kotchasan\Language;

/**
 * ตาราง index
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Orm\Field
{
  /**
   * ชื่อตาราง
   *
   * @var string
   */
  protected $table = 'index I';

  /**
   * อ่านข้อมูลโมดูลจาก $module_id
   *
   * @param int $module_id
   * @return object|false คืนค่าข้อมูล object ไม่พบ คืนค่า false
   */
  public static function getIndex($module_id)
  {
    if (is_int($module_id) && $module_id > 0) {
      $model = new \Kotchasan\Model;
      $select = array('I.id', 'M.module', 'M.owner', 'D.topic', 'D.description', 'D.keywords', 'D.detail', 'I.visited');
      $where = array(
        array('I.index', 1),
        array('I.module_id', $module_id),
        array('I.published', 1),
        array('I.published_date', '<=', Date::mktimeToSqlDate(time()))
      );
      $result = $model->db()->createQuery()
        ->from('modules M')
        ->join('index I', 'INNER', array('I.module_id', 'M.id'))
        ->join('index_detail D', 'INNER', array(array('D.id', 'I.id'), array('D.module_id', 'M.id'), array('D.language', 'I.language')))
        ->where($where)
        ->toArray()
        ->cacheOn(false)
        ->first($select);
      if ($result) {
        $result['visited'] ++;
        $model->db()->cache()->save(array($result));
        $model->db()->update($model->getFullTableName('index'), $result['id'], array('visited' => $result['visited']));
        return (object)$result;
      }
    }
    return false;
  }

  /**
   * อ่านข้อมูลโมดูลจาก $id
   *
   * @param int $id
   * @return object|false คืนค่าข้อมูล object ไม่พบ คืนค่า false
   */
  public static function getIndexById($id)
  {
    if (is_int($id) && $id > 0) {
      $model = new \Kotchasan\Model;
      $select = array('I.id', 'M.module', 'M.owner', 'D.topic', 'D.description', 'D.keywords', 'D.detail', 'I.visited');
      $where = array(
        array('I.id', $id),
        array('I.index', 1),
        array('I.published', 1),
        array('I.published_date', '<=', Date::mktimeToSqlDate(time()))
      );
      $result = $model->db()->createQuery()
        ->from('index I')
        ->join('modules M', 'INNER', array('M.id', 'I.module_id'))
        ->join('index_detail D', 'INNER', array(array('D.id', 'I.id'), array('D.module_id', 'M.id'), array('D.language', 'I.language')))
        ->where($where)
        ->toArray()
        ->cacheOn(false)
        ->first($select);
      if ($result) {
        $result['visited'] ++;
        $model->db()->update($model->getFullTableName('index'), $result['id'], array('visited' => $result['visited']));
        $model->db()->cache()->save(array($result));
        return (object)$result;
      }
    }
    return false;
  }

  /**
   * อ่านข้อมูลโมดูลจากชื่อโมดูล
   *
   * @param string $module
   * @param type $owner
   * @return object|false คืนค่าข้อมูล object ไม่พบ คืนค่า false
   */
  public static function getModule($module, $owner)
  {
    if (is_string($module) && is_string($owner)) {
      $model = new \Kotchasan\Model;
      $select = array('I.id', 'I.module_id', 'M.module', 'M.owner', 'D.topic', 'D.description', 'D.keywords', 'D.detail', 'I.visited');
      $where = array(
        array('I.index', 1),
        array('M.module', $module),
        array('M.owner', $owner),
        array('I.published', 1),
        array('I.published_date', '<=', Date::mktimeToSqlDate(time())),
        array('D.language', array(Language::name(), ''))
      );
      return $model->db()->createQuery()
          ->from('index I')
          ->join('modules M', 'INNER', array('M.id', 'I.module_id'))
          ->join('index_detail D', 'INNER', array(array('D.id', 'I.id'), array('D.module_id', 'M.id'), array('D.language', 'I.language')))
          ->where($where)
          ->cacheOn()
          ->first($select);
    }
    return false;
  }
}