<?php
/*
 * @filesource index/models/install.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Install;

/**
 * ติดตั้งโมดูลและเมนู
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Model
{

  /**
   * ฟังก์ชั่น คิดตั้ง โมดูลและ เมนู
   * ถ้ามีโมดูลติดตั้งแล้ว คืนค่า ID ของโมดูล
   *
   * @param string $owner โฟลเดอร์ของโมดูล
   * @param string $module ชื่อโมดูล
   * @param string $title (optional) ข้อความไตเติลบาร์ของโมดูล
   * @param string $menupos (optional) ตำแหน่งของเมนู (MAINMENU,SIDEMENU,BOTTOMMENU)
   * @param string $menu (optional) ข้อความเมนู
   * @return int คืนค่า ID ของโมดูลที่ติดตั้ง, -1 ติดตั้งแล้ว, 0 มีข้อผิดพลาด
   */
  public static function installing($owner, $module, $title, $menupos = '', $menu = '')
  {
    if (preg_match('/^[a-z]+$/', $owner) && preg_match('/^[a-z]+$/', $module)) {
      // model
      $model = new static;
      $db = $model->db();
      // ตรวจสอบโมดูลที่ติดตั้งแล้ว
      $search = $db->createQuery()->from('modules')->where(array('module', $module))->first('id');
      if (!$search) {
        $className = ucfirst($owner).'\Admin\Settings\Model';
        if (class_exists($className) && method_exists($className, 'defaultSettings')) {
          $config = $className::defaultSettings();
        }
        $id = $db->insert($model->getTableName('modules'), array(
          'owner' => $owner,
          'module' => $module,
          'config' => empty($config) ? '' : serialize($config)
        ));
        $mktime = time();
        $index = $db->insert($model->getTableName('index'), array(
          'module_id' => $id,
          'index' => 1,
          'published' => 1,
          'language' => '',
          'member_id' => 0,
          'create_date' => $mktime,
          'last_update' => $mktime,
          'visited' => 0
        ));
        $db->insert($model->getTableName('index_detail'), array(
          'module_id' => $id,
          'id' => $index,
          'topic' => $title,
          'language' => ''
        ));
        if ($menupos != '' && $menu != '') {
          $db->insert($model->getTableName('menus'), array(
            'index_id' => $index,
            'parent' => $menupos,
            'level' => 0,
            'menu_text' => $menu,
            'menu_tooltip' => $title
          ));
        }
        return $id;
      } else {
        return -1;
      }
    }
    return 0;
  }

  /**
   * บันทึกไฟล์ settings/database.php
   *
   * @param array $tables รายการตารางที่ต้องการอัปเดท (แทนที่ข้อมูลเดิม)
   * @return boolean คืนค่า true ถ้าสำเร็จ
   */
  public static function updateTables($tables)
  {
    // โหลด database
    $database = \Kotchasan\Config::load(ROOT_PATH.'settings/database.php');
    // อัปเดท tables
    foreach ($tables as $key => $value) {
      $database->tables[$key] = $value;
    }
    // save database
    return \Kotchasan\Config::save($database, ROOT_PATH.'settings/database.php');
  }
}