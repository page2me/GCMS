<?php
/*
 * @filesource index/models/module.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Module;

use \Kotchasan\ArrayTool;
use \Gcms\Gcms;

/**
 * คลาสสำหรับโหลดรายการโมดูลที่ติดตั้งแล้วทั้งหมด จากฐานข้อมูลของ GCMS
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Model
{

  /**
   * อ่านรายชื่อโมดูลและไดเร็คทอรี่ของโมดูลทั้งหมดที่ติดตั้งไว้
   *
   * @return array คืนค่าไดเร็คทอรี่ของโมดูลทั้งหมดที่ติดตั้งไว้
   */
  public static function getInstalledModule()
  {
    $owners = array();
    // โมดูลที่ติดตั้ง
    $dir = ROOT_PATH.'modules/';
    $f = @opendir($dir);
    if ($f) {
      while (false !== ($owner = readdir($f))) {
        if ($owner != '.' && $owner != '..') {
          $owners[] = $owner;
          Gcms::$install_owners[strtolower($owner)] = array();
        }
      }
      closedir($f);
    }
    // โหลดเมนูทั้งหมดเรียงตามลำดับเมนู (รายการแรกคือหน้า Home)
    Gcms::$menu = \Index\Menu\Controller::create();
    // โมดูลที่ติดตั้งแล้วจากเมนู
    foreach (Gcms::$menu->getMenus() as $item) {
      $module = $item->module;
      if (!empty($module) && !isset(Gcms::$install_modules[$module])) {
        Gcms::$install_modules[$module] = $item;
        Gcms::$install_owners[$item->owner][] = $module;
      }
    }
    // โหลดโมดูลทั้งหมด
    foreach (self::getModules() AS $item) {
      $module = $item->module;
      if (!isset(Gcms::$install_modules[$module])) {
        Gcms::$install_modules[$module] = $item;
        Gcms::$install_owners[$item->owner][] = $module;
      }
    }
    // คืนค่าไดเร็คทอรี่ที่ติดตั้ง
    return $owners;
  }

  /**
   * อ่านรายชื่อโมดูลที่มีการใช้งาน
   *
   * @return array
   */
  public static function getModules()
  {
    $result = array();
    $model = new static;
    $query = $model->db()->createQuery()
      ->select('id module_id', 'module', 'owner', 'config')
      ->from('modules')
      ->cacheOn()
      ->toArray();
    foreach ($query->execute() as $item) {
      if (!empty($item['config'])) {
        $config = @unserialize($item['config']);
        if (is_array($config)) {
          foreach ($config as $key => $value) {
            $item[$key] = $value;
          }
        }
      }
      unset($item['config']);
      $result[] = (object)$item;
    }
    return $result;
  }

  /**
   * ฟังก์ชั่นอ่านข้อมูลโมดูล
   *
   * @param int $id
   * @return object|false คืนค่าข้อมูลโมดูล (Object) ไม่พบคืนค่า false
   */
  public static function getModule($id)
  {
    if (is_int($id) && $id > 0) {
      $model = new static;
      $module = $model->db()->createQuery()
        ->from('modules')
        ->where($id)
        ->toArray()
        ->cacheOn()
        ->first();
      if ($module) {
        $module = ArrayTool::unserialize($module['config'], $module);
        unset($module['config']);
      }
    }
    return empty($module) ? false : (object)$module;
  }

  /**
   * อ่านรายละเอียดของโมดูล
   * topic, details, keywords, description
   *
   * @param Object $index
   * @return Object
   */
  public static function getDetails($index)
  {
    // Model
    $model = new static;
    $search = $model->db()->createQuery()
      ->from('index_detail D')
      ->join('index I', 'INNER', array(array('I.index', 1), array('I.id', 'D.id'), array('I.module_id', 'D.module_id'), array('I.language', 'D.language')))
      ->where(array(array('I.module_id', (int)$index->module_id), array('D.language', array(\Kotchasan\Language::name(), ''))))
      ->cacheOn()
      ->toArray()
      ->first('D.topic', 'D.detail', 'D.keywords', 'D.description');
    if ($search) {
      foreach ($search as $key => $value) {
        $index->$key = $value;
      }
    }
    return $index;
  }

  /**
   * อ่านข้อมูลโมดูลจาก $module และ $owner
   *
   * @param string $owner
   * @param string $module
   * @param int $module_id
   * @return object|false คืนค่าข้อมูลโมดูล (Object) ไม่พบคืนค่า false
   */
  public static function get($owner, $module, $module_id = 0)
  {
    // Model
    $model = new static;
    if (empty($module_id)) {
      $where = array(array('module', $module), array('owner', $owner));
    } else {
      $where = array(array('id', (int)$module_id), array('owner', $owner));
    }
    $module = $model->db()->createQuery()
      ->from('modules')
      ->where($where)
      ->toArray()
      ->cacheOn()
      ->first('id module_id', 'module', 'config');
    if ($module) {
      $module = ArrayTool::unserialize($module['config'], $module);
      unset($module['config']);
      return (object)$module;
    }
    return false;
  }
}