<?php
/*
 * @filesource index/controllers/languageadd.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Languageadd;

use \Kotchasan\Http\Request;
use \Kotchasan\Login;
use \Kotchasan\Language;
use \Kotchasan\Html;

/**
 * module=system
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Gcms\Controller
{

  /**
   * ฟอร์มเพิ่ม/แก้ไข ภาษาหลัก
   *
   * @param Request $request
   * @return string
   */
  public function render(Request $request)
  {
    // แอดมิน
    if (Login::isAdmin()) {
      // เลือกเมนู
      $this->menu = 'settings';
      // รายการที่ต้องการ
      $id = self::$request->get('id')->toString();
      $title = Language::get(empty($id) ? 'Create' : 'Edit');
      // ข้อความ title bar
      $this->title = $title.' {LNG_Language}';
      // แสดงผล
      $section = Html::create('section');
      // breadcrumbs
      $breadcrumbs = $section->add('div', array(
        'class' => 'breadcrumbs'
      ));
      $ul = $breadcrumbs->add('ul');
      $ul->appendChild('<li><span class="icon-settings">{LNG_Site settings}</span></li>');
      $ul->appendChild('<li><a href="{BACKURL?module=languages&id=0}">{LNG_Language}</a></li>');
      $ul->appendChild('<li><span>'.$title.'</span></li>');
      $section->add('header', array(
        'innerHTML' => '<h1 class="icon-language">'.$this->title.' '.$id.'</h1>'
      ));
      // แสดงฟอร์ม
      $section->appendChild(createClass('Index\Languageadd\View')->render($id));
      return $section->render();
    }
    // 404.html
    return \Index\Error\Controller::page404();
  }
}