<?php
/*
 * @filesource gallery/views/admin/write.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Gallery\Admin\Write;

use \Kotchasan\Html;
use \Gcms\Gcms;

/**
 * ฟอร์มสร้าง/แก้ไข อัลบัม
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Kotchasan\View
{

  /**
   * module=gallery-write
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
        'action' => 'index.php/gallery/model/admin/write/save',
        'onsubmit' => 'doFormSubmit',
        'ajax' => true
    ));
    $fieldset = $form->add('fieldset', array(
      'title' => '{LNG_Details of} {LNG_Album}'
    ));
    // topic
    $fieldset->add('text', array(
      'id' => 'topic',
      'labelClass' => 'g-input icon-edit',
      'itemClass' => 'item',
      'label' => '{LNG_Album}',
      'comment' => '{LNG_Enter the name of the album}',
      'maxlength' => 64,
      'value' => $index->topic
    ));
    // detail
    $fieldset->add('textarea', array(
      'id' => 'detail',
      'labelClass' => 'g-input icon-file',
      'itemClass' => 'item',
      'label' => '{LNG_Detail}',
      'comment' => '{LNG_Fill out a short description About the photo albums or photos}',
      'rows' => 5,
      'value' => $index->detail
    ));
    // image
    if (!empty($index->image) && is_file(ROOT_PATH.DATA_FOLDER.'gallery/'.$index->id.'/'.$index->image)) {
      $img = WEB_URL.DATA_FOLDER.'gallery/'.$index->id.'/'.$index->image;
    } else {
      $img = WEB_URL.'modules/gallery/img/noimage.jpg';
    }
    $fieldset->add('file', array(
      'id' => 'image',
      'labelClass' => 'g-input icon-upload',
      'itemClass' => 'item',
      'label' => '{LNG_Image}',
      'comment' => '{LNG_Upload a photo no larger than :width pixels :type types only larger than a specified size will be resize automatically}',
      'dataPreview' => 'imgPicture',
      'previewSrc' => $img,
      'accept' => $index->img_typies
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
      '/:type/' => implode(', ', $index->img_typies),
      '/:width/' => $index->image_width
      ), false);
    return $form->render();
  }
}