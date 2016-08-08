<?php
/*
 * @filesource Widgets/Board/Controllers/Index.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Widgets\Board\Controllers;

use \Kotchasan\Http\Request;
use \Kotchasan\Template;
use \Gcms\Gcms;
use \Board\Index\Controller;
use \Kotchasan\Grid;
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
    if (preg_match('/^[a-z0-9]{4,}$/', $query_string['module']) && isset(Gcms::$install_modules[$query_string['module']])) {
      // module
      $index = Gcms::$install_modules[$query_string['module']];
      // ค่าที่ส่งมา
      $cat = isset($query_string['cat']) ? $query_string['cat'] : 0;
      $interval = isset($query_string['interval']) ? (int)$query_string['interval'] : 0;
      $count = isset($query_string['count']) ? (int)$query_string['count'] : $index->news_count;
      if ($count > 0) {
        // template
        $template = Template::create('board', $index->module, 'widget');
        $template->add(array(
          '/{DETAIL}/' => '<script>getWidgetNews("{ID}", "Board", '.$interval.')</script>',
          '/{ID}/' => $index->module_id.'_'.$cat.'_'.$count.'_'.$index->new_date,
          '/{MODULE}/' => $index->module
        ));
        return $template->render();
      }
    }
  }

  /**
   * อ่านข้อมูลจาก Ajax
   *
   * @param Request $request
   * @return string
   */
  public function getWidgetNews(Request $request)
  {
    if ($request->isReferer() && preg_match('/^([0-9]+)_([0-9,]+)_([0-9]+)_([0-9]+)$/', $request->post('id')->toString(), $match)) {
      // ตรวจสอบโมดูล
      $index = \Index\Module\Model::get('board', null, $match[1]);
      // รายการ
      $listitem = Grid::create('board', $index->module, 'widgetitem');
      // เครื่องหมาย new
      $valid_date = time() - (int)$match[4];
      // query ข้อมูล
      $bg = 'bg2';
      foreach (\Widgets\Board\Models\Index::get($index->module_id, $match[2], $match[3]) as $item) {
        $bg = $bg == 'bg1' ? 'bg2' : 'bg1';
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
        $listitem->add(array(
          '/{BG}/' => $bg,
          '/{URL}/' => Controller::url($index->module, 0, $item->id),
          '/{TOPIC}/' => $item->topic,
          '/{DATE}/' => Date::format($item->create_date, 'd M Y'),
          '/{UID}/' => $item->member_id,
          '/{SENDER}/' => $item->displayname,
          '/{STATUS}/' => $item->status,
          '/{PICTURE}/' => $thumb,
          '/{ICON}/' => $icon
        ));
      }
      echo createClass('Kotchasan\View')->renderHTML($listitem->render());
    }
  }
}