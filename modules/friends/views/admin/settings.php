<?php
/**
 * @filesource friends/views/admin/settings.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Friends\Admin\Settings;

use \Kotchasan\Html;
use \Kotchasan\Language;
use \Kotchasan\HtmlTable;

/**
 * โมดูลสำหรับจัดการการตั้งค่า
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Kotchasan\View
{

  /**
   * module=friends-settings
   *
   * @param object $index
   * @return string
   */
  public function render($index)
  {
    // form
    $form = Html::create('form', array(
        'id' => 'setup_frm',
        'class' => 'setup_frm',
        'autocomplete' => 'off',
        'action' => 'index.php/friends/model/admin/settings/save',
        'onsubmit' => 'doFormSubmit',
        'ajax' => true
    ));
    $fieldset = $form->add('fieldset', array(
      'title' => Language::get('Post')
    ));
    // per_day
    $fieldset->add('select', array(
      'id' => 'per_day',
      'labelClass' => 'g-input icon-config',
      'itemClass' => 'item',
      'label' => Language::get('Amount'),
      'comment' => Language::get('Limit the number of posts per day (Zero means unlimited)'),
      'options' => array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10),
      'value' => $index->per_day
    ));
    $fieldset = $form->add('fieldset', array(
      'title' => Language::get('Display')
    ));
    $groups = $fieldset->add('groups', array(
      'comment' => Language::get('The number of items displayed per page')
    ));
    // pin_per_page
    $groups->add('number', array(
      'id' => 'pin_per_page',
      'labelClass' => 'g-input icon-pin',
      'itemClass' => 'width50',
      'label' => Language::get('Pin'),
      'value' => $index->pin_per_page
    ));
    // list_per_page
    $groups->add('number', array(
      'id' => 'list_per_page',
      'labelClass' => 'g-input icon-list',
      'itemClass' => 'width50',
      'label' => Language::get('General'),
      'value' => $index->list_per_page
    ));
    // sex_color
    foreach (Language::get('SEXES') as $k => $v) {
      $fieldset->add('color', array(
        'id' => "sex_color_$k",
        'name' => "sex_color[$k]",
        'labelClass' => 'g-input icon-color',
        'itemClass' => 'item',
        'label' => $v,
        'value' => isset($index->sex_color[$k]) ? $index->sex_color[$k] : ''
      ));
    }
    $fieldset = $form->add('fieldset', array(
      'title' => Language::get('Role of Members')
    ));
    $table = new HtmlTable(array(
      'class' => 'responsive config_table'
    ));
    $table->addHeader(array(
      array(),
      array('text' => Language::get('Post')),
      array('text' => Language::get('Moderator')),
      array('text' => Language::get('Settings'))
    ));
    foreach (self::$cfg->member_status AS $i => $item) {
      if ($i != 1) {
        $row = array();
        $row[] = array(
          'scope' => 'col',
          'text' => $item
        );
        $check = isset($index->can_post) && is_array($index->can_post) && in_array($i, $index->can_post) ? ' checked' : '';
        $row[] = array(
          'class' => 'center',
          'text' => '<label data-text="'.Language::get('Post').'"><input type=checkbox name=can_post[] title="'.Language::get('Members of this group can post').'" value='.$i.$check.'></label>'
        );
        $check = isset($index->moderator) && is_array($index->moderator) && in_array($i, $index->moderator) ? ' checked' : '';
        $row[] = array(
          'class' => 'center',
          'text' => $i > 1 ? '<label data-text="'.Language::get('Moderator').'"><input type=checkbox name=moderator[] title="'.Language::get('Members of this group can edit content written by others').'" value='.$i.$check.'></label>' : ''
        );
        $check = isset($index->can_config) && is_array($index->can_config) && in_array($i, $index->can_config) ? ' checked' : '';
        $row[] = array(
          'class' => 'center',
          'text' => $i > 1 ? '<label data-text="'.Language::get('Settings').'"><input type=checkbox name=can_config[] title="'.Language::get('Members of this group can setting the module (not recommend)').'" value='.$i.$check.'></label>' : ''
        );
        $table->addRow($row, array(
          'class' => 'status'.$i
        ));
      }
    }
    $div = $fieldset->add('div', array(
      'class' => 'item'
    ));
    $div->appendChild($table->render());
    $fieldset = $form->add('fieldset', array(
      'class' => 'submit'
    ));
    // submit
    $fieldset->add('submit', array(
      'class' => 'button ok large',
      'value' => Language::get('Save')
    ));
    // id
    $fieldset->add('hidden', array(
      'name' => 'id',
      'value' => $index->module_id
    ));
    return $form->render();
  }
}