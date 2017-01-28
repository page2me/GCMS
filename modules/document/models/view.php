<?php
/*
 * @filesource document/models/view.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Document\View;

use \Kotchasan\Http\Request;
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
   * อ่านบทความที่เลือก
   *
   * @param Request $request
   * @param object $index ข้อมูลที่ส่งมา
   * @return object ข้อมูล object ไม่พบคืนค่า null
   */
  public static function get(Request $request, $index)
  {
    // model
    $model = new static;
    // select
    $fields = array(
      'M.config mconfig',
      'M.module',
      'M.owner',
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
      'I.can_reply canReply',
      'I.published',
      '0 vote',
      '0 vote_count',
      'C.topic category',
      'C.detail cat_tooltip',
      'C.config',
      'U.status',
      'U.id member_id',
      '(CASE WHEN U.`displayname`="" THEN U.`email` ELSE U.`displayname` END) displayname',
    );
    // where
    $where = array();
    if (!empty($index->id)) {
      $where[] = array('I.id', $index->id);
    } elseif (!empty($index->alias)) {
      $where[] = array('I.alias', $index->alias);
    }
    $where[] = array('I.index', 0);
    if (!empty($index->module_id)) {
      $where[] = array('I.module_id', $index->module_id);
    }
    $query = $model->db()->createQuery()
      ->from('index I')
      ->join('modules M', 'INNER', array('M.id', 'I.module_id'))
      ->join('index_detail D', 'INNER', array(array('D.id', 'I.id'), array('D.module_id', 'I.module_id'), array('D.language', array('', Language::name()))))
      ->join('user U', 'LEFT', array('U.id', 'I.member_id'))
      ->join('category C', 'LEFT', array(array('C.category_id', 'I.category_id'), array('C.module_id', 'I.module_id')))
      ->where($where)
      ->toArray();
    if (!$request->request('visited')->exists()) {
      $query->cacheOn(false);
    }
    $result = $query->first($fields);
    if ($result) {
      // อัปเดทการเยี่ยมชม
      $result['visited'] ++;
      $result['visited_today'] ++;
      $model->db()->update($model->getFullTableName('index'), $result['id'], array('visited' => $result['visited'], 'visited_today' => $result['visited_today']));
      $model->db()->cacheSave(array($result));
      // อัปเดทตัวแปร
      foreach ($result as $key => $value) {
        switch ($key) {
          case 'mconfig':
          case 'config':
            $config = @unserialize($value);
            if (is_array($config)) {
              foreach ($config as $k => $v) {
                $index->$k = $v;
              }
            }
            break;
          default:
            $index->$key = $value;
            break;
        }
      }
      // คืนค่าข้อมูลบทความ
      return $index;
    }
    return null;
  }
}