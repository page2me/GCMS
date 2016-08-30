<?php
/*
 * @filesource gallery/views/admin/settings.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Gallery\Admin\Settings;

use \Kotchasan\Html;
use \Kotchasan\HtmlTable;

/**
 * โมดูลสำหรับจัดการการตั้งค่า
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Gcms\Adminview
{

  /**
   * module=gallery-settings
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
        'action' => 'index.php/gallery/model/admin/settings/save',
        'onsubmit' => 'doFormSubmit',
        'ajax' => true
    ));
    $fieldset = $form->add('fieldset', array(
      'title' => '{LNG_Thumbnail}'
    ));
    $groups = $fieldset->add('groups', array(
      'label' => '{LNG_Size of} {LNG_Thumbnail}',
      'comment' => '{LNG_The size of the small picture (Thumbnail) is displayed in albums and galleries (pixels), resize automatically}'
    ));
    // icon_width
    $groups->add('text', array(
      'id' => 'icon_width',
      'labelClass' => 'g-input icon-width',
      'itemClass' => 'width',
      'label' => '{LNG_Width}',
      'value' => $index->icon_width
    ));
    // icon_height
    $groups->add('text', array(
      'id' => 'icon_height',
      'labelClass' => 'g-input icon-height',
      'itemClass' => 'width',
      'label' => '{LNG_Height}',
      'value' => $index->icon_height
    ));
    // image_width
    $fieldset->add('text', array(
      'id' => 'image_width',
      'labelClass' => 'g-input icon-width',
      'itemClass' => 'item',
      'label' => '{LNG_Size of} {LNG_Image} ({LNG_Width})',
      'comment' => '{LNG_The size of the images are stored as pixels. The image will be resized automatically.}',
      'value' => $index->image_width
    ));
    $groups = $fieldset->add('groups-table', array(
      'label' => '{LNG_Type of file uploads}',
      'comment' => '{LNG_Type of files (pictures) that can be used as icon of categories such as jpg, jpeg, gif and png (must choose at least one type)}'
    ));
    // img_typies
    foreach (array('jpg', 'jpeg', 'gif', 'png') as $item) {
      $groups->add('checkbox', array(
        'id' => 'img_typies_'.$item,
        'name' => 'img_typies[]',
        'itemClass' => 'width',
        'label' => $item,
        'value' => $item,
        'checked' => isset($index->img_typies) && is_array($index->img_typies) ? in_array($item, $index->img_typies) : false
      ));
    }
    $fieldset = $form->add('fieldset', array(
      'title' => '{LNG_Display}'
    ));
    $groups = $fieldset->add('groups', array(
      'comment' => '{LNG_The number of items displayed per page}'
    ));
    // cols
    $groups->add('select', array(
      'id' => 'cols',
      'labelClass' => 'g-input icon-width',
      'itemClass' => 'width',
      'label' => '{LNG_Cols}',
      'options' => array(2 => 2, 4 => 4, 6 => 6, 8 => 8),
      'value' => $index->cols
    ));
    // rows
    $groups->add('select', array(
      'id' => 'rows',
      'labelClass' => 'g-input icon-height',
      'itemClass' => 'width',
      'label' => '{LNG_Rows}',
      'options' => array(1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 9, 10 => 10, 11 => 11, 12 => 12, 13 => 13, 14 => 14),
      'value' => $index->rows
    ));
    // sort
    $sorts = array('ID', '{LNG_Last updated}', '{LNG_Random}');
    $fieldset->add('select', array(
      'id' => 'sort',
      'labelClass' => 'g-input icon-sort',
      'itemClass' => 'item',
      'label' => '{LNG_Sort}',
      'comment' => '{LNG_Determine how to sort the items displayed in the list}',
      'options' => $sorts,
      'value' => $index->sort
    ));
    $fieldset = $form->add('fieldset', array(
      'title' => '{LNG_Role of Members}'
    ));
    // สถานะสมาชิก
    $table = new HtmlTable(array(
      'class' => 'responsive config_table'
    ));
    $table->addHeader(array(
      array(),
      array('text' => '{LNG_Viewing}'),
      array('text' => '{LNG_Upload}'),
      array('text' => '{LNG_Settings}')
    ));
    foreach (\Kotchasan\ArrayTool::merge(array(-1 => '{LNG_Guest}'), self::$cfg->member_status) as $i => $item) {
      if ($i != 1) {
        $row = array();
        $row[] = array(
          'scope' => 'col',
          'text' => $item
        );
        $check = isset($index->can_view) && is_array($index->can_view) && in_array($i, $index->can_view) ? ' checked' : '';
        $row[] = array(
          'class' => 'center',
          'text' => '<label data-text="{LNG_Viewing}"><input type=checkbox name=can_view[] title="{LNG_Members of this group can see the content}" value='.$i.$check.'></label>'
        );
        $check = isset($index->can_write) && is_array($index->can_write) && in_array($i, $index->can_write) ? ' checked' : '';
        $row[] = array(
          'class' => 'center',
          'text' => '<label data-text="{LNG_Upload}"><input type=checkbox name=can_write[] title="{LNG_Members of this group can create albums}" value='.$i.$check.'></label>'
        );
        $check = isset($index->can_config) && is_array($index->can_config) && in_array($i, $index->can_config) ? ' checked' : '';
        $row[] = array(
          'class' => 'center',
          'text' => '<label data-text="{LNG_Settings}"><input type=checkbox name=can_config[] title="{LNG_Members of this group can setting the module (not recommend)}" value='.$i.$check.'></label>'
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
      'value' => '{LNG_Save}'
    ));
    // id
    $fieldset->add('hidden', array(
      'name' => 'id',
      'value' => $index->module_id
    ));
    return $form->render();
  }
}