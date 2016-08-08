<?php
/*
 * @filesource Widgets/Counter/Controllers/Index.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Widgets\Counter\Controllers;

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
    $counter = \Widgets\Counter\Models\Index::get();
    $fmt = '%0'.self::$cfg->counter_digit.'d';
    // กรอบ counter
    $widget = '<div id=counter-box>';
    $widget .= '<p class=counter-detail><span class=col>{LNG_Visitors total}</span><span id=counter>'.sprintf($fmt, $counter->counter).'</span></p>';
    $widget .= '<p class=counter-detail><span class=col>{LNG_Visitors today}</span><span id=counter-today>'.sprintf($fmt, $counter->visited).'</span></p>';
    $widget .= '<p class=counter-detail><span class=col>{LNG_Pages View}</span><span id=pages-view>'.sprintf($fmt, $counter->pages_view).'</span></p>';
    $widget .= '<p class=counter-detail><span class=col>{LNG_People online}</span><span id=useronline>'.sprintf($fmt, $counter->useronline).'</span></p>';
    $widget .= '</div>';
    $widget .= '<ul id=counter-online></ul>';
    return $widget;
  }
}