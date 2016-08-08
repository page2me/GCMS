<?php
/*
 * @filesource document/models/calendar.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Document\Calendar;

use \Kotchasan\Language;

/**
 *  Model สำหรับอ่านข้อมูลโมดูล
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Model
{

  /**
   * ฟังก์ชั่นอ่านข้อมูลสำหรับการแสดงบนปฏิทิน
   *
   * @param array $settings ค่ากำหนดของปฎิทิน
   * @param int $first_date วันที่ 1 (mktime)
   * @param int $first_next_month วันที่ 1 ของเดือนถัดไป (mktime)
   * @return array
   */
  public function calendar($settings, $first_date, $first_next_month)
  {
    $where = array(
      array('D.create_date', '>=', $first_date),
      array('D.create_date', '<', $first_next_month)
    );
    if (!empty($settings['module']) && preg_match('/^[a-z0-9]+$/', $settings['module'])) {
      $where[] = array('M.module', $settings['module']);
    } elseif (!empty($settings['owner']) && preg_match('/^[a-z0-9]+$/', $settings['owner'])) {
      $where[] = array('M.owner', $settings['owner']);
    } else {
      $where[] = array('M.owner', 'document');
    }
    $where[] = array('D.published', 1);
    $where[] = array('D.index', 0);
    $query = $this->db()->createQuery()
      ->select('D.id', 'D.create_date', 'M.module')
      ->from('index D')
      ->join('modules M', 'INNER', array('M.id', 'D.module_id'))
      ->where($where)
      ->cacheOn()
      ->toArray();
    return $query->execute();
  }

  /**
   * ฟังก์ชั่นเรียมาจากปฏิทินเพื่อแสดงทูลทิป
   *
   * @param array $ids id ที่ต้องการแสดง tooltip
   * @param array $settings ค่ากำหนด
   * @return array
   */
  public function tooltip($ids, $settings)
  {
    $where = array(array('I.id', $ids));
    if (!empty($settings['module']) && preg_match('/^[a-z0-9]+$/', $settings['module'])) {
      $where[] = array('M.module', $settings['module']);
    } elseif (!empty($settings['owner']) && preg_match('/^[a-z0-9]+$/', $settings['owner'])) {
      $where[] = array('M.owner', $settings['owner']);
    } else {
      $where[] = array('M.owner', 'document');
    }
    $where[] = array('I.published', 1);
    $where[] = array('I.index', 0);
    $where[] = array('D.language', array(Language::name(), ''));
    $query = $this->db()->createQuery()
      ->select('I.id', 'D.topic', 'D.description', 'M.module')
      ->from('index I')
      ->join('modules M', 'INNER', array('M.id', 'I.module_id'))
      ->join('index_detail D', 'INNER', array(array('D.id', 'I.id'), array('M.id', 'D.module_id')))
      ->where($where)
      ->cacheOn()
      ->toArray();
    return $query->execute();
  }
}