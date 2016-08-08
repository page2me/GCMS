<?php
/*
 * @filesource document/models/view.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Document\View;

use \Kotchasan\Language;

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
   * อ่านบทความที่ $id หรือ $alias
   *
   * @param int $module_id
   * @param int $id
   * @param string $alias
   * @return object
   */
  public static function get($module_id, $id, $alias)
  {
    if (is_int($module_id) && $module_id > 0) {
      // model
      $model = new static;
      // select
      $fields = array(
        'I.id',
        'I.module_id',
        'I.category_id',
        'D.topic',
        'I.picture',
        'D.description',
        'D.detail',
        'I.create_date',
        'I.last_update',
        'I.visited',
        'I.visited_today',
        'I.comments',
        'I.alias',
        'D.keywords',
        'D.relate',
        'I.can_reply',
        'I.published',
        '0 vote',
        '0 vote_count',
        'C.topic category',
        'C.detail cat_tooltip',
        'U.status',
        'U.id member_id',
        'U.displayname',
        'U.email'
      );
      // where
      if (empty($id)) {
        $where = array(
          array('I.alias', $alias),
          array('I.index', 0),
          array('I.module_id', $module_id)
        );
      } else {
        $where = array(
          array('I.id', $id),
          array('I.index', 0),
          array('I.module_id', $module_id)
        );
      }
      $query = $model->db()->createQuery()
        ->select($fields)
        ->from('index I')
        ->join('index_detail D', 'INNER', array(array('D.id', 'I.id'), array('D.module_id', 'I.module_id'), array('D.language', array('', Language::name()))))
        ->join('user U', 'INNER', array('U.id', 'I.member_id'))
        ->join('category C', 'LEFT', array(array('C.category_id', 'I.category_id'), array('C.module_id', 'I.module_id')))
        ->where($where)
        ->limit(1);
      if (self::$request->get('visited')->toInt() == 0) {
        $query->cacheOn(false);
      }
      $result = $query->toArray()->execute();
      if (sizeof($result) == 1) {
        $result[0]['visited'] ++;
        $result[0]['visited_today'] ++;
        $model->db()->update($model->getFullTableName('index'), $result[0]['id'], array('visited' => $result[0]['visited'], 'visited_today' => $result[0]['visited_today']));
        $model->db()->cache()->save($result);
        return (object)$result[0];
      }
    }
    return null;
  }
}