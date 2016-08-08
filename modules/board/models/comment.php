<?php
/*
 * @filesource board/models/comment.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Board\Comment;

/**
 *  Model สำหรับแสดงรายการความคิดเห็น
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Model
{

  /**
   * รายการแสดงความคิดเห็น
   *
   * @param object $story
   * @return array
   */
  public static function get($story)
  {
    $model = new static;
    return $model->db()->createQuery()
        ->select('C.*', 'U.status', "(CASE WHEN ISNULL(U.`id`) THEN C.`email` WHEN U.`displayname`='' THEN U.`email` ELSE U.`displayname` END) AS `name`")
        ->from('board_r C')
        ->join('user U', 'LEFT', array('U.id', 'C.member_id'))
        ->where(array(array('C.index_id', (int)$story->id), array('C.module_id', (int)$story->module_id)))
        ->order('C.id')
        ->cacheOn()
        ->execute();
  }
}