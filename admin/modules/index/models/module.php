<?php
/*
 * @filesource index/models/module.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Module;

/**
 * คลาสสำหรับโหลดรายการโมดูลที่ติดตั้งแล้วทั้งหมด จากฐานข้อมูลของ GCMS (Admin)
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
  public $by_owner = array();
  /**
   * รายการโมดูล เรียงลำดับตาม module
   *
   * @var array
   */
  public $by_module = array();

  /**
   * อ่านรายชื่อโมดูลและไดเร็คทอรี่ของโมดูลทั้งหมดที่ติดตั้งไว้
   *
   * @param string $dir
   */
  public function __construct($dir)
  {
    // โมดูลที่ติดตั้ง
    $f = @opendir($dir);
    if ($f) {
      while (false !== ($owner = readdir($f))) {
        if ($owner != '.' && $owner != '..' && $owner != 'js' && $owner != 'css') {
          $this->by_owner[$owner] = array();
        }
      }
      closedir($f);
    }
    // โหลดโมดูลที่ติดตั้งแล้ว จาก DB
    foreach ($this->getModules() as $item) {
      $this->by_module[$item->module] = $item;
      $this->by_owner[$item->owner][] = $item;
    }
  }

  /**
   * โหลดโมดูลที่ติดตั้งแล้ว
   *
   * @return array
   */
  private function getModules()
  {
    // model
    $model = new \Kotchasan\Model;
    // โหลดโมดูลที่ติดตั้ง เรียงตามลำดับโฟลเดอร์
    return $model->db()->createQuery()
        ->select('id', 'module', 'owner')
        ->from('modules')
        ->where(array('owner', '!=', 'index'))
        ->order('owner')
        ->execute();
  }
}