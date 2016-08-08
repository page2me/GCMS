<?php
/*
 * @filesource index/views/system.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\System;

use \Kotchasan\Html;
use \Kotchasan\Language;
use \Gcms\Gcms;

/**
 * ฟอร์ม system
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Kotchasan\View
{

  /**
   * module=system
   *
   * @param object $config
   * @return string
   */
  public function render($config)
  {
    // form
    $form = Html::create('form', array(
        'id' => 'setup_frm',
        'class' => 'setup_frm',
        'autocomplete' => 'off',
        'action' => 'index.php/index/model/system/save',
        'onsubmit' => 'doFormSubmit',
        'ajax' => true
    ));
    $fieldset = $form->add('fieldset', array(
      'title' => '{LNG_General}'
    ));
    // web_title
    $fieldset->add('text', array(
      'id' => 'web_title',
      'labelClass' => 'g-input icon-home',
      'itemClass' => 'item',
      'label' => '{LNG_Website title}',
      'comment' => '{LNG_Site Name (You can add tags to decorate)}',
      'maxlength' => 255,
      'value' => isset($config->web_title) ? $config->web_title : self::$cfg->web_title
    ));
    // web_description
    $fieldset->add('text', array(
      'id' => 'web_description',
      'labelClass' => 'g-input icon-home',
      'itemClass' => 'item',
      'label' => '{LNG_Description}',
      'comment' => '{LNG_Short description about your website}',
      'maxlength' => 255,
      'value' => isset($config->web_description) ? $config->web_description : self::$cfg->web_description
    ));
    // module_url
    $datas = array();
    foreach (Gcms::$urls as $k => $v) {
      $datas[$k] = WEB_URL.str_replace(array('{', '}'), '', $v);
    }
    $fieldset->add('select', array(
      'id' => 'module_url',
      'labelClass' => 'g-input icon-world',
      'itemClass' => 'item',
      'label' => '{LNG_Pretty URL}',
      'comment' => '{LNG_The pretty URL for servers that support mod_rewrite}',
      'options' => $datas,
      'value' => isset($config->module_url) ? $config->module_url : self::$cfg->module_url
    ));
    // use_ajax
    $fieldset->add('select', array(
      'id' => 'use_ajax',
      'labelClass' => 'g-input icon-config',
      'itemClass' => 'item',
      'label' => '{LNG_Use Ajax}',
      'comment' => '{LNG_Define sites using Ajax}',
      'options' => Language::get('USE_AJAX_LIST'),
      'value' => isset($config->use_ajax) ? $config->use_ajax : self::$cfg->use_ajax
    ));
    // timezone
    $datas = array();
    foreach (\DateTimeZone::listIdentifiers() as $item) {
      $datas[$item] = $item;
    }
    $fieldset->add('select', array(
      'id' => 'timezone',
      'labelClass' => 'g-input icon-clock',
      'itemClass' => 'item',
      'label' => '{LNG_Time zone}&nbsp;({LNG_Server time}&nbsp;<em id=server_time>'.date('H:i:s').'</em>&nbsp;{LNG_Local time}&nbsp;<em id=local_time></em>)',
      'comment' => '{LNG_Settings the timing of the server to match the local time (Hour)}',
      'options' => $datas,
      'value' => isset($config->timezone) ? $config->timezone : self::$cfg->timezone
    ));
    // demo_mode
    $fieldset->add('select', array(
      'id' => 'demo_mode',
      'labelClass' => 'g-input icon-config',
      'itemClass' => 'item',
      'label' => '{LNG_Example}',
      'comment' => '{LNG_Settings the demonstration account. When activated this demonstration account. The system will allow a demo account login. Some functions will be disabled. (Used as an demonstration for the site only.)}',
      'options' => Language::get('BOOLEANS'),
      'value' => isset($config->demo_mode) ? $config->demo_mode : self::$cfg->demo_mode
    ));
    // cache_expire
    $div = $fieldset->add('groups-table', array(
      'id' => 'cache_expire',
      'label' => '{LNG_Cache}',
      'comment' => '{LNG_The period of cached pages per second. The recommended value is 2 to 20 seconds. Settings high value, something changed will be slow.(0 means no cache)}',
    ));
    $div->add('number', array(
      'id' => 'cache_expire',
      'labelClass' => 'g-input icon-config',
      'itemClass' => 'width',
      'value' => isset($config->cache_expire) ? $config->cache_expire : self::$cfg->cache_expire
    ));
    $div->add('button', array(
      'id' => 'clear_cache',
      'itemClass' => 'width',
      'class' => 'button red',
      'value' => '{LNG_Clear Cache}'
    ));
    $fieldset = $form->add('fieldset', array(
      'title' => '{LNG_Users}'
    ));
    // user_icon_typies
    $fieldset->add('groups', array(
      'id' => 'user_icon_typies',
      'label' => '{LNG_Avatar}',
      'comment' => '{LNG_The types of files that can be uploaded as a avatar}',
      'checkbox' => array('jpg' => 'jpg', 'jpeg' => 'jpeg', 'gif' => 'gif', 'png' => 'png'),
      'value' => isset($config->user_icon_typies) ? $config->user_icon_typies : self::$cfg->user_icon_typies
    ));
    // user_icon_w, user_icon_h
    $div = $fieldset->add('groups', array(
      'comment' => '{LNG_The size of the avatar (pixels), automatic resizing}'
    ));
    $div->add('number', array(
      'id' => 'user_icon_w',
      'itemClass' => 'width50',
      'labelClass' => 'g-input icon-width',
      'label' => '{LNG_Width}',
      'value' => isset($config->user_icon_w) ? $config->user_icon_w : self::$cfg->user_icon_w
    ));
    $div->add('number', array(
      'id' => 'user_icon_h',
      'itemClass' => 'width50',
      'labelClass' => 'g-input icon-height',
      'label' => '{LNG_Height}',
      'value' => isset($config->user_icon_h) ? $config->user_icon_h : self::$cfg->user_icon_h
    ));
    // user_activate
    $fieldset->add('select', array(
      'id' => 'user_activate',
      'labelClass' => 'g-input icon-email',
      'itemClass' => 'item',
      'label' => '{LNG_Email confirmation}',
      'comment' => '{LNG_If you enable it, System will send a confirmation email to the email address registered.}',
      'options' => Language::get('BOOLEANS'),
      'value' => isset($config->user_activate) ? $config->user_activate : self::$cfg->user_activate
    ));
    $fieldset = $form->add('fieldset', array(
      'title' => '{LNG_Sign In}'
    ));
    $div = $fieldset->add('groups', array(
      'comment' => '{LNG_Define additional options for the register member}',
    ));
    $options = Language::get('BOOLEANS');
    $options[1] = '{LNG_Enabled but not required}';
    // member_invitation
    $div->add('select', array(
      'id' => 'member_invitation',
      'labelClass' => 'g-input icon-customer',
      'itemClass' => 'width33',
      'label' => '{LNG_Invitation code}',
      'options' => $options,
      'value' => isset($config->member_invitation) ? $config->member_invitation : self::$cfg->member_invitation
    ));
    $options[2] = '{LNG_Enabled and required}';
    // member_phone
    $div->add('select', array(
      'id' => 'member_phone',
      'labelClass' => 'g-input icon-phone',
      'itemClass' => 'width33',
      'label' => '{LNG_Phone}',
      'options' => $options,
      'value' => isset($config->member_phone) ? $config->member_phone : self::$cfg->member_phone
    ));
    // member_idcard
    $div->add('select', array(
      'id' => 'member_idcard',
      'labelClass' => 'g-input icon-profile',
      'itemClass' => 'width33',
      'label' => '{LNG_Identification number}',
      'options' => $options,
      'value' => isset($config->member_idcard) ? $config->member_idcard : self::$cfg->member_idcard
    ));
    // login_fields
    $fieldset->add('groups', array(
      'id' => 'login_fields',
      'label' => '{LNG_Login by}',
      'comment' => '{LNG_Settings the conditions for member login}',
      'checkbox' => Language::get('LOGIN_FIELDS'),
      'value' => isset($config->login_fields) ? $config->login_fields : self::$cfg->login_fields
    ));
    // member_only_ip
    $fieldset->add('select', array(
      'id' => 'member_only_ip',
      'labelClass' => 'g-input icon-ip',
      'itemClass' => 'item',
      'label' => '{LNG_Login per IP}',
      'comment' => '{LNG_Limit one member per IP address}',
      'options' => Language::get('BOOLEANS'),
      'value' => isset($config->member_only_ip) ? $config->member_only_ip : self::$cfg->member_only_ip
    ));
    // login_action
    $fieldset->add('select', array(
      'id' => 'login_action',
      'labelClass' => 'g-input icon-signin',
      'itemClass' => 'item',
      'label' => '{LNG_Login}',
      'comment' => '{LNG_Determine how to log in successfully}',
      'options' => Language::get('LOGIN_ACTIONS'),
      'value' => isset($config->login_action) ? $config->login_action : self::$cfg->login_action
    ));
    $fieldset = $form->add('fieldset', array(
      'class' => 'submit'
    ));
    // submit
    $fieldset->add('submit', array(
      'class' => 'button ok large',
      'value' => '{LNG_Save}'
    ));
    $form->script('initSystem();');
    return $form->render();
  }
}