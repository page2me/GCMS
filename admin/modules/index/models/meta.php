<?php
/*
 * @filesource index/models/meta.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Meta;

use \Kotchasan\Login;
use \Kotchasan\Language;
use \Kotchasan\Config;
use \Kotchasan\File;

/**
 * บันทึกการตั้งค่า SEO & Social
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
        // ตรวจสอบค่าที่ส่งมา
        $input = false;
        // อัปโหลดไฟล์
        foreach (self::$request->getUploadedFiles() as $item => $file) {
          if (self::$request->post('delete_'.$item)->toBoolean() == 1) {
            // ลบรูปภาพ
            if (is_file(ROOT_PATH.DATA_FOLDER.'image/facebook_photo.jpg')) {
              @unlink(ROOT_PATH.DATA_FOLDER.'image/facebook_photo.jpg');
            }
          } elseif (!File::makeDirectory(ROOT_PATH.DATA_FOLDER.'image/')) {
            // ไดเรคทอรี่ไม่สามารถสร้างได้
            $ret['ret_'.$item] = sprintf(Language::get('Directory %s cannot be created or is read-only.'), DATA_FOLDER.'image/');
            $input = !$input ? $item : $input;
          } elseif ($file->hasUploadFile()) {
            // ตรวจสอบไฟล์อัปโหลด
            if (!$file->validFileExt(array('jpg'))) {
              $ret['ret_'.$item] = Language::get('The type of file is invalid');
              $input = !$input ? $item : $input;
            } else {
              try {
                $file->moveTo(ROOT_PATH.DATA_FOLDER.'image/'.$item.'.jpg');
              } catch (\Exception $exc) {
                // ไม่สามารถอัปโหลดได้
                $ret['ret_'.$item] = Language::get($exc->getMessage());
                $input = !$input ? $item : $input;
              }
            }
          }
        }
        // other
        foreach (array('google_site_verification', 'google_profile', 'msvalidate', 'facebook_appId') as $item) {
          $value = self::$request->post($item)->text();
          if (empty($value)) {
            unset($config->$item);
          } else {
            $config->$item = $value;
          }
        }
        if (!$input) {
          // save config
          if (Config::save($config, ROOT_PATH.'settings/config.php')) {
            $ret['alert'] = Language::get('Saved successfully');
            $ret['location'] = 'reload';
          } else {
            $ret['alert'] = sprintf(Language::get('File %s cannot be created or is read-only.'), 'settings/config.php');
          }
        } else {
          // คืนค่า input ที่ error
          $ret['input'] = $input;
        }
      }
    } else {
      $ret['alert'] = Language::get('Unable to complete the transaction');
    }
    // คืนค่าเป็น JSON
    echo json_encode($ret);
  }
}