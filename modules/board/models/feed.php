<?php
/*
 * @filesource board/models/feed.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Board\Feed;

use \Kotchasan\Http\Request;

/**
 * RSS Feed
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Model
{

  /**
   * RSS Feed
   *
   * @param Request $request
   * @param object $index ข้อมูลโมดูล
   * @param int $count จำนวนที่ต้องการ
   * @return array
   */
  public static function getStories(Request $request, $index, $count)
  {
    if (defined('MAIN_INIT')) {
      $model = new static;
      $where = array(
        array('module_id', (int)$index->module_id)
      );
      if (preg_match('/^([0-9,]+)$/', $request->get('cat')->toString(), $cat)) {
        $where[] = array('category_id', explode(',', $cat[0]));
      }
      $user = $request->get('user')->toInt();
      if ($user > 0) {
        $where[] = array('member_id', $user);
      }
      if ($request->get('album')->exists()) {
        $where[] = array('picture', '!=', '');
      }
      return $model->db()->createQuery()
          ->select('id', 'topic', 'detail', 'picture', '(CASE WHEN `comment_date`=0 THEN `last_update` ELSE `comment_date` END) last_update')
          ->from('board_q')
          ->where($where)
          ->limit($count)
          ->order(($request->get('rnd')->exists() ? 'RAND()' : 'last_update DESC'))
          ->cacheOn()
          ->execute();
    } else {
      // เรียก method โดยตรง
      new \Kotchasan\Http\NotFound('Do not call method directly');
    }
  }
}