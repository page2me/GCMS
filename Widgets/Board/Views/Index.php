<?php
/*
 * @filesource Widgets/Board/Views/Index.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Widgets\Board\Views;

use \Board\Index\Controller;
use \Kotchasan\Date;

/**
 * Controller หลัก สำหรับแสดงผล Widget
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Index extends \Kotchasan\View
{

  /**
   * แสดงผลรายการ
   *
   * @param object $index
   * @param object $item
   * @param int $valid_date
   * @return array
   */
  public static function renderItem($index, $item, $valid_date)
  {
    if ($item->picture != '' && is_file(ROOT_PATH.DATA_FOLDER.'board/thumb-'.$item->picture)) {
      $thumb = WEB_URL.DATA_FOLDER.'board/thumb-'.$item->picture;
    } else {
      $thumb = WEB_URL.(isset($index->default_icon) ? $index->default_icon : 'modules/board/img/board-icon.png');
    }
    if ($item->create_date > $valid_date && $item->comment_date == 0) {
      $icon = 'new';
    } elseif ($item->last_update > $valid_date || $item->comment_date > $valid_date) {
      $icon = 'update';
    } else {
      $icon = '';
    }
    return array(
      '/{URL}/' => Controller::url($index->module, 0, $item->id),
      '/{TOPIC}/' => $item->topic,
      '/{DATE}/' => Date::format($item->create_date, 'd M Y'),
      '/{UID}/' => $item->member_id,
      '/{SENDER}/' => $item->displayname,
      '/{STATUS}/' => $item->status,
      '/{PICTURE}/' => $thumb,
      '/{ICON}/' => $icon
    );
  }
}