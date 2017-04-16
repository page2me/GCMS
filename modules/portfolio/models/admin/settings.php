<?php
/**
 * @filesource portfolio/models/admin/settings.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Portfolio\Admin\Settings;

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
      'width' => 400,
      'height' => 400,
      'rows' => 4,
      'cols' => 2,
      'can_write' => array(1),
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
    \Index\Install\Model::updateTables(array('portfolio' => 'portfolio'));
    // อัปเดท database
    \Index\Install\Model::execute(ROOT_PATH.'modules/portfolio/models/admin/sql.php');
    // สร้างไดเร็คทอรี่เก็บข้อมูลโมดูล
    \Kotchasan\File::makeDirectory(ROOT_PATH.DATA_FOLDER.'portfolio/');
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
          'width' => max(100, $request->post('width')->toInt()),
          'height' => max(100, $request->post('height')->toInt()),
          'rows' => $request->post('rows')->toInt(),
          'cols' => $request->post('cols')->toInt(),
          'can_write' => $request->post('can_write', array())->toInt(),
          'can_config' => $request->post('can_config', array())->toInt(),
        );
        // โมดูลที่เรียก
        $index = \Index\Adminmodule\Model::get('portfolio', $request->post('id')->toInt());
        // สามารถตั้งค่าได้
        if ($index && Gcms::canConfig($login, $index, 'can_config')) {
          $save['can_write'][] = 1;
          $save['can_config'][] = 1;
          $this->db()->createQuery()->update('modules')->set(array('config' => serialize($save)))->where($index->module_id)->execute();
          // คืนค่า
          $ret['alert'] = Language::get('Saved successfully');
          $ret['location'] = $request->getUri()->postBack('index.php', array('module' => 'portfolio-settings', 'mid' => $index->module_id));
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