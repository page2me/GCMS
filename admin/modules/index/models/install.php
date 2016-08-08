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
        $id = $db->insert($model->getTableName('modules'), array(
          'owner' => $owner,
          'module' => $module
        ));
        $index = $db->insert($model->getTableName('index'), array(
          'module_id' => $id,
          'index' => 1,
          'published' => 1
        ));
        $db->insert($model->getTableName('index_detail'), array(
          'module_id' => $id,
          'id' => $index,
          'topic' => $title
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
}