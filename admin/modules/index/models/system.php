<?php
/*
 * @filesource index/models/system.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\System;

use \Kotchasan\Http\Request;
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
   *
   * @param Request $request
   */
  public function clearCache(Request $request)
  {
    if ($request->initSession() && $request->isReferer() && Login::isAdmin()) {
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
   *
   * @param Request $request
   */
  public function save(Request $request)
  {
    $ret = array();
    // referer, session, member
    if ($request->initSession() && $request->isReferer() && $login = Login::isAdmin()) {
      if ($login['email'] == 'demo') {
        $ret['alert'] = Language::get('Unable to complete the transaction');
      } else {
        // โหลด config
        $config = Config::load(ROOT_PATH.'settings/config.php');
        foreach (array('web_title', 'web_description') as $key) {
          $value = $request->post($key)->quote();
          if (empty($value)) {
            $ret['ret_'.$key] = Language::get('Please fill in');
          } else {
            $config->$key = $value;
          }
        }
        foreach (array('user_icon_typies', 'login_fields') as $key) {
          $value = $request->post($key)->text();
          if (empty($value)) {
            $ret['ret_'.$key] = Language::get('Please select at least one item');
          } else {
            $config->$key = $value;
          }
        }
        $config->user_icon_h = max(16, $request->post('user_icon_h')->toInt());
        $config->user_icon_w = max(16, $request->post('user_icon_w')->toInt());
        $config->cache_expire = max(0, $request->post('cache_expire')->toInt());
        $config->module_url = $request->post('module_url')->toInt();
        $config->timezone = $request->post('timezone')->text();
        $config->demo_mode = $request->post('demo_mode')->toBoolean();
        $config->user_activate = $request->post('user_activate')->toBoolean();
        $config->member_only_ip = $request->post('member_only_ip')->toBoolean();
        $config->login_action = $request->post('login_action')->toInt();
        $config->member_invitation = $request->post('member_invitation')->toInt();
        $config->member_phone = $request->post('member_phone')->toInt();
        $config->member_idcard = $request->post('member_idcard')->toInt();
        $config->use_ajax = $request->post('use_ajax')->toBoolean();
        if (empty($ret)) {
          // save config
          if (Config::save($config, ROOT_PATH.'settings/config.php')) {
            $ret['alert'] = Language::get('Saved successfully');
            $ret['location'] = 'reload';
          } else {
            // ไม่สามารถบันทึก config ได้
            $ret['alert'] = sprintf(Language::get('File %s cannot be created or is read-only.'), 'settings/config.php');
          }
        }
      }
    } else {
      $ret['alert'] = Language::get('Unable to complete the transaction');
    }
    // คืนค่าเป็น JSON
    echo json_encode($ret);
  }
}