<?php
/*
 * @filesource document/models/categories.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Document\Categories;

use \Gcms\Gcms;
use \Kotchasan\Http\Request;

/**
 * อ่านข้อมูลโมดูล
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Model
{

  /**
   * อ่านข้อมูลหมวดหมู่ที่สามารถเผยแพร่ได้
   * สำหรับหน้าแสดงรายการหมวดหมู่
   *
   * @param \Controller $index
   * @return Object
   */
  public static function get(Request $request, $index)
  {
    $model = new static($request);
    $sql = "SELECT * FROM `".$model->tableWithPrefix('category')."`";
    $sql .= " WHERE `module_id`=:module_id AND `published`='1' ORDER BY `category_id` DESC";
    $where = array(
      ':module_id' => (int)$index->module_id,
    );
    $result = array();
    foreach ($model->db()->cacheOn()->customQuery($sql, true, $where) as $item) {
      $item['topic'] = Gcms::ser2Str($item, 'topic');
      $item['detail'] = Gcms::ser2Str($item, 'detail');
      $item['icon'] = Gcms::ser2Str($item, 'icon');
      $item = $model->config($item);
      $result[] = (object)$item;
    }
    return $result;
  }

  /**
   * อ่านข้อมูลหมวดหมู่ทั้งหมด (admin)
   *
   * @return array
   */
  public static function categories(Request $request)
  {
    $model = new static($request);
    $sql = "SELECT `id`,`module_id`,`category_id`,`published`,`config`,`topic`,`detail`,`icon`,`c1`";
    $sql .= " FROM `".$model->tableWithPrefix('category')."` WHERE `module_id`=:module_id ORDER BY `category_id`";
    $where = array(
      ':module_id' => $request->get('id')->toInt()
    );
    $categories = array();
    foreach ($model->db()->customQuery($sql, true, $where) as $item) {
      $config = @unserialize($item['config']);
      $categories[] = array(
        'id' => $item['id'],
        'module_id' => $item['module_id'],
        'category_id' => $item['category_id'],
        'topic' => $model->unserialize($item['topic']),
        'icon' => $item['icon'],
        'published' => $item['published'],
        'can_reply' => empty($config['can_reply']) ? 0 : 1,
        'detail' => $model->unserialize($item['detail']),
        'c1' => $item['c1']
      );
    }
    return $categories;
  }

  /**
   * เตรียมข้อมูล topic, detail
   *
   * @param array $item
   * @return string
   */
  private function unserialize($item)
  {
    $datas = array();
    foreach (unserialize($item) as $lng => $value) {
      $datas[$lng] = empty($lng) ? $value : '<p style="background:0 50% url('.WEB_URL.'language/'.$lng.'.gif) no-repeat;padding-left:21px;">'.$value.'</p>';
    }
    return implode('', $datas);
  }

  /**
   * อ่าน config ของหมวดหมู่
   * และ แปลงข้อมูลหมวดหมู่จาก GCMS เวอร์ชั่นเก่า
   *
   * @param array $item
   * @return array คืนค่าตัวแปร $item ที่ส่งเข้ามารวมกับตัวแปรที่อ่านจาก config
   */
  private function config($item)
  {
    $config = @unserialize($item['config']);
    unset($item['config']);
    if (!is_array($config)) {
      $save = array();
      foreach (explode("\n", $item['config']) as $value) {
        if (preg_match('/^(.*)=(.*)$/', $value, $match)) {
          $save[$match[1]] = trim($match[2]);
        }
      }
      $config = serialize($save);
      $this->db()->update($this->tableWithPrefix('category'), $item['id'], array('config' => $config));
    }
    foreach ($config as $key => $value) {
      $item[$key] = $value;
    }
    return $item;
  }

  /**
   * อ่านรายการหมวดหมู่ตามภาษาที่กำลังใช้งานอยู่
   *
   * @param int $module_id
   * @param boolean $all false (default) คืนค่าเฉพาะรายการที่เผยแพร่, true คืนค่าทุกรายการ
   * @return array
   */
  public static function all(Request $request, $module_id, $all = true)
  {
    $model = new static($request);
    $categories = array();
    $sql = "SELECT `category_id`,`topic` FROM `".$model->tableWithPrefix('category')."` WHERE `module_id`=:module_id";
    if (!$all) {
      $sql .= "`published`=1";
    }
    $sql .= " ORDER BY `category_id`";
    $where = array(':module_id' => (int)$module_id);
    foreach ($model->db()->customQuery($sql, true, $where) as $item) {
      $categories[$item['category_id']] = Gcms::ser2Str($item, 'topic');
    }
    return $categories;
  }
}