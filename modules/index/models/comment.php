<?php
/*
 * @filesource index/models/comment.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Comment;

/**
 * ตาราง comment
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Model
{

  /**
   * อัปเดทจำนวนความคิดเห็น
   *
   * @param int $qid ID ของบทความ
   * @param int $module_id ID ของโมดูล
   */
  public static function update($qid, $module_id)
  {
    $model = new static;
    $count = $model->db()->createQuery()
      ->selectCount()
      ->from('comment')
      ->where(array(array('index_id', $qid), array('module_id', $module_id)));
    $model->db()->createQuery()
      ->update('index')
      ->set(array('comments' => $count))
      ->where(array('id', $qid))
      ->execute();
  }
}