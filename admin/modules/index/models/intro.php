<?php
/*
 * @filesource index/models/intro.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Intro;

use \Kotchasan\Login;
use \Kotchasan\Language;
use \Kotchasan\Config;

/**
 * บันทึก intro
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\KBase
{

  /**
   * บันทึก
   */
  public function save()
  {
    $ret = array();
    // referer, session, member
    if (self::$request->initSession() && self::$request->isReferer() && $login = Login::isAdmin()) {
      if ($login['email'] == 'demo') {
        $ret['alert'] = Language::get('Unable to complete the transaction');
      } else {
        // รับค่าจากการ POST
        $save = array(
          'show_intro' => self::$request->post('show_intro')->toBoolean(),
          'language' => self::$request->post('language')->toString(),
          'detail' => self::$request->post('detail')->detail()
        );
        if (!empty($save['language']) && preg_match('/^[a-z]{2,2}$/', $save['language'])) {
          // save
          $template = ROOT_PATH.DATA_FOLDER.'intro.'.$save['language'].'.php';
          $f = @fopen($template, 'wb');
          if ($f) {
            fwrite($f, "<?php exit;?>\n".$save['detail']);
            fclose($f);
            // โหลด config
            $config = Config::load(ROOT_PATH.'settings/config.php');
            $config->show_intro = $save['show_intro'];
            // save config
            if (Config::save($config, ROOT_PATH.'settings/config.php')) {
              $ret['alert'] = Language::get('Saved successfully');
              $ret['location'] = 'reload';
            } else {
              $ret['alert'] = sprintf(Language::get('File %s cannot be created or is read-only.'), 'settings/config.php');
            }
          } else {
            $ret['alert'] = sprintf(Language::get('File %s cannot be created or is read-only.'), DATA_FOLDER.'intro.'.$save['language'].'.php');
          }
        } else {
          $ret['alert'] = Language::get('Unable to complete the transaction');
        }
      }
    } else {
      $ret['alert'] = Language::get('Unable to complete the transaction');
    }
    // คืนค่าเป็น JSON
    echo json_encode($ret);
  }
}