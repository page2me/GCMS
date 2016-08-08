<?php
/*
 * @filesource index/models/languages.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Languages;

use \Kotchasan\Login;
use \Kotchasan\Language;
use \Kotchasan\Config;

/**
 * บันทึกรายการภาษาที่ติดตั้งแล้ว
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\KBase
{

  /**
   * บันทึกจาก ajax
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
        // รับค่าจากการ POST
        $post = self::$request->getParsedBody();
        // do not saved
        $save = false;
        $reload = false;
        if ($post['action'] === 'changed' || $post['action'] === 'move') {
          if ($post['action'] === 'changed') {
            // เปลี่ยนแปลงสถานะการเผยแพร่ภาษา
            $config->languages = explode(',', str_replace('check_', '', $post['data']));
          } else {
            // จัดลำดับภาษา
            $languages = $config->languages;
            $config->languages = array();
            foreach (explode(',', str_replace('L_', '', $post['data'])) as $lng) {
              if (in_array($lng, $languages)) {
                $config->languages[] = $lng;
              }
            }
          }
          $save = true;
        } elseif ($post['action'] === 'droplang' && preg_match('/^([a-z]{2,2})$/', $post['data'], $match)) {
          // ลบภาษา
          @unlink(ROOT_PATH.'language/'.$match[1].'.php');
          @unlink(ROOT_PATH.'language/'.$match[1].'.js');
          @unlink(ROOT_PATH.'language/'.$match[1].'.gif');
          $languages = array();
          foreach ($config->languages as $item) {
            if ($match[1] !== $item) {
              $languages[] = $item;
            }
          }
          $config->languages = $languages;
          $save = true;
          $reload = true;
        }
        if ($save) {
          // save config
          if (Config::save($config, ROOT_PATH.'settings/config.php')) {
            $ret['alert'] = Language::get('Saved successfully');
            if ($reload) {
              $ret['location'] = 'reload';
            }
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