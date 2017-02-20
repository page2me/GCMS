<?php
/*
 * @filesource board/controllers/admin/settings.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Board\Admin\Settings;

use \Kotchasan\Http\Request;
use \Kotchasan\Html;
use \Kotchasan\Login;
use \Gcms\Gcms;

/**
 * module=board-settings
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Gcms\Controller
{

  /**
   * จัดการการตั้งค่า
   *
   * @param Request $request
   * @return string
   */
  public function render(Request $request)
  {
    // ข้อความ title bar
    $this->title = '{LNG_Module settings} {LNG_Board}';
    // เลือกเมนู
    $this->menu = 'modules';
    // อ่านข้อมูลโมดูล
    $index = \Index\Adminmodule\Model::get('board', $request->get('mid')->toInt());
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
      $ul->appendChild('<li><span class="icon-board">{LNG_Module}</span></li>');
      $ul->appendChild('<li><span>'.ucfirst($index->module).'</span></li>');
      $ul->appendChild('<li><span>{LNG_Settings}</span></li>');
      $section->add('header', array(
        'innerHTML' => '<h1 class="icon-config">'.$this->title.'</h1>'
      ));
      // แสดงฟอร์ม
      $section->appendChild(createClass('Board\Admin\Settings\View')->render($index));
      return $section->render();
    }
    // 404.html
    return \Index\Error\Controller::page404();
  }
}