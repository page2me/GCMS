<?php
/*
 * @filesource index/controllers/menu.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Menu;

use \Gcms\Gcms;
use \Kotchasan\Login;

/**
 * รายการเมนูทั้งหมด.
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Kotchasan\Controller
{
  /**
   * แอเรย์เก็บรายการเมนู
   *
   * @var array
   */
  public static $menus = array();

  /**
   * โหลดรายการเมนูทั้งหมด.
   *
   * @return array รายการเมนูทั้งหมด
   */
  public static function getMenus()
  {
    // menu section
    self::$menus['sections']['home'] = array('h', '<a href="index.php?module=dashboard" accesskey=h title="{LNG_Home}"><span>{LNG_Home}</span></a>');
    self::$menus['sections']['settings'] = array('1', '{LNG_Site settings}');
    self::$menus['sections']['index'] = array('2', '{LNG_Menus} &amp; {LNG_Web pages}');
    self::$menus['sections']['modules'] = array('3', '{LNG_Modules}');
    self::$menus['sections']['widgets'] = array('4', '{LNG_Widgets}');
    self::$menus['sections']['users'] = array('5', '{LNG_Users}');
    self::$menus['sections']['email'] = array('6', '{LNG_Mailbox}');
    self::$menus['sections']['tools'] = array('7', '{LNG_Tools}');
    // settings
    self::$menus['settings']['system'] = '<a href="index.php?module=system"><span>{LNG_General}</span></a>';
    self::$menus['settings']['mailserver'] = '<a href="index.php?module=mailserver"><span>{LNG_Email settings}</span></a>';
    self::$menus['settings']['mailtemplate'] = '<a href="index.php?module=mailtemplate"><span>{LNG_Email template}</span></a>';
    self::$menus['settings']['template'] = '<a href="index.php?module=template"><span>{LNG_Template}</span></a>';
    self::$menus['settings']['skin'] = '<a href="index.php?module=skin"><span>{LNG_Template settings}</span></a>';
    self::$menus['settings']['maintenance'] = '<a href="index.php?module=maintenance"><span>{LNG_Maintenance Mode}</span></a>';
    self::$menus['settings']['intro'] = '<a href="index.php?module=intro"><span>{LNG_Intro Page}</span></a>';
    self::$menus['settings']['languages'] = '<a href="index.php?module=languages"><span>{LNG_Language}</span></a>';
    self::$menus['settings']['other'] = '<a href="index.php?module=other"><span>{LNG_Other}</span></a>';
    self::$menus['settings']['meta'] = '<a href="index.php?module=meta"><span>{LNG_SEO &amp; Social}</span></a>';
    // email
    self::$menus['email']['sendmail'] = '<a href="index.php?module=sendmail"><span>{LNG_Email send}</span></a>';
    // เมนู
    self::$menus['index']['pages'] = '<a href="index.php?module=pages"><span>{LNG_Web pages}</span></a>';
    self::$menus['index']['insmod'] = '<a href="index.php?module=mods"><span>{LNG_installed module}</span></a>';
    self::$menus['index']['menu'] = '<a href="index.php?module=menus"><span>{LNG_Menus}</span></a>';
    // เมนูสมาชิก
    self::$menus['users']['memberstatus'] = '<a href="index.php?module=memberstatus"><span>{LNG_Member status}</span></a>';
    self::$menus['users']['member'] = '<a href="index.php?module=member"><span>{LNG_Member List}</span></a>';
    self::$menus['users']['register'] = '<a href="index.php?module=register"><span>{LNG_Register}</span></a>';
    // tools
    self::$menus['tools']['install'] = array();
    self::$menus['tools']['database'] = '<a href="index.php?module=database"><span>{LNG_Database}</span></a>';
    self::$menus['tools']['language'] = '<a href="index.php?module=language"><span>{LNG_Language}</span></a>';
    self::$menus['tools']['debug'] = '<a href="index.php?module=debug"><span>{LNG_Debug tool}</span></a>';
    self::$menus['modules'] = array();
    // โมดูลที่ติดตั้งแล้ว
    foreach (Gcms::$install_modules as $item) {
      // ตรวจสอบไฟล์ config
      if (is_file(ROOT_PATH."modules/$item[owner]/controllers/settings.php")) {
        self::$menus['modules'][$item['module']]['config'] = '<a href="index.php?module='.$item['owner'].'-settings&amp;id='.$item['id'].'"><span>{LNG_Config}</span></a>';
      }
      // ตรวจสอบไฟล์ category
      if (is_file(ROOT_PATH."modules/$item[owner]/controllers/category.php")) {
        self::$menus['modules'][$item['module']]['category'] = '<a href="index.php?module='.$item['owner'].'-category&amp;id='.$item['id'].'"><span>{LNG_Category}</span></a>';
      }
      // ตรวจสอบไฟล์ setup
      if (is_file(ROOT_PATH."modules/$item[owner]/controllers/setup.php")) {
        self::$menus['modules'][$item['module']]['setup'] = '<a href="index.php?module='.$item['owner'].'-setup&amp;id='.$item['id'].'"><span>{LNG_Contents}</span></a>';
      }
    }
    if (!Login::isAdmin()) {
      unset(self::$menus['sections']['settings']);
      unset(self::$menus['sections']['index']);
      unset(self::$menus['sections']['menus']);
      unset(self::$menus['sections']['widgets']);
      unset(self::$menus['sections']['users']);
      unset(self::$menus['sections']['tools']);
    }
    if (sizeof(self::$menus['modules']) == 0) {
      unset(self::$menus['sections']['modules']);
    }
    if (isset(self::$menus['widgets']) && sizeof(self::$menus['widgets']) == 0) {
      unset(self::$menus['sections']['widgets']);
    }
    if (sizeof(self::$menus['tools']['install']) == 0) {
      unset(self::$menus['tools']['install']);
    }
  }

  /**
   * สร้างเมนูตามตำแหน่งของเมนู (parent)
   *
   * @return array รายการเมนูทั้งหมด
   */
  public static function render()
  {
    $controller = new static;
    return $controller->createView('Index\Menu\View')->render(self::$menus);
  }

  /**
   * อ่านเมนู (MAINMENU) ของโมดูล
   *
   * @param string $module ชื่อโมดูลที่ต้องการ
   * @return array รายการเมนูของเมนูที่เลือก ถ้าไม่พบคืนค่าแอเรย์ว่าง
   */
  public function moduleMenu($module)
  {
    $result = array();
    if (isset($this->menus->MAINMENU['toplevel'])) {
      foreach ($this->menus->MAINMENU['toplevel'] as $item) {
        if ($item->module == $module) {
          $result = $item;
          break;
        }
      }
    }
    return $result;
  }
}