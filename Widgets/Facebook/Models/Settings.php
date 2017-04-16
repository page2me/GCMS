<?php
/**
 * @filesource Widgets/Facebook/Models/Settings.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Widgets\Facebook\Models;

use \Kotchasan\Http\Request;
use \Kotchasan\Login;
use \Kotchasan\Language;
use \Kotchasan\Config;

/**
 * บันทึกการตั้งค่าเว็บไซต์
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Settings extends \Kotchasan\KBase
{

  public static function defaultSettings()
  {
    return array(
      'height' => 214,
      'user' => 'gcmscms',
      'show_facepile' => 1,
      'small_header' => 0,
      'hide_cover' => 0
    );
  }

  /**
   * form submit
   */
  public function save(Request $request)
  {
    $ret = array();
    // referer, session, admin
    if (self::$request->initSession() && self::$request->isReferer() && $login = Login::isAdmin()) {
      if ($login['email'] == 'demo') {
        $ret['alert'] = Language::get('Unable to complete the transaction');
      } else {
        $save = array(
          'user' => $request->post('user')->username(),
          'height' => max(70, $request->post('height')->toInt()),
          'show_facepile' => $request->post('show_facepile')->toBoolean(),
          'small_header' => $request->post('small_header')->toBoolean(),
          'hide_cover' => $request->post('hide_cover')->toBoolean()
        );
        // โหลด config
        $config = Config::load(ROOT_PATH.'settings/config.php');
        $config->facebook_page = $save;
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