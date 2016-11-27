<?php
/*
 * @filesource index/models/module.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Module;

/**
 * คลาสสำหรับโหลดรายการโมดูลที่ติดตั้งแล้วทั้งหมด จากฐานข้อมูลของ GCMS
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model
{
  /**
   * รายการโมดูล เรียงลำดับตาม owner
   *
   * @var array
   */
  public $by_owner;
  /**
   * รายการโมดูล เรียงลำดับตาม module
   *
   * @var array
   */
  public $by_module = array();

  /**
   * อ่านรายชื่อโมดูลและไดเร็คทอรี่ของโมดูลทั้งหมดที่ติดตั้งไว้
   */
  public function __construct($dir, \Index\Menu\Controller $menu)
  {
    $this->by_owner = array('index' => array());
    // โมดูลที่ติดตั้ง
    $f = @opendir($dir);
    if ($f) {
      while (false !== ($owner = readdir($f))) {
        if ($owner != '.' && $owner != '..' && $owner != 'index' && $owner != 'js' && $owner != 'css') {
          $this->by_owner[$owner] = array();
        }
      }
      closedir($f);
    }
    // โหลดโมดูลที่ติดตั้งแล้ว และสามารถใช้งานได้
    $modules = $this->getModules();
    // ใส่ข้อมูลโมดูลลงในเมนู
    foreach ($menu->getMenus() as $item) {
      if (isset($modules[$item->index_id])) {
        $item->module = $modules[$item->index_id];
        $this->by_module[$item->module->module] = null;
      }
    }
    // เรียงลำดับข้อมูลโมดูลตาม module และ owner
    foreach ($modules as $item) {
      $this->by_module[$item->module] = $item;
      $this->by_owner[$item->owner][] = $item;
    }
  }

  /**
   * โหลดโมดูลที่ติดตั้งแล้ว และสามารถใช้งานได้
   *
   * @return array
   */
  private function getModules()
  {
    $model = new \Kotchasan\Model;
    $query = $model->db()->createQuery()
      ->select('I.id index_id', 'I.module_id', 'M.module', 'M.owner', 'M.config', 'D.topic')
      ->from('modules M')
      ->join('index I', 'INNER', array(
        array('I.index', 1),
        array('I.module_id', 'M.id'),
        array('I.published', 1),
        array('I.language', array(\Kotchasan\Language::name(), ''))
      ))
      ->join('index_detail D', 'INNER', array(array('D.id', 'I.id'), array('D.module_id', 'I.module_id'), array('D.language', 'I.language')))
      ->cacheOn()
      ->toArray();
    $result = array();
    foreach ($query->execute() as $item) {
      $config = @unserialize($item['config']);
      if (is_array($config)) {
        $config['index_id'] = $item['index_id'];
        $config['module_id'] = $item['module_id'];
        $config['module'] = $item['module'];
        $config['owner'] = $item['owner'];
        $config['topic'] = $item['topic'];
        $result[$item['index_id']] = (object)$config;
      } else {
        unset($item['config']);
        $result[$item['index_id']] = (object)$item;
      }
    }
    return $result;
  }

  /**
   * อ่านข้อมูลโมดูลและค่ากำหนด จาก DB
   *
   * @param string $owner
   * @param string $module
   * @param int $module_id
   * @return object|false คืนค่าข้อมูลโมดูล (Object) ไม่พบคืนค่า false
   */
  public static function getModuleWithConfig($owner, $module = '', $module_id = 0)
  {
    if (empty($module) && empty($module_id)) {
      $where = array('owner', $owner);
    } elseif (empty($owner) && empty($module)) {
      $where = array('id', (int)$module_id);
    } elseif (empty($owner) && empty($module_id)) {
      $where = array('module', $module);
    } elseif (empty($module_id)) {
      $where = array(array('module', $module), array('owner', $owner));
    } else {
      $where = array(array('id', (int)$module_id), array('owner', $owner));
    }
    $model = new \Kotchasan\Model;
    $search = $model->db()->createQuery()
      ->from('modules')
      ->where($where)
      ->cacheOn()
      ->toArray()
      ->first('id', 'module', 'owner', 'config');
    if ($search) {
      $config = @unserialize($search['config']);
      if (is_array($config)) {
        $config['id'] = $search['id'];
        $config['module'] = $search['module'];
        $config['owner'] = $search['owner'];
        return (object)$config;
      } else {
        unset($search['config']);
        return (object)$search;
      }
    }
    return null;
  }

  /**
   * อ่านรายละเอียดของโมดูล
   * topic, details, keywords, description
   *
   * @param Object $index
   * @return Object ไม่พบคืนค่า null
   */
  public static function getDetails($index)
  {
    // Model
    $model = new \Kotchasan\Model;
    $search = $model->db()->createQuery()
      ->from('index I')
      ->join('index_detail D', 'INNER', array(array('D.id', 'I.id'), array('D.module_id', 'I.module_id'), array('D.language', 'I.language')))
      ->where(array(
        array('I.id', (int)$index->index_id),
        array('I.module_id', (int)$index->module_id),
      ))
      ->cacheOn()
      ->toArray()
      ->first('D.detail', 'D.keywords', 'D.description');
    if ($search) {
      $index->detail = $search['detail'];
      $index->keywords = $search['keywords'];
      $index->description = $search['description'];
      return $index;
    }
    return null;
  }
}