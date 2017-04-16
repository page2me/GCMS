<?php
/**
 * @filesource product/views/admin/settings.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Product\Admin\Settings;

use \Kotchasan\Html;
use \Kotchasan\Language;
use \Kotchasan\HtmlTable;

/**
 * module=product-settings
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Gcms\Adminview
{

  /**
   * จัดการการตั้งค่า
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
        'action' => 'index.php/product/model/admin/settings/save',
        'onsubmit' => 'doFormSubmit',
        'ajax' => true
    ));
    $fieldset = $form->add('fieldset', array(
      'title' => '{LNG_Details of} {LNG_Product}'
    ));
    // product_no
    $fieldset->add('text', array(
      'id' => 'product_no',
      'labelClass' => 'g-input icon-edit',
      'itemClass' => 'item',
      'label' => '{LNG_Product Code}',
      'comment' => '{LNG_number format such as %04d (%04d means the number on 4 digits, up to 11 digits)}',
      'placeholder' => '%04d',
      'value' => isset($index->product_no) ? $index->product_no : ''
    ));
    // currency_unit
    $fieldset->add('select', array(
      'id' => 'currency_unit',
      'labelClass' => 'g-input icon-currency',
      'itemClass' => 'item',
      'label' => '{LNG_Currency Unit}',
      'comment' => '{LNG_Currency for goods and services}',
      'options' => Language::get('CURRENCY_UNITS'),
      'value' => isset($index->currency_unit) ? $index->currency_unit : 'THB'
    ));
    $groups = $fieldset->add('groups');
    // thumb_width
    $groups->add('text', array(
      'id' => 'thumb_width',
      'labelClass' => 'g-input icon-width',
      'itemClass' => 'width',
      'label' => '{LNG_Size of} {LNG_Thumbnail}',
      'placeholder' => '696 {LNG_Pixel}',
      'comment' => '{LNG_Images shown in the catalog of products}',
      'value' => $index->thumb_width
    ));
    // image_width
    $groups->add('text', array(
      'id' => 'image_width',
      'labelClass' => 'g-input icon-width',
      'itemClass' => 'width',
      'label' => '{LNG_Size of} {LNG_Image}',
      'placeholder' => '800 {LNG_Pixel}',
      'comment' => '{LNG_Pictures displayed at the product details page}',
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
      array('text' => '{LNG_Writing}'),
      array('text' => '{LNG_Settings}')
    ));
    foreach (self::$cfg->member_status as $i => $item) {
      if ($i > 1) {
        $row = array();
        $row[] = array(
          'scope' => 'col',
          'text' => $item
        );
        $check = isset($index->can_write) && is_array($index->can_write) && in_array($i, $index->can_write) ? ' checked' : '';
        $row[] = array(
          'class' => 'center',
          'text' => $i > 0 ? '<label data-text="{LNG_Writing}"><input type=checkbox name=can_write[] title="{LNG_Members of this group can create or edit product details}" value='.$i.$check.'></label>' : ''
        );
        $check = isset($index->can_config) && is_array($index->can_config) && in_array($i, $index->can_config) ? ' checked' : '';
        $row[] = array(
          'class' => 'center',
          'text' => $i > 1 ? '<label data-text="{LNG_Settings}"><input type=checkbox name=can_config[] title="{LNG_Members of this group can setting the module (not recommend)}" value='.$i.$check.'></label>' : ''
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