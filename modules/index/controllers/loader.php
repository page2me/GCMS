<?php
/*
 * @filesource index/controllers/loader.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Loader;

use \Gcms\Gcms;
use \Gcms\Login;
use \Kotchasan\Template;
use \Kotchasan\Http\Request;

/**
 * Controller สำหรับโหลดข้อมูลด้วย GLoader
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Kotchasan\Controller
{

  /**
   * GLoader
   *
   * @param Request $request
   */
  public function index(Request $request)
  {
    // ตรวจสอบ Referer
    if ($request->initSession() && $request->isReferer()) {
      // ตัวแปรป้องกันการเรียกหน้าเพจโดยตรง
      define('MAIN_INIT', __FILE__);
      // ตรวจสอบการ login
      Login::create();
      // กำหนด skin ให้กับ template
      Template::init(self::$cfg->skin);
      // counter และ useronline
      \Index\Counter\Model::init();
      // View
      Gcms::$view = new \Index\Loader\View;
      // โมดูลที่ติดตั้ง
      $dir = ROOT_PATH.'modules/';
      // โหลดโมดูลทั้งหมด
      foreach (\Index\Module\Model::getInstalledModule() AS $owner) {
        if (is_file($dir.$owner.'/controllers/init.php')) {
          include $dir.$owner.'/controllers/init.php';
          $class = ucfirst($owner).'\Init\Controller';
          if (method_exists($class, 'init')) {
            createClass($class)->init();
          }
        }
      }
      // โหลด Init ของ Widgets
      $dir = ROOT_PATH.'Widgets/';
      $f = @opendir($dir);
      if ($f) {
        while (false !== ($text = readdir($f))) {
          if ($text != "." && $text != "..") {
            if (is_dir($dir.$text)) {
              if (is_file($dir.$text.'/Controllers/Init.php')) {
                include $dir.$text.'/Controllers/Init.php';
                $class = 'Widgets\\'.ucfirst($text).'\Controllers\Init';
                if (method_exists($class, 'init')) {
                  createClass($class)->init();
                }
              }
            }
          }
        }
        closedir($f);
      }
      // หน้า home มาจากเมนูรายการแรก
      $home = Gcms::$menu->homeMenu();
      if ($home) {
        $home->canonical = WEB_URL.'index.php';
        // breadcrumb หน้า home
        Gcms::$view->addBreadcrumb($home->canonical, $home->menu_text, $home->menu_tooltip, 'icon-home');
      }
      // ตรวจสอบโมดูลที่เรียก
      $posts = $request->getParsedBody();
      $modules = \Index\Module\Controller::get($posts);
      if (!empty($modules)) {
        // โหลดโมดูลที่เรียก
        $page = createClass($modules->className)->{$modules->method}($request->withQueryParams($posts), $modules->module);
      }
      if (empty($page)) {
        // ไม่พบหน้าที่เรียก (index)
        $page = createClass('Index\PageNotFound\Controller')->init($request, 'index');
      }
      // output เป็น HTML
      $ret = array(
        'db_elapsed' => round(microtime(true) - REQUEST_TIME, 4),
        'db_quries' => \Kotchasan\Database\Driver::queryCount()
      );
      foreach ($page as $key => $value) {
        $ret[$key] = $value;
      }
      if (empty($ret['menu'])) {
        $ret['menu'] = $ret['module'];
      }
      $ret['detail'] = Gcms::$view->renderHTML($page->detail);
      echo json_encode($ret);
    }
  }
}