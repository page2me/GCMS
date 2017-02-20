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
use \Kotchasan\Http\Response;
use \Gcms\Gcms;

/**
 * Controller หลัก สำหรับแสดงหน้าเว็บไซต์
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Kotchasan\Controller
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
    self::$cfg->skin = $request->get('skin', $request->session('skin', self::$cfg->skin)->toString())->toString();
    self::$cfg->skin = is_file(ROOT_PATH.'skin/'.self::$cfg->skin.'/style.css') ? self::$cfg->skin : 'rooster';
    $_SESSION['skin'] = self::$cfg->skin;
    Template::init(self::$cfg->skin);
    // ตรวจสอบหน้าที่จะแสดง
    if (!empty(self::$cfg->maintenance_mode) && !Login::isAdmin()) {
      Gcms::$view = new \Index\Maintenance\View;
    } elseif (!empty(self::$cfg->show_intro) && str_replace(array(BASE_PATH, '/'), '', $request->getUri()->getPath()) == '') {
      Gcms::$view = new \Index\Intro\View;
    } else {
      // View
      Gcms::$view = new \Gcms\View;
      // โหลดเมนูทั้งหมดเรียงตามลำดับเมนู (รายการแรกคือหน้า Home)
      Gcms::$menu = \Index\Menu\Controller::create();
      // counter
      $counter = \Index\Counter\Model::init();
      // โหลดโมดูลที่ติดตั้งแล้ว และสามารถใช้งานได้ และ โหลด Counter
      Gcms::$module = \Index\Module\Controller::create(Gcms::$menu, $counter->new_day);
      // ข้อมูลเว็บไซต์
      Gcms::$site = array(
        '@type' => 'Organization',
        'name' => self::$cfg->web_title,
        'description' => self::$cfg->web_description,
        'url' => WEB_URL.'index.php',
      );
      // logo
      if (!empty(self::$cfg->logo) && is_file(ROOT_PATH.DATA_FOLDER.'image/'.self::$cfg->logo)) {
        $info = @getImageSize(ROOT_PATH.DATA_FOLDER.'image/'.self::$cfg->logo);
        if ($info && $info[0] > 0 && $info[1] > 0) {
          $exts = explode('.', self::$cfg->logo);
          $ext = strtolower(end($exts));
          $logo = WEB_URL.DATA_FOLDER.'image/'.self::$cfg->logo;
          Gcms::$view->addScript('if ($E("logo")) {new GMedia("logo_'.$ext.'", "'.$logo.'", '.$info[0].', '.$info[1].').write("logo");}');
          if ($ext !== 'swf') {
            $image_logo = '<img src="'.$logo.'" alt="{WEBTITLE}">';
            // site logo
            Gcms::$site['logo'] = array(
              '@type' => 'ImageObject',
              'url' => $logo,
              'width' => $info[0],
            );
          }
        }
      }
      if (!isset(Gcms::$site['logo']) && is_file(ROOT_PATH.DATA_FOLDER.'image/site_logo.jpg')) {
        $info = @getImageSize(ROOT_PATH.DATA_FOLDER.'image/site_logo.jpg');
        if ($info && $info[0] > 0 && $info[1] > 0) {
          Gcms::$site['logo'] = array(
            '@type' => 'ImageObject',
            'url' => WEB_URL.DATA_FOLDER.'image/site_logo.jpg',
            'width' => $info[0],
          );
        }
      }
      // หน้า home (เมนูรายการแรกสุด)
      $home = Gcms::$menu->homeMenu();
      if ($home) {
        $home->canonical = WEB_URL.'index.php';
        // breadcrumb หน้า home
        Gcms::$view->addBreadcrumb($home->canonical, $home->menu_text, $home->menu_tooltip, 'icon-home');
      }
      // โมดูลแรกสุด ใส่ลงใน Javascript
      Gcms::$view->addScript('var FIRST_MODULE = "'.Gcms::$module->getFirst().'";');
      // ตรวจสอบโมดูลที่เรียก
      $modules = Gcms::$module->checkModuleCalled($request->getQueryParams());
      if (!empty($modules)) {
        // โหลดโมดูลที่เรียก
        $page = createClass($modules->className)->{$modules->method}($request, $modules->module);
      }
      if (empty($page)) {
        // ไม่พบหน้าที่เรียก (index)
        $page = createClass('Index\PageNotFound\Controller')->init('index');
      }
      // title ของเว็บไซต์
      $web_title = strip_tags($page->topic);
      // meta tag
      $meta = array(
        'generator' => '<meta name=generator content="GCMS AJAX CMS design by https://gcms.in.th">',
        'og:title' => '<meta property="og:title" content="'.$web_title.'">',
        'description' => '<meta name=description content="'.$page->description.'">',
        'keywords' => '<meta name=keywords content="'.$page->keywords.'">',
        'og:site_name' => '<meta property="og:site_name" content="'.$web_title.'">',
        'og:type' => '<meta property="og:type" content="article">'
      );
      if (empty($page->image_src) && isset(Gcms::$site['logo'])) {
        $page->image_src = Gcms::$site['logo']['url'];
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
        '/{LOGO}/' => isset($image_logo) ? $image_logo : ''
      ));
    }
    // ส่งออก เป็น HTML
    $response = new Response;
    if (isset($page->status) && $page->status == 404) {
      $response = $response->withStatus(404)->withAddedHeader('Status', '404 Not Found');
    }
    $response->withContent(Gcms::$view->renderHTML())->send();
  }
}