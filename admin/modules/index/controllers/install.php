<?php
/*
 * @filesource index/controllers/install.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Install;

use \Kotchasan\Http\Request;
use \Kotchasan\Login;
use \Kotchasan\Html;

/**
 * เพิ่มโมดูลแบบที่สามารถใช้ซ้ำได้
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Kotchasan\Controller
{
  private $module;

  /**
   * แสดงผล
   */
  public function render(Request $request)
  {
    // แอดมิน
    if (Login::isAdmin()) {
      // โมดูลที่ต้องการติดตั้ง
      $module = $request->get('m')->filter('a-z');
      $widget = $request->get('w')->filter('a-z');
      $this->module = $module !== '' ? $module : $widget;
      // แสดงผล
      $section = Html::create('section');
      // breadcrumbs
      $breadcrumbs = $section->add('div', array(
        'class' => 'breadcrumbs'
      ));
      $ul = $breadcrumbs->add('ul');
      if ($module !== '') {
        $ul->appendChild('<li><span class="icon-modules">{LNG_Module}</span></li>');
        $type = 'module';
      } elseif ($widget !== '') {
        $ul->appendChild('<li><span class="icon-widgets">{LNG_Widgets}</span></li>');
        $type = 'widget';
      } else {
        // 404.html
        return \Index\Error\Controller::page404();
      }
      $ul->appendChild('<li><span>{LNG_Install}</span></li>');
      $section->add('header', array(
        'innerHTML' => '<h1 class="icon-inbox">'.$this->title().'</h1>'
      ));
      // แสดงฟอร์ม
      $section->appendChild(createClass('Index\Install\View')->render($type, $this->module));
      return $section->render();
    }
    // 404.html
    return \Index\Error\Controller::page404();
  }

  /**
   * title bar
   */
  public function title()
  {
    return ucfirst($this->module).' - {LNG_First Install}';
  }
}