<?php
/**
 * @filesource Widgets/Rss/ModelsAction.php
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
 * Rss Action
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Action extends \Kotchasan\Model
{

  public function get(Request $request)
  {
    if ($request->initSession() && $request->isReferer() && Login::isAdmin()) {
      // ค่าที่ส่งมา
      $action = $request->post('action')->toString();
      $id = $request->post('id')->filter('0-9,');
      $value = $request->post('val')->toString();
      // โหลด config
      $config = Config::load(ROOT_PATH.'settings/config.php');
      $save = false;
      if ($action == 'delete') {
        // ลบ
        $cfg = array();
        $id = explode(',', $id);
        foreach ($config->rss_tabs as $i => $v) {
          if (!in_array($i, $id)) {
            $cfg[$i] = $v;
          }
        }
        if (empty($cfg)) {
          unset($config->rss_tabs);
        } else {
          $config->rss_tabs = $cfg;
        }
        $save = true;
      } elseif ($action == 'move') {
        // sort
        $cfg = $config->rss_tabs;
        $config->rss_tabs = array();
        $n = 1;
        foreach (explode(',', $request->post('data')->filter('0-9,')) as $i) {
          if (isset($cfg[$i])) {
            $config->rss_tabs[$n] = $cfg[$i];
            $n++;
          }
        }
        $save = true;
      }
      if ($save) {
        // save config
        if (!Config::save($config, ROOT_PATH.'settings/config.php')) {
          $ret['alert'] = sprintf(Language::get('File %s cannot be created or is read-only.'), 'settings/config.php');
        } else if ($action == 'delete') {
          // reload
          $ret['location'] = 'index.php?module=Rss-settings';
        }
      }
      // คืนค่าเป็น JSON
      if (!empty($ret)) {
        echo json_encode($ret);
      }
    }
  }
}