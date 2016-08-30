<?php
/*
 * @filesource document/views/admin/settings.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Document\Admin\Settings;

use \Kotchasan\Html;
use \Kotchasan\Language;
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
   * module=document-settings
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
        'action' => 'index.php/document/model/admin/settings/save',
        'onsubmit' => 'doFormSubmit',
        'ajax' => true
    ));
    $fieldset = $form->add('fieldset', array(
      'title' => '{LNG_Thumbnail}'
    ));
    $groups = $fieldset->add('groups', array(
      'label' => '{LNG_Size of the icons}',
      'comment' => '{LNG_Size of the image thumbnail at pixels}'
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
    // default_icon
    $fieldset->add('file', array(
      'id' => 'default_icon',
      'labelClass' => 'g-input icon-upload',
      'itemClass' => 'item',
      'label' => '{LNG_Browse file}',
      'comment' => '{LNG_Upload icons (default) as defined above. Can be used as thumbnail if no thumbnail of story. (Resized automatically, if you want to use animated images or images transparent Please be prepared to fit the image size set.)}',
      'dataPreview' => 'iconImage',
      'previewSrc' => WEB_URL.$index->default_icon
    ));
    $fieldset = $form->add('fieldset', array(
      'title' => '{LNG_Default Settings}'
    ));
    // published
    $fieldset->add('select', array(
      'id' => 'published',
      'labelClass' => 'g-input icon-published1',
      'itemClass' => 'item',
      'label' => '{LNG_Published}',
      'comment' => '{LNG_If you choose to unpublish contributions will not be displayed on the page immediately. The Admin can review and published it later.}',
      'options' => Language::get('PUBLISHEDS'),
      'value' => $index->published
    ));
    $fieldset = $form->add('fieldset', array(
      'title' => '{LNG_Display}'
    ));
    // list_per_page
    $fieldset->add('number', array(
      'id' => 'list_per_page',
      'labelClass' => 'g-input icon-published1',
      'itemClass' => 'item',
      'label' => '{LNG_Amount}',
      'comment' => '{LNG_Set the number of entries displayed per page}',
      'value' => $index->list_per_page
    ));
    // sort
    $sorts = array('{LNG_Last updated}', '{LNG_Article Date}', '{LNG_Published date}', 'ID');
    $fieldset->add('select', array(
      'id' => 'sort',
      'labelClass' => 'g-input icon-sort',
      'itemClass' => 'item',
      'label' => '{LNG_Sort}',
      'comment' => '{LNG_Determine how to sort the items displayed in the list}',
      'options' => $sorts,
      'value' => $index->sort
    ));
    // new_date
    $options = array();
    for ($i = 0; $i < 31; $i++) {
      $options[$i] = $i.' {LNG_days}';
    }
    $fieldset->add('select', array(
      'id' => 'new_date',
      'labelClass' => 'g-input icon-clock',
      'itemClass' => 'item',
      'label' => '{LNG_New mark}',
      'comment' => '{LNG_Setting the number of days an item will show up as New (0 means not shown)}',
      'options' => $options,
      'value' => $index->new_date / 86400
    ));
    // viewing
    $fieldset->add('select', array(
      'id' => 'viewing',
      'labelClass' => 'g-input icon-published1',
      'itemClass' => 'item',
      'label' => '{LNG_Viewing}',
      'comment' => '{LNG_Determine how to view the content for the page is reserved for members only}',
      'options' => Language::get('MEMBER_ONLY_LIST'),
      'value' => $index->viewing
    ));
    // category_display
    $fieldset->add('select', array(
      'id' => 'category_display',
      'labelClass' => 'g-input icon-category',
      'itemClass' => 'item',
      'label' => '{LNG_Display Category}',
      'comment' => '{LNG_Set the Display category list. If you choose to disable. System will jump to display the list of articles.}',
      'options' => Language::get('BOOLEANS'),
      'value' => $index->category_display
    ));
    $fieldset = $form->add('fieldset', array(
      'title' => '{LNG_Display in the widget}'
    ));
    // news_count
    $fieldset->add('number', array(
      'id' => 'news_count',
      'labelClass' => 'g-input icon-published1',
      'itemClass' => 'item',
      'label' => '{LNG_Amount}',
      'comment' => '{LNG_Set the number of entries displayed (0 means not shown)}',
      'value' => $index->news_count
    ));
    // news_sort
    $fieldset->add('select', array(
      'id' => 'news_sort',
      'labelClass' => 'g-input icon-sort',
      'itemClass' => 'item',
      'label' => '{LNG_Sort}',
      'comment' => '{LNG_Determine how to sort the items displayed in the list}',
      'options' => $sorts,
      'value' => $index->news_sort
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
      array('text' => '{LNG_Comment}'),
      array('text' => '{LNG_Viewing}'),
      array('text' => '{LNG_Writing}'),
      array('text' => '{LNG_Moderator}'),
      array('text' => '{LNG_Settings}')
    ));
    foreach (\Kotchasan\ArrayTool::merge(array(-1 => '{LNG_Guest}'), self::$cfg->member_status) as $i => $item) {
      $row = array();
      $row[] = array(
        'scope' => 'col',
        'text' => $item
      );
      $check = in_array($i, $index->can_reply) ? ' checked' : '';
      $row[] = array(
        'class' => 'center',
        'text' => '<label data-text="{LNG_Comment}"><input type=checkbox name=can_reply[] title="{LNG_Members of this group can post comment}" value='.$i.$check.'></label>'
      );
      $check = isset($index->can_view) && is_array($index->can_view) && in_array($i, $index->can_view) ? ' checked' : '';
      $row[] = array(
        'class' => 'center',
        'text' => $i == 1 ? '' : '<label data-text="{LNG_Viewing}"><input type=checkbox name=can_view[] title="{LNG_Members of this group can see the content}" value='.$i.$check.'></label>'
      );
      $check = isset($index->can_write) && is_array($index->can_write) && in_array($i, $index->can_write) ? ' checked' : '';
      $row[] = array(
        'class' => 'center',
        'text' => $i > 1 ? '<label data-text="{LNG_Writing}"><input type=checkbox name=can_write[] title="{LNG_Members of this group can create the content}" value='.$i.$check.'></label>' : ''
      );
      $check = isset($index->moderator) && is_array($index->moderator) && in_array($i, $index->moderator) ? ' checked' : '';
      $row[] = array(
        'class' => 'center',
        'text' => $i > 1 ? '<label data-text="{LNG_Moderator}"><input type=checkbox name=moderator[] title="{LNG_Members of this group can edit, delete items created by others}" value='.$i.$check.'></label>' : ''
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