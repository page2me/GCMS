<?php
/*
 * @filesource index/models/report.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Report;

use \Kotchasan\Text;

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
          if (preg_match_all('%(?P<browser>Camino|Kindle(\ Fire)?|Firefox|Iceweasel|Safari|MSIE|Trident|AppleWebKit|TizenBrowser|Chrome|
				Vivaldi|IEMobile|Opera|OPR|Silk|Midori|Edge|CriOS|
				Baiduspider|Googlebot|YandexBot|bingbot|Lynx|Version|Wget|curl|MJ12bot|DotBot|
				Valve\ Steam\ Tenfoot|
				NintendoBrowser|PLAYSTATION\ (\d|Vita)+)
				(?:\)?;?)
				(?:(?:[:/ ])(?P<version>[0-9._A-Z]+)|/(?:[A-Z]*))%ix', $sagent, $result, PREG_PATTERN_ORDER)) {
            $sagent = '<span title="'.$sagent.'">'.$result['browser'][0].(empty($result['version'][0]) ? '' : '/'.$result['version'][0]).'</span>';
          } elseif (preg_match('%^(?!Mozilla)(?P<browser>[A-Z0-9\-]+)(/(?P<version>v?[0-9._A-Z]+))?%ix', $sagent, $result)) {
            $sagent = '<span title="'.$sagent.'">'.$result['browser'].(empty($result['version']) ? '' : '/'.$result['version']).'</span>';
          } elseif ($sagent != '') {
            $sagent = '<span title="'.$sagent.'">unknown</span>';
          }
          $datas[$sip.$sref] = array(
            'time' => isset($datas[$sip.$sref]) ? $datas[$sip.$sref]['time'] : $time,
            'count' => isset($datas[$sip.$sref]) ? $datas[$sip.$sref]['count'] + 1 : 1,
            'ip' => '<a href="http://'.$sip.'" target=_blank>'.$sip.'</a>',
            'agent' => $sagent,
            'referer' => '',
          );
          if (preg_match('/^(https?.*(www\.)?google(usercontent)?.*)\/.*[\&\?]q=(.*)($|\&.*)/iU', $sref, $match)) {
            // จาก google search
            $title = rawurldecode(rawurldecode($match[4]));
          } elseif (preg_match('/^(https?:\/\/(www.)?google[\.a-z]+\/url\?).*&url=(.*)($|\&.*)/iU', $sref, $match)) {
            // จาก google cached
            $title = rawurldecode(rawurldecode($match[3]));
          } elseif ($sref != '') {
            // ลิงค์ภายในไซต์
            $title = rawurldecode(rawurldecode($sref));
          }
          if ($sref != '') {
            $datas[$sip.$sref]['referer'] = '<a href="'.$sref.'" title="'.$title.'" target=_blank>'.Text::cut($title, 149).'</a>';
          }
        }
      }
    }
    return $datas;
  }
}