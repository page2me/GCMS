<?php
/*
 * @filesource board/models/admin/settings.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Board\Admin\Settings;

use \Kotchasan\Login;
use \Kotchasan\Language;
use \Gcms\Gcms;
use \Kotchasan\File;

/**
 *  บันทึกการตั้งค่า
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
      'icon_width' => 600,
      'icon_height' => 400,
      'img_typies' => array('jpg', 'jpeg'),
      'default_icon' => 'modules/board/img/default_icon.png',
      'list_per_page' => 20,
      'new_date' => 604800,
      'viewing' => 0,
      'category_display' => 1,
      'news_count' => 10,
      'img_upload_type' => array('jpg'),
      'img_upload_size' => 1024,
      'img_law' => 0,
      'can_post' => array(1),
      'can_reply' => array(1),
      'can_view' => array(1),
      'moderator' => array(1),
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
        $save = array(
          'icon_width' => max(75, self::$request->post('icon_width')->toInt()),
          'icon_height' => max(75, self::$request->post('icon_height')->toInt()),
          'img_typies' => self::$request->post('img_typies', array())->toString(),
          'list_per_page' => self::$request->post('list_per_page')->toInt(),
          'new_date' => self::$request->post('new_date')->toInt(),
          'viewing' => self::$request->post('viewing')->toInt(),
          'category_display' => self::$request->post('category_display')->toBoolean(),
          'news_count' => self::$request->post('news_count')->toInt(),
          'img_upload_type' => self::$request->post('img_upload_type', array())->toString(),
          'img_upload_size' => self::$request->post('img_upload_size', array())->toInt(),
          'img_law' => self::$request->post('img_law')->toBoolean(),
          'can_post' => self::$request->post('can_post', array())->toInt(),
          'can_reply' => self::$request->post('can_reply', array())->toInt(),
          'can_view' => self::$request->post('can_view', array())->toInt(),
          'moderator' => self::$request->post('moderator', array())->toInt(),
          'can_config' => self::$request->post('can_config', array())->toInt(),
        );
        // โมดูลที่เรียก
        $index = \Index\Adminmodule\Model::get('board', self::$request->post('id')->toInt());
        if ($index && Gcms::canConfig($login, $index, 'can_config')) {
          if (empty($save['img_typies'])) {
            // คืนค่า input ที่ error
            $ret['input'] = 'img_typies_jpg';
          } else {
            $input = false;
            $save['default_icon'] = $index->default_icon;
            // อัปโหลดไฟล์
            foreach (self::$request->getUploadedFiles() as $item => $file) {
              if ($file->hasUploadFile()) {
                if (!File::makeDirectory(ROOT_PATH.DATA_FOLDER.'board/')) {
                  // ไดเรคทอรี่ไม่สามารถสร้างได้
                  $ret['ret_'.$item] = sprintf(Language::get('Directory %s cannot be created or is read-only.'), DATA_FOLDER.'board/');
                  $input = !$input ? $item : $input;
                } elseif (!$file->validFileExt($save['img_typies'])) {
                  // รูปภาพเท่านั้น
                  $ret['ret_'.$item] = Language::get('The type of file is invalid');
                  $input = !$input ? $item : $input;
                } else {
                  // อัปโหลด
                  $save['default_icon'] = DATA_FOLDER.'board/default-'.$index->module_id.'.'.$file->getClientFileExt();
                  try {
                    $file->moveTo(ROOT_PATH.$save['default_icon']);
                  } catch (\Exception $exc) {
                    // ไม่สามารถอัปโหลดได้
                    $ret['ret_'.$item] = Language::get($exc->getMessage());
                    $input = !$input ? $item : $input;
                  }
                }
              }
            }
            if (!$input) {
              $save['new_date'] = $save['new_date'] * 86400;
              $save['can_post'][] = 1;
              $save['can_reply'][] = 1;
              $save['can_view'][] = 1;
              $save['moderator'][] = 1;
              $save['can_config'][] = 1;
              $this->db()->createQuery()->update('modules')->set(array('config' => serialize($save)))->where($index->module_id)->execute();
              // คืนค่า
              $ret['alert'] = Language::get('Saved successfully');
              $ret['location'] = self::$request->getUri()->postBack('index.php', array('module' => 'board-settings', 'mid' => $index->module_id));
            } else {
              // คืนค่า input ที่ error
              $ret['input'] = $input;
            }
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
