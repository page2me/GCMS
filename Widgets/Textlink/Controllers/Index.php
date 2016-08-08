<?php
/*
 * @filesource Widgets/Textlink/Controllers/Index.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Widgets\Textlink\Controllers;

use \Kotchasan\Date;

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
    if (defined('MAIN_INIT') && preg_match('/[a-z0-9]{1,11}/', $query_string['module'])) {
      // template
      $styles = include (ROOT_PATH.'Widgets/Textlink/styles.php');
      $patt = array('/{TITLE}/', '/{DESCRIPTION}/', '/{LOGO}/', '/{URL}/', '/{TARGET}/');
      // อ่านข้อมูล
      $textlinks = array();
      $type = '';
      $banner = array('last_preview' => time());
      foreach (\Widgets\Textlink\Models\Index::get(Date::month(), Date::day(), Date::year()) AS $item) {
        if ($item['name'] == $query_string['module']) {
          $type = $type == '' ? $item['type'] : $type;
          if ($item['type'] == 'banner') {
            // แสดงแบนเนอร์เพียงอันเดียว
            if ($item['last_preview'] < $banner['last_preview']) {
              $banner = $item;
            }
          } else {
            if ($item['type'] == 'custom') {
              $textlinks[] = $item['template'];
            } elseif ($item['type'] == 'slideshow') {
              $row = '<figure>';
              $row .= '<img class=nozoom src="'.WEB_URL.DATA_FOLDER.'image/'.$item['logo'].'" alt="'.$item['text'].'">';
              $row .= '<figcaption><a'.(empty($item['url']) ? '' : ' href="'.$item['url'].'"').($item['target'] == '_blank' ? ' target=_blank' : '').' title="'.$item['text'].'">';
              $row .= $item['text'] == '' ? '' : '<span>'.$item['text'].'</span>';
              $row .= '</a></figcaption>';
              $row .= '</figure>';
              $textlinks[] = $row;
            } else {
              $replace = array();
              $replace[] = $item['text'];
              $replace[] = $item['description'];
              $replace[] = WEB_URL.DATA_FOLDER.'image/'.$item['logo'];
              $replace[] = $item['url'] == '' ? '' : ' href="'.$item['url'].'"';
              $replace[] = $item['target'] == '_blank' ? ' target=_blank' : '';
              $textlinks[] = preg_replace($patt, $replace, $styles[$item['type']]);
            }
          }
        }
      }
      if (in_array($type, array('custom', 'menu'))) {
        return implode("\n", $textlinks);
      } elseif ($type == 'slideshow') {
        $id = 'textlinks_slideshow_'.$query_string['module'];
        $widget = array();
        $widget[] = '<div id='.$id.'>';
        $widget[] = implode("\n", $textlinks);
        $widget[] = '</div>';
        $widget[] = '<script>';
        $widget[] = 'new gBanner("'.$id.'").playSlideShow();';
        $widget[] = '</script>';
        return implode("\n", $widget);
      } elseif ($type == 'banner') {
        // แสดงแบนเนอร์เพียงอันเดียว
        $replace = array();
        $replace[] = $banner['text'];
        $replace[] = $banner['description'];
        $replace[] = WEB_URL.DATA_FOLDER.'image/'.$banner['logo'];
        $replace[] = empty($banner['url']) ? '' : ' href="'.$banner['url'].'"';
        $replace[] = $banner['target'] == '_blank' ? ' target=_blank' : '';
        $textlinks[] = preg_replace($patt, $replace, $styles['banner']);
        // อัปเดทรายการว่าแสดงผลแล้ว
        \Widgets\Textlink\Models\Index::previewUpdate($banner['id']);
        return '<div class="widget_textlink '.$query_string['module'].'">'.implode('', $textlinks).'</div>';
      } else {
        return '<div class="widget_textlink '.$query_string['module'].'">'.implode('', $textlinks).'</div>';
      }
    }
  }
}