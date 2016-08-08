<?php
/*
 * @filesource Widgets/Map/Controllers/Settings.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Widgets\Map\Models;

use \Kotchasan\Login;
use \Kotchasan\Config;
use \Kotchasan\Language;

/**
 * บันทึกการตั้งค่า
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Settings extends \Kotchasan\KBase
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
        // ค่าที่ส่งมา
        $config->map_height = max(100, self::$request->post('map_height')->toInt());
        $config->map_zoom = max(1, self::$request->post('map_zoom')->toInt());
        $config->map_latitude = self::$request->post('map_latitude')->topic();
        $config->map_lantigude = self::$request->post('map_lantigude')->topic();
        $config->map_info = self::$request->post('map_info')->textarea();
        $config->map_info_latigude = self::$request->post('map_info_latigude')->topic();
        $config->map_info_lantigude = self::$request->post('map_info_lantigude')->topic();
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