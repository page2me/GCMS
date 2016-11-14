<?php
/*
 * @filesource document/models/admin/settings.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Document\Admin\Settings;

use \Kotchasan\Http\Request;
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
   * ค่าติดตั้งเริ่มต้น
   *
   * @return array
   */
  public static function defaultSettings()
  {
    return array(
      'icon_width' => 600,
      'icon_height' => 400,
      'img_typies' => array('jpg', 'jpeg'),
      'default_icon' => 'modules/document/img/default_icon.png',
      'published' => 1,
      'rows' => 20,
      'cols' => 1,
      'sort' => 1,
      'new_date' => 604800,
      'viewing' => 0,
      'category_display' => 1,
      'news_count' => 10,
      'news_sort' => 1,
      'can_reply' => array(1),
      'can_view' => array(1),
      'can_write' => array(1),
      'moderator' => array(1),
      'can_config' => array(1)
    );
  }

  /**
   * บันทึกข้อมูล config ของโมดูล
   *
   * @param Request $request
   */
  public function save(Request $request)
  {
    $ret = array();
    // referer, session, member
    if ($request->initSession() && $request->isReferer() && $login = Login::isMember()) {
      if ($login['email'] == 'demo') {
        $ret['alert'] = Language::get('Unable to complete the transaction');
      } else {
        // รับค่าจากการ POST
        $save = array(
          'icon_width' => max(75, $request->post('icon_width')->toInt()),
          'icon_height' => max(75, $request->post('icon_height')->toInt()),
          'img_typies' => $request->post('img_typies', array())->toString(),
          'published' => $request->post('published')->toBoolean(),
          'rows' => max(1, $request->post('rows')->toInt()),
          'cols' => max(1, $request->post('cols')->toInt()),
          'sort' => $request->post('sort')->toInt(),
          'new_date' => $request->post('new_date')->toInt(),
          'viewing' => $request->post('viewing')->toInt(),
          'category_display' => $request->post('category_display')->toBoolean(),
          'news_count' => $request->post('news_count')->toInt(),
          'news_sort' => $request->post('news_sort')->toInt(),
          'can_reply' => $request->post('can_reply', array())->toInt(),
          'can_view' => $request->post('can_view', array())->toInt(),
          'can_write' => $request->post('can_write', array())->toInt(),
          'moderator' => $request->post('moderator', array())->toInt(),
          'can_config' => $request->post('can_config', array())->toInt(),
        );
        // โมดูลที่เรียก
        $index = \Index\Adminmodule\Model::get('document', $request->post('id')->toInt());
        // สามารถตั้งค่าได้
        if ($index && Gcms::canConfig($login, $index, 'can_config')) {
          if (empty($save['img_typies'])) {
            // คืนค่า input ที่ error
            $ret['ret_img_typies_jpg'] = Language::get('Please select at least one item');
          } else {
            $save['default_icon'] = $index->default_icon;
            // อัปโหลดไฟล์
            foreach ($request->getUploadedFiles() as $item => $file) {
              if ($file->hasUploadFile()) {
                if (!File::makeDirectory(ROOT_PATH.DATA_FOLDER.'document/')) {
                  // ไดเรคทอรี่ไม่สามารถสร้างได้
                  $ret['ret_'.$item] = sprintf(Language::get('Directory %s cannot be created or is read-only.'), DATA_FOLDER.'document/');
                } elseif (!$file->validFileExt($save['img_typies'])) {
                  // รูปภาพเท่านั้น
                  $ret['ret_'.$item] = Language::get('The type of file is invalid');
                } else {
                  // อัปโหลด
                  $save['default_icon'] = DATA_FOLDER.'document/default-'.$index->module_id.'.'.$file->getClientFileExt();
                  try {
                    $file->moveTo(ROOT_PATH.$save['default_icon']);
                  } catch (\Exception $exc) {
                    // ไม่สามารถอัปโหลดได้
                    $ret['ret_'.$item] = Language::get($exc->getMessage());
                  }
                }
              }
            }
            if (empty($ret)) {
              $save['new_date'] = $save['new_date'] * 86400;
              $save['can_view'][] = 1;
              $save['can_write'][] = 1;
              $save['moderator'][] = 1;
              $save['can_config'][] = 1;
              $this->db()->createQuery()->update('modules')->set(array('config' => serialize($save)))->where($index->module_id)->execute();
              // คืนค่า
              $ret['alert'] = Language::get('Saved successfully');
              $ret['location'] = $request->getUri()->postBack('index.php', array('module' => 'document-settings', 'mid' => $index->module_id));
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
