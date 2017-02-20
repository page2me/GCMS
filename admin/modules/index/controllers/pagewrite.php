<?php
/*
 * @filesource index/controllers/pagewrite.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Pagewrite;

use \Kotchasan\Http\Request;
use \Kotchasan\Login;
use \Kotchasan\Html;

/**
 * module=pagewrite
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Gcms\Controller
{

  /**
   * ฟอร์มสร้าง/แก้ไข โมดูล
   *
   * @param Request $request
   * @return string
   */
  public function render(Request $request)
  {
    // แอดมิน
    if (Login::isAdmin()) {
      // รายการที่ต้องการ
      $index = \Index\Pagewrite\Model::getIndex($request->get('id')->toInt(), $request->get('owner', 'index')->topic());
      if ($index) {
        // ข้อความ title bar
        $this->title = '{LNG_Create or Edit} {LNG_Module} ('.$index->owner.')';
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
        $ul->appendChild('<li><a href="{BACKURL?module=pages&id=0}">{LNG_Web pages}</a></li>');
        $ul->appendChild('<li><span>{LNG_'.(empty($index->id) ? 'Create' : 'Edit').'}</span></li>');
        $section->add('header', array(
          'innerHTML' => '<h1 class="icon-write">'.$this->title.'</h1>'
        ));
        // แสดงฟอร์ม
        $section->appendChild(createClass('Index\Pagewrite\View')->render($index));
        return $section->render();
      }
    }
    // 404.html
    return \Index\Error\Controller::page404();
  }
}