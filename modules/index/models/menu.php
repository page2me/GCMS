<?php
/*
 * @filesource index/models/menu.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Menu;

use \Kotchasan\Language;

/**
 * คลาสสำหรับโหลดรายการเมนูจากฐานข้อมูลของ GCMS
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Model
{
  /**
   * รายการเมนูทั้งหมด
   *
   * @var object
   */
  private $datas;
  /**
   * รายการเมนูเรียงลำกับตามระดับของเมนู
   *
   * @var object
   */
  private $menus_by_pos;

  /**
   * โหลดเมนูทั้งหมดเรียงตามลำดับเมนู (รายการแรกคือหน้า Home)
   *
   * @return array
   */
  public static function create()
  {
    if (defined('MAIN_INIT')) {
      $model = new static;
      // โหลดเมนูทั้งหมดเรียงตามลำดับเมนู (รายการแรกคือหน้า Home)
      $lng = array(Language::name(), '');
      $select = array(
        'M.id module_id',
        'M.module',
        'M.owner',
        'M.config',
        'U.index_id',
        'U.parent',
        'U.level',
        'U.menu_text',
        'U.menu_tooltip',
        'U.accesskey',
        'U.menu_url',
        'U.menu_target',
        'U.alias',
        'U.published',
        "(CASE U.`parent` WHEN 'MAINMENU' THEN 0 WHEN 'BOTTOMMENU' THEN 1 WHEN 'SIDEMENU' THEN 2 ELSE 3 END ) AS `pos`"
      );
      $query = $model->db()->createQuery()
        ->select($select)
        ->from('menus U')
        ->join('index I', 'LEFT', array(array('I.id', 'U.index_id'), array('I.index', '1'), array('I.language', $lng)))
        ->join('modules M', 'LEFT', array('M.id', 'I.module_id'))
        ->where(array(array('U.language', $lng), array('U.parent', '!=', '')))
        ->order(array('pos', 'U.parent', 'U.menu_order'));
      // จัดลำดับเมนูตามระดับของเมนู
      $datas = array();
      $model->datas = $query->cacheOn()->execute();
      foreach ($model->datas AS $i => $item) {
        if (!empty($item->config)) {
          $config = @unserialize($item->config);
          if (is_array($config)) {
            foreach ($config as $key => $value) {
              $item->$key = $value;
            }
          }
        }
        unset($item->config);
        $level = $item->level;
        if ($level == 0) {
          $datas[$item->parent]['toplevel'][$i] = $item;
        } else {
          $datas[$item->parent][$toplevel[$level - 1]][$i] = $item;
        }
        $toplevel[$level] = $i;
      }
      $model->menus_by_pos = (object)$datas;
      return $model;
    } else {
      // เรียก method โดยตรง
      new \Kotchasan\Http\NotFound('Do not call method directly');
    }
  }

  /**
   * อ่านรายการเมนูทั้งหมด
   *
   * @return object
   */
  public function getMenus()
  {
    return $this->datas;
  }

  /**
   * อ่านรายการเมนูทั้งหมด เรียงลำดับตามตำแหน่งของเมนู
   *
   * @return object
   */
  public function getMenusByPos()
  {
    return $this->menus_by_pos;
  }

  /**
   * อ่านเมนูตามตำแหน่งที่กำหนด
   *
   * @param string $pos
   * @return array ไม่พบคืนค่าแอเรย์ว่าง
   */
  public function get($pos)
  {
    return isset($this->menus_by_pos->{$pos}) ? $this->menus_by_pos->{$pos} : array();
  }
}