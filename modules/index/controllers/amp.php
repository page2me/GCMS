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
      // ส่งออก เป็น HTML
      $response = new Response;
      $response->withContent(Gcms::$view->renderHTML($page->detail))->send();
    }
  }
}