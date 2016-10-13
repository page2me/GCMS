<?php
/*
 * @filesource index/models/upgrade.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Upgrade;

/**
 * อัปเกรด
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\KBase
{

  /**
   * ตรวจสอบว่ามีตารางหรือไม่
   *
   * @param resource $db
   * @param string $table_name
   * @return boolean
   */
  public static function tableExists($db, $table_name)
  {
    try {
      $result = $db->connection()->query("SELECT 1 FROM `$table_name` LIMIT 1");
    } catch (\PDOException $e) {
      return false;
    }
    return true;
  }

  /**
   * ตรวจสอบฟิลด์
   *
   * @param resource $db
   * @param string $table_name
   * @param type $field
   * @return boolean
   */
  public static function fieldExists($db, $table_name, $field)
  {
    $result = $db->customQuery("SHOW COLUMNS FROM `$table_name` LIKE '$field'");
    return empty($result) ? false : true;
  }

  /**
   * บันทึกไฟล์ settings/database.php
   *
   * @param array $tables รายการตารางที่ต้องการอัปเดท (แทนที่ข้อมูลเดิม)
   * @return boolean คืนค่า true ถ้าสำเร็จ
   */
  public static function updateTables($tables)
  {
    // โหลด database
    $database = \Kotchasan\Config::load(ROOT_PATH.'settings/database.php');
    // อัปเดท tables
    foreach ($tables as $key => $value) {
      $database->tables[$key] = $value;
    }
    // save database
    return \Kotchasan\Config::save($database, ROOT_PATH.'settings/database.php');
  }
}