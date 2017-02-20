<?php
/*
 * @filesource personnel/controllers/admin/category.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Personnel\Admin\Category;

use \Kotchasan\Http\Request;
use \Kotchasan\Html;
use \Kotchasan\Login;
use \Gcms\Gcms;

/**
 * module=personnel-category
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Gcms\Controller
{

  /**
   * แสดงรายการหมวดหมู่
   *
   * @param Request $request
   * @return string
   */
  public function render(Request $request)
  {
    // ข้อความ title bar
    $this->title = '{LNG_List of} {LNG_Personnel groups}';
    // เลือกเมนู
    $this->menu = 'modules';
    // อ่านข้อมูลโมดูล
    $index = \Index\Adminmodule\Model::get('personnel', $request->get('mid')->toInt());
    // login
    $login = Login::isMember();
    // สมาชิกและสามารถตั้งค่าได้
    if ($index && Gcms::canConfig($login, $index, 'can_config')) {
      // แสดงผล
      $section = Html::create('section');
      // breadcrumbs
      $breadcrumbs = $section->add('div', array(
        'class' => 'breadcrumbs'
      ));
      $ul = $breadcrumbs->add('ul');
      $ul->appendChild('<li><span class="icon-customer">{LNG_Module}</span></li>');
      $ul->appendChild('<li><a href="{BACKURL?module=personnel-settings&mid='.$index->module_id.'}">'.ucfirst($index->module).'</a></li>');
      $ul->appendChild('<li><span>{LNG_Personnel groups}</span></li>');
      $section->add('header', array(
        'innerHTML' => '<h1 class="icon-category">'.$this->title.'</h1>'
      ));
      // แสดงตาราง
      $section->appendChild(createClass('Personnel\Admin\Category\View')->render($index));
      return $section->render();
    }
    // 404.html
    return \Index\Error\Controller::page404();
  }
}