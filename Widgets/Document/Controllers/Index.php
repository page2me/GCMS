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
use \Kotchasan\Text;
use \Kotchasan\Grid;
use \Widgets\Document\Views\Index as View;

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
      $cols = isset($query_string['cols']) ? (int)$query_string['cols'] : 1;
      if (isset($query_string['count'])) {
        $rows = ceil($query_string['count'] / $cols);
      } elseif (isset($query_string['rows'])) {
        $rows = (int)$query_string['rows'];
      }
      if (empty($rows)) {
        $rows = ceil((int)$index->news_count / $cols);
      }
      if ($rows > 0 && $cols > 0) {
        $cat = isset($query_string['cat']) ? $query_string['cat'] : 0;
        $interval = isset($query_string['interval']) ? (int)$query_string['interval'] : 0;
        $sort = isset($query_string['sort']) ? (int)$query_string['sort'] : $index->news_sort;
        $show = isset($query_string['show']) && preg_match('/^[a-z0-9]+$/', $query_string['show']) ? $query_string['show'] : '';
        $style = isset($query_string['style']) && in_array($query_string['style'], array('list', 'icon', 'thumb')) ? $query_string['style'] : 'list';
        // template
        $template = Template::create('document', $index->module, 'widget');
        $template->add(array(
          '/{DETAIL}/' => '<script>getWidgetNews("{ID}", "Document", '.$interval.')</script>',
          // module_id_cat_rows_cols_sort_show
          '/{ID}/' => Text::rndname(10).'_'.$index->module_id.'_'.$cat.'_'.$rows.'_'.$cols.'_'.$sort.'_'.$show,
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
    // module_id_cat_rows_cols_sort_show
    if ($request->isReferer() && preg_match('/^([a-z]{10,10})_([0-9]+)_([0-9,]{0,})_([0-9]+)_([0-9]+)_([0-9]+)_([a-z]{0,})$/', $request->post('id')->toString(), $match)) {
      $rows = (int)$match[4];
      $cols = (int)$match[5];
      // ตรวจสอบโมดูล
      $index = \Index\Module\Model::get('document', null, $match[2]);
      if ($index) {
        // รายการ
        $listitem = Grid::create('document', $index->module, 'widgetitem');
        $listitem->setCols($cols);
        // เครื่องหมาย new
        $valid_date = time() - (int)$index->new_date;
        // query ข้อมูล
        foreach (\Widgets\Document\Models\Index::get($index->module_id, $match[3], $match[7], $match[6], $rows * $cols) as $item) {
          $listitem->add(View::renderItem($index, $item, $valid_date, $cols));
        }
        echo createClass('Gcms\View')->renderHTML($listitem->render());
      }
    }
  }
}