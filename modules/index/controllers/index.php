<?php
/*
 * @filesource index/controllers/index.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Index;

use \Gcms\Gcms;
use \Gcms\Login;
use \Kotchasan\Template;
use \Kotchasan\Http\Request;
use \Kotchasan\Http\Response;

/**
 * Controller หลัก สำหรับแสดง frontend ของ GCMS
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Kotchasan\Controller
{

  /**
   * แสดงผล index.html
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
    self::$cfg->skin = $request->get('skin', self::$request->session('skin', self::$cfg->skin)->toString())->toString();
    self::$cfg->skin = is_file(ROOT_PATH.'skin/'.self::$cfg->skin.'/style.css') ? self::$cfg->skin : 'bighead';
    $_SESSION['skin'] = self::$cfg->skin;
    Template::init(self::$cfg->skin);
    // ตรวจสอบหน้าที่จะแสดง
    if (!empty(self::$cfg->maintenance_mode) && !Login::isAdmin()) {
      Gcms::$view = new \Index\Maintenance\View;
    } elseif (!empty(self::$cfg->show_intro) && str_replace(array(BASE_PATH, '/'), '', $request->getUri()->getPath()) == '') {
      Gcms::$view = new \Index\Intro\View;
    } else {
      // counter และ useronline
      $new_day = \Index\Counter\Model::init();
      // View
      Gcms::$view = new \Gcms\View;
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
        if ($new_day && is_file($dir.$owner.'/controllers/cron.php')) {
          include $dir.$owner.'/controllers/cron.php';
          $class = ucfirst($owner).'\Cron\Controller';
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
      $modules = \Index\Module\Controller::get($request->getQueryParams());
      if (!empty($modules)) {
        // โหลดโมดูลที่เรียก
        $page = createClass($modules->className)->{$modules->method}($request, $modules->module);
      }
      if (empty($page)) {
        // ไม่พบหน้าที่เรียก (index)
        $page = createClass('Index\PageNotFound\Controller')->init($request, 'index');
      }
      // title ของเว็บไซต์
      $web_title = strip_tags($page->topic);
      // meta tag
      $meta = array(
        'generator' => '<meta name=generator content="GCMS AJAX CMS design by http://gcms.in.th">',
        'og:title' => '<meta property="og:title" content="'.$web_title.'">',
        'description' => '<meta name=description content="'.$page->description.'">',
        'keywords' => '<meta name=keywords content="'.$page->keywords.'">',
        'og:site_name' => '<meta property="og:site_name" content="'.$web_title.'">',
        'og:type' => '<meta property="og:type" content="article">'
      );
      // โมดูลแรกสุด ใส่ลงใน Javascript
      $module_list = array_keys(Gcms::$install_modules);
      $script = array('var FIRST_MODULE = "'.reset($module_list).'";');
      // logo
      $image_logo = '';
      if (!empty(self::$cfg->logo) && is_file(ROOT_PATH.DATA_FOLDER.'image/'.self::$cfg->logo)) {
        $image_src = WEB_URL.DATA_FOLDER.'image/'.self::$cfg->logo;
        $info = getImageSize(ROOT_PATH.DATA_FOLDER.'image/'.self::$cfg->logo);
        if ($info[0] > 0 || $info[1] > 0) {
          $ext = explode('.', self::$cfg->logo);
          if (strtolower(end($ext)) == 'swf') {
            $script[] = '$G(window).Ready(function(){';
            $script[] = 'if ($E("logo")) {';
            $script[] = "new GMedia('logo_swf', '".$image_src."', $info[0], $info[1]).write('logo');";
            $script[] = '}';
            $script[] = '});';
          } else {
            $image_logo = '<img src="'.$image_src.'" alt="{WEBTITLE}">';
          }
        }
      }
      if (empty($page->image_src)) {
        if (is_file(ROOT_PATH.DATA_FOLDER.'image/facebook_photo.jpg')) {
          $page->image_src = WEB_URL.DATA_FOLDER.'image/facebook_photo.jpg';
        }
      } elseif (!empty($image_src)) {
        $page->image_src = $image_src;
      }
      if (!empty($page->image_src)) {
        $meta['image_src'] = '<link rel=image_src href="'.$page->image_src.'">';
        $meta['og:image'] = '<meta property="og:image" content="'.$page->image_src.'">';
      }
      if (!empty(self::$cfg->facebook_appId)) {
        $meta['og:app_id'] = '<meta property="fb:app_id" content="'.self::$cfg->facebook_appId.'">';
      }
      if (isset($page->canonical)) {
        $meta['canonical'] = '<meta name=canonical content="'.$page->canonical.'">';
        $meta['og:url'] = '<meta property="og:url" content="'.$page->canonical.'">';
      }
      $meta['script'] = "<script>\n".implode("\n", $script)."\n</script>";
      Gcms::$view->setMetas($meta);
      // ภาษาที่ติดตั้ง
      $languages = Template::create('', '', 'language');
      foreach (self::$cfg->languages as $lng) {
        $languages->add(array(
          '/{LNG}/' => $lng
        ));
      }
      // เมนูหลัก
      Gcms::$view->setContents(Gcms::$menu->render(isset($page->menu) ? $page->menu : $page->module));
      // เนื้อหา
      Gcms::$view->setContents(array(
        // content
        '/{CONTENT}/' => $page->detail,
        // title
        '/{TITLE}/' => $web_title,
        // ภาษาที่ติดตั้ง
        '/{LANGUAGES}/' => $languages->render(),
        // โลโก
        '/{LOGO}/' => $image_logo
      ));
    }
    // ส่งออก เป็น HTML
    $response = new Response;
    $response->withContent(Gcms::$view->renderHTML())->send();
  }
}