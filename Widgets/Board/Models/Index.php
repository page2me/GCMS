<?php
/*
 * @filesource Widgets/Board/Models/Index.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Widgets\Board\Models;

/**
 * อ่านรายการอัลบัมทั้งหมด
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Index extends \Kotchasan\Model
{

  /**
   * รายการกระทู้
   *
   * @param int $module_id
   * @param string $categories
   * @param int $limit
   * @return array
   */
  public static function get($module_id, $categories, $limit)
  {
    // query
    $model = new static;
    $where = array(
      array('Q.module_id', (int)$module_id),
    );
    if (!empty($categories)) {
      $where[] = "Q.`category_id` IN ($categories)";
    }
    $sql = "(CASE WHEN ISNULL(U.`id`) THEN (CASE WHEN Q.`comment_date`>0 THEN Q.`commentator` ELSE Q.`email` END) ELSE (CASE WHEN U.`displayname`='' THEN U.`email` ELSE U.`displayname` END) END) AS `displayname`";
    return $model->db()->createQuery()
        ->select('Q.id', 'Q.topic', 'Q.picture', 'Q.last_update', 'Q.comment_date', 'Q.create_date', 'Q.detail', 'U.status', 'Q.member_id', $sql)
        ->from('board_q Q')
        ->join('user U', 'LEFT', array('U.id', '(CASE WHEN Q.`comment_date`>0 THEN Q.`commentator_id` ELSE Q.`member_id` END)'))
        ->where($where)
        ->order('Q.last_update DESC')
        ->limit((int)$limit)
        ->cacheOn()
        ->execute();
  }
}