<?php
/*
 * @filesource Widgets/Twitter/Models/Settings.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Widgets\Twitter\Models;

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
      'id' => '348368123554062336',
      'user' => 'goragod',
      'height' => 200,
      'amount' => 2,
      'theme' => 'light',
      'border_color' => '',
      'link_color' => ''
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
          'id' => $request->post('twitter_id')->number(),
          'user' => $request->post('twitter_user')->username(),
          'height' => max(100, $request->post('twitter_height')->toInt()),
          'amount' => $request->post('twitter_amount')->toInt(),
          'theme' => $request->post('twitter_theme')->topic(),
          'link_color' => $request->post('twitter_link_color')->color(),
          'border_color' => $request->post('twitter_border_color')->color()
        );
        // โหลด config
        $config = Config::load(ROOT_PATH.'settings/config.php');
        $config->twitter = $save;
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