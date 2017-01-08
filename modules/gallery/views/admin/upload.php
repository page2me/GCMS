<?php
/*
 * @filesource gallery/views/admin/upload.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Gallery\Admin\Upload;

use \Kotchasan\Html;
use \Gcms\Gcms;

/**
 * ฟอร์มสร้าง/แก้ไข อัลบัม
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Gcms\Adminview
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
        'id' => 'gallery_upload',
        'class' => 'setup_frm',
        'action' => 'index.php/gallery/model/admin/upload/save',
        'ajax' => false
    ));
    $fieldset = $form->add('fieldset', array(
      'title' => '{LNG_Upload or Delete pictures in albums} '.$index->topic
    ));
    // fileupload
    $fieldset->add('file', array(
      'id' => 'fileupload',
      'itemClass' => 'item',
      'labelClass' => 'g-input icon-upload',
      'label' => '{LNG_Browse file}',
      'comment' => '{LNG_Upload a photo no larger than :width pixels :type types only larger than a specified size will be resize automatically}',
      'accept' => $index->img_typies
    ));
    $fieldset = $form->add('fieldset', array(
      'class' => 'submit'
    ));
    // btnCancle
    $fieldset->add('button', array(
      'id' => 'btnCancle',
      'class' => 'button orange large',
      'value' => '{LNG_Cancel Upload}',
      'disabled' => true
    ));
    // selectAll
    $fieldset->add('button', array(
      'id' => 'selectAll',
      'class' => 'button blue large',
      'value' => '{LNG_Select all}'
    ));
    // clearSelected
    $fieldset->add('button', array(
      'id' => 'clearSelected',
      'class' => 'button pink large',
      'value' => '{LNG_Clear selectd}'
    ));
    // btnDelete
    $fieldset->add('button', array(
      'id' => 'btnDelete',
      'class' => 'button red large',
      'value' => '{LNG_Delete}'
    ));
    // id
    $fieldset->add('hidden', array(
      'id' => 'album_id',
      'value' => $index->id
    ));
    // module_id
    $fieldset->add('hidden', array(
      'id' => 'module_id',
      'value' => $index->module_id
    ));
    Gcms::$view->setContentsAfter(array(
      '/:type/' => implode(', ', $index->img_typies),
      '/:width/' => $index->image_width
    ));
    $form->add('fieldset', array(
      'id' => 'fsUploadProgress'
    ));
    $tb_upload = $form->add('fieldset', array(
      'id' => 'tb_upload'
    ));
    foreach (\Gallery\Admin\Write\Model::pictures($index) as $i => $item) {
      $id = $item->id;
      $div = $tb_upload->add('fieldset', array(
        'id' => 'L_'.$id,
        'class' => 'item'
      ));
      $figure = $div->add('figure');
      $a = $figure->add('a', array(
        'id' => 'preview_'.$id,
        'href' => WEB_URL.DATA_FOLDER.'gallery/'.$index->id.'/'.$item->image,
        'innerHTML' => '<img src="'.WEB_URL.DATA_FOLDER.'gallery/'.$index->id.'/thumb_'.$item->image.'" alt='.$id.'>'
      ));
      if ($i > 0) {
        $a = $figure->add('a', array(
          'id' => 'delete_'.$id.'_'.$index->id,
          'class' => 'icon-uncheck',
          'title' => '{LNG_Delete}'
        ));
      }
    }
    $form->script('initGalleryUpload("gallery_upload", '.$index->id.')');
    Gcms::$view->metas[] = '<script src="'.WEB_URL.'js/uploads.js"></script>';
    return $form->render();
  }
}