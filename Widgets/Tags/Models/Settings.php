<?php
/**
 * @filesource Widgets/Tags/Controllers/Settings.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Widgets\Tags\Models;

use \Kotchasan\Login;
use \Kotchasan\Language;

/**
 * Controller สำหรับจัดการการตั้งค่าเริ่มต้น
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Settings extends \Kotchasan\Model
{

  /**
   * query รายการ tag ทั้งหมด
   *
   * @return array
   */
  public static function all()
  {
    $model = new static;
    return $model->db()->createQuery()
        ->select()
        ->from('tags')
        ->order('count')
        ->toArray()
        ->order('id')
        ->execute();
  }

  /**
   * save
   */
  public function save()
  {
    $ret = array();
    // referer, session, admin
    if (self::$request->initSession() && self::$request->isReferer() && $login = Login::isAdmin()) {
      if ($login['email'] == 'demo') {
        $ret['alert'] = Language::get('Unable to complete the transaction');
      } else {
        // ค่าที่ส่งมา
        $action = self::$request->post('action')->toString();
        if (preg_match('/^config_status_(add|name|delete)(_([0-9]+))?$/', $action, $match)) {
          if ($match[1] == 'add') {
            // เพิ่ม
            $value = Language::get('click to edit');
            $i = $this->db()->insert($this->getTableName('tags'), array('tag' => $value, 'count' => 0));
            // ข้อมูลใหม่
            $row = '<li id="config_status_'.$i.'">';
            $row .= '<span class="no">'.Language::get('Clicked').' [ 0 ]</span>';
            $row .= '<span class="icon-delete" id="config_status_delete_'.$i.'" title="'.Language::get('Delete').'"></span>';
            $row .= '<span id="config_status_name_'.$i.'" title="'.$value.'">'.$value.'</span>';
            $row .= '</li>';
            // คืนค่าข้อมูลเข้ารหัส
            $ret['data'] = $row;
            $ret['newId'] = "config_status_$i";
          } elseif ($match[1] == 'delete') {
            // ลบ
            $this->db()->delete($this->getTableName('tags'), (int)$match[3]);
            // รายการที่ลบ
            $ret['del'] = str_replace('delete_', '', $action);
          } elseif ($match[1] == 'name') {
            // แก้ไข Tag
            $value = self::$request->post('value')->topic();
            $this->db()->update($this->getTableName('tags'), (int)$match[3], array('tag' => $value));
            // ส่งข้อมูลใหม่ไปแสดงผล
            $ret['edit'] = $value;
            $ret['editId'] = $action;
          }
        }
      }
    }
    // คืนค่าเป็น JSON
    if (!empty($ret)) {
      echo json_encode($ret);
    }
  }
}