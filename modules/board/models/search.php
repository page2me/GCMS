<?php
/*
 * @filesource board/models/search.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Board\Search;

use \Kotchasan\Http\Request;

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
  public function findAll(Request $request, $index)
  {
    $where1 = array();
    $where2 = array();
    // ค้นหาข้อมูล
    foreach ($index->words as $item) {
      $where1[] = array('Q.topic', 'LIKE', '%'.$item.'%');
      $where1[] = array('Q.detail', 'LIKE', '%'.$item.'%');
      $where2[] = array('R.detail', 'LIKE', '%'.$item.'%');
    }
    $db = $this->db();
    $q1 = $db->createQuery()
      ->select('Q.id', 'Q.topic alias', 'M.module', 'M.owner', 'Q.topic', 'Q.detail description', 'Q.visited', '0 index')
      ->from('board_q Q')
      ->join('modules M', 'INNER', array(array('M.id', 'Q.module_id'), array('M.owner', 'board')))
      ->where($where1);
    $q2 = $db->createQuery()
      ->select('Q.id', 'Q.topic alias', 'M.module', 'M.owner', 'Q.topic', 'R.detail description', 'Q.visited', '0 index')
      ->from('board_r R')
      ->join('board_q Q', 'INNER', array(array('Q.id', 'R.index_id'), array('Q.module_id', 'R.module_id')))
      ->join('modules M', 'INNER', array(array('M.id', 'Q.module_id'), array('M.owner', 'board')))
      ->where($where2);
    // union all queries
    $q3 = $db->createQuery()->union(array($q1, $q2));
    // groub by id
    $index->sqls[] = $db->createQuery()->select()->from(array($q3, 'Y'))->groupBy('Y.id');
  }
}