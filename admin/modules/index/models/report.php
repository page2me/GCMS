<?php
/*
 * @filesource index/models/report.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Report;

/**
 * อ่านข้อมูลการเยี่ยมชมในวันที่เลือก
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\KBase
{

  /**
   * อ่านข้อมูลการเยี่ยมชมในวันที่เลือก
   *
   * @param string $date
   * @return array
   */
  public static function get($date)
  {
    $datas = array();
    if (preg_match('/^([0-9]+)\-([0-9]+)\-([0-9]+)$/', $date, $match)) {
      $y = $match[1];
      $m = $match[2];
      $d = $match[3];
      $counter_dat = ROOT_PATH.DATA_FOLDER.'counter/'.(int)$y.'/'.(int)$m.'/'.(int)$d.'.dat';
      if (is_file($counter_dat)) {
        foreach (file($counter_dat) AS $a => $item) {
          list($sid, $sip, $sref, $sagent, $time) = explode(chr(1), $item);
          $datas[$sip.$sref] = array(
            'time' => isset($datas[$sip.$sref]) ? $datas[$sip.$sref]['time'] : $time,
            'ip' => '<a href="http://'.$sip.'" target=_blank>'.$sip.'</a>',
            'count' => isset($datas[$sip.$sref]) ? $datas[$sip.$sref]['count'] + 1 : 1,
            'referer' => '',
            'agent' => $sagent
          );
          if (preg_match('/^(https?.*(www\.)?google(usercontent)?.*)\/.*[\&\?]q=(.*)($|\&.*)/iU', $sref, $match)) {
            // จาก google search
            $datas[$sip.$sref]['referer'] = '<a href="'.$sref.'" target=_blank>'.rawurldecode(rawurldecode($match[4])).'</a>';
          } elseif (preg_match('/^(https?:\/\/(www.)?google[\.a-z]+\/url\?).*&url=(.*)($|\&.*)/iU', $sref, $match)) {
            // จาก google cached
            $datas[$sip.$sref]['referer'] = '<a href="'.$sref.'" target=_blank>'.rawurldecode(rawurldecode($match[3])).'</a>';
          } elseif ($sref != '') {
            // ลิงค์ภายในไซต์
            $datas[$sip.$sref]['referer'] = '<a href="'.$sref.'" target=_blank>'.rawurldecode(rawurldecode($sref)).'</a>';
          }
        }
      }
    }
    return $datas;
  }
}