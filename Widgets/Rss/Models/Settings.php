<?php
/*
 * @filesource Widgets/Rss/Models/Settings.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Widgets\Rss\Models;

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

  /**
   * form submit
   */
  public function save(Request $request)
  {
    $ret = array();
    // referer, session, member
    if (self::$request->initSession() && self::$request->isReferer() && $login = Login::isAdmin()) {
      if ($login['email'] == 'demo') {
        $ret['alert'] = Language::get('Unable to complete the transaction');
      } else {
        $save = array(
          'url' => $request->post('rss_url')->url(),
          'topic' => $request->post('rss_topic')->topic(),
          'index' => $request->post('rss_index')->number(),
          'rows' => max(1, $request->post('rss_rows')->toInt()),
          'cols' => max(1, $request->post('rss_cols')->toInt()),
        );
        $id = $request->post('rss_id')->toInt();
        // โหลด config
        $config = Config::load(ROOT_PATH.'settings/config.php');
        if ($id > 0 && !isset($config->rss_tabs[$id])) {
          $ret['alert'] = Language::get('Unable to complete the transaction');
        } elseif ($save['url'] == '') {
          $ret['ret_rss_url'] = 'this';
        } elseif ($save['topic'] == '') {
          $ret['ret_rss_topic'] = 'this';
        } else {
          if (!isset($config->rss_tabs)) {
            $config->rss_tabs = array();
          }
          $n = 1;
          $cfg = array();
          foreach ($config->rss_tabs as $i => $v) {
            if ($i == $id) {
              $cfg[$n] = $save;
            } else {
              $cfg[$n] = $v;
            }
            $n++;
          }
          if ($id == 0) {
            $cfg[$n] = $save;
          }
          $config->rss_tabs = $cfg;
          // save config
          if (Config::save($config, ROOT_PATH.'settings/config.php')) {
            $ret['alert'] = Language::get('Saved successfully');
            $ret['location'] = 'index.php?module=Rss-settings';
          } else {
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