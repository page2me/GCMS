<?php
/*
 * @filesource Widgets/Calendar/Controllers/Calendar.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Widgets\Calendar\Controllers;

use \Kotchasan\Date;
use \Kotchasan\Language;
use \Kotchasan\Http\Request;

/**
 * Controller สำหรับแสดงปฏิทิน
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Calendar extends \Kotchasan\Controller
{

  /**
   * แสดงผลปฎิทิน
   *
   * @param array $query_string $query_string ข้อมูลที่ส่งมา
   * @param array $settings ค่ากำหนดของปฎิทิน
   * @return string
   */
  public function render(Request $request, $settings)
  {
    // ค่าที่ส่งมา
    $query_string = $request->getQueryParams();
    // ภาษา
    $lng = Language::getItems(array(
        'Prev Month',
        'Next Month',
        'DATE_SHORT',
        'MONTH_LONG',
        'YEAR_OFFSET'
    ));
    // วันนี้
    $year = Date::year();
    $month = Date::month();
    $today = Date::day();
    if (isset($query_string['id']) && preg_match('/^(prev|next|today)\-([0-9]{0,2})\-([0-9]{0,2})\-([0-9]{0,4})$/', $query_string['id'], $match)) {
      // มาจาก Ajax
      if ($match[1] == 'today') {
        // ใช้วันที่ ที่กำหนดมา
        $c_year = (int)$match[4] - $lng['YEAR_OFFSET'];
        $c_month = (int)$match[3];
      } else {
        $c_mkdate = mktime(0, 0, 0, (int)$match[3] + ($match[1] == 'prev' ? -1 : 1), 1, $match[4] - $lng['YEAR_OFFSET']);
        $c_year = (int)date('Y', $c_mkdate);
        $c_month = (int)date('m', $c_mkdate);
      }
    } else {
      // ไม่ได้เลือกวันที่มา แสดงวันที่วันนี้
      $c_year = $year;
      $c_month = $month;
    }
    $d = "$today-$c_month-".((int)$c_year + $lng['YEAR_OFFSET']);
    // วันที่ 1 ของเดือนนี้
    $first_date = mktime(0, 0, 0, $c_month, 1, $c_year);
    $weekday = date('w', $first_date);
    $endday = date('t', $first_date);
    $day = 1;
    // วันที่ 1 ของเดือนถัดไป
    $first_next_month = $c_month == 12 ? mktime(0, 0, 0, 1, 1, $c_year + 1) : mktime(0, 0, 0, $c_month + 1, 1, $c_year);
    // จำนวนวันของเดือนก่อนหน้า
    $days_of_last_month = $c_month == 1 ? date('t', mktime(0, 0, 0, 12, 1, $c_year - 1)) : date('t', mktime(0, 0, 0, $c_month - 1, 1, $c_year));
    // query และจัดกลุ่มข้อมูลตามวันที่
    $events = array();
    if (!empty($settings['controller']) && class_exists($settings['controller'], 'calendar')) {
      $controller = createClass($settings['controller']);
      // ตรวจสอบรายการที่เกี่ยวข้องจากฐานข้อมูล
      foreach ($controller->calendar($settings, $first_date, $first_next_month) as $item) {
        $cd = (int)date('d', $item['create_date']);
        $events[$cd]['id'][] = $item['id'];
        $events[$cd]['module'] = $item['module'];
      }
    }
    // แสดงปฏิทิน
    $calendar = array();
    $calendar[] = '<div class="calendar">';
    $calendar[] = '<div>';
    $calendar[] = '<p>';
    $calendar[] = '<a id="prev-'.$d.'">&lt;</a>';
    $calendar[] = '<span>'.$lng['MONTH_LONG'][$c_month].' '.($c_year + $lng['YEAR_OFFSET']).'</span>';
    $calendar[] = '<a id="next-'.$d.'">&gt;</a>';
    $calendar[] = '</p>';
    $calendar[] = '<table>';
    $calendar[] = '<thead><tr><th>'.implode('</th><th>', $lng['DATE_SHORT']).'</th></tr></thead>';
    $calendar[] = '<tbody>';
    $start = 1;
    $data = '<tr>';
    while ($start <= $weekday) {
      $data .= '<td class="ex">'.($days_of_last_month - $weekday + $start).'</td>';
      $start++;
    }
    $weekday++;
    while ($day <= $endday) {
      if (isset($events[$day])) {
        $e_day = $day.'-'.$c_month.'-'.$c_year;
        $href = isset($controller) ? $controller->url($e_day) : '';
        $_day = '<a href="'.$href.'" id="calendar-'.$e_day.'-'.implode('_', $events[$day]['id']).'">'.$day.'</a>';
      } else {
        $_day = $day;
      }
      if ($today == $day && $month == $c_month && $year == $c_year) {
        $c = 'today';
      } else {
        $c = 'curr';
      }
      $data .= '<td class="'.$c.'">'.$_day.'</td>';
      if ($weekday == 7 && $day != $endday) {
        $calendar[] = $data.'</tr>';
        $data = '<tr>';
        $weekday = 0;
      }
      $day++;
      $weekday++;
    }
    $n = 1;
    while ($weekday <= 7) {
      $data .= '<td class="ex">'.$n.'</td>';
      $weekday++;
      $n++;
    }
    $calendar[] = $data.'</tr>';
    $calendar[] = '</tbody>';
    $calendar[] = '<tfoot>';
    $year += $lng['YEAR_OFFSET'];
    $calendar[] = '<tr><td colspan="7"><a id="today-'.$today.'-'.$month.'-'.$year.'">'.$today.' '.$lng['MONTH_LONG'][$month].' '.$year.'</a></td></tr>';
    $calendar[] = '</tfoot>';
    $calendar[] = '</table>';
    $calendar[] = '</div>';
    $calendar[] = '</div>';
    return implode('', $calendar);
  }
}