<?php
/*
 * @filesource index/controllers/menuwrite.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Menuwrite;

use \Kotchasan\Http\Request;
use \Kotchasan\Login;
use \Kotchasan\Html;

/**
 * module=menuwrite
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Gcms\Controller
{

  /**
   * ฟอร์มสร้าง/แก้ไข หน้าเว็บไซต์
   *
   * @param Request $request
   * @return string
   */
  public function render(Request $request)
  {
    // แอดมิน
    if (Login::isAdmin()) {
      // รายการที่ต้องการ
      $index = \Index\Menuwrite\Model::getMenu(self::$request->get('id')->toInt());
      if ($index) {
        $title = '{LNG_'.(empty($index->id) ? 'Create' : 'Edit').'}';
        // ข้อความ title bar
        $this->title = $title.' {LNG_Menu}';
        // เลือกเมนู
        $this->menu = 'index';
        // แสดงผล
        $section = Html::create('section');
        // breadcrumbs
        $breadcrumbs = $section->add('div', array(
          'class' => 'breadcrumbs'
        ));
        $ul = $breadcrumbs->add('ul');
        $ul->appendChild('<li><span class="icon-modules">{LNG_Menus} &amp; {LNG_Web pages}</span></li>');
        $ul->appendChild('<li><a href="{BACKURL?module=pages&id=0}">{LNG_Menus}</a></li>');
        $ul->appendChild('<li><span>'.$title.'</span></li>');
        $section->add('header', array(
          'innerHTML' => '<h1 class="icon-write">'.$this->title.'</h1>'
        ));
        if ($index) {
          // แสดงฟอร์ม
          $section->appendChild(createClass('Index\Menuwrite\View')->render($index));
          return $section->render();
        }
      }
    }
    // 404.html
    return \Index\Error\Controller::page404();
  }
}