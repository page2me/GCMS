<?php
/*
 * @filesource Widgets/Map/Views/Settings.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Widgets\Map\Views;

use \Kotchasan\Language;
use \Kotchasan\Html;
use \Gcms\Gcms;

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
   * module=Map-Settings
   *
   * @return string
   */
  public function render()
  {
    // google map
    Gcms::$view->addJavascript('//maps.google.com/maps/api/js?sensor=false&amp;language='.Language::name());
    // default
    self::$cfg->map_height = isset(self::$cfg->map_height) ? self::$cfg->map_height : 400;
    self::$cfg->map_zoom = isset(self::$cfg->map_zoom) ? self::$cfg->map_zoom : 14;
    self::$cfg->map_latitude = isset(self::$cfg->map_latitude) ? self::$cfg->map_latitude : '14.132081110519639';
    self::$cfg->map_lantigude = isset(self::$cfg->map_lantigude) ? self::$cfg->map_lantigude : '99.69822406768799';
    self::$cfg->map_info_latigude = isset(self::$cfg->map_info_latigude) ? self::$cfg->map_info_latigude : '14.132081110519639';
    self::$cfg->map_info_lantigude = isset(self::$cfg->map_info_lantigude) ? self::$cfg->map_info_lantigude : '99.69822406768799';
    // form
    $form = Html::create('form', array(
        'id' => 'setup_frm',
        'class' => 'setup_frm',
        'autocomplete' => 'off',
        'action' => 'index.php/Widgets/Map/Models/Settings/save',
        'onsubmit' => 'doFormSubmit',
        'ajax' => true
    ));
    $fieldset = $form->add('fieldset', array(
      'title' => Language::get('Determine the size and position of the map')
    ));
    $groups = $fieldset->add('groups-table');
    // map_height
    $groups->add('number', array(
      'id' => 'map_height',
      'labelClass' => 'g-input icon-height',
      'label' => '{LNG_Size of} {LNG_Google Map} ({LNG_Height})',
      'itemClass' => 'width',
      'value' => self::$cfg->map_height
    ));
    // map_zoom
    $groups->add('text', array(
      'id' => 'map_zoom',
      'labelClass' => 'g-input icon-search',
      'label' => '{LNG_Zoom}',
      'itemClass' => 'width',
      'readonly' => true,
      'value' => self::$cfg->map_zoom
    ));
    $fieldset = $form->add('fieldset', array(
      'title' => '{LNG_Location of the map}'
    ));
    $groups = $fieldset->add('groups-table', array(
      'comment' => '{LNG_Click Find me button to configure the map to the current location of the computer, or click the Search button to find the approximate location you need.}'
    ));
    // map_latitude
    $groups->add('text', array(
      'id' => 'map_latitude',
      'labelClass' => 'g-input icon-location',
      'label' => '{LNG_Latitude}',
      'itemClass' => 'width',
      'pattern' => '[0-9\.]+',
      'value' => self::$cfg->map_latitude
    ));
    // map_lantigude
    $groups->add('text', array(
      'id' => 'map_lantigude',
      'labelClass' => 'g-input icon-location',
      'label' => '{LNG_Longitude}',
      'itemClass' => 'width',
      'pattern' => '[0-9\.]+',
      'value' => self::$cfg->map_lantigude
    ));
    $groups->add('button', array(
      'id' => 'find_me',
      'itemClass' => 'width bottom',
      'title' => '{LNG_Find me}',
      'class' => 'button go icon-gps'
    ));
    $groups->add('button', array(
      'id' => 'map_search',
      'itemClass' => 'width bottom',
      'title' => '{LNG_Search}',
      'class' => 'button go icon-search'
    ));
    $fieldset->add('div', array(
      'id' => 'map_canvas',
      'class' => 'item',
      'innerHTML' => 'Google Map',
      'style' => 'height:'.self::$cfg->map_height.'px'
    ));
    $fieldset = $form->add('fieldset', array(
      'title' => '{LNG_Settings the dialog box shown on the map}'
    ));
    // map_info
    $fieldset->add('textarea', array(
      'id' => 'map_info',
      'labelClass' => 'g-input icon-file',
      'label' => '{LNG_Info}',
      'itemClass' => 'item',
      'comment' => '{LNG_Text (HTML) to be displayed at the location of the shop or company}',
      'rows' => 5,
      'value' => isset(self::$cfg->map_info) ? self::$cfg->map_info : ''
    ));
    $groups = $fieldset->add('groups-table', array(
      'comment' => '{LNG_Location of the info}'
    ));
    // map_info_latigude
    $groups->add('text', array(
      'id' => 'map_info_latigude',
      'labelClass' => 'g-input icon-location',
      'label' => '{LNG_Latitude}',
      'itemClass' => 'width',
      'pattern' => '[0-9\.]+',
      'value' => self::$cfg->map_info_latigude
    ));
    // map_info_lantigude
    $groups->add('text', array(
      'id' => 'map_info_lantigude',
      'labelClass' => 'g-input icon-location',
      'label' => '{LNG_Longitude}',
      'itemClass' => 'width',
      'pattern' => '[0-9\.]+',
      'value' => self::$cfg->map_info_lantigude
    ));
    $fieldset = $form->add('fieldset', array(
      'class' => 'submit'
    ));
    // submit
    $fieldset->add('submit', array(
      'class' => 'button ok large',
      'value' => '{LNG_Save}'
    ));
    $form->script('inintMapDemo();');
    return $form->render();
  }
}