<?php
/*
 * @filesource Widgets/Album/Controllers/Index.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Widgets\Album\Controllers;

use \Gcms\Gcms;

/**
 * Controller หลัก สำหรับแสดงผล Widget
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Index extends \Kotchasan\Controller
{

  /**
   * แสดงผล Widget
   *
   * @param array $query_string ข้อมูลที่ส่งมาจากการเรียก Widget
   * @return string
   */
  public function get($query_string)
  {
    if (preg_match('/^[a-z0-9]{3,}$/', $query_string['module']) && isset(Gcms::$install_modules[$query_string['module']])) {
      // module
      $index = Gcms::$install_modules[$query_string['module']];
      $menu = '';
      // query หมวด
      foreach (\Index\Category\Model::categories($index->module_id) as $category_id => $topic) {
        $menu .= '<li><a href="'.Gcms::createUrl($index->module, '', $category_id).'"><span>'.$topic.'</span></a></li>';
      }
      if (empty($query_string['itemonly'])) {
        return $menu == '' ? '' : '<ul>'.$menu.'</ul>';
      } else {
        return $menu;
      }
    }
  }
}