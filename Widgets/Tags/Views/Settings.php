<?php
/*
 * @filesource Widgets/Tags/Views/Settings.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Widgets\Tags\Views;

use \Kotchasan\Html;

/**
 * โมดูลสำหรับจัดการการตั้งค่าเริ่มต้น
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Settings extends \Gcms\Adminview
{

  /**
   * module=Tags-settings
   *
   * @return string
   */
  public function render()
  {
    $section = Html::create('div', array(
        'class' => ''
    ));
    $div = $section->add('div', array(
      'class' => 'item'
    ));
    $list = $div->add('ol', array(
      'class' => 'editinplace_list',
      'id' => 'config_status'
    ));
    foreach (\Widgets\Tags\Models\Settings::all() as $item) {
      $row = $list->add('li', array(
        'id' => 'config_status_'.$item['id']
      ));
      $row->add('span', array(
        'innerHTML' => '{LNG_Clicked} [ '.$item['count'].' ]',
        'class' => 'no'
      ));
      $row->add('span', array(
        'id' => 'config_status_delete_'.$item['id'],
        'class' => 'icon-delete',
        'title' => '{LNG_Delete}'
      ));
      $row->add('span', array(
        'id' => 'config_status_name_'.$item['id'],
        'innerHTML' => $item['tag'],
        'title' => '{LNG_click to edit}'
      ));
    }
    $div = $section->add('div', array(
      'class' => 'submit'
    ));
    $a = $div->add('a', array(
      'class' => 'button add large',
      'id' => 'config_status_add'
    ));
    $a->add('span', array(
      'class' => 'icon-plus',
      'innerHTML' => '{LNG_Add New} {LNG_Tags}'
    ));
    $section->script('initEditInplace("config_status", "Widgets/Tags/Models/Settings/save");');
    return $section->render();
  }
}