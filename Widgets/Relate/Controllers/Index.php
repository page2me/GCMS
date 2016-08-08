<?php
/*
 * @filesource Widgets/Relate/Controllers/Index.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Widgets\Relate\Controllers;

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
    if (!empty($query_string['id']) && !empty($query_string['module']) && isset(Gcms::$install_modules[$query_string['module']])) {
      // module
      $index = Gcms::$install_modules[$query_string['module']];
      $cols = isset($query_string['cols']) ? (int)$query_string['cols'] : 1;
      $rows = isset($query_string['rows']) ? (int)$query_string['rows'] : 1;
      if ($cols > 0 && $rows > 0) {
        $style = isset($query_string['style']) && in_array($query_string['style'], array('list', 'icon', 'thumb')) ? $query_string['style'] : 'list';
        // template
        $template = Template::create('document', $index->module, 'relate');
        $template->add(array(
          '/{DETAIL}/' => '<script>getWidgetNews("{ID}", "Relate", 0)</script>',
          '/{ID}/' => (int)$query_string['id'].'_'.$cols.'_'.$rows.'_0_'.$style,
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
    // id_cols_rows_sort_style
    if ($request->isReferer() && preg_match('/^([0-9]+)_([0-9]+)_([0-9]+)_([0-9]+)_(list|icon|thumb)$/', $request->get('id')->toString(), $match)) {
      // query
      $index = \Widgets\Relate\Models\Index::get((int)$match[1], (int)$match[3], (int)$match[2]);
      if ($index) {
        echo \Widgets\Relate\Views\Index::render($index);
      }
    }
  }
}