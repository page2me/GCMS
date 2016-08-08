<?php
/*
 * @filesource Widgets/Document/Controllers/Index.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Widgets\Document\Controllers;

use \Kotchasan\Http\Request;
use \Kotchasan\Template;
use \Gcms\Gcms;
use \Document\Index\Controller;
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
    if (preg_match('/^[a-z0-9]{3,}$/', $query_string['module']) && isset(Gcms::$install_modules[$query_string['module']])) {
      // module
      $index = Gcms::$install_modules[$query_string['module']];
      // ค่าที่ส่งมา
      $cat = isset($query_string['cat']) ? $query_string['cat'] : 0;
      $interval = isset($query_string['interval']) ? (int)$query_string['interval'] : 0;
      $cols = isset($query_string['cols']) ? (int)$query_string['cols'] : 1;
      $rows = isset($query_string['rows']) ? (int)$query_string['rows'] : 0;
      $show = isset($query_string['show']) && preg_match('/^[a-z0-9]+$/', $query_string['show']) ? $query_string['show'] : '';
      if ($rows > 0) {
        $count = $rows * $cols;
      } else {
        $count = isset($query_string['count']) ? (int)$query_string['count'] : $index->news_count;
      }
      if ($count > 0) {
        $sort = isset($query_string['sort']) ? (int)$query_string['sort'] : $index->news_sort;
        $style = isset($query_string['style']) && in_array($query_string['style'], array('list', 'icon', 'thumb')) ? $query_string['style'] : 'list';
        // template
        $template = Template::create('document', $index->module, 'widget');
        $template->add(array(
          '/{DETAIL}/' => '<script>getWidgetNews("{ID}", "Document", '.$interval.')</script>',
          '/{ID}/' => $index->module_id.'_'.$cat.'_'.$count.'_'.$index->new_date.'_'.$sort.'_'.$cols.'_'.$style.'_'.$show,
          '/{MODULE}/' => $index->module,
          '/{STYLE}/' => $style.'view'
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
    if ($request->isReferer() && preg_match('/^([0-9]+)_([0-9,]+)_([0-9]+)_([0-9]+)_([0-9]+)_([0-9]+)_(list|icon|thumb)_([a-z0-9]+)?$/', $request->post('id')->toString(), $match)) {
      // ตรวจสอบโมดูล
      $index = \Index\Module\Model::get('document', null, $match[1]);
      // รายการ
      $listitem = Grid::create('document', $index->module, 'widgetitem');
      $listitem->setCols($match[7]);
      // เครื่องหมาย new
      $valid_date = time() - (int)$match[4];
      // query ข้อมูล
      $bg = 'bg2';
      foreach (\Widgets\Document\Models\Index::get($index->module_id, $match[2], (isset($match[8]) ? $match[8] : ''), $match[5], $match[3]) as $item) {
        $bg = $bg == 'bg1' ? 'bg2' : 'bg1';
        if (!empty($item->picture) && is_file(ROOT_PATH.DATA_FOLDER.'document/'.$item->picture)) {
          $thumb = WEB_URL.DATA_FOLDER.'document/'.$item->picture;
        } elseif (!empty($index->icon) && is_file(ROOT_PATH.DATA_FOLDER.'document/'.$index->icon)) {
          $thumb = WEB_URL.DATA_FOLDER.'document/'.$index->icon;
        } else {
          $thumb = WEB_URL.(isset($index->default_icon) ? $index->default_icon : 'modules/document/img/document-icon.png');
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
          '/{URL}/' => Controller::url($index->module, $item->alias, $item->id),
          '/{TOPIC}/' => $item->topic,
          '/{DETAIL}/' => $item->description,
          '/{CATEGORY}/' => $item->category,
          '/{DATE}/' => Date::format($item->create_date, 'd M Y'),
          '/{UID}/' => $item->member_id,
          '/{SENDER}/' => $item->sender,
          '/{STATUS}/' => $item->status,
          '/{COMMENTS}/' => number_format($item->comments),
          '/{VISITED}/' => number_format($item->visited),
          '/{PICTURE}/' => $thumb,
          '/{ICON}/' => $icon,
          '/{COLS}/' => $match[6]
        ));
      }
      echo createClass('Kotchasan\View')->renderHTML($listitem->render());
    }
  }
}