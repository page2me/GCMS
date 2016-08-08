<?php
/*
 * @filesource index/models/other.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Other;

use \Kotchasan\Login;
use \Kotchasan\Language;
use \Kotchasan\Config;

/**
 * บันทึกการตั้งค่าอื่นๆ
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\KBase
{

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
        $config->member_reserv = array();
        foreach (explode("\n", self::$request->post('member_reserv')->text()) as $item) {
          $config->member_reserv[] = trim($item);
        }
        $config->wordrude = array();
        foreach (explode("\n", self::$request->post('wordrude')->text()) as $item) {
          $config->wordrude[] = trim($item);
        }
        $config->wordrude_replace = self::$request->post('wordrude_replace', 'xxx')->toString();
        $config->counter_digit = max(4, self::$request->post('counter_digit')->toInt());
        // save config
        if (Config::save($config, ROOT_PATH.'settings/config.php')) {
          $ret['alert'] = Language::get('Saved successfully');
          $ret['location'] = 'reload';
        } else {
          $ret['alert'] = sprintf(Language::get('File %s cannot be created or is read-only.'), 'settings/config.php');
        }
      }
    } else {
      $ret['alert'] = Language::get('Unable to complete the transaction');
    }
    // คืนค่าเป็น JSON
    echo json_encode($ret);
  }
}