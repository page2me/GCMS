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
use \Kotchasan\Language;
use \Gcms\Gcms;
use \Kotchasan\Http\Response;

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
   * แสดงผลหน้าหลักเว็บไซต์
   *
   * @param Request $request
   */
  public function index(Request $request)
  {
    // ตัวแปรป้องกันการเรียกหน้าเพจโดยตรง
    define('MAIN_INIT', __FILE__);
    // session cookie
    $request->initSession();
    // ตรวจสอบการ login
    Login::create();
    // กำหนด skin ให้กับ template
    Template::init($request->get('skin', self::$cfg->skin)->toString());
    // backend
    Gcms::$view = new \Kotchasan\View;
    if ($login = Login::adminAccess()) {
      // โหลดโมดูลที่ติดตั้งแล้ว
      \Index\Index\Model::installedmodules();
      // Controller หลัก
      $main = new \Index\Main\Controller;
    } else {
      // forgot or login
      if ($request->request('action')->toString() === 'forgot') {
        $main = new \Index\Forgot\Controller;
      } else {
        $main = new \Index\Login\Controller;
      }
    }
    $languages = array();
    $uri = $request->getUri();
    foreach (array_merge(self::$cfg->languages, Language::installedLanguage()) AS $i => $item) {
      $languages[$item] = '<a id=lang_'.$item.' href="'.$uri->withParams(array('lang' => $item), true).'" title="{LNG_Language} '.strtoupper($item).'" style="background-image:url('.WEB_URL.'language/'.$item.'.gif)" tabindex=1>&nbsp;</a>';
    }
    // เนื้อหา
    Gcms::$view->setContents(array(
      // main template
      '/{MAIN}/' => $main->execute($request),
      // GCMS Version
      '/{VERSION}/' => self::$cfg->version,
      // language menu
      '/{LANGUAGES}/' => implode('', $languages),
      // title
      '/{TITLE}/' => $main->title().' (Admin)',
      // url สำหรับกลับไปหน้าก่อนหน้า
      '/{BACKURL(\?([a-zA-Z0-9=&\-_@\.]+))?}/e' => '\Kotchasan\View::back'
    ));
    if ($login) {
      $name = trim($login['fname'].' '.$login['lname']);
      Gcms::$view->setContents(array(
        // ID สมาชิก
        '/{LOGINID}/' => $login['id'],
        // ชื่อ นามสกุล
        '/{LOGINNAME}/' => empty($name) ? $login['email'] : $name,
        // สถานะสมาชิก
        '/{STATUS}/' => $login['status'],
        // เมนู
        '/{MENUS}/' => \Index\Menu\View::render()
      ));
    }
    // ส่งออก เป็น HTML
    $response = new Response;
    $response->withContent(Gcms::$view->renderHTML())->send();
  }
}