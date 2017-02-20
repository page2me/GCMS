<?php
/*
 * @filesource index/controllers/main.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Main;

use \Kotchasan\Http\Request;
use \Kotchasan\Template;

/**
 * Controller หลัก สำหรับแสดง backend ของ GCMS
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Gcms\Controller
{

  /**
   * หน้าหลักแอดมิน
   *
   * @param Request $request
   * @return string
   */
  public function execute(Request $request)
  {
    // โมดูลจาก URL ถ้าไม่มีใช้ default (dashboard)
    $module = $request->get('module', 'dashboard')->toString();
    if (preg_match('/^([a-z]+)([\/\-]([a-z]+))?$/i', $module, $match)) {
      if (empty($match[3])) {
        $owner = 'index';
        $module = $match[1];
      } else {
        $owner = $match[1];
        $module = $match[3];
      }
    } else {
      // หน้า default ถ้าไม่ระบุ module มา
      $owner = 'index';
      $module = 'dashboard';
    }
    // ตรวจสอบหน้าที่เรียก
    if (is_file(APP_PATH.'modules/'.$owner.'/controllers/'.$module.'.php')) {
      // หน้าที่เรียก (Admin)
      include APP_PATH.'modules/'.$owner.'/controllers/'.$module.'.php';
      $className = ucfirst($owner).'\\'.ucfirst($module).'\Controller';
    } elseif (is_file(ROOT_PATH.'modules/'.$owner.'/controllers/admin/'.$module.'.php')) {
      // เรียกโมดูลที่ติดตั้ง
      include ROOT_PATH.'modules/'.$owner.'/controllers/admin/'.$module.'.php';
      $className = ucfirst($owner).'\Admin\\'.ucfirst($module).'\Controller';
    } elseif (is_file(ROOT_PATH.'Widgets/'.ucfirst($owner).'/Controllers/'.ucfirst($module).'.php')) {
      // เรียก Widgets ที่ติดตั้ง
      include ROOT_PATH.'Widgets/'.ucfirst($owner).'/Controllers/'.ucfirst($module).'.php';
      $className = 'Widgets\\'.ucfirst($owner).'\\Controllers\\'.ucfirst($module);
    } else {
      // หน้า default ถ้าไม่พบหน้าที่เรียก
      include APP_PATH.'modules/index/controllers/dashboard.php';
      $className = 'Index\Dashboard\Controller';
    }
    $controller = new $className;
    // tempalate
    $template = Template::create('', '', 'main');
    $template->add(array(
      '/{CONTENT}/' => $controller->render($request)
    ));
    // ข้อความ title bar
    $this->title = $controller->title();
    // เมนูที่เลือก
    $this->menu = $controller->menu();
    return $template->render();
  }
}