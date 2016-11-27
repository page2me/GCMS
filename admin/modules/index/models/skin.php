<?php
/*
 * @filesource index/models/skin.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Skin;

use \Kotchasan\Login;
use \Kotchasan\Language;
use \Kotchasan\File;
use \Kotchasan\Config;

/**
 * บันทึกการตั้งค่า template
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\KBase
{

  /**
   * save config
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
        $save = array(
          'delete_logo' => self::$request->post('delete_logo')->toBoolean(),
          'delete_bg_image' => self::$request->post('delete_bg_image')->toBoolean(),
          'bg_color' => self::$request->post('bg_color')->color()
        );
        // อัปโหลดไฟล์
        foreach (self::$request->getUploadedFiles() as $item => $file) {
          if ($save['delete_'.$item] == 1) {
            // ลบรูปภาพ
            if (isset($config->$item) && is_file(ROOT_PATH.DATA_FOLDER.'image/'.$config->$item)) {
              @unlink(ROOT_PATH.DATA_FOLDER.'image/'.$config->$item);
              unset($config->$item);
            }
          } elseif ($file->hasUploadFile()) {
            // ชนิดของไฟล์ที่ยอมรับ
            $typies = $item == 'logo' ? array('jpg', 'gif', 'png', 'swf') : array('jpg', 'gif', 'png');
            if (!$file->validFileExt($typies)) {
              // ชนิดของไฟล์ไม่รองรับ
              $ret['ret_'.$item] = Language::get('The type of file is invalid');
            } elseif (!File::makeDirectory(ROOT_PATH.DATA_FOLDER.'image/')) {
              // ไดเรคทอรี่ไม่สามารถสร้างได้
              $ret['ret_'.$item] = sprintf(Language::get('Directory %s cannot be created or is read-only.'), DATA_FOLDER.'image/');
            } else {
              try {
                $ext = $file->getClientFileExt();
                $file->moveTo(ROOT_PATH.DATA_FOLDER.'image/'.$item.'.'.$ext);
                $config->$item = $item.'.'.$ext;
              } catch (\Exception $exc) {
                // ไม่สามารถอัปโหลดได้
                $ret['ret_'.$item] = Language::get($exc->getMessage());
              }
            }
          }
        }
        // bg_color
        if (empty($save['bg_color']) || !preg_match('/^\#[0-9A-Fa-f]{4,6}$/', $save['bg_color'])) {
          unset($config->bg_color);
        } else {
          $config->bg_color = strtoupper($save['bg_color']);
        }
        if (empty($ret)) {
          // save config
          if (Config::save($config, ROOT_PATH.'settings/config.php')) {
            $ret['alert'] = Language::get('Saved successfully');
            $ret['location'] = 'reload';
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