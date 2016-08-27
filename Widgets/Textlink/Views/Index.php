<?php
/*
 * @filesource Widgets/Textlink/Views/Index.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Widgets\Textlink\Views;

/**
 * โมดูลสำหรับจัดการการตั้งค่าเริ่มต้น
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Index extends \Kotchasan\View
{

  /**
   * แสดงแบนเนอร์ทีละรูป หมุนวน
   *
   * @param array $items รายการ textlinks
   * @return string
   */
  public static function banner($items)
  {
    // template
    $styles = include (ROOT_PATH.'Widgets/Textlink/styles.php');
    $patt = array('/{TITLE}/', '/{DESCRIPTION}/', '/{LOGO}/', '/{URL}/', '/{TARGET}/');
    // เรียงลำดับตาม last_preview
    $items = \Kotchasan\ArrayTool::sort($items, 'last_preview');
    // ใช้แบนเนอร์รายการแรก
    $banner = reset($items);
    // อัปเดทรายการว่าแสดงผลแล้ว
    \Widgets\Textlink\Models\Index::previewUpdate($banner['id']);
    // แสดงผล
    $replace = array();
    $replace[] = $banner['text'];
    $replace[] = $banner['description'];
    $replace[] = WEB_URL.DATA_FOLDER.'image/'.$banner['logo'];
    $replace[] = empty($banner['url']) ? '' : ' href="'.$banner['url'].'"';
    $replace[] = $banner['target'] == '_blank' ? ' target=_blank' : '';
    return '<div class="widget_textlink '.$banner['name'].'">'.preg_replace($patt, $replace, $styles['banner']).'</div>';
  }

  /**
   * แสดงแบนเนอร์ทีละรูป หมุนวน
   *
   * @param array $items รายการ textlinks
   * @return string
   */
  public static function custom($items)
  {
    // แสดงผล
    $textlinks = array();
    foreach ($items as $banner) {
      $textlinks[] = $banner['template'];
    }
    return '<div class="widget_textlink '.$banner['name'].'">'.implode('', $textlinks).'</div>';
  }

  /**
   * แสดงแบนเนอร์ทีละรูป หมุนวน
   *
   * @param array $items รายการ textlinks
   * @return string
   */
  public static function slideshow($items)
  {
    $textlinks = array();
    foreach ($items as $item) {
      $row = '<figure>';
      $row .= '<img class=nozoom src="'.WEB_URL.DATA_FOLDER.'image/'.$item['logo'].'" alt="'.$item['text'].'">';
      $row .= '<figcaption><a'.(empty($item['url']) ? '' : ' href="'.$item['url'].'"').($item['target'] == '_blank' ? ' target=_blank' : '').' title="'.$item['text'].'">';
      $row .= $item['text'] == '' ? '' : '<span>'.$item['text'].'</span>';
      $row .= '</a></figcaption>';
      $row .= '</figure>';
      $textlinks[] = $row;
    }
    // แสดงผล
    $id = 'textlinks_slideshow_'.$item['name'];
    return '<div id='.$id.'>'.implode("\n", $textlinks).'</div><script>new gBanner("'.$id.'").playSlideShow();</script>';
  }

  /**
   * แสดงแบนเนอร์ทีละรูป หมุนวน
   *
   * @param array $items รายการ textlinks
   * @return string
   */
  public static function template($items)
  {
    // template
    $styles = include (ROOT_PATH.'Widgets/Textlink/styles.php');
    $patt = array('/{TITLE}/', '/{DESCRIPTION}/', '/{LOGO}/', '/{URL}/', '/{TARGET}/');
    $item = reset($items);
    if (isset($styles[$item['type']])) {
      $template = $styles[$item['type']];
      // แสดงผล
      $textlinks = array();
      foreach ($items as $banner) {
        $replace = array();
        $replace[] = $banner['text'];
        $replace[] = $banner['description'];
        $replace[] = WEB_URL.DATA_FOLDER.'image/'.$banner['logo'];
        $replace[] = empty($banner['url']) ? '' : ' href="'.$banner['url'].'"';
        $replace[] = $banner['target'] == '_blank' ? ' target=_blank' : '';
        $textlinks[] = preg_replace($patt, $replace, $template);
      }
      return implode('', $textlinks);
    }
  }
}