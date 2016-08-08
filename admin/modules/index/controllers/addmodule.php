<?php
/*
 * @filesource index/controllers/addmodule.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Addmodule;

use \Kotchasan\Login;
use \Kotchasan\Html;
use \Gcms\Gcms;

/**
 * เพิ่มโมดูลแบบที่สามารถใช้ซ้ำได้
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
      $ul->appendChild('<li><span class="icon-modules">{LNG_Menus} &amp; {LNG_Web pages}</span></li>');
      $ul->appendChild('<li><a href="{BACKURL?module=mods&id=0}">{LNG_installed module}</a></li>');
      $ul->appendChild('<li><span>{LNG_Create}</span></li>');
      $section->add('header', array(
        'innerHTML' => '<h1 class="icon-new">'.$this->title().'</h1>'
      ));
      // owner
      $modules = array();
      foreach (Gcms::$install_owners as $owner => $item) {
        if (file_exists(ROOT_PATH.'modules/'.$owner.'/controllers/admin/init.php')) {
          $class = ucfirst($owner).'\Admin\Init\Controller';
          if (method_exists($class, 'description')) {
            // get module description
            $description = $class::description();
            if (!empty($description)) {
              $modules[$owner] = $description.' ['.$owner.']';
            }
          }
        }
      }
      // แสดงฟอร์ม
      $section->appendChild(createClass('Index\Addmodule\View')->render($modules));
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
    return '{LNG_Add New} {LNG_Module}';
  }
}