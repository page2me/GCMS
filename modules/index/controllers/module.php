<?php
/*
 * @filesource index/controllers/module.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Module;

use \Gcms\Gcms;

/**
 * คลาสสำหรับตรวจสอบโมดูลที่เลือก
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Kotchasan\Controller
{

  /**
   * ตรวจสอบโมดูลที่เลือก
   *
   * @param array $modules ข้อมูลจาก $_GET หรือ $_POST
   * @return object||null คืนค่าโมดูลที่ใช้งานได้ ไม่พบคืนค่า null
   */
  public static function get($modules)
  {
    // รายชื่อโมดูลทั้งหมด
    $module_list = array_keys(Gcms::$install_modules);
    // ตรวจสอบโมดูลที่เรียก
    if (isset($modules['module']) && preg_match('/^(tag|calendar)([\/\-](.*)|)$/', $modules['module'], $match)) {
      // โมดูล document (tag, calendar)
      $modules['module'] = 'document';
      $modules['page'] = ucfirst($match[1]);
      if (isset($match[3])) {
        $modules['alias'] = $match[3];
      }
    } elseif (isset($modules['module']) && preg_match('/^([a-z]+)[\/\-]([a-z]+)$/', $modules['module'], $match)) {
      // โมดูลที่ติดตั้ง
      $modules['module'] = $match[1];
      $modules['page'] = ucfirst($match[2]);
    } else {
      // โมดูล index
      $modules['page'] = 'Index';
    }
    // ตรวจสอบโมดูลที่เลือกกับโมดูลที่ติดตั้งแล้ว
    $module = null;
    if (!empty($module_list)) {
      if (empty($modules['module'])) {
        // ไม่ได้กำหนดโมดูลมา ใช้โมดูลแรกสุด
        $module = Gcms::$install_modules[reset($module_list)];
      } elseif ($modules['module'] == 'search') {
        // เรียกหน้าค้นหา (โมดูล index)
        $module = (object)array(
            'owner' => 'search'
        );
      } elseif ($modules['module'] == 'index' && isset($modules['id'])) {
        // เรียกโมดูล index จาก id
        $module = (object)array(
            'owner' => 'index',
            'id' => $modules['id']
        );
      } elseif (in_array($modules['module'], $module_list)) {
        // โมดูลที่เลือก
        $module = Gcms::$install_modules[$modules['module']];
      } elseif (in_array($modules['module'], array_keys(Gcms::$install_owners))) {
        // เรียกโมดูลที่ติดตั้ง (ไดเร็คทอรี่)
        $modules['owner'] = $modules['module'];
        $module = (object)$modules;
      }
    }
    if ($module) {
      if ($module->owner == 'index') {
        // เรียกจากโมดูล index
        $className = 'Index\Main\Controller';
      } elseif ($module->owner == 'search') {
        // ค้นหา
        $className = 'Index\Search\Controller';
        $module->owner = 'index';
        $module->module = 'search';
        $module->page = 'init';
      } else {
        // เรียกจากโมดูลที่ติดตั้ง
        $className = ucfirst($module->owner).'\\'.$modules['page'].'\Controller';
        if (!class_exists($className)) {
          $className = null;
        }
      }
      // เรียก method init
      $method = 'init';
    } elseif (!empty($modules['module']) && method_exists('Index\Member\Controller', $modules['module'])) {
      // หน้าสมาชิก
      $className = 'Index\Member\Controller';
      // method ที่เลือก
      $method = $modules['module'];
    }
    if (empty($className)) {
      return null;
    }
    return (object)array(
        'className' => $className,
        'method' => $method,
        'module' => $module
    );
  }
}