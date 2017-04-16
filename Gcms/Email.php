<?php
/**
 * @filesource Gcms/Email.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Gcms;

use \Kotchasan\ArrayTool;
use \Kotchasan\Language;
use \Kotchasan\Date;

/**
 * Email function for GCMS
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Email extends \Kotchasan\Email
{

  /**
   * ฟังก์ชั่นส่งเมล์จากแม่แบบจดหมาย
   *
   * @param int $id ID ของจดหมายที่ต้องการส่ง
   * @param string $module ชื่อโมดูลของจดหมายที่ต้องการส่ง
   * @param array $datas ข้อมูลที่จะถูกแทนที่ลงในจดหมาย ในรูป 'ตัวแปร'=>'ข้อความ'
   * @param string $to ที่อยู่อีเมล์ผู้รับ  คั่นแต่ละรายชื่อด้วย ,
   * @return string สำเร็จคืนค่าว่าง ไม่สำเร็จ คืนค่าข้อความผิดพลาด
   */
  public static function send($id, $module, $datas, $to)
  {
    $model = new static;
    $email = $model->db()->createQuery()
      ->from('emailtemplate')
      ->where(array(
        array('module', $module),
        array('email_id', (int)$id),
        array('language', array(Language::name(), ''))
      ))
      ->cacheOn()
      ->toArray()
      ->first('from_email', 'copy_to', 'subject', 'detail');
    if ($email === false) {
      return Language::get('email template not found');
    } else {
      // ผู้ส่ง
      $from = empty($email['from_email']) ? self::$cfg->noreply_email : $email['from_email'];
      // ข้อความในอีเมล์
      $replace = ArrayTool::replace(array(
          '/%WEBTITLE%/' => strip_tags(self::$cfg->web_title),
          '/%WEBURL%/' => WEB_URL,
          '/%ADMINEMAIL%/' => $from,
          '/%TIME%/' => Date::format()
          ), $datas);
      ArrayTool::extract($replace, $keys, $values);
      $msg = preg_replace($keys, $values, $email['detail']);
      $subject = preg_replace($keys, $values, $email['subject']);
      $to = explode(',', $to);
      if (!empty($email['copy_to'])) {
        $to[] = $email['copy_to'];
      }
      // ส่งอีเมล์
      return parent::send(implode(',', $to), $from, $subject, $msg);
    }
  }
}