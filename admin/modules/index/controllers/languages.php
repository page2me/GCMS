<?php
/*
 * @filesource index/controllers/languages.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Languages;

use \Kotchasan\Http\Request;
use \Kotchasan\Login;
use \Kotchasan\Html;

/**
 * module=languages
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Gcms\Controller
{

  /**
   * รายการภาษาที่ติดตั้งแล้ว
   *
   * @param Request $request
   * @return string
   */
  public function render(Request $request)
  {
    // แอดมิน
    if (Login::isAdmin()) {
      // ข้อความ title bar
      $this->title = '{LNG_Language settings}';
      // เลือกเมนู
      $this->menu = 'settings';
      // แสดงผล
      $section = Html::create('section');
      // breadcrumbs
      $breadcrumbs = $section->add('div', array(
        'class' => 'breadcrumbs'
      ));
      $ul = $breadcrumbs->add('ul');
      $ul->appendChild('<li><span class="icon-settings">{LNG_Site settings}</span></li>');
      $ul->appendChild('<li><span>{LNG_Language}</span></li>');
      $section->add('header', array(
        'innerHTML' => '<h1 class="icon-language">'.$this->title.'</h1>'
      ));
      // แสดงฟอร์ม
      $section->appendChild(createClass('Index\Languages\View')->render());
      return $section->render();
    }
    // 404.html
    return \Index\Error\Controller::page404();
  }
}