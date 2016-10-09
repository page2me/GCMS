<?php
/*
 * @filesource document/views/admin/write.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Document\Admin\Write;

use \Kotchasan\Html;
use \Kotchasan\Language;
use \Gcms\Gcms;
use \Kotchasan\ArrayTool;

/**
 * ฟอร์มสร้าง/แก้ไข เนื้อหา
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Gcms\Adminview
{

  /**
   * module=document-write
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
        'action' => 'index.php/document/model/admin/write/save',
        'onsubmit' => 'doFormSubmit',
        'ajax' => true
    ));
    foreach ($index->languages as $item) {
      // รายละเอียด
      $details = isset($index->details[$item]) ? $index->details[$item] : (object)array('topic' => '', 'keywords' => '', 'description' => '', 'detail' => '', 'relate' => '');
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
      // relate
      $fieldset->add('text', array(
        'id' => 'relate_'.$item,
        'labelClass' => 'g-input icon-edit',
        'itemClass' => 'item',
        'label' => '{LNG_Relate}',
        'comment' => '{LNG_Title or topic 3 to 255 characters}',
        'value' => $details->relate
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
    // alias
    $fieldset->add('text', array(
      'id' => 'alias',
      'labelClass' => 'g-input icon-world',
      'itemClass' => 'item',
      'label' => '{LNG_Alias}',
      'comment' => '{LNG_Used for the URL of the web page (SEO) can use letters, numbers and _ only can not have duplicate names.}',
      'value' => $index->alias
    ));
    $groups = $fieldset->add('groups-table', array(
      'label' => '{LNG_Article Date}',
      'comment' => '{LNG_The date that the story was written}'
    ));
    // create_date
    preg_match('/([0-9]{4,4}\-[0-9]{2,2}\-[0-9]{2,2})\s([0-9]+):([0-9]+)/', date('Y-m-d H:i', $index->create_date), $match);
    $groups->add('date', array(
      'id' => 'create_date',
      'labelClass' => 'g-input icon-calendar',
      'itemClass' => 'width',
      'value' => $match[1]
    ));
    // create_hour
    $datas = array();
    for ($i = 0; $i < 24; $i++) {
      $d = sprintf('%02d', $i);
      $datas[$d] = $d;
    }
    $groups->add('select', array(
      'id' => 'create_hour',
      'labelClass' => 'width',
      'label' => '{LNG_Time}',
      'options' => $datas,
      'value' => $match[2]
    ));
    $groups->add('span', array(
      'class' => 'width',
      'innerHTML' => ':'
    ));
    // create_minute
    $datas = array();
    for ($i = 0; $i < 60; $i++) {
      $d = sprintf('%02d', $i);
      $datas[$d] = $d;
    }
    $groups->add('select', array(
      'id' => 'create_minute',
      'labelClass' => 'width',
      'options' => $datas,
      'value' => $match[3]
    ));
    // picture
    if (!empty($index->picture) && is_file(ROOT_PATH.DATA_FOLDER.'document/'.$index->picture)) {
      $img = WEB_URL.DATA_FOLDER.'document/'.$index->picture;
    } else {
      $img = WEB_URL.(isset($index->default_icon) ? $index->default_icon : 'modules/document/img/document-icon.png');
    }
    $fieldset->add('file', array(
      'id' => 'picture',
      'labelClass' => 'g-input icon-upload',
      'itemClass' => 'item',
      'label' => '{LNG_Thumbnail}',
      'comment' => '{LNG_Browse image uploaded, type :type size :width*:height pixel (automatic resize)}',
      'dataPreview' => 'imgPicture',
      'previewSrc' => $img
    ));
    // category_id
    $fieldset->add('select', array(
      'id' => 'category_'.$index->module_id,
      'name' => 'category_id',
      'labelClass' => 'g-input icon-category',
      'label' => '{LNG_Category}',
      'comment' => '{LNG_Select the category you want}',
      'itemClass' => 'item',
      'options' => ArrayTool::merge(array(0 => '{LNG_Uncategorized}'), \Index\Category\Model::categories((int)$index->module_id)),
      'value' => $index->category_id
    ));
    // can_reply
    $fieldset->add('select', array(
      'id' => 'can_reply',
      'labelClass' => 'g-input icon-comments',
      'itemClass' => 'item',
      'label' => '{LNG_Comment}',
      'comment' => '{LNG_Comment the story}',
      'options' => Language::get('REPLIES'),
      'value' => $index->can_reply
    ));
    // show_news
    $groups = $fieldset->add('groups-table', array(
      'label' => '{LNG_Display in the widget} <a href="http://gcms.in.th/index.php?module=howto&id=311" target=_blank class=icon-help></a>',
      'comment' => '{LNG_Use this option if you want a list that is presented in part by itself.}'
    ));
    foreach (Language::get('SHOW_NEWS') as $key => $value) {
      $groups->add('checkbox', array(
        'id' => 'show_news_'.$key,
        'name' => 'show_news[]',
        'itemClass' => 'width',
        'label' => $value,
        'value' => $key,
        'checked' => strpos($index->show_news, "$key=1") === false ? false : true
      ));
    }
    // published_date
    $fieldset->add('date', array(
      'id' => 'published_date',
      'labelClass' => 'g-input icon-calendar',
      'itemClass' => 'item',
      'label' => '{LNG_Published date}',
      'comment' => '{LNG_The date of publication of this information. The publisher will start automatically when you log on due date}',
      'value' => $index->published_date
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
    Gcms::$view->setContents(array(
      '/:type/' => implode(', ', $index->img_typies),
      '/:width/' => $index->icon_width,
      '/:height/' => $index->icon_height
      ), false);
    return $form->render();
  }
}