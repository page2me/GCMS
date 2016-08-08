<?php
/*
 * @filesource index/models/system.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\System;

use \Kotchasan\Login;
use \Kotchasan\Language;
use \Kotchasan\Cache\FileCache as Cache;
use \Kotchasan\Config;

/**
 * บันทึกการตั้งค่าเว็บไซต์
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\KBase
{

  /**
   * เคลียร์แคช
   */
  public function clearCache()
  {
    if (self::$request->initSession() && self::$request->isReferer() && Login::isAdmin()) {
      $cahce = new Cache();
      if ($cahce->clear()) {
        $ret = array('alert' => Language::get('Cache cleared successfully'));
      } else {
        $ret = array('alert' => Language::get('Some files cannot be deleted'));
      }
      // คืนค่าเป็น JSON
      echo json_encode($ret);
    }
  }

  /**
   * form submit
   */
  public function save()
  {
    $ret = array();
    // referer, session, member
    if (self::$request->initSession() && self::$request->isReferer() && $login = Login::isAdmin()) {
      if ($login['email'] == 'demo') {
        $ret['alert'] = Language::get('Unable to complete the transaction');
      } else {
        // โหลด config
        $config = Config::load(ROOT_PATH.'settings/config.php');
        // ตรวจสอบค่าที่ส่งมา
        $input = false;
        foreach (array('web_title', 'web_description') as $key) {
          $value = self::$request->post($key)->quote();
          if (empty($value)) {
            $ret['ret_'.$key] = Language::get('Please fill in');
            $input = !$input ? $key : $input;
          } else {
            $ret['ret_'.$key] = '';
            $config->$key = $value;
          }
        }
        foreach (array('user_icon_typies', 'login_fields') as $key) {
          $value = self::$request->post($key)->text();
          if (empty($value)) {
            $ret['ret_'.$key] = Language::get('Please select at least one item');
            $input = !$input ? $key : $input;
          } else {
            $ret['ret_'.$key] = '';
            $config->$key = $value;
          }
        }
        $config->user_icon_h = max(16, self::$request->post('user_icon_h')->toInt());
        $config->user_icon_w = max(16, self::$request->post('user_icon_w')->toInt());
        $config->cache_expire = max(0, self::$request->post('cache_expire')->toInt());
        $config->module_url = self::$request->post('module_url')->toInt();
        $config->timezone = self::$request->post('timezone')->text();
        $config->demo_mode = self::$request->post('demo_mode')->toBoolean();
        $config->user_activate = self::$request->post('user_activate')->toBoolean();
        $config->member_only_ip = self::$request->post('member_only_ip')->toBoolean();
        $config->login_action = self::$request->post('login_action')->toInt();
        $config->member_invitation = self::$request->post('member_invitation')->toInt();
        $config->member_phone = self::$request->post('member_phone')->toInt();
        $config->member_idcard = self::$request->post('member_idcard')->toInt();
        $config->use_ajax = self::$request->post('use_ajax')->toBoolean();
        if (!$input) {
          // save config
          if (Config::save($config, ROOT_PATH.'settings/config.php')) {
            $ret['alert'] = Language::get('Saved successfully');
            $ret['location'] = 'reload';
          } else {
            $ret['alert'] = sprintf(Language::get('File %s cannot be created or is read-only.'), 'settings/config.php');
          }
        } else {
          // คืนค่า input ที่ error
          $ret['input'] = $input;
        }
      }
    } else {
      $ret['alert'] = Language::get('Unable to complete the transaction');
    }
    // คืนค่าเป็น JSON
    echo json_encode($ret);
  }
}