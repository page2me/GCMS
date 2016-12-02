<?php
/*
 * @filesource index/models/meta.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Meta;

use \Kotchasan\Http\Request;
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
   *
   * @param Request $request
   */
  public function save(Request $request)
  {
    $ret = array();
    // referer, session, member
    if ($request->initSession() && $request->isReferer() && $login = Login::isAdmin()) {
      if ($login['email'] == 'demo') {
        $ret['alert'] = Language::get('Unable to complete the transaction');
      } else {
        // โหลด config
        $config = Config::load(ROOT_PATH.'settings/config.php');
        // อัปโหลดไฟล์
        foreach ($request->getUploadedFiles() as $item => $file) {
          if ($request->post('delete_'.$item)->toBoolean() == 1) {
            // ลบรูปภาพ
            if (is_file(ROOT_PATH.DATA_FOLDER.'image/'.$item.'.jpg')) {
              @unlink(ROOT_PATH.DATA_FOLDER.'image/'.$item.'.jpg');
            }
          } elseif (!File::makeDirectory(ROOT_PATH.DATA_FOLDER.'image/')) {
            // ไดเรคทอรี่ไม่สามารถสร้างได้
            $ret['ret_'.$item] = sprintf(Language::get('Directory %s cannot be created or is read-only.'), DATA_FOLDER.'image/');
          } elseif ($file->hasUploadFile()) {
            // ตรวจสอบไฟล์อัปโหลด
            if (!$file->validFileExt(array('jpg', 'jpeg'))) {
              $ret['ret_'.$item] = Language::get('The type of file is invalid');
            } else {
              try {
                $file->moveTo(ROOT_PATH.DATA_FOLDER.'image/'.$item.'.jpg');
              } catch (\Exception $exc) {
                // ไม่สามารถอัปโหลดได้
                $ret['ret_'.$item] = Language::get($exc->getMessage());
              }
            }
          }
        }
        // other
        foreach (array('google_site_verification', 'google_profile', 'msvalidate', 'facebook_appId') as $item) {
          $value = $request->post($item)->text();
          if (empty($value)) {
            unset($config->$item);
          } else {
            $config->$item = $value;
          }
        }
        $config->amp = $request->post('amp')->toBoolean();
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