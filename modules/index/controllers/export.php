<?php
/*
 * @filesource index/controllers/export.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Export;

use \Kotchasan\Template;
use \Kotchasan\Http\Request;

/**
 * Controller สำหรับการ Export หรือ Print
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Kotchasan\Controller
{

  /**
   * print.php
   *
   * @param Request $request
   */
  public function index(Request $request)
  {
    // session cookie
    $request->initSession();
    // กำหนด skin ให้กับ template
    Template::init(self::$cfg->skin);
    // ตรวจสอบโมดูลที่เรียก
    $index = \Index\Module\Model::getModuleWithConfig('', $request->get('module')->filter('a-z0-9'));
    if ($index) {
      $className = ucfirst($index->owner).'\Export\Controller';
      if (class_exists($className) && method_exists($className, 'init')) {
        $detail = createClass($className)->init($request, $index);
      }
      if ($detail != '') {
        $view = new \Gcms\View;
        $view->setContents(array(
          '/{CONTENT}/' => $detail
        ));
        echo $view->renderHTML(Template::load('', '', 'print'));
        exit;
      }
    }
    // ไม่พบโมดูลหรือไม่มีสิทธิ
    new \Kotchasan\Http\NotFound();
  }
}