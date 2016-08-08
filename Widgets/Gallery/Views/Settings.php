<?php
/*
 * @filesource Widgets/Gallery/Views/Settings.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Widgets\Gallery\Views;

use \Kotchasan\Html;

/**
 * โมดูลสำหรับจัดการการตั้งค่าเริ่มต้น
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Settings extends \Kotchasan\View
{

  /**
   * module=Gallery-Settings
   *
   * @return string
   */
  public function render()
  {
    if (empty(self::$cfg->rss_gallery)) {
      self::$cfg->rss_gallery = array(
        'rows' => 3,
        'cols' => 2,
        'url' => 'http://gallery.gcms.in.th/gallery.rss'
      );
    }
    // form
    $form = Html::create('form', array(
        'id' => 'setup_frm',
        'class' => 'setup_frm',
        'autocomplete' => 'off',
        'action' => 'index.php/Widgets/Gallery/Models/Settings/save',
        'onsubmit' => 'doFormSubmit',
        'ajax' => true
    ));
    $fieldset = $form->add('fieldset', array(
      'title' => '{LNG_Set up or configure other details}'
    ));
    $groups = $fieldset->add('groups-table', array(
      'comment' => '{LNG_Settings the number and display as row * column}'
    ));
    // cols
    $groups->add('number', array(
      'id' => 'cols',
      'labelClass' => 'g-input icon-width',
      'itemClass' => 'width',
      'label' => '{LNG_Cols}',
      'value' => self::$cfg->rss_gallery['cols']
    ));
    // rows
    $groups->add('number', array(
      'id' => 'rows',
      'labelClass' => 'g-input icon-height',
      'itemClass' => 'width',
      'label' => '{LNG_Rows}',
      'value' => self::$cfg->rss_gallery['rows']
    ));
    // url
    $fieldset->add('text', array(
      'id' => 'url',
      'labelClass' => 'g-input icon-world',
      'itemClass' => 'item',
      'label' => '{LNG_URL}',
      'comment' => '{LNG_URL of the RSS published galleries. If you have not installed the gallery. You can use a free RSS content from http://gallery.gcms.in.th/gallery.rss}',
      'value' => self::$cfg->rss_gallery['url']
    ));
    $fieldset = $form->add('fieldset', array(
      'class' => 'submit'
    ));
    // submit
    $fieldset->add('submit', array(
      'class' => 'button ok large',
      'value' => '{LNG_Save}'
    ));
    return $form->render();
  }
}