<?php
/*
 * @filesource index/models/mailwrite.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Mailwrite;

use \Kotchasan\Login;
use \Kotchasan\Validator;
use \Kotchasan\Language;

/**
 * อ่าน/บันทึก ข้อมูลหน้าเพจ
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Model
{

  /**
   * อ่านอีเมล์ที่แก้ไข
   * id = 0 สร้างอีเมล์ใหม่
   *
   * @param int $id
   * @return object|bool คืนค่าข้อมูล object ไม่พบคืนค่า false
   */
  public static function getIndex($id)
  {
    if (is_int($id)) {
      if (empty($id)) {
        $index = (object)array(
            'id' => 0,
            'from_email' => '',
            'copy_to' => '',
            'subject' => '',
            'language' => Language::name(),
            'detail' => Template::load('', '', 'mailtemplate'),
            'name' => ''
        );
      } else {
        $model = new static;
        $index = $model->db()->first($model->getTableName('emailtemplate'), array('id', $id));
      }
      return $index;
    }
    return false;
  }

  /**
   * บันทึก
   */
  public function save()
  {
    $ret = array();
    // referer, session, member
    if (self::$request->initSession() && self::$request->isReferer() && $login = Login::isAdmin()) {
      if ($login['email'] == 'demo' || !empty($login['fb'])) {
        $ret['alert'] = Language::get('Unable to complete the transaction');
      } else {
        $model = new static;
        $table_email = $model->getTableName('emailtemplate');
        // รับค่าจากการ POST
        $save = array(
          'from_email' => self::$request->post('from_email')->url(),
          'copy_to' => self::$request->post('copy_to')->url(),
          'subject' => self::$request->post('subject')->topic(),
          'language' => self::$request->post('language')->text(),
          'detail' => self::$request->post('detail')->toString()
        );
        $id = self::$request->post('id')->toInt();
        // ตรวจสอบค่าที่ส่งมา
        if (!empty($id)) {
          $email = $model->db()->first($table_email, array('id', $id));
        }
        // มีการแก้ไขภาษา ตรวจสอบว่ามีรายการในภาษาที่เลือกหรือไม่
        if (!empty($id) && $save['language'] != $email->language) {
          $where = array(
            array('email_id', $email->email_id),
            array('module', $email->module),
            array('language', $save['language'])
          );
          $search = $model->db()->first($table_email, $where);
          if ($search === false) {
            // บันทึกเป็นรายการใหม่
            $save['name'] = $email->name;
            $save['email_id'] = $email->email_id;
            $save['module'] = $email->module;
            $id = 0;
          } else {
            // มีอีเมล์ในภาษาที่เลือกอยู่แล้ว
            $ret['ret_language'] = Language::get('This entry is in selected language');
          }
        }
        // from_email
        if (!empty($save['from_email']) && !Validator::email($save['from_email'])) {
          $ret['ret_from_email'] = 'this';
        }
        // copy_to
        if (!empty($save['copy_to'])) {
          foreach (explode(',', $save['copy_to']) as $item) {
            if (!Validator::email($item)) {
              if (empty($ret)) {
                $ret['ret_copy_to'] = 'this';
                break;
              }
            }
          }
        }
        // subject
        if (empty($save['subject'])) {
          $ret['ret_subject'] = 'this';
        }
        // detail
        $patt = array(
          '/^(&nbsp;|\s){0,}<br[\s\/]+?>(&nbsp;|\s){0,}$/iu' => '',
          '/<\?(.*?)\?>/su' => '',
          '@<script[^>]*?>.*?</script>@siu' => ''
        );
        $save['detail'] = trim(preg_replace(array_keys($patt), array_values($patt), $save['detail']));
        $save['last_update'] = time();
        if (empty($ret)) {
          if (empty($id)) {
            // ใหม่
            $model->db()->insert($table_email, $save);
          } else {
            // แก้ไข
            $model->db()->update($table_email, $id, $save);
          }
          // ส่งค่ากลับ
          $ret['alert'] = Language::get('Saved successfully');
          $ret['location'] = self::$request->getUri()->postBack('index.php', array('module' => 'mailtemplate', 'id' => 0));
        }
      }
    } else {
      $ret['alert'] = Language::get('Unable to complete the transaction');
    }
    // คืนค่าเป็น JSON
    echo json_encode($ret);
  }
}