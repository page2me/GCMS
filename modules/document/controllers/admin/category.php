<?php
/*
 * @filesource document/controllers/admin/category.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Document\Admin\Category;

use \Kotchasan\Http\Request;
use \Kotchasan\Login;
use \Kotchasan\Html;
use \Kotchasan\Language;
use \Gcms\Gcms;

/**
 * แสดงรายการหมวดหมู่
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
  public function render(Request $request)
  {
    // อ่านข้อมูลโมดูล
    $index = \Index\Adminmodule\Model::get('document', $request->get('mid')->toInt());
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
      $ul->appendChild('<li><span class="icon-documents">{LNG_Module}</span></li>');
      $ul->appendChild('<li><a href="{BACKURL?module=document-settings&mid='.$index->module_id.'}">'.ucfirst($index->module).'</a></li>');
      $ul->appendChild('<li><span>{LNG_Category}</span></li>');
      $section->add('header', array(
        'innerHTML' => '<h1 class="icon-category">'.$this->title().'</h1>'
      ));
      // แสดงตาราง
      $section->appendChild(createClass('Document\Admin\Category\View')->render($index));
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
    return str_replace(':name', Language::get('Category'), Language::get('list of all :name'));
  }
}
