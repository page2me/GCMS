<?php
/*
 * @filesource index/controllers/database.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Database;

use \Kotchasan\Login;
use \Kotchasan\Html;

/**
 * Database
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Kotchasan\Controller
{

  /**
   * แสดงผล
   */
  public function render()
  {
    // แอดมิน
    if (Login::isAdmin()) {
      // แสดงผล
      $section = Html::create('section');
      // breadcrumbs
      $breadcrumbs = $section->add('div', array(
        'class' => 'breadcrumbs'
      ));
      $ul = $breadcrumbs->add('ul');
      $ul->appendChild('<li><span class="icon-tools">{LNG_Tools}</span></li>');
      $ul->appendChild('<li><span>{LNG_Database}</span></li>');
      $section->add('header', array(
        'innerHTML' => '<h1 class="icon-database">'.$this->title().'</h1>'
      ));
      $div = $section->add('div', array(
        'class' => 'setup_frm'
      ));
      // แสดงฟอร์ม
      $view = new \Index\Database\View;
      $div->appendChild($view->export());
      $div->appendChild($view->import());
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
    return '{LNG_Backup and restore database}';
  }
}