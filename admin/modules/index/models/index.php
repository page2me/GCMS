<?php
/*
 * @filesource index/models/index.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Index;

use \Gcms\Gcms;
use \Kotchasan\Language;
use \Kotchasan\Login;

/**
 * Model สำหรับโหลดโมดูลและเมนูของ GCMS
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
  protected $table = 'index I';
  /**
   * รายการเมนู (Backend)
   *
   * @var array
   */
  public static $menus;

  /**
   * โหลดโมดูลที่ติดตั้ง
   */
  public static function installedmodules()
  {
    if (defined('MAIN_INIT')) {
      // ตรวจสอบโมดูลที่ติดตั้ง ตามโฟลเดอร์
      $dir = ROOT_PATH.'modules/';
      $f = @opendir($dir);
      if ($f) {
        while (false !== ($text = readdir($f))) {
          if ($text !== '.' && $text !== '..' && $text !== 'css' && $text !== 'js') {
            Gcms::$install_owners[$text] = array();
          }
        }
        closedir($f);
      }
      // ตรวจสอบ Widgets ที่ติดตั้ง ตามโฟลเดอร์
      $dir = ROOT_PATH.'Widgets/';
      $f = @opendir($dir);
      if ($f) {
        while (false !== ($text = readdir($f))) {
          Gcms::$install_widgets[] = $text;
        }
        closedir($f);
      }
      // model
      $model = new \Kotchasan\Model;
      // โหลดโมดูลที่ติดตั้ง เรียงตามลำดับโฟลเดอร์
      $query = $model->db()->createQuery()
        ->select('id', 'module', 'owner')
        ->from('modules')
        ->where(array('owner', '!=', 'index'))
        ->order('owner');
      foreach ($query->execute() as $item) {
        Gcms::$install_modules[$item->module] = $item;
        Gcms::$install_owners[$item->owner][] = $item;
      }
      // โหลดเมนู
      self::$menus = self::loadMenus();
      // called Initial
      foreach (Gcms::$install_owners as $owner => $items) {
        if (is_file(ROOT_PATH.'modules/'.$owner.'/controllers/admin/init.php')) {
          include ROOT_PATH.'modules/'.$owner.'/controllers/admin/init.php';
          $class = ucfirst($owner).'\Admin\Init\Controller';
          if (method_exists($class, 'init')) {
            // module Initial
            $class::init($items);
          }
        }
      }
    } else {
      // เรียก method โดยตรง
      new \Kotchasan\Http\NotFound('Do not call method directly');
    }
  }

  /**
   * โหลดรายการเมนูทั้งหมด.
   *
   * @return array รายการเมนูทั้งหมด
   */
  public static function loadMenus()
  {
    $menus = array();
    // menu section
    $menus['sections']['home'] = array('h', '<a href="index.php?module=dashboard" accesskey=h title="{LNG_Home}"><span>{LNG_Home}</span></a>');
    $menus['sections']['settings'] = array('1', '{LNG_Site settings}');
    $menus['sections']['index'] = array('2', '{LNG_Menus} &amp; {LNG_Web pages}');
    $menus['sections']['modules'] = array('3', '{LNG_Modules}');
    $menus['sections']['widgets'] = array('4', '{LNG_Widgets}');
    $menus['sections']['users'] = array('5', '{LNG_Users}');
    $menus['sections']['email'] = array('6', '{LNG_Mailbox}');
    $menus['sections']['tools'] = array('7', '{LNG_Tools}');
    // settings
    $menus['settings']['system'] = '<a href="index.php?module=system"><span>{LNG_General}</span></a>';
    $menus['settings']['mailserver'] = '<a href="index.php?module=mailserver"><span>{LNG_Email settings}</span></a>';
    $menus['settings']['mailtemplate'] = '<a href="index.php?module=mailtemplate"><span>{LNG_Email template}</span></a>';
    $menus['settings']['template'] = '<a href="index.php?module=template"><span>{LNG_Template}</span></a>';
    $menus['settings']['skin'] = '<a href="index.php?module=skin"><span>{LNG_Template settings}</span></a>';
    $menus['settings']['maintenance'] = '<a href="index.php?module=maintenance"><span>{LNG_Maintenance Mode}</span></a>';
    $menus['settings']['intro'] = '<a href="index.php?module=intro"><span>{LNG_Intro Page}</span></a>';
    $menus['settings']['languages'] = '<a href="index.php?module=languages"><span>{LNG_Language}</span></a>';
    $menus['settings']['other'] = '<a href="index.php?module=other"><span>{LNG_Other}</span></a>';
    $menus['settings']['meta'] = '<a href="index.php?module=meta"><span>{LNG_SEO &amp; Social}</span></a>';
    // email
    $menus['email']['sendmail'] = '<a href="index.php?module=sendmail"><span>{LNG_Email send}</span></a>';
    // เมนู
    $menus['index']['pages'] = '<a href="index.php?module=pages"><span>{LNG_Web pages}</span></a>';
    $menus['index']['insmod'] = '<a href="index.php?module=mods"><span>{LNG_installed module}</span></a>';
    $menus['index']['menu'] = '<a href="index.php?module=menus"><span>{LNG_Menus}</span></a>';
    // เมนูสมาชิก
    $menus['users']['memberstatus'] = '<a href="index.php?module=memberstatus"><span>{LNG_Member status}</span></a>';
    $menus['users']['member'] = '<a href="index.php?module=member"><span>{LNG_Member List}</span></a>';
    $menus['users']['register'] = '<a href="index.php?module=register"><span>{LNG_Register}</span></a>';
    // tools
    $menus['tools']['install'] = array();
    $menus['tools']['database'] = '<a href="index.php?module=database"><span>{LNG_Database}</span></a>';
    $menus['tools']['language'] = '<a href="index.php?module=language"><span>{LNG_Language}</span></a>';
    $menus['tools']['debug'] = '<a href="index.php?module=debug"><span>{LNG_Debug tool}</span></a>';
    $menus['modules'] = array();
    // โมดูลที่ติดตั้งแล้ว
    foreach (Gcms::$install_modules as $item) {
      // ตรวจสอบไฟล์ config
      if (is_file(ROOT_PATH.'modules/'.$item->owner.'/controllers/admin/settings.php')) {
        $menus['modules'][$item->module]['config'] = '<a href="index.php?module='.$item->owner.'-settings&amp;mid='.$item->id.'"><span>{LNG_Config}</span></a>';
      }
      // ตรวจสอบไฟล์ category
      if (is_file(ROOT_PATH.'modules/'.$item->owner.'/controllers/admin/category.php')) {
        $menus['modules'][$item->module]['category'] = '<a href="index.php?module='.$item->owner.'-category&amp;mid='.$item->id.'"><span>{LNG_Category}</span></a>';
      }
      // ตรวจสอบไฟล์ setup
      if (is_file(ROOT_PATH.'modules/'.$item->owner.'/controllers/admin/setup.php')) {
        $menus['modules'][$item->module]['setup'] = '<a href="index.php?module='.$item->owner.'-setup&amp;mid='.$item->id.'"><span>{LNG_Contents}</span></a>';
      }
    }
    // Widgets ที่ติดตั้งแล้ว
    foreach (Gcms::$install_widgets as $item) {
      if (is_file(ROOT_PATH.'Widgets/'.$item.'/Controllers/Settings.php')) {
        $menus['widgets'][$item] = '<a href="index.php?module='.$item.'-settings"><span>'.$item.'</span></a>';
      }
    }
    if (!Login::isAdmin()) {
      unset($menus['sections']['settings']);
      unset($menus['sections']['index']);
      unset($menus['sections']['menus']);
      unset($menus['sections']['widgets']);
      unset($menus['sections']['users']);
      unset($menus['sections']['tools']);
    }
    if (empty($menus['modules'])) {
      unset($menus['sections']['modules']);
    }
    if (isset($menus['widgets']) && sizeof($menus['widgets']) == 0) {
      unset($menus['sections']['widgets']);
    }
    if (empty($menus['tools']['install'])) {
      unset($menus['tools']['install']);
    }
    return $menus;
  }
}