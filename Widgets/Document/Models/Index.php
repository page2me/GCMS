<?php
/**
 * @filesource Widgets/Document/Models/Index.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Widgets\Document\Models;

use \Kotchasan\Language;

/**
 * รายการบทความ
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Index extends \Kotchasan\Model
{

  /**
   * รายการบทความ
   *
   * @param int $module_id
   * @param string $categories
   * @param string $show_news
   * @param int $sort
   * @param int $limit
   * @return array
   */
  public static function get($module_id, $categories, $show_news, $sort, $limit)
  {
    // query
    $model = new static;
    // เรียงลำดับ
    // เรียงลำดับ
    $sorts = array(
      array('I.last_update DESC', 'I.id DESC'),
      array('I.create_date DESC', 'I.id DESC'),
      array('I.published_date DESC', 'I.last_update DESC'),
      array('I.id DESC')
    );
    $where = array(
      array('I.module_id', (int)$module_id),
      array('I.index', 0),
      array('I.published', 1),
      array('I.published_date', '<=', date('Y-m-d'))
    );
    if (!empty($categories)) {
      $where[] = "I.`category_id` IN ($categories)";
    }
    if (!empty($show_news) && preg_match('/^[a-z0-9]+$/', $show_news)) {
      $where[] = "I.`show_news` LIKE '%$show_news=1%'";
    }
    return $model->db()->createQuery()
        ->select('I.id', 'D.topic', 'I.alias', 'D.description', 'I.picture', 'I.create_date', 'I.last_update', 'I.comment_date', 'C.topic category', 'I.member_id', 'I.sender', 'U.status', 'I.comments', 'I.visited')
        ->from('index I')
        ->join('index_detail D', 'INNER', array(array('D.id', 'I.id'), array('D.module_id', 'I.module_id'), array('D.language', array(Language::name(), ''))))
        ->join('user U', 'LEFT', array('U.id', 'I.member_id'))
        ->join('category C', 'LEFT', array(array('C.category_id', 'I.category_id'), array('C.module_id', 'I.module_id')))
        ->where($where)
        ->order($sorts[$sort])
        ->limit((int)$limit)
        ->cacheOn()
        ->execute();
  }
}