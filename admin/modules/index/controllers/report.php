<?php
/*
 * @filesource index/controllers/report.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Report;

use \Kotchasan\Http\Request;
use \Gcms\Login;
use \Kotchasan\Html;
use \Kotchasan\Date;

/**
 * module=report
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Gcms\Controller
{

  /**
   * Report
   *
   * @param Request $request
   * @return string
   */
  public function render(Request $request)
  {
    // แอดมิน
    if (Login::adminAccess()) {
      // ค่าที่ส่งมา
      $ip = $request->get('ip')->filter('0-9\.');
      $date = $request->get('date', date('Y-m-d'))->date();
      // ข้อความ title bar
      $this->title = '{LNG_Visitors report} '.Date::format($date, 'd M Y').(empty($ip) ? '' : ' IP '.$ip);
      // เลือกเมนู
      $this->menu = 'dashboard';
      // แสดงผล
      $section = Html::create('section');
      // breadcrumbs
      $breadcrumbs = $section->add('div', array(
        'class' => 'breadcrumbs'
      ));
      $ul = $breadcrumbs->add('ul');
      $ul->appendChild('<li><span class="icon-home">{LNG_Home}</span></li>');
      $ul->appendChild('<li><span>{LNG_Report}</span></li>');
      $section->add('header', array(
        'innerHTML' => '<h1 class="icon-stats">'.$this->title.'</h1>'
      ));
      // แสดงฟอร์ม
      $section->appendChild(createClass('Index\Report\View')->render($ip, $date));
      return $section->render();
    }
    // 404.html
    return \Index\Error\Controller::page404();
  }
}