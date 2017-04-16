<?php
/**
 * @filesource Widgets/Download/Views/Lists.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Widgets\Download\Views;

use \Kotchasan\Text;
use \Kotchasan\Grid;
use \Kotchasan\Date;

/**
 * Controller หลัก สำหรับแสดงผล Widget
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Lists extends \Gcms\View
{

  /**
   * List รายการไฟล์ดาวน์โหลด
   *
   * @param array $query_string ข้อมูลที่ส่งมาจากการเรียก Widget
   * @return string
   */
  public static function render($index, $query_string)
  {
    $id = Text::rndname(10);
    // รายการ
    $listitem = Grid::create('download', $index->module, 'widgetitem');
    // query ข้อมูล
    $bg = 'bg2';
    foreach (\Widgets\Download\Models\Lists::get($index->module_id, $query_string['cat'], $query_string['count']) as $item) {
      $bg = $bg == 'bg1' ? 'bg2' : 'bg1';
      $listitem->add(array(
        '/{BG}/' => $bg,
        '/{ID}/' => $item->id,
        '/{NAME}/' => $item->name,
        '/{EXT}/' => $item->ext,
        '/{ICON}/' => WEB_URL.'skin/ext/'.(is_file(ROOT_PATH.'skin/ext/'.$item->ext.'.png') ? $item->ext : 'file').'.png',
        '/{DETAIL}/' => $item->detail,
        '/{DATE}/' => Date::format($item->last_update),
        '/{DATEISO}/' => date(DATE_ISO8601, $item->last_update),
        '/{DOWNLOADS}/' => number_format($item->downloads),
        '/{SIZE}/' => Text::formatFileSize($item->size)
      ));
    }
    $content = '<div id="'.$id.'" class="document-list download"><div class="row listview">';
    $content .= createClass('Gcms\View')->renderHTML($listitem->render());
    $content .= '</div></div>';
    $content .= '<script>initDownloadList("'.$id.'");</script>';
    return $content;
  }
}