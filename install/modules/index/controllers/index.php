<?php
/*
 * @filesource index/controllers/index.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Index;

use \Kotchasan\Http\Request;

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
    // session
    $request->initSession();
    define('INSTALL', __FILE__);
    $content = array();
    if (empty(self::$cfg->version)) {
      // ติดตั้งครั้งแรก
      $class = 'Index\Install'.$request->request('step')->toInt().'\View';
      if (class_exists($class) && method_exists($class, 'render')) {
        $page = createClass($class)->render($request);
      } else {
        $page = createClass('Index\Install\View')->render($request);
      }
    } elseif (version_compare(self::$cfg->version, self::$cfg->new_version) == -1) {
      $page = createClass('Index\Upgrade\View')->render($request);
    } else {
      $page = createClass('Index\Success\View')->render($request);
    }
    // แสดงผล
    $view = new \Gcms\View;
    $view->setContents(array(
      '/{CONTENT}/' => $page->content,
      '/{TITLE}/' => $page->title
    ));
    echo $view->renderHTML(file_get_contents(ROOT_PATH.'install/modules/index/views/index.html'));
  }
}
