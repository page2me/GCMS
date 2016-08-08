<?php
/*
 * @filesource index/views/languageadd.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Languageadd;

use \Kotchasan\Html;
use \Kotchasan\Language;
use \Gcms\Gcms;

/**
 * ฟอร์มเพิ่ม/แก้ไข ภาษาหลัก
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Kotchasan\View
{

  /**
   * module=languageadd
   *
   * @param string $id
   * @return string
   */
  public function render($id)
  {
    // form
    $form = Html::create('form', array(
        'id' => 'setup_frm',
        'class' => 'setup_frm',
        'autocomplete' => 'off',
        'action' => 'index.php/index/model/languageadd/save',
        'onsubmit' => 'doFormSubmit',
        'ajax' => true
    ));
    $fieldset = $form->add('fieldset', array(
      'title' => '{LNG_Details of} {LNG_Language}'
    ));
    // language_name
    $fieldset->add('text', array(
      'id' => 'language_name',
      'labelClass' => 'g-input icon-language',
      'itemClass' => 'item',
      'label' => '{LNG_Language}',
      'comment' => '{LNG_Language name English lowercase two letters}',
      'maxlength' => 2,
      'value' => $id
    ));
    if (empty($id)) {
      // copy
      $fieldset->add('select', array(
        'id' => 'lang_copy',
        'labelClass' => 'g-input icon-copy',
        'itemClass' => 'item',
        'label' => '{LNG_Copy}',
        'comment' => '{LNG_Copy language from the installation}',
        'options' => Language::installedLanguage()
      ));
    }
    // lang_icon
    $img = is_file(ROOT_PATH."language/$id.gif") ? WEB_URL."language/$id.gif" : "../skin/img/blank.gif";
    $fieldset->add('file', array(
      'id' => 'lang_icon',
      'labelClass' => 'g-input icon-upload',
      'itemClass' => 'item',
      'label' => '{LNG_Icon}',
      'comment' => '{LNG_Image upload types :type only, should be prepared to have the same size}',
      'dataPreview' => 'icoImage',
      'previewSrc' => $img
    ));
    $fieldset = $form->add('fieldset', array(
      'class' => 'submit'
    ));
    // submit
    $fieldset->add('submit', array(
      'class' => 'button ok large',
      'value' => '{LNG_Save}'
    ));
    // language
    $fieldset->add('hidden', array(
      'id' => 'language',
      'value' => $id
    ));
    Gcms::$view->setContents(array(
      '/:type/' => 'gif'
      ), false);
    return $form->render();
  }
}