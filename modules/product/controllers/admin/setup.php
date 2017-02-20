<?php
/*
 * @filesource product/controllers/admin/setup.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Product\Admin\Setup;

use \Kotchasan\Http\Request;
use \Kotchasan\Html;
use \Kotchasan\Login;
use \Gcms\Gcms;

/**
 * module=product-setup
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Gcms\Controller
{

  /**
   * แสดงรายการสินค้า
   *
   * @param Request $request
   * @return string
   */
  public function render(Request $request)
  {
    // ข้อความ title bar
    $this->title = '{LNG_List of} {LNG_Product}';
    // เลือกเมนู
    $this->menu = 'modules';
    // อ่านข้อมูลโมดูล
    $index = \Index\Adminmodule\Model::get('product', $request->get('mid')->toInt());
    // login
    $login = Login::isMember();
    // สมาชิกและสามารถตั้งค่าได้
    if ($index && Gcms::canConfig($login, $index, 'can_write')) {
      // แสดงผล
      $section = Html::create('section');
      // breadcrumbs
      $breadcrumbs = $section->add('div', array(
        'class' => 'breadcrumbs'
      ));
      $ul = $breadcrumbs->add('ul');
      $ul->appendChild('<li><span class="icon-product">{LNG_Module}</span></li>');
      $ul->appendChild('<li><a href="{BACKURL?module=product-settings&mid='.$index->module_id.'}">'.ucfirst($index->module).'</a></li>');
      $ul->appendChild('<li><span>{LNG_List of} {LNG_Product}</span></li>');
      $section->add('header', array(
        'innerHTML' => '<h1 class="icon-list">'.$this->title.'</h1>'
      ));
      // แสดงตาราง
      $section->appendChild(createClass('Product\Admin\Setup\View')->render($index));
      return $section->render();
    }
    // 404.html
    return \Index\Error\Controller::page404();
  }
}