<?php
/*
 * @filesource board/models/view.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Board\View;

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
   * อ่านกระทู้ที่ $id
   *
   * @param int $id
   * @return object ข้อมูล object ไม่พบคืนค่า null
   */
  public static function get($id)
  {
    // model
    $model = new static;
    // select
    $fields = array(
      'I.*',
      'U.status',
      'U.id member_id',
      'C.config',
      'C.topic category',
      'C.detail cat_tooltip',
      "(CASE WHEN ISNULL(U.`id`) THEN (CASE WHEN I.`sender`='' THEN I.`email` ELSE I.`sender` END) WHEN U.`displayname`='' THEN U.`email` ELSE U.`displayname` END) name",
    );
    $query = $model->db()->createQuery()
      ->select($fields)
      ->from('board_q I')
      ->join('user U', 'LEFT', array('U.id', 'I.member_id'))
      ->join('category C', 'LEFT', array(array('C.category_id', 'I.category_id'), array('C.module_id', 'I.module_id')))
      ->where(array('I.id', $id))
      ->limit(1)
      ->toArray();
    if (self::$request->get('visited')->toInt() == 0) {
      $query->cacheOn(false);
    }
    $result = $query->execute();
    if (sizeof($result) == 1) {
      $result[0]['visited'] ++;
      $model->db()->update($model->getFullTableName('board_q'), $result[0]['id'], array('visited' => $result[0]['visited']));
      $model->db()->cacheSave($result[0]);
      $result[0]['config'] = @unserialize($result[0]['config']);
      return (object)$result[0];
    }
    return null;
  }
}