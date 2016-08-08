<?php
/*
 * @filesource index/models/languageedit.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Languageedit;

use \Kotchasan\Login;
use \Kotchasan\Language;

/**
 * บันทึกการเขียน/แก้ไข ภาษา
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
        // ชนิดของภาษาที่เลือก php,js
        $type = self::$request->post('write_type')->toString();
        $type = $type == 'js' ? 'js' : 'php';
        // โหลดไฟล์ภาษา ที่ติดตั้ง
        $languages = Language::installed($type);
        // -1 ใหม่ มากกว่า -1แก้ไข
        $write_id = self::$request->post('write_id', -1)->toInt();
        $id = $write_id >= 0 ? $write_id : sizeof($languages);
        // ข้อมูลที่ POST มา
        $key = self::$request->post('write_topic')->quote();
        // ตรวจสอบข้อมูลซ้ำ
        $search = Language::keyExists($languages, $key);
        if ($search == -1 || $search == $id) {
          $save = array();
          $languages[$id] = array('key' => $key);
          foreach (self::$request->post('save_array')->toString() AS $key => $value) {
            foreach (Language::installedLanguage() as $lng) {
              $v = self::$request->post('language_'.$lng)->get($key)->quote();
              if ($type == 'php') {
                if ($v != '' && $v != $languages[$id]['key']) {
                  $languages[$id][$lng]["$value"] = $v;
                }
              } elseif ($type == 'js') {
                if ($v == '' || $v == $languages[$id]['key']) {
                  if ($v == '') {
                    $v = $languages[$id]['key'];
                  }
                  $languages[$id]['key'] = strtoupper(trim(preg_replace(array('/[\s_\!]{1,}/', '/[\?\[\]<>\{\}%]/', '/_$/'), array('_', '', ''), $languages[$id]['key'])));
                }
                $languages[$id][$lng][''] = $v;
              }
            }
          }
          foreach ($languages[$id] as $lng => $value) {
            if ($lng != 'key' && sizeof($value) == 1) {
              $keys = array_keys($value);
              if (reset($keys) === '') {
                $languages[$id][$lng] = $value[$keys[0]];
              }
            }
          }
          // บันทึกเป็นไฟล์
          $result = Language::save($languages, $type);
          // คืนค่า
          if (empty($result)) {
            $ret['alert'] = Language::get('Saved successfully');
            if ($write_id >= 0) {
              $ret['location'] = self::$request->getUri()->postBack('index.php', array('module' => 'language', 'type' => $type)).'#datatable_'.$id;
            } else {
              $ret['location'] = self::$request->getUri()->postBack('index.php', array('module' => 'language', 'type' => $type, 'sort' => 'id', 'sort_type' => 'desc')).'#datatable_'.$id;
            }
          } else {
            $ret['alert'] = $result;
          }
        } else {
          $ret['alert'] = Language::get('This message already exist');
        }
      }
    } else {
      $ret['alert'] = Language::get('Unable to complete the transaction');
    }
    // คืนค่า json
    echo json_encode($ret);
  }
}