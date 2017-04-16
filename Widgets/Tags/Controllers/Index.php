<?php
/**
 * @filesource Widgets/Tags/Controllers/Index.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Widgets\Tags\Controllers;

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
    if (defined('MAIN_INIT')) {
      $tag_result = \Widgets\Tags\Models\Index::get();
      $min = 1000000;
      $max = 0;
      $nmax = sizeof($tag_result) - 1;
      $min = isset($tag_result[1]) ? $tag_result[1]['count'] : 0;
      $max = isset($tag_result[$nmax - 1]) ? $tag_result[$nmax - 1]['count'] : 0;
      $step = ($max - $min > 0) ? ($max - $min) / 7 : 0.1;
      $items = array();
      for ($i = $nmax; $i >= 0; $i--) {
        $value = $tag_result[$i]['count'];
        $key = $tag_result[$i]['tag'];
        $id = $tag_result[$i]['id'];
        if ($i == 0) {
          $classname = 'class0';
        } elseif ($i == $nmax) {
          $classname = 'class9';
        } else {
          $classname = 'class'.(floor(($value - $min) / $step) + 1);
        }
        $url = self::url($key);
        $items[] = '<a href="'.$url.'" class='.$classname.' id=tags-'.$id.'>'.str_replace(' ', '&nbsp;', $key).'</a>';
      }
      return \Widgets\Tags\Views\Index::render($items);
    }
  }

  /**
   * ฟังก์ชั่นสร้าง URL
   *
   * @param string $tag ชื่อ Tag
   * @return string
   */
  public static function url($tag)
  {
    if (self::$cfg->module_url == 1) {
      return Gcms::createUrl('tag', $tag);
    } else {
      return Gcms::createUrl('tag', '', 0, 0, 'tag='.$tag);
    }
  }
}