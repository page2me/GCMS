<?php
/*
 * @filesource download/views/admin/write.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Download\Admin\Write;

use \Kotchasan\Html;
use \Gcms\Gcms;
use \Kotchasan\Text;

/**
 * ฟอร์มเพิ่ม/แก้ไข ไฟล์ดาวน์โหลด
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Gcms\Adminview
{

  /**
   * module=download-write
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
        'action' => 'index.php/download/model/admin/write/save',
        'onsubmit' => 'doFormSubmit',
        'ajax' => true
    ));
    $fieldset = $form->add('fieldset', array(
      'title' => '{LNG_Details of} {LNG_Download file}'
    ));
    // name
    $fieldset->add('text', array(
      'id' => 'name',
      'labelClass' => 'g-input icon-edit',
      'itemClass' => 'item',
      'label' => '{LNG_File Name}',
      'comment' => '{LNG_The name of the file you want to download it the file name extension. To upload a new file name if not specified. The program uses the name of a file to upload. (Can be used in Thai language)}',
      'maxlength' => 50,
      'value' => isset($index->name) ? $index->name : ''
    ));
    // category_id
    $categories = array(0 => '{LNG_Uncategorized}');
    foreach (\Index\Category\Model::categories((int)$index->module_id) as $item) {
      $categories[$item['category_id']] = Gcms::ser2Str($item, 'topic');
    }
    $fieldset->add('select', array(
      'id' => 'category_id',
      'labelClass' => 'g-input icon-category',
      'label' => '{LNG_Category}',
      'comment' => '{LNG_Select the category you want}',
      'itemClass' => 'item',
      'options' => $categories,
      'value' => isset($index->category_id) ? $index->category_id : 0
    ));
    // reciever
    $fieldset->add('checkboxgroups', array(
      'id' => 'reciever',
      'name' => 'reciever[]',
      'labelClass' => 'g-input icon-group',
      'itemClass' => 'item',
      'label' => '{LNG_Select the group of recipients}',
      'comment' => '{LNG_The recipient is listed in the selected group can be downloaded (You can select multiple groups)}',
      'options' => \Kotchasan\ArrayTool::merge(array(-1 => '{LNG_Guest}'), self::$cfg->member_status),
      'value' => $index->reciever
    ));
    // detail
    $fieldset->add('text', array(
      'id' => 'detail',
      'labelClass' => 'g-input icon-file',
      'itemClass' => 'item',
      'label' => '{LNG_Description}',
      'comment' => '{LNG_Brief details about the file. (Page may be downloaded).}',
      'maxlength' => 200,
      'value' => isset($index->detail) ? $index->detail : ''
    ));
    // file
    $fieldset->add('text', array(
      'id' => 'file',
      'labelClass' => 'g-input icon-world',
      'itemClass' => 'item',
      'label' => '{LNG_Name include file path}',
      'comment' => '{LNG_The name and address files such as upload/file.ext. If a file is already on the Server, or upload them below.}',
      'maxlength' => 255,
      'value' => isset($index->file) ? $index->file : ''
    ));
    // file
    $fieldset->add('file', array(
      'id' => 'file',
      'labelClass' => 'g-input icon-upload',
      'itemClass' => 'item',
      'label' => '{LNG_Browse file}',
      'comment' => '{LNG_Upload :type files no larger than :size}',
      'accept' => $index->file_typies
    ));
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
      'id' => 'id',
      'value' => $index->id
    ));
    // module_id
    $fieldset->add('hidden', array(
      'id' => 'module_id',
      'value' => $index->module_id
    ));
    Gcms::$view->setContents(array(
      '/:type/' => implode(', ', $index->file_typies),
      '/:size/' => Text::formatFileSize($index->upload_size)
      ), false);
    return $form->render();
  }
}