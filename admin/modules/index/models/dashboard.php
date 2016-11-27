<?php
/*
 * @filesource index/models/dashboard.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Dashboard;

use \Kotchasan\Language;

/**
 * ตรวจสอบข้อมูลสมาชิกด้วย Ajax
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Model
{

  /**
   * get counter datas
   *
   * @return array
   */
  public function counter()
  {
    $db = $this->db();
    $sql1 = $db->createQuery()->selectCount()->from('user');
    $sql2 = $db->createQuery()->selectCount()->from('user')->where(array('activatecode', '!=', ''));
    $sql3 = $db->createQuery()->selectCount()->from('user')->where(array('ban', '>', 0));
    $sql4 = $db->createQuery()->selectCount()->from('useronline');
    return $db->createQuery()
        ->from('counter')
        ->order('id DESC')
        ->toArray()
        ->first('counter', 'visited', array($sql1, 'members'), array($sql2, 'activate'), array($sql3, 'ban'), array($sql4, 'useronline'));
  }

  public function pageviews()
  {
    $db = $this->db();
    $select = array(
      'id',
      'MONTH(`date`) AS `month`',
      'YEAR(`date`) AS `year`',
      'SUM(`pages_view`) AS `pages_view`',
      'SUM(`visited`) AS `visited`',
      'date'
    );
    $sql1 = $db->createQuery()
      ->select($select)
      ->from('counter')
      ->groupBy('MONTH(`date`)', 'YEAR(`date`)')
      ->order('date DESC')
      ->limit(12);
    return $db->createQuery()
        ->select()
        ->from(array($sql1, 'A'))
        ->order('A.date')
        ->toArray()
        ->execute();
  }

  public function popularpage()
  {
    return $this->db()->createQuery()
        ->select('D.topic', 'I.visited_today')
        ->from('index I')
        ->join('modules M', 'INNER', array(array('M.id', 'I.module_id'), array('M.owner', 'document')))
        ->join('index_detail D', 'INNER', array(array('D.id', 'I.id'), array('D.module_id', 'I.module_id'), array('D.language', array(Language::name(), ''))))
        ->order('I.visited_today DESC', 'I.visited DESC')
        ->limit(12)
        ->toArray()
        ->execute();
  }
}