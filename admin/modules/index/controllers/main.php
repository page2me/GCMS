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
class Controller extends \Kotchasan\Controller
{
  /**
   * Controller ที่กำลังทำงาน
   *
   * @var \Kotchasan\Controller
   */
  private $controller;

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
      $owner = 'index';
      $module = 'dashboard';
    }
    // ตรวจสอบหน้าที่เรียก
    if (is_file(APP_PATH.'modules/'.$owner.'/controllers/'.$module.'.php')) {
      // หน้าที่เรียก (Admin)
      include APP_PATH.'modules/'.$owner.'/controllers/'.$module.'.php';
      $controller = ucfirst($owner).'\\'.ucfirst($module).'\Controller';
    } elseif (is_file(ROOT_PATH.'modules/'.$owner.'/controllers/admin/'.$module.'.php')) {
      // เรียกโมดูลที่ติดตั้ง
      include ROOT_PATH.'modules/'.$owner.'/controllers/admin/'.$module.'.php';
      $controller = ucfirst($owner).'\Admin\\'.ucfirst($module).'\Controller';
    } elseif (is_file(ROOT_PATH.'Widgets/'.ucfirst($owner).'/Controllers/'.ucfirst($module).'.php')) {
      // เรียก Widgets ที่ติดตั้ง
      include ROOT_PATH.'Widgets/'.ucfirst($owner).'/Controllers/'.ucfirst($module).'.php';
      $controller = 'Widgets\\'.ucfirst($owner).'\\Controllers\\'.ucfirst($module);
    } else {
      // หน้า default ของ backend
      include APP_PATH.'modules/index/controllers/dashboard.php';
      $controller = 'Index\Dashboard\Controller';
    }
    $this->controller = new $controller;
    // tempalate
    $template = Template::create('', '', 'main');
    $template->add(array(
      '/{CONTENT}/' => $this->controller->render($request)
    ));
    return $template->render();
  }

  /**
   * title bar
   */
  public function title()
  {
    return $this->controller->title();
  }
}