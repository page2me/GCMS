<?php
/*
 * @filesource edocument/models/admin/init.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Edocument\Admin\Init;

/**
 *  Model สำหรับอ่านข้อมูลโมดูล
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Model
{

  /**
   * เตรียมรายการเมนูสำหรับการเพิ่มเมนูที่สามารถใช้งานได้
   *
   * @param array $match ข้อมูลเมนู
   * @return array ข้อมูลเมนู
   */
  public static function initMenuwrite($match)
  {
    $ret = array();
    foreach ($match as $key => $value) {
      $ret[$key] = $value;
      list($owner, $id) = explode('_', $key);
      $ret[$owner.'_write_'.$id] = $value.' ({LNG_Add New} {LNG_E-Document})';
    }
    return $ret;
  }

  /**
   * แปลงรายการที่เลือกเป็นข้อมูลเมนู
   *
   * @param array $match ข้อมูลเมนู
   * @return array ข้อมูลเมนู
   */
  public static function parseMenuwrite($match)
  {
    \Gcms\Gcms::$module_menus[$match[1]][$match[2]] = array('{LNG_Add New} {LNG_E-Document}', '{WEBURL}index.php?module='.$match[1].'-write', $match[1]);
  }
}