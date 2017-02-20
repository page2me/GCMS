<?php
/*
 * @filesource index/controllers/index.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Index;

use \Kotchasan\Http\Request;
use \Gcms\Login;
use \Kotchasan\Template;
use \Gcms\Gcms;
use \Kotchasan\Http\Response;

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
   * หน้าหลักเว็บไซต์ (index.html)
   *
   * @param Request $request
   */
  public function index(Request $request)
  {
    // ตัวแปรป้องกันการเรียกหน้าเพจโดยตรง
    define('MAIN_INIT', 'indexhtml');
    // session cookie
    $request->initSession();
    // ตรวจสอบการ login
    Login::create();
    // กำหนด skin ให้กับ template
    Template::init(self::$cfg->skin);
    // backend
    Gcms::$view = new \Gcms\Adminview;
    if ($login = Login::adminAccess()) {
      // โหลดโมดูลที่ติดตั้งแล้ว
      Gcms::$module = \Index\Module\Controller::create();
      // โหลดเมนู
      Gcms::$menu = \Index\Menu\Controller::init();
      // เรียก init ของโมดูล
      foreach (Gcms::$module->getInstalledOwners() as $owner => $modules) {
        $class = ucfirst($owner).'\Admin\Init\Controller';
        if (class_exists($class) && method_exists($class, 'init')) {
          $class::init($modules);
        }
      }
      // Controller หลัก
      $main = new \Index\Main\Controller;
    } else {
      // forgot or login
      if (self::$request->get('action')->toString() === 'forgot') {
        $main = new \Index\Forgot\Controller;
      } else {
        $main = new \Index\Login\Controller;
      }
    }
    $languages = array();
    $uri = $request->getUri();
    foreach (Gcms::installedLanguage() as $item) {
      $languages[$item] = '<a id=lang_'.$item.' href="'.$uri->withParams(array('lang' => $item), true).'" title="{LNG_Language} '.strtoupper($item).'" style="background-image:url('.WEB_URL.'language/'.$item.'.gif)" tabindex=1>&nbsp;</a>';
    }
    // เนื้อหา
    Gcms::$view->setContents(array(
      // main template
      '/{MAIN}/' => $main->execute(self::$request),
      // GCMS Version
      '/{VERSION}/' => self::$cfg->version,
      // language menu
      '/{LANGUAGES}/' => implode('', $languages),
      // title
      '/{TITLE}/' => $main->title().' (Admin)',
      // url สำหรับกลับไปหน้าก่อนหน้า
      '/{BACKURL(\?([a-zA-Z0-9=&\-_@\.]+))?}/e' => '\Gcms\Adminview::back'
    ));
    if ($login) {
      $name = trim($login['fname'].' '.$login['lname']);
      Gcms::$view->setContents(array(
        // ID สมาชิก
        '/{LOGINID}/' => $login['id'],
        // แสดงชื่อคน Login
        '/{LOGINNAME}/' => empty($name) ? $login['email'] : $name,
        // สถานะสมาชิก
        '/{STATUS}/' => $login['status'],
        // เมนู
        '/{MENUS}/' => Gcms::$menu->render($main->menu())
      ));
    }
    // ส่งออก เป็น HTML
    $response = new Response;
    $response->withContent(Gcms::$view->renderHTML())->send();
  }
}