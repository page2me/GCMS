<?php
/*
 * @filesource download/models/admin/settings.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Download\Admin\Settings;

use \Kotchasan\Login;
use \Kotchasan\Language;
use \Gcms\Gcms;

/**
 *  การตั้งค่า
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Model
{

  /**
   * ค่าติดตั้งเรื่มต้น
   *
   * @return array
   */
  public static function defaultSettings()
  {
    return array(
      'file_typies' => array('doc', 'ppt', 'pptx', 'docx', 'rar', 'zip', 'jpg', 'pdf'),
      'upload_size' => 2097152,
      'list_per_page' => 20,
      'sort' => 1,
      'can_download' => array(-1, 0, 1),
      'can_upload' => array(1),
      'can_config' => array(1)
    );
  }

  /**
   * บันทึกข้อมูล config ของโมดูล
   */
  public function save()
  {
    $ret = array();
    // referer, session, member
    if (self::$request->initSession() && self::$request->isReferer() && $login = Login::isMember()) {
      if ($login['email'] == 'demo') {
        $ret['alert'] = Language::get('Unable to complete the transaction');
      } else {
        // รับค่าจากการ POST
        $typies = array();
        foreach (explode(',', strtolower(self::$request->post('file_typies')->filter('a-zA-Z0-9,'))) as $typ) {
          if ($typ != '') {
            $typies[$typ] = $typ;
          }
        }
        $save = array(
          'file_typies' => array_keys($typies),
          'upload_size' => self::$request->post('upload_size')->toInt(),
          'list_per_page' => self::$request->post('list_per_page')->toInt(),
          'sort' => self::$request->post('sort')->toInt(),
          'can_download' => self::$request->post('can_download', array())->toInt(),
          'can_upload' => self::$request->post('can_upload', array())->toInt(),
          'can_config' => self::$request->post('can_config', array())->toInt(),
        );
        // โมดูลที่เรียก
        $index = \Download\Admin\Index\Model::module(self::$request->post('id')->toInt());
        if ($index && Gcms::canConfig($login, $index, 'can_config')) {
          if (empty($save['file_typies'])) {
            // คืนค่า input ที่ error
            $ret['input'] = 'file_typies';
            $ret['ret_file_typies'] = 'this';
          } else {
            $save['can_upload'][] = 1;
            $save['can_config'][] = 1;
            $this->db()->createQuery()->update('modules')->set(array('config' => serialize($save)))->where($index->module_id)->execute();
            // คืนค่า
            $ret['alert'] = Language::get('Saved successfully');
            $ret['location'] = self::$request->getUri()->postBack('index.php', array('module' => 'download-settings', 'mid' => $index->module_id));
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