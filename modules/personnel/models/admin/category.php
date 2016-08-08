<?php
/*
 * @filesource personnel/models/admin/category.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Personnel\Admin\Category;

use \Kotchasan\Http\Request;
use \Gcms\Gcms;
use \Kotchasan\ArrayTool;
use \Kotchasan\Language;
use \Kotchasan\Login;

/**
 * อ่านข้อมูลหมวดหมู่ (Backend)
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Model
{

  /**
   * อ่านข้อมูลหมวดหมู่
   * สำหรับหน้าแสดงรายการหมวดหมู่
   *
   * @param int $module_id
   * @return array คืนค่าแอเรย์ของ Object ไม่มีคืนค่าแอเรย์ว่าง
   */
  public static function all($module_id)
  {
    foreach (Language::installedLanguage() as $lng) {
      $default[$lng] = '';
    }
    $result = array();
    if (is_int($module_id) && $module_id > 0) {
      $model = new static;
      $query = $model->db()->createQuery()
        ->select('id', 'category_id', 'topic')
        ->from('category')
        ->where(array('module_id', $module_id))
        ->cacheOn()
        ->order('category_id');
      foreach ($query->toArray()->execute() as $item) {
        $tmp = ArrayTool::merge($default, @unserialize($item['topic']));
        unset($item['topic']);
        $result[] = ArrayTool::merge($item, $tmp);
      }
    }
    if (empty($result)) {
      $result[] = ArrayTool::merge(array('id' => 0, 'category_id' => 1), $default);
    }
    return $result;
  }

  /**
   * บันทึก
   */
  public function save(Request $request)
  {
    // referer, session, member
    if ($request->initSession() && $request->isReferer() && $login = Login::isMember()) {
      $ret = array();
      if ($login['email'] == 'demo') {
        $ret['alert'] = Language::get('Unable to complete the transaction');
      } else {
        // อ่านข้อมูลโมดูล
        $index = \Personnel\Admin\Index\Model::module($request->post('module_id')->toInt());
        if (Gcms::canConfig($login, $index, 'can_config')) {
          // ค่าที่ส่งมา
          $save = array();
          $category_exists = array();
          foreach ($request->post('category_id')->toInt() as $key => $value) {
            if (isset($category_exists[$value])) {
              $ret['ret_category_id_'.$key] = Language::replace('This :name already exist', array(':name' => 'ID'));
            } else {
              $category_exists[$value] = $value;
              $save[$key]['category_id'] = $value;
              $save[$key]['module_id'] = $index->module_id;
              $save[$key]['published'] = '1';
            }
          }
          foreach (Language::installedLanguage() as $lng) {
            foreach ($request->post($lng)->topic() as $key => $value) {
              $save[$key]['topic'][$lng] = $value;
            }
          }
          if (empty($ret)) {
            // ชื่อตาราง
            $table_name = $this->getFullTableName('category');
            // db
            $db = $this->db();
            // ลบข้อมูลเดิม
            $db->delete($table_name, array('module_id', $index->module_id), 0);
            // เพิ่มข้อมูลใหม่
            foreach ($save as $item) {
              $item['topic'] = serialize($item['topic']);
              $db->insert($table_name, $item);
            }
            // คืนค่า
            $ret['alert'] = Language::get('Saved successfully');
            $ret['location'] = 'reload';
          }
        } else {
          $ret['alert'] = Language::get('Unable to complete the transaction');
        }
      }
      // คืนค่า JSON
      echo json_encode($ret);
    }
  }
}