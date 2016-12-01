<?php
/*
 * @filesource index/controllers/amp.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Amp;

use \Gcms\Gcms;
use \Kotchasan\Template;
use \Kotchasan\Http\Request;
use \Kotchasan\Http\Response;

/**
 * Controller หลัก สำหรับแสดงหน้า AMP ของ GCMS
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Kotchasan\Controller
{

  /**
   * แสดงผล amp.html
   *
   * @param Request $request
   */
  public function index(Request $request)
  {
    // ตัวแปรป้องกันการเรียกหน้าเพจโดยตรง
    define('MAIN_INIT', 'amphtml');
    // session cookie
    $request->initSession();
    // View
    Gcms::$view = new \Gcms\Amp;
    // กำหนด skin ให้กับ template
    Template::init(self::$cfg->skin);
    // โหลดโมดูลที่ติดตั้งแล้ว และสามารถใช้งานได้
    Gcms::$module = \Index\Module\Controller::create(null, false);
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
        if (strtolower(end($exts)) !== 'swf') {
          // site logo
          Gcms::$site['logo'] = array(
            '@type' => 'ImageObject',
            'url' => WEB_URL.DATA_FOLDER.'image/'.self::$cfg->logo,
            'width' => $info[0],
          );
        }
      }
    }
    if (!isset(Gcms::$site['logo']) && is_file(ROOT_PATH.DATA_FOLDER.'image/facebook_photo.jpg')) {
      $info = @getImageSize(ROOT_PATH.DATA_FOLDER.'image/facebook_photo.jpg');
      if ($info && $info[0] > 0 && $info[1] > 0) {
        Gcms::$site['logo'] = array(
          '@type' => 'ImageObject',
          'url' => WEB_URL.DATA_FOLDER.'image/facebook_photo.jpg',
          'width' => $info[0],
        );
      }
    }
    // ตรวจสอบโมดูลที่เรียก
    $modules = Gcms::$module->checkModuleCalled($request->getQueryParams());
    if (!empty($modules)) {
      // โหลดโมดูลที่เรียก
      $page = createClass($modules->className)->{$modules->method}($request, $modules->module);
    }
    if (empty($page) || (isset($page->status) && $page->status == 404)) {
      // 404
      new \Kotchasan\Http\NotFound('404 Page not found!');
    } else {
      $contents = array();
      foreach ($page as $key => $value) {
        $contents['/{'.strtoupper($key).'}/'] = $value;
      }
      // เนื้อหา
      Gcms::$view->setContents($contents);
      // ส่งออก เป็น HTML
      $response = new Response;
      $response->withContent(Gcms::$view->renderHTML($page->detail))->send();
    }
  }
}