<?php
/*
 * @filesource index/models/category.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Category;

use \Gcms\Gcms;
use \Kotchasan\ArrayTool;

/**
 * อ่านข้อมูลหมวดหมู่ (Frontend)
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Orm\Field
{
  /**
   * ชื่อตาราง
   *
   * @var string
   */
  protected $table = 'category G';

  /**
   * อ่านข้อมูลหมวดหมู่ที่เลือก และสามารถเผยแพร่ได้
   *
   * @param int $category_id
   * @param int $module_id
   * @return object|null ข้อมูลที่เลือก (Object) หรือ null หากไม่พบ
   */
  public static function get($category_id, $module_id)
  {
    $model = new \Kotchasan\Model;
    $query = $model->db()->createQuery()
      ->select()
      ->from('category')
      ->where(array(array('category_id', (int)$category_id), array('module_id', (int)$module_id), array('published', '1')))
      ->limit(1);
    foreach ($query->toArray()->execute() as $item) {
      $item['topic'] = Gcms::ser2Str($item, 'topic');
      $item['detail'] = Gcms::ser2Str($item, 'detail');
      $item['icon'] = Gcms::ser2Str($item, 'icon');
      return (object)ArrayTool::unserialize($item['config'], $item);
    }
    return null;
  }

  /**
   * อ่านข้อมูลหมวดหมู่ที่สามารถเผยแพร่ได้
   * สำหรับหน้าแสดงรายการหมวดหมู่
   *
   * @param int $module_id
   * @return array คืนค่าแอเรย์ของ Object ไม่มีคืนค่าแอเรย์ว่าง
   */
  public static function all($module_id)
  {
    $result = array();
    $model = new \Kotchasan\Model;
    $query = $model->db()->createQuery()
      ->select()
      ->from('category')
      ->where(array(array('module_id', (int)$module_id), array('published', '1')))
      ->cacheOn()
      ->order('category_id');
    foreach ($query->toArray()->execute() as $item) {
      $item['topic'] = Gcms::ser2Str($item, 'topic');
      $item['detail'] = Gcms::ser2Str($item, 'detail');
      $item['icon'] = Gcms::ser2Str($item, 'icon');
      $result[] = (object)ArrayTool::unserialize($item['config'], $item);
    }
    return $result;
  }

  /**
   * อ่านข้อมูลหมวดหมู่ที่สามารถเผยแพร่ได้
   * สำหรับใส่ select หรือ menu
   *
   * @param int $module_id
   * @return array
   */
  public static function categories($module_id)
  {
    $result = array();
    $model = new \Kotchasan\Model;
    $query = $model->db()->createQuery()
      ->select('category_id', 'topic')
      ->from('category')
      ->where(array(array('module_id', (int)$module_id), array('published', '1')))
      ->cacheOn()
      ->order('category_id');
    foreach ($query->toArray()->execute() as $item) {
      $result[$item['category_id']] = Gcms::ser2Str($item, 'topic');
    }
    return $result;
  }
}