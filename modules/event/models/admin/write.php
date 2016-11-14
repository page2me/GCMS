<?php
/*
 * @filesource event/models/admin/write.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Event\Admin\Write;

use \Kotchasan\Http\Request;
use \Kotchasan\Language;
use \Gcms\Gcms;
use \Kotchasan\Login;
use \Kotchasan\ArrayTool;

/**
 * อ่านข้อมูลโมดูล
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Model
{

  /**
   * อ่านข้อมูลรายการที่เลือก
   *
   * @param int $module_id ของโมดูล
   * @param int $id ID
   * @return object|null คืนค่าข้อมูล object ไม่พบคืนค่า null
   */
  public static function get($module_id, $id)
  {
    // model
    $model = new static;
    $query = $model->db()->createQuery();
    if (empty($id)) {
      // ใหม่ ตรวจสอบโมดูล
      $query->select('0 id', 'M.id module_id', 'M.owner', 'M.module', 'M.config')
        ->from('modules M')
        ->where(array(
          array('M.id', $module_id),
          array('M.owner', 'event'),
      ));
    } else {
      // แก้ไข ตรวจสอบรายการที่เลือก
      $query->select('A.*', 'M.owner', 'M.module', 'M.config')
        ->from('event A')
        ->join('modules M', 'INNER', array(array('M.id', 'A.module_id'), array('M.owner', 'event')))
        ->where(array('A.id', $id));
    }
    $result = $query->limit(1)->toArray()->execute();
    if (sizeof($result) == 1) {
      $result = ArrayTool::unserialize($result[0]['config'], $result[0], empty($id));
      unset($result['config']);
      return (object)$result;
    }
    return null;
  }

  /**
   * บันทึก
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
        // ค่าที่ส่งมา
        $save = array(
          'topic' => $request->post('topic')->topic(),
          'color' => $request->post('color')->topic(),
          'keywords' => $request->post('keywords')->keywords(),
          'description' => $request->post('description')->description(),
          'detail' => $request->post('detail')->detail(),
          'published' => $request->post('published')->toBoolean(),
          'begin_date' => $request->post('begin_date')->date().' '.$request->post('begin_time')->date(),
          'published_date' => $request->post('published_date')->date()
        );
        if ($request->post('forever')->toBoolean()) {
          $save['end_date'] = '0000-00-00 00:00:00';
        } else {
          $save['end_date'] = $request->post('begin_date')->date().' '.$request->post('to_time')->date();
        }
        if (empty($save['keywords'])) {
          $save['keywords'] = $request->post('topic')->keywords(255);
        }
        if (empty($save['description'])) {
          $save['description'] = $request->post('detail')->description(255);
        }
        $id = $request->post('id')->toInt();
        // ตรวจสอบรายการที่เลือก
        $index = self::get($request->post('module_id')->toInt(), $id);
        if ($index && Gcms::canConfig($login, $index, 'can_write')) {
          // topic
          if (mb_strlen($save['topic']) < 4) {
            $ret['ret_topic'] = 'this';
          }
          // detail
          if ($save['detail'] == '') {
            $ret['ret_detail'] = Language::get('Please fill in').' '.Language::get('Detail');
          }
          if (empty($ret)) {
            $save['last_update'] = time();
            if ($id == 0) {
              // ใหม่
              $save['module_id'] = $index->module_id;
              $save['member_id'] = $login['id'];
              $save['create_date'] = date('Y-m-d H:i:s');
              $this->db()->insert($this->getFullTableName('event'), $save);
            } else {
              // แก้ไข
              $this->db()->update($this->getFullTableName('event'), $id, $save);
            }
            // ส่งค่ากลับ
            $ret['alert'] = Language::get('Saved successfully');
            $ret['location'] = $request->getUri()->postBack('index.php', array('mid' => $index->module_id, 'module' => 'event-setup'));
          }
        } else {
          $ret['alert'] = Language::get('Can not be performed this request. Because they do not find the information you need or you are not allowed');
        }
      }
    } else {
      $ret['alert'] = Language::get('Can not be performed this request. Because they do not find the information you need or you are not allowed');
    }
    // คืนค่าเป็น JSON
    echo json_encode($ret);
  }
}