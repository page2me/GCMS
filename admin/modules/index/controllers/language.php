<?php
/*
 * @filesource index/controllers/language.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Language;

use \Kotchasan\Http\Request;
use \Kotchasan\Login;
use \Kotchasan\Html;

/**
 * module=language
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Gcms\Controller
{

  /**
   * รายการภาษา
   *
   * @param Request $request
   * @return string
   */
  public function render()
  {
    // แอดมิน
    if (Login::isAdmin()) {
      // ข้อความ title bar
      $this->title = '{LNG_Add and manage the display language of the site}';
      // เลือกเมนู
      $this->menu = 'tools';
      // แสดงผล
      $section = Html::create('section');
      // breadcrumbs
      $breadcrumbs = $section->add('div', array(
        'class' => 'breadcrumbs'
      ));
      $ul = $breadcrumbs->add('ul');
      $ul->appendChild('<li><span class="icon-tools">{LNG_Tools}</span></li>');
      $ul->appendChild('<li><span>{LNG_Language}</span></li>');
      $section->add('header', array(
        'innerHTML' => '<h1 class="icon-language">'.$this->title.'</h1>'
      ));
      // แสดงตาราง
      $section->appendChild(createClass('Index\Language\View')->render());
      return $section->render();
    }
    // 404.html
    return \Index\Error\Controller::page404();
  }
}