<?php
/*
 * @filesource index/models/languageedit.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Languageedit;

use \Kotchasan\Http\Request;
use \Kotchasan\Login;
use \Kotchasan\Language;

/**
 * บันทึกการเขียน/แก้ไข ภาษา
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Model
{

  /**
   * อ่านรายการ owner จากฐานข้อมูลภาษา
   *
   * @return array
   */
  public static function getOwners()
  {
    $model = new static;
    $query = $model->db()->createQuery()
      ->select('owner')
      ->from('language')
      ->groupBy('owner')
      ->toArray();
    $result = array();
    foreach ($query->execute() as $item) {
      $result[$item['owner']] = $item['owner'];
    }
    return $result;
  }

  /**
   * ยอมรับ tag บางตัว ในภาษา a em b strong ul ol li dd dt dl
   *
   * @param string $string
   * @return string
   */
  public function allowTags($string)
  {
    return preg_replace_callback('/(&lt;(\/?(a|em|b|strong|ul|ol|li|dd|dt|dl)).*?&gt;)/is', function($matches) {
      return html_entity_decode($matches[1]);
    }, $string);
  }

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
        // ค่าที่ส่งมา
        $save = array(
          'js' => $request->post('write_js')->toBoolean(),
          'type' => $request->post('write_type')->topic(),
          'owner' => $request->post('write_owner')->topic(),
          'key' => $this->allowTags($request->post('write_key')->topic())
        );
        // ภาษาที่ติดตั้ง
        $languages = \Gcms\Gcms::installedLanguage();
        $array = false;
        foreach ($request->post('datas')->topic() as $items) {
          foreach ($languages as $lng) {
            if ($items[$lng] != '') {
              $save[$lng][$items['key']] = $items[$lng];
              if (sizeof($save[$lng]) > 1) {
                $array = true;
              }
            }
          }
        }
        foreach ($languages as $lng) {
          if ($array) {
            $save[$lng] = $this->allowTags(serialize($save[$lng]));
          } elseif (isset($save[$lng])) {
            $save[$lng] = $this->allowTags(reset($save[$lng]));
          } else {
            $save[$lng] = '';
          }
        }
        if ($array) {
          $save['type'] = 'array';
        }
        if ($save['js']) {
          if (isset($save['en']) && $save['en'] == '') {
            $save['en'] = $save['key'];
          }
          $save['key'] = strtoupper(preg_replace(array('/[\s]+/', '/([^A-Z_]+)/i'), array('_', ''), $save['key']));
        }
        $id = $request->post('write_id')->toInt();
        // Model
        $model = new static;
        // ตาราง
        $table_language = $model->getTableName('language');
        // ตรวจสอบรายการที่แก้ไข
        if ($id > 0) {
          $language = $model->db()->first($table_language, $id);
        }
        if ($id > 0 && !$language) {
          $ret['alert'] = Language('Sorry, Item not found It&#39;s may be deleted');
        } elseif ($save['key'] == '') {
          $ret['ret_write_key'] = Language::get('Please fill in');
        } else {
          // ตรวจสอบข้อมูลซ้ำ
          $search = $model->db()->first($table_language, array(
            array('key', $save['key']),
            array('js', $save['js'])
          ));
          if ($search && ($id == 0 || $id != $search->id)) {
            $ret['ret_write_key'] = Language::replace('This :name already exist', array(':name' => Language::get('Key')));
          } else {
            // บันทึก
            if ($id == 0) {
              // ใหม่
              $id = $model->db()->insert($table_language, $save);
              // redirect
              $ret['location'] = $request->getUri()->postBack('index.php', array('module' => 'language', 'js' => $save['js'], 'sort' => 'id DESC')).'#datatable_'.$id;
            } else {
              // แก้ไข
              $model->db()->update($table_language, $id, $save);
              // redirect
              $ret['location'] = $request->getUri()->postBack('index.php', array('module' => 'language', 'js' => $save['js'])).'#datatable_'.$id;
            }
            // อัปเดทไฟล์ ภาษา
            $error = \Index\Language\Model::updateLanguageFile();
            if (empty($error)) {
              $ret['alert'] = Language::get('Saved successfully');
            } else {
              unset($ret['location']);
              $ret['alert'] = $error;
            }
          }
        }
      }
    }
    // คืนค่า json
    echo json_encode($ret);
  }
}