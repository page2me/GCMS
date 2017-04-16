<?php
/**
 * @filesource Widgets/Video/Views/Index.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Widgets\Video\Views;

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
   * แสดงผล Video
   *
   * @param int $cols
   * @param int $count
   * @param array $videos
   * @return string
   */
  public static function render($cols, $count, $videos)
  {
    if ($cols == 1 && $cols == 1) {
      return '<div class="youtube"><iframe src="//www.youtube.com/embed/'.$videos[0]['youtube'].'?wmode=transparent"></iframe></div>';
    } else {
      $a = Text::rndname(10);
      $widget = array('<div id="'.$a.'" class="document-list video">');
      // รายการ
      $listitem = Grid::create('video', 'video', 'widgetitem');
      $listitem->setCols($cols);
      foreach ($videos as $item) {
        $listitem->add(array(
          '/{ID}/' => $item['id'],
          '/{TOPIC}/' => $item['topic'],
          '/{PICTURE}/' => is_file(ROOT_PATH.DATA_FOLDER.'video/'.$item['youtube'].'.jpg') ? WEB_URL.DATA_FOLDER.'video/'.$item['youtube'].'.jpg' : WEB_URL.'modules/video/img/nopicture.jpg',
          '/{YOUTUBE}/' => $item['youtube']
        ));
      }
      $widget[] = str_replace('{COLS}', $cols, $listitem->render());
      $widget[] = '</div>';
      $widget[] = '<script>initVideoList("'.$a.'");</script>';
      return implode('', $widget);
    }
  }
}