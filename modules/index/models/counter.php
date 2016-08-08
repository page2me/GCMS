<?php
/*
 * @filesource index/models/counter.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Counter;

use \Kotchasan\File;

/**
 * ข้อมูล Counter และ Useronline
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Model
{

  /**
   * Initial Counter & Useronline
   */
  public static function init()
  {
    if (defined('MAIN_INIT')) {
      // วันนี้
      $y = (int)date('Y');
      $m = (int)date('m');
      $d = (int)date('d');
      // ตรวจสอบ ว่าเคยเยี่ยมชมหรือไม่
      if (self::$request->cookie('counter_date')->toInt() != $d) {
        // เข้ามาครั้งแรกในวันนี้, บันทึก counter 1 วัน
        setCookie('counter_date', $d, time() + 3600 * 24, '/');
        $new_visitor = true;
      } else {
        $new_visitor = false;
      }
      // โฟลเดอร์ของ counter
      $counter_dir = ROOT_PATH.DATA_FOLDER.'counter';
      // ตรวจสอบโฟลเดอร์
      File::makeDirectory($counter_dir);
      // ตรวจสอบวันใหม่
      $c = (int)@file_get_contents($counter_dir.'/index.php');
      if ($d != $c) {
        $f = @fopen($counter_dir.'/index.php', 'wb');
        if ($f) {
          fwrite($f, $d);
          fclose($f);
        }
        $f = @opendir($counter_dir);
        if ($f) {
          while (false !== ($text = readdir($f))) {
            if ($text != '.' && $text != '..') {
              if ($text != $y) {
                File::removeDirectory($counter_dir."/$text");
              }
            }
          }
          closedir($f);
        }
      }
      // ตรวจสอบ + สร้าง โฟลเดอร์
      File::makeDirectory("$counter_dir/$y");
      File::makeDirectory("$counter_dir/$y/$m");
      // ip ปัจจุบัน
      $counter_ip = self::$request->getClientIp();
      // session ปัจจุบัน
      $counter_ssid = session_id();
      // วันนี้
      $counter_day = date('Y-m-d');
      // Model
      $model = new static;
      $db = $model->db();
      // อ่าน counter รายการล่าสุด
      $my_counter = $db->createQuery()->from('counter')->order('id DESC')->toArray()->first();
      if (!$my_counter) {
        $my_counter = array('date' => '', 'counter' => 0);
      }
      if ($my_counter['date'] != $counter_day) {
        // วันใหม่
        $my_counter['visited'] = 0;
        $my_counter['pages_view'] = 0;
        $my_counter['date'] = $counter_day;
        $new_day = true;
        // clear useronline
        $db->emptyTable($model->getFullTableName('useronline'));
        // clear visited_today
        $db->updateAll($model->getFullTableName('index'), array('visited_today' => 0));
      } else {
        $new_day = false;
      }
      // บันทึกลง log
      $counter_log = "$counter_dir/$y/$m/$d.dat";
      if (is_file($counter_log)) {
        // เปิดไฟล์เพื่อเขียนต่อ
        $f = @fopen($counter_log, 'ab');
      } else {
        // สร้างไฟล์ log ใหม่
        $f = @fopen($counter_log, 'wb');
      }
      if ($f) {
        $data = $counter_ssid.chr(1).$counter_ip.chr(1).self::$request->server('HTTP_REFERER', '').chr(1).self::$request->server('HTTP_USER_AGENT', '').chr(1).date('H:i:s')."\n";
        fwrite($f, $data);
        fclose($f);
      }
      if ($new_visitor) {
        // ยังไม่เคยเยี่ยมชมในวันนี้
        $my_counter['visited'] ++;
        $my_counter['counter'] ++;
      }
      $my_counter['pages_view'] ++;
      $my_counter['time'] = time();
      if ($new_day) {
        unset($my_counter['id']);
        $db->insert($model->getFullTableName('counter'), $my_counter);
      } else {
        $db->update($model->getFullTableName('counter'), $my_counter['id'], $my_counter);
      }
      return $new_day;
    } else {
      // เรียก method โดยตรง
      new \Kotchasan\Http\NotFound('Do not call method directly');
    }
  }
}