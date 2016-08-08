<?php
/*
 * @filesource Widgets/Video/Controllers/Index.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Widgets\Video\Controllers;

use \Kotchasan\Text;
use \Kotchasan\Grid;

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
    if (preg_match('/^([0-9]+)_([0-9]+)$/', $query_string['module'], $match)) {
      $cols = max(1, (int)$match[1]);
      $count = max(1, (int)$match[2]);
      $videos = \Widgets\Video\Models\Index::get(0, $count * $cols);
    } elseif (preg_match('/[a-zA-Z0-9\-_]{11,11}/', $query_string['module'])) {
      $cols = 1;
      $count = 1;
      $videos = array(
        array(
          'id' => 0,
          'topic' => '',
          'youtube' => $query_string['module']
        )
      );
    } elseif (preg_match('/[0-9]+/', $query_string['module'])) {
      $cols = 1;
      $count = 1;
      $videos = \Widgets\Video\Models\Index::get((int)$query_string['module'], 1);
    } else {
      $cols = 2;
      $count = 2;
      $videos = \Widgets\Video\Models\Index::get(0, $count);
    }
    return \Widgets\Video\Views\Index::render($cols, $count, $videos);
    if ($cols == 1 && $count == 1) {
      return '<div class="youtube"><iframe src="//www.youtube.com/embed/'.$videos[0]['youtube'].'?wmode=transparent"></iframe></div>';
    } else {
      $a = Text::rndname(10);
      $widget = array('<div id="'.$a.'" class="document-list video">');
      // รายการ
      $listitem = Grid::create('video', 'video', 'listitem');
      $listitem->setCols($cols);
      foreach ($videos as $item) {
        $listitem->add(array(
          '/{ID}/' => $item['id'],
          '/{TOPIC}/' => '',
          '/{PICTURE}/' => is_file(ROOT_PATH.DATA_FOLDER.'video/'.$item['youtube'].'.jpg') ? WEB_URL.DATA_FOLDER.'video/'.$item['youtube'].'.jpg' : WEB_URL.'modules/video/img/nopicture.jpg',
          '/{YOUTUBE}/' => $item['youtube'],
          '/{COLS}/' => $cols
        ));
      }
      $widget[] = $listitem->render();
      $widget[] = '</div>';
      $widget[] = '<script>initVideoList("'.$a.'");</script>';
      return implode('', $widget);
    }
  }
}