<?php
/*
 * @filesource personnel/controllers/admin/init.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Personnel\Admin\Init;

use \Index\Index\Model as Menu;

/**
 * จัดการการตั้งค่าเริ่มต้น
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Kotchasan\Controller
{

  /**
   * คำอธิบายเกี่ยวกับโมดูล ถ้าไม่มีฟังก์ชั่นนี้ โมดูลนี้จะไม่สามารถใช้ซ้ำได้
   */
  public static function description()
  {
    return '{LNG_Module} {LNG_Personnel}';
  }

  /**
   * ฟังก์ชั่นเรียกโดย admin
   */
  public static function init($items)
  {
    if (!empty($items)) {
      // เมนู
      foreach ($items AS $item) {
        Menu::$menus['modules'][$item->module]['category'] = '<a href="index.php?module=personnel-category&amp;mid='.$item->id.'"><span>{LNG_Personnel groups}</span></a>';
        Menu::$menus['modules'][$item->module]['write'] = '<a href="index.php?module=personnel-write&amp;mid='.$item->id.'"><span>{LNG_Add New} {LNG_Personnel}</span></a>';
        Menu::$menus['modules'][$item->module]['setup'] = '<a href="index.php?module=personnel-setup&amp;mid='.$item->id.'"><span>{LNG_List of} {LNG_Personnel}</span></a>';
      }
    }
  }
}