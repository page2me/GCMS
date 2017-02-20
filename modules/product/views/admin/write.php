<?php
/*
 * @filesource product/views/admin/write.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Product\Admin\Write;

use \Kotchasan\Html;
use \Kotchasan\Language;
use \Gcms\Gcms;

/**
 * module=product-write
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Gcms\Adminview
{

  /**
   * ฟอร์มสร้าง/แก้ไข สินค้า
   *
   * @param object $index
   * @return string
   */
  public function render($index)
  {
    // สกุลเงิน
    $currency_unit = Language::get('CURRENCY_UNITS')[$index->currency_unit];
    // form
    $form = Html::create('form', array(
        'id' => 'setup_frm',
        'class' => 'setup_frm',
        'autocomplete' => 'off',
        'action' => 'index.php/product/model/admin/write/save',
        'onsubmit' => 'doFormSubmit',
        'ajax' => true
    ));
    foreach ($index->languages as $item) {
      // รายละเอียด
      $details = isset($index->details[$item]) ? $index->details[$item] : (object)array('topic' => '', 'keywords' => '', 'description' => '', 'detail' => '');
      // รายละเอียดแต่ละภาษา
      $fieldset = $form->add('fieldset', array(
        'id' => 'detail_'.$item,
        'title' => '{LNG_Detail}&nbsp;<img src='.WEB_URL.'language/'.$item.'.gif alt='.$item.'>'
      ));
      // topic
      $fieldset->add('text', array(
        'id' => 'topic_'.$item,
        'labelClass' => 'g-input icon-edit',
        'itemClass' => 'item',
        'label' => '{LNG_Topic}',
        'comment' => '{LNG_Title or topic 3 to 255 characters}',
        'maxlength' => 255,
        'value' => $details->topic
      ));
      // keywords
      $fieldset->add('textarea', array(
        'id' => 'keywords_'.$item,
        'labelClass' => 'g-input icon-tags',
        'itemClass' => 'item',
        'label' => '{LNG_Keywords}',
        'comment' => '{LNG_Text keywords for SEO or Search Engine to search}',
        'value' => $details->keywords
      ));
      // description
      $fieldset->add('textarea', array(
        'id' => 'description_'.$item,
        'labelClass' => 'g-input icon-file',
        'itemClass' => 'item',
        'label' => '{LNG_Description}',
        'comment' => '{LNG_Text short summary of your story. Which can be used to show in your theme. (If not the program will fill in the contents of the first paragraph)}',
        'value' => $details->description
      ));
      // detail
      $fieldset->add('ckeditor', array(
        'id' => 'details_'.$item,
        'itemClass' => 'item',
        'height' => 300,
        'language' => Language::name(),
        'toolbar' => 'Document',
        'upload' => true,
        'label' => '{LNG_Detail}',
        'value' => $details->detail
      ));
    }
    // รายละเอียดอื่นๆ
    $fieldset = $form->add('fieldset', array(
      'id' => 'options',
      'title' => '{LNG_Set up or configure other details}'
    ));
    // product_no
    $fieldset->add('text', array(
      'id' => 'product_no',
      'labelClass' => 'g-input icon-edit',
      'itemClass' => 'item',
      'label' => '{LNG_Product Code}',
      'comment' => '{LNG_Please fill in} {LNG_Product Code}',
      'value' => $index->product_no
    ));
    // price
    $fieldset->add('currency', array(
      'id' => 'price',
      'labelClass' => 'g-input icon-money',
      'itemClass' => 'item',
      'label' => '{LNG_Price}',
      'unit' => $currency_unit,
      'comment' => '{LNG_The full price of the product (Display only)}',
      'value' => isset($index->price[$index->currency_unit]) ? $index->price[$index->currency_unit] : 0
    ));
    // net
    $fieldset->add('currency', array(
      'id' => 'net',
      'labelClass' => 'g-input icon-money',
      'itemClass' => 'item',
      'label' => '{LNG_Net Price}',
      'unit' => $currency_unit,
      'comment' => '{LNG_The net price of the product, after deducting discounts}',
      'value' => isset($index->net[$index->currency_unit]) ? $index->net[$index->currency_unit] : 0
    ));
    // alias
    $fieldset->add('text', array(
      'id' => 'alias',
      'labelClass' => 'g-input icon-world',
      'itemClass' => 'item',
      'label' => '{LNG_Alias}',
      'comment' => '{LNG_Used for the URL of the web page (SEO) can use letters, numbers and _ only can not have duplicate names.}',
      'value' => isset($index->alias) ? $index->alias : ''
    ));
    // picture
    if (!empty($index->picture) && is_file(ROOT_PATH.DATA_FOLDER.'product/'.$index->picture)) {
      $img = WEB_URL.DATA_FOLDER.'product/'.$index->picture;
    } else {
      $img = WEB_URL.'skin/img/blank.gif';
    }
    $fieldset->add('file', array(
      'id' => 'picture',
      'labelClass' => 'g-input icon-upload',
      'itemClass' => 'item',
      'label' => '{LNG_Image}',
      'comment' => '{LNG_Upload a photo no larger than :width pixels :type types only larger than a specified size will be resize automatically}',
      'dataPreview' => 'imgPicture',
      'previewSrc' => $img
    ));
    // published
    $fieldset->add('select', array(
      'id' => 'published',
      'labelClass' => 'g-input icon-published1',
      'itemClass' => 'item',
      'label' => '{LNG_Published}',
      'comment' => '{LNG_Publish this item}',
      'options' => Language::get('PUBLISHEDS'),
      'value' => $index->published
    ));
    $fieldset = $form->add('fieldset', array(
      'class' => 'submit'
    ));
    // submit
    $fieldset->add('submit', array(
      'class' => 'button ok large',
      'value' => '{LNG_Save}'
    ));
    // preview
    $fieldset->add('button', array(
      'id' => 'preview',
      'class' => 'button preview large',
      'value' => '{LNG_Preview}'
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
    // tab ที่เลือก
    $tab = self::$request->get('tab')->toString();
    $tab = empty($tab) ? 'detail_'.reset($index->languages) : $tab;
    $form->script('initWriteTab("accordient_menu", "'.$tab.'");');
    $form->script('checkSaved("preview", "'.WEB_URL.'index.php?module='.$index->module.'", "id");');
    $form->script('new GValidator("alias", "keyup,change", checkAlias, "index.php/index/model/checker/alias", null, "setup_frm");');
    $form->script('selectChanged("category_'.$index->module_id.'","index.php/index/model/admincategory/action",doFormSubmit);');
    // tab
    $fieldset->add('hidden', array(
      'id' => 'tab',
      'value' => $tab
    ));
    Gcms::$view->setContentsAfter(array(
      '/:type/' => implode(', ', $index->img_typies),
      '/:width/' => $index->image_width,
    ));
    return $form->render();
  }
}