<?php
/*
 * @filesource index/models/search.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Search;

use \Kotchasan\Http\Request;
use \Kotchasan\Language;
use \Gcms\Gcms;

/**
 * search model
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Model
{

  /**
   * ค้นหาข้อมูลทั้งหมด
   *
   * @param Request $request
   * @param object $index
   * @return object
   */
  public static function findAll(Request $request, $index)
  {
    // model
    $model = new static;
    $db = $model->db();
    // ข้อความค้นหา
    $index->q = $request->globals(array('POST', 'GET'), 'q')->topic();
    $index->words = array();
    $where1 = array();
    $where2 = array();
    // แยกข้อความค้นหาออกเป็นคำๆ ค้นหาข้อความที่มีความยาวมากกว่า 1 ตัวอักษร
    foreach (explode(' ', $index->q) AS $item) {
      if (mb_strlen($item) > 1) {
        $index->words[] = $item;
        $where1[] = array('D.topic', 'LIKE', '%'.$item.'%');
        $where1[] = array('D.detail', 'LIKE', '%'.$item.'%');
        $where2[] = array('C.detail', 'LIKE', '%'.$item.'%');
      }
    }
    $index->sqls = array();
    $select = array('I.id', 'I.alias', 'M.module', 'M.owner', 'D.topic', 'D.description', 'I.visited', 'I.index');
    $q1 = $db->createQuery()
      ->select($select)
      ->from('modules M')
      ->join('index I', 'INNER', array(array('I.module_id', 'M.id'), array('I.published', 1), array('I.published_date', '<=', date('Y-m-d')), array('I.language', array(Language::name(), ''))))
      ->join('index_detail D', 'INNER', array(array('D.id', 'I.id'), array('D.module_id', 'M.id')))
      ->where($where1, 'OR');
    $q2 = $db->createQuery()
      ->select($select)
      ->from('comment C')
      ->join('modules M', 'INNER', array('M.id', 'C.module_id'))
      ->join('index I', 'INNER', array(array('I.module_id', 'M.id'), array('I.published', 1), array('I.published_date', '<=', date('Y-m-d')), array('I.language', array(Language::name(), ''))))
      ->join('index_detail D', 'INNER', array(array('D.id', 'I.id'), array('D.module_id', 'M.id')))
      ->where($where2, 'OR');
    // union all queries
    $q3 = $db->createQuery()->union(array($q1, $q2));
    // groub by id
    $index->sqls[] = $db->createQuery()->select()->from(array($q3, 'Q'))->groupBy('Q.id');
    // ค้นหาจากโมดูลอื่นๆที่ติดตั้ง
    foreach (Gcms::$install_owners as $item => $modules) {
      if ($item != 'index' && is_file(ROOT_PATH."modules/$item/models/search.php")) {
        include (ROOT_PATH."modules/$item/models/search.php");
        //createClass(ucfirst($item).'\Search\Model')->findAll($request, $index);
      }
    }
    // union all queries
    $query = $db->createQuery()->from(array($db->createQuery()->union($index->sqls), 'Z'));
    // จำนวน
    $index->total = $query->cacheOn()->count();
    // ข้อมูลแบ่งหน้า
    if (empty($index->list_per_page)) {
      $index->list_per_page = 20;
    }
    $index->page = $request->request('page')->toInt();
    $index->totalpage = ceil($index->total / $index->list_per_page);
    $index->page = max(1, ($index->page > $index->totalpage ? $index->totalpage : $index->page));
    $index->start = $index->list_per_page * ($index->page - 1);
    $index->end = ($index->start + $index->list_per_page > $index->total) ? $index->total : $index->start + $index->list_per_page;
    // query
    $index->items = $query->select()
      ->order('visited')
      ->limit($index->list_per_page, $index->start)
      ->cacheOn()
      ->execute();
    return $index;
  }
}