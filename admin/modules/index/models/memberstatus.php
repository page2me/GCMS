<?php
/*
 * @filesource index/models/memberstatus.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Memberstatus;

use \Kotchasan\Login;
use \Kotchasan\Language;
use \Kotchasan\Config;

/**
 * บันทึกสถานะสมาชิก
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\KBase
{

  /**
   * รับค่าจาก action
   */
  public function action()
  {
    $ret = array();
    // referer, session, admin
    if (self::$request->initSession() && self::$request->isReferer() && $login = Login::isAdmin()) {
      if ($login['email'] == 'demo') {
        $ret['alert'] = Language::get('Unable to complete the transaction');
      } else {
        // โหลด config
        $config = Config::load(ROOT_PATH.'settings/config.php');
        // รับค่าจากการ POST
        $action = self::$request->post('action')->toString();
        // do not saved
        $save = false;
        // default
        if (!isset($config->member_status[0])) {
          $config->member_status[0] = 'สมาชิก';
          $save = true;
        }
        if (!isset($config->member_status[1])) {
          $config->member_status[1] = 'ผู้ดูแลระบบ';
          $save = true;
        }
        if (!isset($config->color_status[0])) {
          $config->color_status[0] = '#006600';
          $save = true;
        }
        if (!isset($config->color_status[1])) {
          $config->color_status[1] = '#FF0000';
          $save = true;
        }
        if ($action === 'config_status_add') {
          // เพิ่มสถานะสมาชิกใหม่
          $config->member_status[] = Language::get('click to edit');
          $config->color_status[] = '#000000';
          // id ของสถานะใหม่
          $i = sizeof($config->member_status) - 1;
          // ข้อมูลใหม่
          $row = '<li id="config_status_'.$i.'">';
          $row .= '<span class="icon-delete" id="config_status_delete_'.$i.'" title="'.Language::get('Delete').'"></span>';
          $row .= '<span id="config_status_color_'.$i.'" title="'.$config->color_status[$i].'"></span>';
          $row .= '<span id="config_status_name_'.$i.'" title="'.$config->member_status[$i].'">'.htmlspecialchars($config->member_status[$i]).'</span>';
          $row .= '</li>';
          // คืนค่าข้อมูลเข้ารหัส
          $ret['data'] = $row;
          $ret['newId'] = "config_status_$i";
          $save = true;
        } elseif (preg_match('/^config_status_delete_([0-9]+)$/', $action, $match)) {
          // ลบ
          $save1 = array();
          $save2 = array();
          // ลบสถานะและสี
          for ($i = 0; $i < sizeof($config->member_status); $i++) {
            if ($i < 2 || $i != $match[1]) {
              $save1[] = $config->member_status[$i];
              $save2[] = $config->color_status[$i];
            }
          }
          $config->member_status = $save1;
          $config->color_status = $save2;
          // รายการที่ลบ
          $ret['del'] = str_replace('delete_', '', $action);
          $save = true;
        } elseif (preg_match('/^config_status_(name|color)_([0-9]+)$/', $action, $match)) {
          // แก้ไขชื่อสถานะหรือสี
          $value = self::$request->post('value')->text();
          $match[2] = (int)$match[2];
          if ($value == '' && $match[1] == 'name') {
            $value = $config->member_status[$match[2]];
          } elseif ($value == '' && $match[1] == 'color') {
            $value = $config->color_status[$match[2]];
          } elseif ($match[1] == 'name') {
            $config->member_status[$match[2]] = $value;
            $save = true;
          } else {
            $config->color_status[$match[2]] = $value;
            $save = true;
          }
          // ส่งข้อมูลใหม่ไปแสดงผล
          $ret['edit'] = $value;
          $ret['editId'] = $action;
        }
        // save config
        if ($save && !Config::save($config, ROOT_PATH.'settings/config.php')) {
          $ret['alert'] = sprintf(Language::get('File %s cannot be created or is read-only.'), 'settings/config.php');
        }
      }
    } else {
      $ret['alert'] = Language::get('Unable to complete the transaction');
    }
    // คืนค่าเป็น JSON
    echo json_encode($ret);
  }
}