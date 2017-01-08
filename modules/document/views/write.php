<?php
/*
 * @filesource document/views/write.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Document\Write;

use \Kotchasan\Http\Request;
use \Gcms\Gcms;
use \Kotchasan\Login;
use \Kotchasan\Language;
use \Kotchasan\Date;
use \Kotchasan\Html;
use \Kotchasan\ArrayTool;

/**
 * ฟอร์มเพิ่ม/แก้ไข บทความ
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Gcms\View
{

  /**
   * เขียน-แก้ไข เรื่องที่เขียนโดยสมาชิก
   *
   * @param Request $request
   * @param object $index
   * @return object
   */
  public function render(Request $request, $index)
  {
    // login
    $login = Login::isMember();
    // ตรวจสอบรายการที่เลือก
    $index = \Document\Admin\Write\Model::get($request->request('mid')->toInt(), $request->request('id')->toInt(), $request->request('cat')->toInt());
    // สามารถเขียนได้
    if ($login && $index && in_array($login['status'], $index->can_write) && ($index->id == 0 || $index->member_id == $login['id'])) {
      // topic
      $index->topic = Language::get($index->id == 0 ? 'Add New' : 'Edit').' '.ucfirst($index->module);
      // ภาษาที่ติดตั้ง
      $languages = Language::installedLanguage();
      if (!empty($index->id)) {
        $index->details = \Document\Admin\Write\Model::details((int)$index->module_id, (int)$index->id, reset(self::$cfg->languages));
      } else {
        $index->details = array();
      }
      // form
      $form = Html::create('form', array(
          'id' => 'documentwrite_frm',
          'class' => 'main_frm member_section',
          'autocomplete' => 'off',
          'action' => 'index.php/document/model/write/save',
          'onsubmit' => 'doFormSubmit',
          'ajax' => true
      ));
      $form->add('header', array(
        'innerHTML' => '<h2 class=icon-write>'.$index->topic.'</h2>'
      ));
      foreach (self::$cfg->languages as $item) {
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
      // alias
      $fieldset->add('text', array(
        'id' => 'alias',
        'labelClass' => 'g-input icon-world',
        'itemClass' => 'item',
        'label' => '{LNG_Alias}',
        'comment' => '{LNG_Used for the URL of the web page (SEO) can use letters, numbers and _ only can not have duplicate names.}',
        'value' => $index->alias
      ));
      // create_date
      $groups = $fieldset->add('groups-table', array(
        'label' => '{LNG_Article Date}',
        'comment' => '{LNG_The date that the story was written}'
      ));
      $row = $groups->add('row');
      $row->add('date', array(
        'id' => 'create_date',
        'labelClass' => 'g-input icon-calendar',
        'itemClass' => 'width',
        'value' => date('Y-m-d', $index->create_date)
      ));
      $row->add('time', array(
        'id' => 'create_time',
        'labelClass' => 'g-input icon-clock',
        'itemClass' => 'width',
        'label' => '{LNG_Time}',
        'value' => date('H:i:s', $index->create_date)
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
      $fieldset = $form->add('fieldset', array(
        'class' => 'submit'
      ));
      // submit
      $fieldset->add('submit', array(
        'class' => 'button large ok',
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
      Gcms::$view->setContentsAfter(array(
        '/:type/' => implode(', ', $index->img_typies),
        '/:width/' => $index->icon_width,
        '/:height/' => $index->icon_height
      ));
      $form->script('new GValidator("alias", "keyup,change", checkAlias, "index.php/index/model/checker/alias", null, "setup_frm");');
      // คืนค่า
      $index->detail = $form->render();
      $index->description = $index->topic;
      $index->tab = $index->module;
      return $index;
    }
    return null;
  }

  /**
   * จัดรูปแบบการแสดงผลในแต่ละแถว
   *
   * @param array $item
   * @return array
   */
  public function onRow($item)
  {
    $item['topic'] = '<a href="'.WEB_URL.'index.php?module='.$this->index->module.'&amp;id='.$item['id'].'" target=_blank>'.$item['topic'].'</a>';
    if (is_file(ROOT_PATH.DATA_FOLDER.'document/'.$item['picture'])) {
      $item['picture'] = '<img src="'.WEB_URL.DATA_FOLDER.'document/'.$item['picture'].'" width=22 height=22 alt=thumbnail>';
    } else {
      $item['picture'] = '';
    }
    $item['create_date'] = Date::format($item['create_date'], 'd M Y H:i');
    $item['category_id'] = empty($item['category_id']) || empty($this->categories[$item['category_id']]) ? '{LNG_Uncategorized}' : $this->categories[$item['category_id']];
    $item['last_update'] = Date::format($item['last_update'], 'd M Y H:i');
    $item['published'] = '<span class="icon-published'.$item['published'].'"></span>';
    return $item;
  }
}