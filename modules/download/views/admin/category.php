<?php
/*
 * @filesource download/views/admin/category.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Download\Admin\Category;

use \Kotchasan\Language;
use \Kotchasan\DataTable;
use \Kotchasan\Html;
use \Kotchasan\Form;

/**
 * แสดงรายการหมวดหมู่
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Kotchasan\View
{
  /**
   * ข้อมูลโมดูล
   *
   * @var object
   */
  private $languages;

  /**
   * module=download-category
   *
   * @param object $index
   * @return string
   */
  public function render($index)
  {
    // ภาษาที่ติดตั้ง
    $this->languages = Language::installedLanguage();
    // form
    $form = Html::create('form', array(
        'id' => 'setup_frm',
        'class' => 'setup_frm',
        'autocomplete' => 'off',
        'action' => 'index.php/download/model/admin/category/save',
        'onsubmit' => 'doFormSubmit',
        'ajax' => true
    ));
    $fieldset = $form->add('fieldset', array(
      'title' => '{LNG_Details of} {LNG_Category}'
    ));
    // ตารางหมวดหมู่
    $table = new DataTable(array(
      /* ข้อมูลใส่ลงในตาราง */
      'datas' => \Download\Admin\Category\Model::all((int)$index->module_id),
      /* ฟังก์ชั่นจัดรูปแบบการแสดงผลแถวของตาราง */
      'onRow' => array($this, 'onRow'),
      /* คอลัมน์ที่ไม่ต้องแสดงผล */
      'hideColumns' => array('id'),
      /* ฟังก์ชั่นจัดการข้อมูลในแต่ละแถว เมื่อมีการเปลี่ยนแปลง */
      'onInitRow' => 'downloadInitRow',
      'border' => true,
      'responsive' => true,
      'pmButton' => true,
      'showCaption' => false,
      'headers' => array(
        'category_id' => array(
          'text' => '{LNG_ID}'
        )
      )
    ));
    $fieldset->add('div', array(
      'class' => 'item',
      'innerHTML' => $table->render()
    ));
    $fieldset = $form->add('fieldset', array(
      'class' => 'submit'
    ));
    // submit
    $fieldset->add('submit', array(
      'class' => 'button save large',
      'value' => '{LNG_Save}'
    ));
    // module_id
    $fieldset->add('hidden', array(
      'id' => 'module_id',
      'value' => $index->module_id
    ));
    return $form->render();
  }

  /**
   * จัดรูปแบบการแสดงผลในแต่ละแถว
   *
   * @param array $item
   * @return array
   */
  public function onRow($item)
  {
    $item['category_id'] = Form::text(array(
        'name' => 'category_id[]',
        'labelClass' => 'g-input icon-edit',
        'size' => 2,
        'value' => $item['category_id']
      ))->render();
    foreach ($this->languages as $lng) {
      $item[$lng] = Form::text(array(
          'name' => $lng.'[]',
          'labelClass' => 'g-input',
          'value' => $item[$lng],
          'style' => 'background-image:url(../language/'.$lng.'.gif)'
        ))->render();
    }
    return $item;
  }
}