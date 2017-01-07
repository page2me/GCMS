<?php
/*
 * @filesource friends/models/admin/settings.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Friends\Admin\Settings;

use \Kotchasan\Http\Request;
use \Kotchasan\Login;
use \Kotchasan\Language;
use \Gcms\Gcms;

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
      'per_day' => 3,
      'pin_per_page' => 5,
      'list_per_page' => 20,
      'sex_color' => array('f' => '#FFB7FF', 'm' => '#C4C4FF'),
      'moderator' => 1,
      'can_post' => array(1),
      'moderator' => array(1),
      'can_config' => array(1)
    );
  }

  /**
   * เมธอดสำหรับการติดตั้งโมดูลแบบใช้ซ้ำได้
   *
   * @param array $module ข้อมูลโมดูล
   */
  public static function install($module)
  {
    // อัปเดทชื่อตาราง
    \Index\Install\Model::updateTables(array('friends' => 'friends'));
    // อัปเดท database
    \Index\Install\Model::execute(ROOT_PATH.'modules/friends/models/admin/sql.php');
    // สร้างไดเร็คทอรี่เก็บข้อมูลโมดูล
    \Kotchasan\File::makeDirectory(ROOT_PATH.DATA_FOLDER.'friends/');
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
          'per_day' => $request->post('per_day')->toInt(),
          'pin_per_page' => $request->post('pin_per_page')->toInt(),
          'list_per_page' => $request->post('list_per_page')->toInt(),
          'sex_color' => $request->post('sex_color')->color(),
          'can_post' => $request->post('can_post', array())->toInt(),
          'moderator' => $request->post('moderator', array())->toInt(),
          'can_config' => $request->post('can_config', array())->toInt(),
        );
        // โมดูลที่เรียก
        $index = \Index\Adminmodule\Model::get('friends', $request->post('id')->toInt());
        // สามารถตั้งค่าได้
        if ($index && Gcms::canConfig($login, $index, 'can_config')) {
          $save['can_post'][] = 1;
          $save['moderator'][] = 1;
          $save['can_config'][] = 1;
          $this->db()->createQuery()->update('modules')->set(array('config' => serialize($save)))->where($index->module_id)->execute();
          // คืนค่า
          $ret['alert'] = Language::get('Saved successfully');
          $ret['location'] = $request->getUri()->postBack('index.php', array('module' => 'friends-settings', 'mid' => $index->module_id));
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