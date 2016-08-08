<?php
/*
 * @filesource index/controllers/pagesview.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Pagesview;

use \Kotchasan\Http\Request;
use \Kotchasan\Login;
use \Kotchasan\Html;
use \Kotchasan\Date;

/**
 * Pagesview
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Kotchasan\Controller
{
  private $date;

  /**
   * แสดงผล
   */
  public function render(Request $request)
  {
    // แอดมิน
    if (Login::isAdmin()) {
      $this->date = $request->get('date', date('Y-m'))->date();
      // แสดงผล
      $section = Html::create('section');
      // breadcrumbs
      $breadcrumbs = $section->add('div', array(
        'class' => 'breadcrumbs'
      ));
      $ul = $breadcrumbs->add('ul');
      $ul->appendChild('<li><span class="icon-home">{LNG_Home}</span></li>');
      $ul->appendChild('<li><span>{LNG_Pagesview}</span></li>');
      $section->add('header', array(
        'innerHTML' => '<h1 class="icon-stats">'.$this->title().'</h1>'
      ));
      // แสดงฟอร์ม
      $section->appendChild(createClass('Index\Pagesview\View')->render($this->date));
      return $section->render();
    } else {
      // 404.html
      return \Index\Error\Controller::page404();
    }
  }

  /**
   * title bar
   */
  public function title()
  {
    return '{LNG_Visitors report} '.Date::format($this->date.'-1', 'M Y');
  }
}